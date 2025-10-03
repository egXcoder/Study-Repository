# Normalizing

- organizing data in a database to reduce redundancy (duplicate data) and ensure data integrity. 
- It involves structuring data into tables with defined relationships, ensuring each piece of information is stored in a single location
- It involves applying a sequence of rules called Normal Forms (NF) to structure tables and their relationships properly.

Benefit:
- Avoid duplicate data to Prevent storing the same data in multiple places.
- Improve consistency as If a value changes, you only update it in one place.
- Improve query accuracy as	Data relationships are clear and reliable.

- 1NF (First Normal Form): 
Each field must be atomic : If a phone_numbers column stores "123, 456", split into separate rows or a child table.

- 2NF (Second Normal Form)
No partial dependency on part of a composite key: If a table has a composite key (student_id, course_id) and stores student_name, move student_name to Students table.

- 3NF (Third Normal Form)
No transitive dependency: If employees table has dept_id and dept_name, remove dept_name to a Departments table.


Q: but applying normalizing yes make data good. but i can sometimes violate them to speed query instead of joins?
Normalization is great for consistency and clean design, but too much normalization can hurt performance because it requires many joins to fetch related data. That’s why in real-world systems, we often intentionally violate normalization rules — this is called denormalization.

Example:
To display an order summary, we’d need 3 JOINs:

SELECT o.order_id, c.name, p.name, p.price FROM orders o
JOIN customers c ON o.customer_id = c.id
JOIN products p ON o.product_id = p.id;

If this query is run millions of times per minute, JOINs become expensive.

✅ Denormalized for Performance
We might add customer_name and product_price directly into orders, duplicating data:
Now the query becomes: SELECT order_id, customer_name, product_price FROM orders;
⚡ Super fast — no JOINs — even though it violates normalization.


🧠 Golden Rule
Normalize for integrity. Denormalize for performance — but only when necessary.
