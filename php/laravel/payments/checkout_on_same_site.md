# Direct Payments


### Payment Intent

Payment Intent object is critical when initializing payment. 

transitional statuses
- requires_payment_method .. Newly created PaymentIntent (no method yet).
- requires_action .. still needs 3D Secure challenge
- processing.. The payment has been submitted and is being processed by the payment network

final status (they will never change again)
- succeeded
- payment_failed
- canceled .. canceled by the developer or automatically by Stripe (e.g., because it expired or had too many failed attempts).


payment is done through
- normal flow (Typical)
    - create payment intent and associate it with cart
    - use intent client secret to create payment method which will finalize it
    - ui/ux will have a message to say payment successful etc..

- webhook (backup)
    - when payment intent succeed or fails.. it will send a request to your webhook 
    - this is useful if normal flow had issue like network drop

- cronjob
    - manually go through your carts and check if their payment intents which has transition status and check if they are succeeded or failed and act accordingly
    - if webhook is down on your side. cronjob will reconcile the intents against orders daily or something

Off-Session (Subscription renewal): 

- charging can happen while customer is on session which means user is here and interacting with your app (off_session: false)
- or The customer is not present — payment is attempted in the background. (off_session: true)

How Stripe behaves with off_session: true
- If the payment succeeds without authentication → ✅ PaymentIntent goes to succeeded.
- If the bank/card requires authentication (3D Secure, SCA, etc.) → Stripe will fail the off-session attempt and mark the PaymentIntent with status=requires_action. You should notify the customer (e.g., “we couldn’t charge your card, please update payment”) and then let them retry on-session.


Tip: By Payment Method we are refering to card data.. its a good practice to save payment methods in stripe for users to make it easy for our users to pay quickly without having to put their card data again.

Tip: you can create the intent and finalize it straight away on creation. typically used for off-session (background payment) .. at date of subscription renewal you would try below code..
```javascript
const paymentIntent = await stripe.paymentIntents.create({
  amount: 2000,
  currency: 'usd',
  customer: 'cus_123',
  payment_method: 'pm_123', // default saved card
  off_session: true,
  confirm: true, //it means charge it now, otherwise it will be created as requires_confirmation
});
```

```html

<div>
    Pay With Your Card
    
    <!-- Stripe Elements Placeholder -->
    <div id="card-element"></div>
    
    <button id="card-button" type="button" onclick="submitPayment({{$cart_id}})">
        Update Payment Method
    </button>
</div>


<script src="https://js.stripe.com/v3/"></script>
 
<script>
    const stripe = Stripe(@json(config('cashier.key')));

    const elements = stripe.elements();
    const cardElement = elements.create('card');

    cardElement.mount('#card-element');
</script>

<script>
window.submitPayment = async function(cart_id) {
    // Call backend to create PaymentIntent and attach it with cart
    const res = await fetch("{{route('payment_intent.submit')}}", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart_id: cart_id })
    });
    const { clientSecret } = await res.json();


    // Confirm the payment with card details
    // if there is 3d secure.. popup will show to complete 3d secure
    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
        payment_method: {card: cardElement}
    });

    if (error) {
        alert("Payment failed: " + error.message);
        onPaymentFailure(cart_id);
    } else if (paymentIntent.status === "succeeded") {
        alert("Payment succeeded!");
        onPaymentSuccess(cart_id);
    }
}

function onPaymentSuccess(cart_id){
    //call to backend
    //backend will get payment intent id from cart_id and check payment intent
    //if stripe payment intent is succeeded then it will be happy to continue
    //create the order and attach payment intent id with it
    //delete cart
    //create payment method against user, then we can use it again
}

function onPaymentFailure(cart_id){
    //call to backend to mark payment as failed
}
</script>

```

```php

class PaymentIntentController extends Controller
{
    public function index(){
        $cart = Cart::session()->first();
        return view('payment_intent.index',['cart_id'=>$cart->id]);
    }

    public function submit(){
        $cart = Cart::find(request('cart_id'));

        $intent = Auth::user()->pay($cart->courses->sum('price'),[
            'metadata' => [
                'cart_id' => $cart->id,
            ],
        ]);
        
        // Store ID for reconciliation later
        $cart->update(['payment_intent_id'=>$intent->id]);

        return response()->json([
            'clientSecret' => $intent->client_secret,
        ]);
    }
}

```