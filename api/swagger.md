# Swagger

Swagger is a toolset used to document, test, and interact with REST APIs — usually through a beautiful auto-generated web interface.

It’s part of a larger ecosystem called OpenAPI Specification (OAS).


## What is Open API?
A standardized language (JSON/YAML format) used to describe APIs so that both humans and machines can understand and use them.


## What Problem Does Swagger Solve?

When you build an API, you usually need:
- ✅ Documentation for other developers
- ✅ A way to test endpoints without manually writing curl or Postman requests
- ✅ A contract that front-end / mobile developers can follow

Swagger generates all of this automatically, usually from your backend code or a YAML/JSON file.


## Components

- Swagger UI : A web interface that displays API docs and lets you try requests directly from the browser
- Swagger/OpenAPI Specification: The format (YAML or JSON) describing your API (endpoints, parameters, responses)
- Swagger Editor: helps edit and validate OpenAPI files.
- Swagger Codegen: generates code from OpenAPI files.


## generating the open api files

- there are multiple tools that can scan your code and auto generate the open api file for you
    - L5-Swagger 
    - zircote/swagger-php

- on every controll method, we can put the documenation of this api .. get, post, patch, etc..

```php
/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"Users"},
 *     summary="Get a list of users",
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
 *     ),
 *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Error"))
 * )
 */
```

- you can define empty php file for global swagger annotation, you can put it in /app/Swagger/SwaggerAnnotations.php

```php

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="This is the API documentation for My API.",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     title="Article",
 *     required={"id", "title"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="News Title"),
 *     @OA\Property(property="description", type="string", example="Content of the news article"),
 *     @OA\Property(property="content", type="string", example="Full Content of the news article"),
 * )
 */


/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth", 
 *     type="http",
 *     scheme="basic"
 *     in="cookie",
 *     name="laravel_session"
 * )
 */

```

## Swagger Security Scheme
- securityScheme
    - name it whatever you want but has to match when you mention it on the api

- type can be 
    - http  .. Basic or Bearer (JWT) auth
    - apikey .. Custom header, query, or cookie
    - oauth2 .. OAuth2 flows

- scheme="basic"
    - basic: Authorization: Basic dXNlcjpwYXNz
    - bearer: Authorization: Bearer dXNlcjpwYXNz
    - digest: Authorization: Digest username="admin",...

- in="header" | "query" | "cookie"

- name: is the name of key like name of auth key in cookie which is laravel_session