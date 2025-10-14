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

- Link carts to users as soon as possible
    - then reuse user cart without creating new cards

- Clean up carts automatically
    - Run a scheduled job (cron) to remove or archive carts older than X days.

- Don’t create the cart row too early
    - delay creating a cart record until the user actually adds the first item.

- Use caching for short-lived guest carts
    - For guest users, you can rely on redis to store carts and hit the database only when user login


Q: so what if guest added lines to his cart .. then he logged in while he was having cart already?

- Merge guest cart into user’s existing cart (most common)
- discard guest cart/or user cart
- Ask the user to choose guest cart or user cart (rare, but safest)