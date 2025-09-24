# caching in browsers

if you are sending response such as 
[
  { "id": 1, "header": { "id": 10, "title": "Invoice A" }, "amount": 100 },
  { "id": 2, "header": { "id": 10, "title": "Invoice A" }, "amount": 200 },
  { "id": 3, "header": { "id": 10, "title": "Invoice A" }, "amount": 300 }
]

return response()->json($lines)
    ->header('Cache-Control', 'public, max-age=3600'); // cache for 1 hour

- by sending cache-control header with http response, you ask the browser to cache the data for a specific time

- by default when server send back css,js files, it put cache-control header with for example 'max-age=1296000, public'

[request/response verbs]
- GET → naturally cacheable.
- POST → only cacheable if you force it with headers, but it’s rarely recommended unless you know it’s safe and idempotent.