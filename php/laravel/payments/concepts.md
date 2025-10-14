## Payments Concept

## which payment gatway should i choose?


1. Where are your customers?
- Mostly in Egypt → go for Paymob or Fawry.
    - Customers can pay with Meeza cards, wallets, Fawry outlets, which most Egyptians trust.
    - Settlement happens in EGP directly to your bank account.
    - Stripe isn’t officially supported for Egyptian merchants, so using it locally can be tricky.

- Mostly international (US, EU, Gulf, etc.) → go for Stripe, PayPal, or Adyen.
    - Stripe has excellent support for global cards, subscriptions, and recurring billing.
    - PayPal is widely recognized (though fees can be higher).
    - Adyen is enterprise-level, used by Uber, Netflix, etc., but harder to set up.


2. What is your business model?
- E-commerce / One-time payments (checkout)

    - Paymob or Fawry locally → because people can pay cash at Fawry kiosks or via wallets.
    - Stripe / PayPal globally.

- Subscriptions / SaaS
    - Stripe is the best in class here → integrates perfectly with Laravel Cashier.
    - Paymob/Fawry are still catching up on recurring billing support.

- Marketplaces (you pay out to vendors)
    - Stripe Connect is very strong for split payments.
    - Locally, you may need custom agreements with Paymob/Fawry to support payouts.


3. Fees
- Stripe/PayPal: ~2.9% + $0.30 (plus extras for international).
- Paymob/Fawry: ~2–3% (sometimes with setup fee, monthly fees, or integration costs, but lower fixed cost per transaction for small payments).
- Bank transfers (wire payments): Cheaper, but no instant confirmation.


### what is bank transfer is gateways can do this?

In some occasions, customers can pay you by transferring money directly from their bank account (instead of card/wallet).

This is usually outside of gateways (e.g., customer goes into their banking app, types your IBAN, and sends money). 

It’s cheap but has two issues:
- No instant confirmation – you don’t know right away if they really paid until you check your bank.
- Hard to automate – unless you reconcile bank statements or use a special service.

When to use bank transfer payments?
- High-value B2B transactions (e.g., $5,000 invoice → fees on cards would be huge).
- Recurring billing (via direct debit, not manual transfers).
- Regions where cards aren’t widely used (some customers prefer paying from their bank directly).


### What is wire transfer?

A wire transfer is when money is sent electronically from one bank to another, typically across countries.

Uses systems like SWIFT (international) or Fedwire/CHIPS (US).

Example: If a client in Germany wants to pay your Egyptian company, they send a SWIFT transfer from their Deutsche Bank account to your Egyptian bank account.

You provide them your IBAN / SWIFT code / bank details, and they push the money.


Characteristics of wire transfers
- Not instant → may take 1–5 business days.
- Not free → both sender and receiver banks usually charge fees (e.g., $10–$50 per transfer).
- Secure → directly between banks, with strong regulation.
- Good for big payments (e.g., $5,000, $50,000), because percentage fees (like Stripe’s 3%) would be too costly.


### what does payment gateways really do? how they take the money?

Step-by-step: When a customer pays with a card

Say you charge 100 EGP:

- Customer enters card info (Card number, expiry, CVV)
- Gateway encrypts and sends it securely to visa/mastercard/meeza,etc.. (so you never store raw card data).
- visa route the request to the card bank and asks Is this card valid? Does it have enough balance?
- Customer’s bank (issuing bank) approves or declines.
- If approved → 100 EGP is reserved on the customer’s account.
- Settlement: At the end of the day, the funds are moved through the card network into the gateway’s acquiring bank account.
- Payout: Gateway deducts its fee (say 3 EGP).
- The rest (97 EGP) is transferred to your merchant account (usually in 1–7 days).


### so what if gateway said everything is good and success etc.. and later on bank declined?

Normally no, because approval = money reserved. But there are exceptions where settlement can fail later:

if customer reported claim fraud or dispute the charge.

Example: Customer says “I never made this purchase” → Bank Misr investigates → money can be pulled back from the merchant.

But if a chargeback happens later, the merchant usually bears the loss (unless they fight and win the dispute).


### what is direct debit vs recuring payments?

DD:
- The company (merchant) pulls money directly from your bank (after you give authorization/mandate).
- You sign a direct debit mandate (online or paper) authorizing future withdrawals.
- Slower (2–5 business days in EU/US).
- Bank usually informs you when a new mandate is created.
- More secure, harder to “cancel instantly” (you must revoke the mandate).
- Utilities, rent, insurance, taxes, gym memberships.
- If account is empty, debit may declined

RP: 
- reuses your stored card to charge periodically.
- You check out once and allow the merchant/gateway to save your card for recurring charges.
You subscribe to Netflix and set up a direct debit from your bank account.
- Fast (near-instant authorization).
- Bank just shows a normal card transaction — no special notice.
- Subscriptions like Netflix, Spotify, Google Play, SaaS services.
- If card expires or is blocked, payment fails.


### What is direct debit mandate?

- customer (me) want to buy things from supplier and i am going to do this periodically
- the supplier gives me the mandate form.
- i fill in my bank details and sign → this becomes the authorization.
- Then the supplier keeps that mandate on file and uses its unique Mandate ID when submitting direct debit instructions to their bank.

👉 Example:
- Supplier: Vodafone
- Creditor ID: EG55ZZZ123456 (from bank)
- Customer: Ahmed
- Mandate Reference: MANDATE-AHMED-001
- When Vodafone debits your account, they send both Creditor ID + Mandate Reference → your bank checks if it’s valid.