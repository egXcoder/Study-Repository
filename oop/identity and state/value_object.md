# Value Object

A Value Object is an object that represents a value, and is defined only by its data, not by an identity.

If two objects have the same data, they are the same value.

### Entity Vs Value Object
Entity
- Has identity
- Changes over time
- Equality is based on ID
    ```java
    User u1 = new User(id=1, name="Ahmed");
    User u2 = new User(id=1, name="Ali");

    u1 == u2  // same entity
    ```

Value Object
- Has no identity
- Equality is based on fields
- Represents a value
    ```java
    Money m1 = new Money(100, "EGP");
    Money m2 = new Money(100, "EGP");

    m1.equals(m2) // true
    ```

---

### Common Examples of Value Objects

- Money (amount, currency)
- Price
- TaxRate
- Discount
- Percentage
- ExchangeRate
- Distance
- Weight
- Height
- Temperature

---

### Why would i use object for a value, i can use a variable and thats it?

Because a variable only stores data, but a value object stores meaning, rules, and guarantees.

Variable = raw data, no meaning

```java
int amount = 100;
String currency = "USD";
```

Problems:

- Nothing prevents:
```java
amount = -999;
currency = "ABC";
```

- No rule enforcement
- No guarantee they belong together
- Easy to mix up

For Example:

```java
Money.of(-100, "USD");  // ❌ rejected
```

---

### Why Value Object Should be immutable


Main reason: Reliability

```java

Map<Money, String> discountMap = new HashMap<>();
Money key = new Money(100, "USD");
discountMap.put(key, "Special discount");

```

If Money were mutable:

```java
key.amount = 200;
```

- hashCode() changes
- Map entry becomes unreachable
- Silent data corruption
- bug of discount amount became 200 and not sure where


Same Story here

```java

Money price = new Money(100, "USD");
order.setTotal(price);
invoice.setTotal(price);

```

If Money is mutable:

```java
price.amount = 200;
```

- Both order and invoice totals change unexpectedly
- Side effects are invisible and hard to debug

---

Money Implementation Should Be Like Below
```java

final class Money {
    private final int amount;
    private final String currency;

    public Money add(int value) {
        return new Money(amount + value, currency);
    }
}

```

--- 

Immutability Take Away:

A value object must be immutable because its identity is defined entirely by its data, and changing that data would break correctness, safety, and predictability.

--- 
Bugs: 

if you left value object mutable .. bugs will happens and its very hard to debug because it doesnt happen frequently they happens far away from each other and its hard to debug or to fix because now system rely on value object is mutable and its hard to make it otherwise