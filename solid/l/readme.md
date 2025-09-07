# Liskov Substitution Principle (LSP)

this is typically a rule for inheritance.. when you use inheritance. all subclasses must follow the expectations set by the parent. then any subclass can replace his parent without breaking the code.. 


---

## 🔧 Theoretical
The whole point of having FlyableBird as parent class is that you trust any flyable bird can fly. I don’t care if it’s Owl or Eagle, or whatever — as long as it fly(). If some implementations don’t behave the same, like Penguine. this will make the caller confused, he uses penguine he expects penguine to fly and the he surprised penguine can't fly so why did it inherit flyablebird from the beginning? ohh i see.. it maybe because some shared code but i think it required deeper thought to refactor this as penguine shouldnt ever inherit flyablebird .. you need to look for alternative approach..

---

## 🔧 In Practice
subclass `StripePaymentGateway` and `PaypalPaymentGateway` can replace class `PaymentGateway` anywhere in your code without crash or produce unexpected results set by the parent

---

## Why is breaking LSP a problem?
I don’t care if it’s Stripe, PayPal, or whatever — as long as it charge(). If PayPal implementation is different, the caller uses paypal gateway it should have charged why paypal implementation doesnt charge.. mmm, i see .. its because paypal implementation requires > 1$ charge.. i thought any gateway would charge, now we have a gateway that sometimes charge and sometimes don't.. i have to rememberize that always when i call it?? and also why gateway has to care about validation rule of how much money to charge.. its violating LSP and SRP in same time
---

## 🎯 Why LSP is Useful in Laravel Projects
- ✅ Ensures services or channels can be **swapped without bugs**  
- ✅ Makes subclasses usage reliable so that swapping between them shouldnt cause bugs  
- ✅ Prevents nasty runtime errors where you think a subclass “fits” but it doesn’t  
- ✅ Makes **dependency injection safe**  
- ✅ Improves **testability** — you can mock or swap implementations freely  