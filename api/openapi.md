# Open API

## What is Open API

OpenAPI = A standardized language (JSON/YAML format) used to describe APIs so that both humans and machines can understand and use them. Swagger is actually a toolset built around OpenAPI — Swagger UI, Swagger Codegen, etc., all use OpenAPI files.

## Top-Level Fields

- openapi: Version of the format
- info: Metadata about the API (title, version, description)
- servers: Base URL(s) of the API
- tags: labels that group your endpoints together
- paths: Endpoints and methods
- components.schemas: Reusable models (User, Error, etc.)
- components.securitySchemes Define auth (JWT, OAuth, API Key)

```yaml
openapi: 3.0.0
info:
  title: My API
  description: This is a sample API specification that shows the most common features.
  version: 1.0.0
  contact:
    name: API Support
    url: https://example.com/support
    email: support@example.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: https://api.example.com/v1
    description: Production server
  - url: https://staging.example.com/v1
    description: Staging server

tags:
  - name: Users
    description: Operations related to users
  - name: Auth
    description: Authentication endpoints

paths:

components:

```

## paths in depth


```yaml

paths:
  /users:               # <-- path (endpoint)
    get:                # <-- operation (HTTP method)
      summary: Get all users
      tags: [Users] # it can be also - Users which is the same 
      parameters:       # <-- query, header, path, or cookie params
        - name: limit   # this - means paramters is list items
          in: query # ['query','path','header','cookie'] can be one of these .. query like ?limit=10 .. path like /users/20
          schema:
            type: integer
          required: false
          description: Number of users to return
      responses:        # <-- responses for this operation
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: '#/components/schemas/User' # Reference schema declared in components

```















## YAML or JSON

- YAML is more common in practice.
    - Easier for humans to read and write (less {}, [], and quotes).
    - Cleaner for large specs (enterprise APIs with hundreds of endpoints).
    - Most tutorials, Swagger Editor, and examples online use YAML.

```yaml
openapi: 3.0.0
info:
  title: User API
  version: 1.0.0

paths:
  /users:
    get:
      summary: Get all users
      responses:
        200:
          description: List of users
```

``` json
{
  "openapi": "3.0.0",
  "info": {
    "title": "User API",
    "version": "1.0.0",
    "description": "A simple API example in OpenAPI (JSON format)"
  },
  "paths": {
    "/users": {
      "get": {
        "summary": "Get all users",
        "responses": {
          "200": {
            "description": "List of users",
            "content": {
              "application/json": {
                "schema": {
                  "type": "array",
                  "items": {
                    "$ref": "#/components/schemas/User"
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "User": {
        "type": "object",
        "properties": {
          "id": {
            "type": "integer",
            "example": 1
          },
          "name": {
            "type": "string",
            "example": "John Doe"
          },
          "email": {
            "type": "string",
            "format": "email",
            "example": "john@example.com"
          }
        }
      }
    }
  }
}
```