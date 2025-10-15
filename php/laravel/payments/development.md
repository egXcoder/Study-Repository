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

### Prices to send to gateway like stripe

you can add products in the gateway, each gateway product can have multiple prices. save gateway price_id against your internal products so that when doing checkout you ask gateway i want to checkout using these prices ids


```php
//this is using cashier package for stripe and billable trait inside user model
public function checkout(){
    $cart = Cart::session()->first();

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

    //meta data will be brought back when i ask for session on success or failure
    $customerOptions = [
        'metadata' => [
            'user_id'=>Auth::id()
        ]
    ];

    //this will send you to stripe
    return Auth::user()->checkout($prices,$sessionOptions,$customerOptions);
}

public function success(){
    $sessionId = $request->get('session_id');
 
    if ($sessionId === null) {
        return;
    }
 
    $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId); //this make api call to stripe to get session
 
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