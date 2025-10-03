# Migrations


`php artisan make:migration create_flights_table`


`php artisan migrate`

`php artisan migrate:status` .. see how far you have run your migration


`php artisan migrate:rollback` .. Rolls back the last batch.
`php artisan migrate:rollback --step=2` .. Rolls back last 2 batches


`php artisan migrate:reset` .. roll back everything


`php artisan migrate:refresh` .. rollback everything and migrate


`php artisan migrate:fresh` .. drop all tables and migrate


`php artisan migrate --seed --database=xyz` ..options to include