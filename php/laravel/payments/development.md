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


### Checkout On Gateway Site

this is explained on another page


### Direct Payments









Payment Intent (Most Common): every time you buy they will ask about your card details ..  This is very common for “guest checkout.” or small online shops

Setup Intent: this is for large stores. stripe will hold your card details after authenticating it by SCA / 3-D Secure authentication.. then whenever

“Hey Stripe, I want this customer to authorize their card so I can safely charge it later.”




- Purpose: Charge a specific amount now (without saving the card).
- It appears on the Stripe Dashboard as an incomplete transaction until the user provides and confirms their payment method.
- Once confirmed, the payment completes automatically.

Steps:
- Create a PaymentIntent on Stripe (with amount, currency, etc.).
- use the payment intent to create a payment method (once its done, payment is completed)



Setup Intent:

if you are buying from Large platforms (Amazon, Netflix, Uber) .. They save your card for reuse, so you don’t enter it every time.
That requires:
- SetupIntent → authenticate & save card details (PaymentMethod).
- PaymentIntent → whenever they need to actually charge you (immediately, or later in background).

- Purpose: Collect and save a card for future payments (without charging immediately).
- A SetupIntent ensures that authentication (e.g. 3D Secure / SCA) is handled when the card is saved.
- At this stage, no money moves.
- Later, when ready, you can use the saved PaymentMethod to create a PaymentIntent and charge the user.

Steps:
- Create a SetupIntent on Stripe.
- Collect card details (creates a PaymentMethod) and confirm it against the SetupIntent.
- When ready to charge later, create a PaymentIntent using that saved PaymentMethod.


Payment Method: 
- Purpose: Save a card (or other payment details), possibly to charge now or later.
- A PaymentMethod on its own is just card (or other) details, stored securely in Stripe. It does not move money.
- When created, Stripe returns an ID like pm_12345… which can be attached to a Customer for later use.

Steps:
- Create a PaymentMethod on Stripe → get back an ID (pm_12345).
- Save the PaymentMethod against the user in Stripe.
- When needed, create a PaymentIntent and charge the user using that PaymentMethod.










### Payment Method (Direct Payment)

- another flow of single charge payments is that customer fill the card data on your website. 
- you can take some html and js provided from stripe and put it in your website 
- form is provided though from stripe (review laravel cashier Payment Methods for Single Charges)

Steps:
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
