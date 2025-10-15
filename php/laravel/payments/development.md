# Laravel Payments

you can generate temp email to use it in testing at https://temp-mail.org/


## Laravel Cashier

Laravel Cashier is Laravel’s out-of-the-box solution for recurring payments and subscription billing with Stripe or Paddle. It wraps the payment provider SDK into expressive Laravel syntax.


gateways always deal with money as cents, for example 20 USD .. gateways api always will be 2000.. since its easier to work with integer more safer and quicker.. so its like a convention for payment gateways to work with integer rather than decimals


`php artisan make:model Cart -mc` .. create model + migration + controller


```php 

//format amount into money like 20.00 USD
Cashier::formatAmount(2000)

```



## Cart

- cart and cart content is stored in database (header and lines)
- cart header is based on session .. in carts table there is a column called session_id .. so every cart is associated with session
- cart header can be associated with user_id if possible as well.
- whern user login/logout/register.. session id is regenerated
- when this happens .. you should update cart with the new session_id then user dont lose his cart


Q: if i am going to create a cart for every session.. i will end up with carts table to be untouchable, so how developers commonly address this?

- Link carts to users as soon as possible then reuse user cart without creating new cards
- Run a scheduled job (cron) to remove or archive carts older than X days.
- Delay creating a cart record until the user actually adds the first item.
- For guest users, you can rely on redis to store carts and hit the database only when user login
- On success payment, then delete cart


Q: so what if guest added lines to his cart .. then he logged in while he was having cart already?
- Merge guest cart into user’s existing cart (most common)
- discard guest cart/or user cart
- Ask the user to choose guest cart or user cart (rare, but safest)


## Checkout

### Putting Products In Gateway and use them

- you can add products in the gateway, each product can have multiple prices. 
- save gateway price_id against your internal products 
- on checkout you ask gateway i want checkout these prices ids


```php
//this is using cashier package for stripe and billable trait inside user model
public function checkout(){
    $cart = Cart::where('session_id',session()->getId())->first();

    $prices = $cart->courses->pluck('stripe_price_id')->toArray();

    $sessionOptions = [
        'success_url' => route('checkout-success').'?session_id={CHECKOUT_SESSION_ID}'
        'cancel_url' =>  route('checkout-cancel'),


        //by default, its only card as its universally used and least confusion
        //you can allow other methods based on your audience with below parameter like wallets
        //you need to make sure methods are active within stripe global settings
        'payment_method_types'=>['card','bancontact','eps'],


        'metadata'=>[
            'cart_id'=>$cart->id
        ]
    ];

    //this will send you to stripe to checkout on their website then redirect back to either success_url or cancel_url
    return Auth::user()->checkout($prices,$sessionOptions);
}

public function success(){
    $sessionId = $request->get('session_id');
 
    if ($sessionId === null) {
        return;
    }
 
    $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId); //this make api call to stripe to get session
 
    //make sure stripe session says its paid
    if ($session->payment_status !== 'paid') {
        return;
    }

    $cart = Cart::find($session->metadata->cart_id);

    $order = Order::create(['user_id'=>Auth::id()]);

    $order->courses()->attach($cart->courses->pluck('id'));

    $cart->delete();

    return redirect()->route('home',['message'=>'Payment Successful']);
}

```


### checkout for amount directly

use checkoutCharge

```php

public function checkout(){
    $cart = Cart::session()->first();

    $session_options = [
        'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('checkout.cancel'),
        'metadata'=>[
            'cart_id'=>$cart->id,
        ]
    ];

    return Auth::user()->checkoutCharge(
        $cart->courses->sum('price'), //amount
        'Bundle of Courses', //description of what to charge
        1, //quantity
        $session_options
    );
}

```

### checkout for line items which not listed as product in stripe

use line_items

```php

public function checkout(){
    $cart = Cart::session()->first();

    $courses = $cart->courses->map(function($course){
        return [
            'quantity'=>1,
            'price_data'=>[
                'currency'=>config('cashier.currency'),
                'product_data'=>[
                    'name'=>$course->name
                ],
                'unit_amount'=> $course->price
            ]
        ];
    })->toArray();

    $session_options = [
        'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('checkout.cancel'),
        'line_items'=>$courses,
        'metadata'=>[
            'cart_id'=>$cart->id,
        ]
    ];

    return Auth::user()->checkout([],$session_options);
}

```


### guest checkout

its when you allow to checkout without having users to be registered


```php

public function checkout(){
    $cart = Cart::session()->first();

    $courses = $cart->courses->map(function($course){
        return [
            'quantity'=>1,
            'price_data'=>[
                'currency'=>config('cashier.currency'),
                'product_data'=>[
                    'name'=>$course->name
                ],
                'unit_amount'=> $course->price
            ]
        ];
    })->toArray();

    $session_options = [
        'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('checkout.cancel'),
        'line_items'=>$courses,
        // 'customer_email'=> 'buyer@example.com',
        'metadata'=>[
            'cart_id'=>$cart->id,
        ]
    ];

    return Checkout::guest()->create([],$session_options);
}

```

Tip: for guest checkout, stripe always asks for email to be filled in the form.. you can send the email from your side using customer_email property if you have it.. 


### Coupons and promotion code

Coupon: 
- a voucher (paper or digital) that gives a specific discount when presented.
- you can say from when to when this coupon can be applied
- is coupon for specific products or specific customers


Promotion Code (Promo Code):
- Always involves entering a code (letters/numbers) during checkout.
- is only for first time order?
- how many times it can be used before its inactive
- does it require minimum order value
- Example: entering WELCOME10 at checkout for 10% off.

- Coupon → broader term, can be physical or digital, doesn’t always need a code.
- Promotion Code → always a code, usually online, primarily for digital checkout systems.


in most e-commerce it doenst matter this differentation between them, we can say coupon and we are refering to promotion codes

in stripe design they differentiate between them.. 
- first you define coupon with discount you want
- with each coupon you can define multiple promotion codes



give user ability to write the promotion code on stripe form on paying

```php
public function checkout(){
    $cart = Cart::session()->first();

    $prices = $cart->courses->pluck('stripe_price_id')->toArray();

    $sessionOptions = [
        'success_url' => route('checkout-success').'?session_id={CHECKOUT_SESSION_ID}'
        'cancel_url' =>  route('checkout-cancel'),
        'allow_promotion_code'=>true, //enable user to write promotion code
    ];

    //this will send you to stripe
    return Auth::user()
    ->allowPromotionCode() // or you can do it like this instead of above parameter. which is doing same thing
    ->checkout($prices,$sessionOptions,$customerOptions);
}

```

auto apply coupon/promotion code. and user cant amend it

```php
public function checkout(){
    $cart = Cart::session()->first();

    $prices = $cart->courses->pluck('stripe_price_id')->toArray();

    $sessionOptions = [
        'success_url' => route('checkout-success').'?session_id={CHECKOUT_SESSION_ID}'
        'cancel_url' =>  route('checkout-cancel'),
        'allow_promotion_code'=>true, //enable user to write promotion code
    ];

    //this will send you to stripe with coupon auto applied
    return Auth::user()
    ->withCoupon($stripe_coupon_id)
    ->checkout($prices,$sessionOptions,$customerOptions);

    // or

    //this will send you to stripe with promotion code auto applied
    return Auth::user()
    ->withPromotionCode($stripe_promotion_id)
    ->checkout($prices,$sessionOptions,$customerOptions);
}

```



### Payment Method (Direct Payment)

- another flow of payments is that you dont send users to gateway website
- its you provide the form on your end as this will give you extra control over ui/ux
- you can take some html and js provided from stripe and put it in your website (review laravel cashier Payment Methods for Single Charges)
- customer will put his card data into the form which is going to create a payment method in stripe and it will return payment method object which contains id
- in your backend. you can use this payment method id to do interesting thing
    - first: you can charge against this payment method id
    ```php
        $paymentIntent = $user->charge(100, $paymentMethodId);
        if($paymentIntent->status == 'succeeded'){
            //all good
        }
    ``` 

    - second: add payment method to stripe then stripe would remember your card data
    ```php
        $user->updateOrCreateStripeCustomer(); //make sure user added as a customer then we can add payment methods
        $user->updateDefaultPaymentMethod($paymentMethodId); //stripe will remember always last card info as the default
    ```

    - third: now since we have default payment method defined with customer. we can have another button called one click checkout.then user dont have to re-enter his card details again as stripe remember it
    ```php
    @if(Auth::user()->hasDefaultPaymentMethod())
        <button class='btn btn-primary'>One Click Checkout</button>
    @endif

    public function oneClickButtonSubmit(){
        if(Auth::user()->hasDefaultPaymentMethod()){
            $defaultMethodId = Auth::user()->defaultPaymentMethod()->id
            $paymentIntent = $user->charge(100, $paymentMethodId);
            if($paymentIntent->status == 'succeeded'){
                //all good
            }
        }
    }
    ```
