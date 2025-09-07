# Liskov Substitution Principle (LSP)

any subclass can replace his parent without breaking the code.. 

this is typically a rule for inheritance when you define contract. all subclasses shouldn't change the expectations set by the parent.  

---

## 🔧 Theoretical
The whole point of having FlyableBird as parent class is that you trust any flyable bird can fly. I don’t care if it’s Owl or Eagle, or whatever — as long as it fly(). If some implementations don’t behave the same, like Penguine. this will make the caller confused, he uses penguine he expects penguine to fly and the he surprised penguine can't fly so why did it inherit flyablebird from the beginning? i understand it maybe because some shared code but i think it required deeper thought to refactor this as penguine shouldnt ever inherit flyablebird .. you need to look for alternative approach..

---

## 🔧 In Practice
subclass `StripePaymentGateway` and `PaypalPaymentGateway` can replace class `PaymentGateway` anywhere in your code without crash or produce unexpected results set by the parent

---

## Why is breaking LSP a problem?
I don’t care if it’s Stripe, PayPal, or whatever — as long as it charge(). If PayPal implementation is different, the caller get surprised later on by a bug of unexpected result. it should have charged why paypal implementation doesnt charge, its because paypal implementation requires > 1$ charge, ahh i see.. but this is a special case why paypal gateway should care it violdates LSP and SRP in same time
---

## 🎯 Why LSP is Useful in Laravel Projects
- ✅ Ensures services or channels can be **swapped without bugs**  
- ✅ Makes subclasses usage reliable so that swapping between them shouldnt cause bugs  
- ✅ Prevents nasty runtime errors where you think a subclass “fits” but it doesn’t  
- ✅ Makes **dependency injection safe**  
- ✅ Improves **testability** — you can mock or swap implementations freely  