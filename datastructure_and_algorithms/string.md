# String

## String By Design is immutable in most of the languages

```java
//this would instantiate another object and it won't amend string in place
String s = "ahmed";
s + " ibrahim";

//this will be true, because java and many langauges creates string pools to optimize memory for strings, so that if string already exists in pool it would use it and dont create another object
String x = "ahmed";
s === x

//if you want to amend string in place, you should use different technicque such as StringBuilder in java which allow you to do this, because if you stick with strings you may use much memory and cpu because of overhead of creating string object whenever you amend string
```

### But Why Immutable??
- Security (The BIGGEST reason)

    Strings are used in security-sensitive places:
    ```text
    "admin"
    "password"
    "/usr/bin"
    "jdbc:mysql://..."
    ```

    If strings were mutable:
    ```java
    String s = "admin";
    useAsUsername(s);
    // some code changes s to "guest"
    ```

    That would be catastrophic.

- Design Philosophy: Value Object 🧱

    A String represents a value, not an entity: Like Integer, Boolean... 
    
    Value objects should be immutable