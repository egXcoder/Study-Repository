# API Resources

In Laravel, API Resources (also called Eloquent API Resources) are a way to transform your models and collections into structured JSON responses.


## Why Resources?

- add security then attackers dont know the database structure
- encapsulate return array map into separate place rather than in controllers

## Single Resource

`php artisan make:resource UserResource`

```php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
        ];
    }
}

// {
//   "id": 1,
//   "name": "Ahmed Ibrahim",
//   "email": "ahmed@example.com"
// }
return new UserResource(User::find(1));

```

## Collection of resouces

```php
// [
//   { "id": 1, "name": "Ahmed", "email": "ahmed@example.com" },
//   { "id": 2, "name": "Sara", "email": "sara@example.com" }
// ]
return UserResource::collection(User::all());

```


## Nested Resources

```php

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'author'  => new UserResource($this->user),
            'comments'=> CommentResource::collection($this->comments),
        ];
    }
}

```