# Security

## Enable SSL on connecting from client to database server. 
- to ensure no one can sniff between client and database server
- typically required when backend and database lives in two different servers

## Sql User to do migrations should be different from Sql User who query
- users doing query should have lower permission then if someone sql injected then he cant alter or drop tables
- its best practice to lower the damage if the query sql user was exploited