## Checkout On Gateway Site

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



### if i am doing checkout using stripe website, what if after i pay in stripe and now stripe failed to redirect back .. is this means payment is lost now?


🚨 If redirect fails (user closes browser, network drops, DNS fails, etc.)

The payment itself is not lost because Stripe already processed it.

Your webhook is the source of truth → it tells you the real status (succeeded, requires_payment_method, canceled, etc.).

The redirect is just for user experience (thank you page, receipt, etc.), not for accounting or order state.


### what if webhook broken?

as a third backup you can do a cronjob which check stripe intents and reconcile with the orders

```php

$intents = \Stripe\PaymentIntent::all([
    'created' => ['gte' => strtotime('-1 day')],
]);

```