# Direct Payments





### Payment Method + Payment Intent:

One click one charge .. and optionally payment method can be saved for later usage


Tip: if you are trying to reuse one of the payment methods.. and card require 3D secure.. then it will ask for 3D secure everytime.. to avoid 3D secure prompt you may need to use Setup Intent

Tip: for guest checkout.. you can do the same flow.. just skip the part where you link payment method with user

Tip: you can do the below flow but little in reverse, you can create payment intent with php `$paymentIntent = Auth::user()->pay($amount)` before rendering the checkout form then use the intent secret key to create payment method with javascript which would finalize transaction.. notice payment intent will create a placeholder transaction in stripe dashboard. and if user didnt put his card data the placeholder will stay there as incomplete transaction

Tip: payment method typically is card data

```html

@if(Auth::user()->hasPaymentMethod())
    <form action="{{route('payment_method.oneClick')}}" method="POST">
        @csrf
            Payment Methods
            @foreach(Auth::user()->paymentMethods() as $method)
            <div>
                <input type="radio" name="payment_method_id" value="{{$method->id}}"> {{$method->card->last4}}
            </div>
            @endforeach
            {{-- this button will take payment_method_id and use it to charge customer --}}
            <button class="btn btn-primary">One Click Payment</button>
            <hr class="my-5">
        </form>
    @endif
    <form action="{{route('payment_method.submit')}}" method="POST" id='payment_method_submit_form'>
        @csrf

        Pay With Your Card

        <!-- Stripe Elements Placeholder -->
        <div id="card-element"></div>

        <input type="hidden" name="payment_method_id" id='payment_method_id'>
        
        <button id="card-button" type="button" class="btn">
            Process Payment
        </button>
    </form>

<script src="https://js.stripe.com/v3/"></script>
 
<script src="https://js.stripe.com/v3/"></script>
 
    <script>
        const stripe = Stripe(@json(config('cashier.key')));
    
        const elements = stripe.elements();
        const cardElement = elements.create('card');
    
        cardElement.mount('#card-element');
    </script>

    <script>
        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        
        cardButton.addEventListener('click', async (e) => {
            const { paymentMethod, error } = await stripe.createPaymentMethod(
                'card', cardElement
            );
        
            if (error) {
                // Display "error.message" to the user...
            } else {
                // The card has been verified successfully...
                document.getElementById('payment_method_id').value = paymentMethod.id;
                document.getElementById('payment_method_submit_form').submit();
            }
        });
    </script>
</x-app-layout>
```


```php

public function submit(){
    if(!request('payment_method_id')){
        return;
    }

    Auth::user()->createOrGetStripeCustomer();

    if(!$this->isPaymentMethodExistsWithUser(request('payment_method_id'))){
        Auth::user()->addPaymentMethod(request('payment_method_id'));
        Auth::user()->updateDefaultPaymentMethod(request('payment_method_id'));
    }
    
    $cart = Cart::session()->first();

    $amount = $cart->courses->sum('price');

    //this card requires 3d authentication 4000 0027 6000 3184
    $payment = Auth::user()->charge($amount,request('payment_method_id'),[
        'return_url'=>route('home',['message'=>'payment successfull'])
    ]);

    if($payment->status == 'succeeded'){
        //create order

        return redirect()->route('home',['message'=>'Payment Successful']);
    }
}

protected function isPaymentMethodExistsWithUser($payment_method_id){
    $method = Cashier::stripe()->paymentMethods->retrieve($payment_method_id);
    $last4 = $method->card->last4;

    if(Auth::user()->hasPaymentMethod()){
        foreach(Auth::user()->paymentMethods() as $method){
            $l4 = $method->card->last4;

            if($last4 == $l4){
                return true;
            }
        }
    }

    return false;
}

public function oneClick(){
    if(!request('payment_method_id')){
        return;
    }

    $cart = Cart::session()->first();

    $amount = $cart->courses->sum('price');

    try {
        $payment = Auth::user()->charge($amount, request('payment_method_id'), [
            'return_url' => route('payment_method.confirm'),
        ]);

        // If payment succeeded immediately
        if ($payment->status === 'succeeded') {
            return redirect()->route('payment_method.confirm', ['payment_intent' => $payment->id]);
        }

        // Fallback (shouldn't happen often)
        return redirect()->route('home')->with('message', 'Payment created but not completed');

    } catch (IncompletePayment $exception) {
        // Redirect user to Stripe 3D Secure page
        return redirect()->route(
            'cashier.payment',
            [$exception->payment->id, 'redirect' => route('payment_method.confirm', ['payment_intent' => $exception->payment->id])]
        );
    }
}

public function confirm()
{
    $paymentIntentId = request('payment_intent');

    if (! $paymentIntentId) {
        return redirect()->route('home')->with('message', 'Missing payment intent');
    }

    // Fetch the payment intent from Stripe
    $paymentIntent = Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);

    // Check if succeeded
    if ($paymentIntent->status === 'succeeded') {
        // Get payment method details
        $paymentMethodId = $paymentIntent->payment_method;

        $paymentMethod = Cashier::stripe()->paymentMethods->retrieve($paymentMethodId);

        $cardLast4 = $paymentMethod->card->last4 ?? 'unknown';
        $cardBrand = $paymentMethod->card->brand ?? 'unknown';

        // ✅ Now you can create your order
        // Order::create([...]);

        return redirect()->route('home')->with('message', "Payment successful using $cardBrand ending in $cardLast4");
    }

    return redirect()->route('home')->with('message', 'Payment failed or still pending');
}


```