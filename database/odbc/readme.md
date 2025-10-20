# ODBC (Open Database Connectivity)

is a standard API that allows applications to talk to different databases using the same interface, regardless of the underlying database system.


### How it works

- Application calls query Example: SELECT * FROM Customers
- ODBC Driver Manager -> loads the right driver (e.g., SQL Server ODBC driver, MySQL ODBC driver).
- Driver translates the call into the database’s native API/protocol.
- Database executes query and sends results back through the driver → ODBC → Application.


### Key Benefits

- Database independence: Your code can stay the same whether the backend is SQL Server, MySQL, PostgreSQL, or even Excel spreadsheets.
- Portability: You can switch databases by just changing the driver and connection string.
- Wide support: Almost all relational databases provide an ODBC driver.


### Connection String

In Windows, you often configure a DSN (Data Source Name) in ODBC Data Source Administrator.
Or 

you can use a direct connection string:

```ini
Driver={ODBC Driver 17 for SQL Server};
Server=localhost;
Database=MyAppDB;
Uid=sa;
Pwd=SuperSecret123;
```

Then your app (C, C++, PHP, Python, etc.) uses the ODBC API to connect.



### Where you’ll see ODBC

- Legacy systems (like Sage 50, Access, older ERP apps)
- Reporting tools (Excel, Crystal Reports, Tableau can all use ODBC)
- Integration when you don’t want to tie your app to one database vendor


### Configure Datasource

- in windows Open ODBC Data Source Administrator by win+R then odbcad32
- Add a Data Source either in user or in system
    - choose driver that suitable and if driver is not installed then install it
    - after this, choose the source and give it a name DSN (data source name)
- now in your application, you can connect by `DSN=MyCompanyDB;Uid=myuser;Pwd=mypassword;`


### is odbc windows only?

No. It’s a cross-platform standard. ODBC is everywhere, but Windows apps tend to rely on it more because Microsoft integrated it heavily into Office, Access, and business apps.
- Windows: Built into the OS (native GUI, registry-based config).
- Linux/macOS: Requires unixODBC or iODBC, with config files instead of GUI tools.


### currently am i relying on native clients or ODBC?

On Linux, developers sometimes skip ODBC and use native drivers (e.g., psycopg2 for PostgreSQL, mysqlclient for MySQL), unless they specifically need one unified interface across multiple databases.

for mysql/postgres .. typically native clients mysqlclient, psycopg2

for sql server: Microsoft officially says:
- 👉 Use the ODBC Driver for SQL Server as the primary connectivity option for cross-platform apps.
- 👉 Use OLE DB mainly if you’re in a Windows-only ecosystem and need features ODBC doesn’t expose.


### What are the common usage of odbc nowadays?

- Legacy / Business Applications
    - Many older ERPs, accounting, and payroll systems (e.g., Sage 50, QuickBooks, MS Access, some Oracle/SQL Server apps) expose data through ODBC.

    - Companies keep ODBC around because rewriting these systems would be expensive.

    - Example: Exporting data from Sage 50 into Excel using ODBC.

- Reporting & Analytics Tools
    - BI tools like Excel, Power BI, Crystal Reports, Tableau can connect to any database if there’s an ODBC driver.
    - ODBC acts as the universal connector when no direct integration exists.
    - Example: Power BI pulling from MySQL via ODBC.

- Linux to sql server
    - On Linux, unixODBC is still used to connect to SQL Server (since Microsoft provides an official ODBC driver for Linux).


### odbc always takes input as sql query?
Exactly 👍 — ODBC is built around SQL.


### is odbc support joins?

- ODBC is not a database engine, so it doesn’t decide how joins work.
- Some file-based ODBC drivers (CSV, Excel, dBase) support only limited SQL features — sometimes no joins at all, or only inner joins.
- With real databases (SQL Server, MySQL, PostgreSQL, Oracle), joins work exactly as if you connected natively.