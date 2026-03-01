# Query

- you can count length of characters
    `select char_length(message) from t1`

- you can round column
    `select round(salary,2) from t1`

- Count records
    - `select count(*) from employee` .. this will count all records within t1
    - `select count(phone) from employee` .. count employees with phone not null

- if condition
    - `select if(salary>1000,1,0) from t1`
    - `select if(salary>1000,1,if(salary>500,.5,0)) from t1` .. if,else if

- ifnull
    - `select ifnull(salary,0)` .. this will give salary otherwise 0

- date between
    - `select created_at between start_date and end_date from t1`

- Cross Join: when you have table t1 that will join fully with t2, its called cross join
    - `select * from t1 left join t2 on true` (X)
    - `select * from t1 cross join t2` (✓)

- Join and group by can be in same query
```sql
SELECT a1.machine_id, ROUND(AVG(a2.timestamp - a1.timestamp), 3) AS processing_time
FROM Activity a1
JOIN Activity a2
    ON a1.machine_id = a2.machine_id
   AND a1.process_id = a2.process_id
   and a1.activity_type!=a2.activity_type
where a1.activity_type = 'start'
GROUP BY a1.machine_id;
```

