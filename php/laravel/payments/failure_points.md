## What are the failure points that can happen while paying

Card / Payment Method Issues
- Insufficient funds (the bank rejects).
- Card expired.
- Invalid card number or CVC.
- Card blocked / stolen (fraud detection).
- Unsupported card (e.g., local-only card being used internationally).
- 3D Secure (SCA) required but not completed by user.

👉You should show a clear error message and allow the customer to retry with another payment method.


Double charges
- customer clicks "Pay" twice

👉 Use Idempotent keys.

Payment Flow Issues + Network Issues
- Payment stuck in "requires_action" (like waiting for 3D Secure authentication).
- Canceled midway — user closes the window before confirmation.
- Slow bank response → user leaves thinking it failed, but later succeeds.
- Timeouts between your server and the payment gateway.

can be because
- User closed the popup instead of approving.
- Bank app didn’t send the confirmation.
- Browser didn’t redirect back properly.
- network dropped after paying on stripe and going back to my site

👉 Webhook is your backup to mark payment as succeeded if any issue happens

Third Backup (CronJob)
- if webhook was down because of your endpoint was down or something.. 
- its good idea to do a cron job to review payment intents that didnt reached final state 
- query these payment intents to check if payment has reached final state as succeeded or failed and act accordingly



Fraud & Compliance

- High-risk transactions (gateway may flag/suspend).
- Chargebacks / disputes — customer later says “I didn’t authorize”.
- KYC / AML restrictions — payment blocked due to compliance rules.

👉 Have a fraud prevention policy and handle disputes properly.



Currency & Amount Issues

- Wrong currency conversion (e.g., charging in USD instead of EUR).
- Amount mismatch between your system and gateway.
- Rounding errors if you use floating-point math instead of integers for money.

👉 Always store money in minor units (e.g., cents, pence, fils).


User Experience / UX Issues

- User retries unnecessarily and creates duplicate orders.
- User pays but doesn’t get redirected back → order marked unpaid.
- Failed to link payment to the right order/session.

👉 Always reconcile payments against your order IDs instead of trusting frontend.