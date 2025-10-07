# HMVC Hierarchical Model-View-Controller.


It’s an extension of the classic MVC (Model-View-Controller) pattern — the one you already know from frameworks like Laravel, CodeIgniter, etc.


split your application into modules, each module have its own routes, controllers, models, migrations, resources etc...


Q: is models and migrations should be different per module?

For models and migrations: there are two approaches

- Approache 1: put models and migrations into core (common way). 
    - for clients it doesnt matter what modules enabled or disabled
    - they always have same unified scheme

- Approache 2: put models and migrations into modules
    - if two modules want to use same eloquent, then we have to duplicate the eloquent and his migration file
    - this duplicate is problematic, first its duplicate code. second one module can mistakenly rely on the other module eloquent which is very bad adn messy
    - if we don't duplicate eloquent, we end up with two modules are relying on each other. if one of them disabled which hold the eloquent and its structure. the other module will crash. which is cyclic dependency
    - this approach sound suitable more for a big stable system and you want to extend its functionality by a custom plugin which will do very custom things for this client



Q: if we are using approach 1 which is common. what we end up is every module have screens + routes + controllers + config + permissions which is good, but what if there is some logic a service for example getPostComments and i will need to use it in two different modules. i need to duplicate this service?

mmm,
- If you make module to rely on another module code, you will create dependecy and modules never should depend on each other
- Duplicating the service would violate DRY — not acceptable either.

✅ The Right Solution: Shared Service Layer (in Core or Domain)
- You move the shared business logic into a Core (or Domain) service layer
- and make modules depend only on that layer, never on each other

```text
app/
 └── Core/
      ├── Models/
      │    ├── Post.php
      │    └── Comment.php
      ├── Services/
           ├── PostService.php
           ├── CommentService.php
           └── AnalyticsService.php
      
Modules/
 ├── Post/
 │   └── Http/Controllers/PostController.php
 ├── Comment/
 │   └── Http/Controllers/CommentController.php
 └── Analytics/
     └── Http/Controllers/AnalyticsController.php
```


Q: now if i am going to deliver my software to multiple clients.. some clients will take it as it is easy... some clients may ask for customization on bp screen or stock item screen.. can i create another Folder called tenants and within it we can say folders client_x, client_y, client_z and with every client i can override the modules?

Yes — absolutely, and this is actually a common pattern in enterprise SaaS systems that need client-specific customizations while keeping a single codebase. It’s essentially a “tenant override layer” on top of your core modular system.


```text

app/
 └── Core/
      ├── Models/
      ├── Services/
      └── Contracts/
Modules/
 ├── BP/
 ├── StockItem/
 └── Sales/
Tenants/
 ├── ClientX/
 │    ├── Modules/
 │    │    └── BP/          # overrides or extends BP module
 │    │    └── StockItem/   # optional overrides
 │    └── Config/
 ├── ClientY/
 │    └── Modules/
 │         └── StockItem/
 └── ClientZ/
      └── Modules/

```

Key Idea:
- Core owns shared logic
- Modules own generic screens and features
- Tenants/ClientX can override or extend modules for custom behavior
- you need to put some logic to allow clients to override routes + views + controllers from the modules, otherwise it refer back to base module



Q: when would i use hmvc?

hmvc isn’t something you need to use for every project — it’s a tool for specific architectural problems. You use it when your application benefits from modularity and separation at the feature level.


You would use HMVC when your application:

- Wants reusable or pluggable modules
- Has multiple distinct features/modules.
- Needs clean separation of controllers, models, and views per modules
- Might need optional modules or client-specific overrides.
- Is expected to scale (multi-developer or multi-client).