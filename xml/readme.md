# XML

XML stands for eXtensible Markup Language.

```xml
<person id="123" type="employee">
    <name>Ahmed</name>
    <age>27</age>
    <city>Cairo</city>

    <h:to>Tove</h:to>
    <h:from>Jani</h:from>
    <h:message>Hello</h:message>
</person>
```

- person is `tag` or `element`
- ahmed is `value` 
- id is `attribute`



#### XML Rules
- Must have one root element.
- All tags must be closed: <name>Ahmed</name> or <name />.
- Tags are case-sensitive: <Name> ≠ <name>.
- Attribute values must be in quotes.


#### XML Namespace

```xml
<person id="123" type="employee" xmlns:h="http://www.w3.org/TR/html4/" xmlns:f="https://www.w3schools.com/furniture">
    <h:to>Tove</h:to>
    <f:table>Dining table</f:table>
    <f:table f:width="120cm" f:length="80cm"/>
</person>
```

- xmlns:h="http://www.w3.org/TR/html4/" → declares a namespace with prefix h
- All elements prefixed with h: belong to that namespace.
- Useful when combining XML from different sources to avoid conflicts.

Tip: value of namespace like `http://www.w3.org/TR/html4/` is just a unique indentifier, parser doesn’t care about it at all