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
    - both of below can scan annotations on controllers and generate open api file that you can preview and share
    - zircote/swagger-php
    - L5-Swagger


Example:

```php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Operations related to users"
 * )
 */
class UserController extends Controller
{
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
    public function index(Request $request)
    {
        // Fetch users
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/UserCreate")),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=400, description="Invalid input", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function store(Request $request)
    {
        // Create user
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get user by ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User details", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function show($id)
    {
        // Fetch single user
    }
}


```




