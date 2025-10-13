# Blade


## Components

Blade components are reusable, self-contained pieces of HTML (with optional logic) that you can use across your Laravel views.

There are two types: class-based and anonymous.

### anonymous:

`php artisan make:component Button --view`

```php
// resources/views/components/button.blade.php
<button {{ $attributes->merge(['class' => 'px-4 py-2 bg-blue-500 text-white rounded']) }}>
    {{ $slot }}
</button>

// {{ $slot }} → whatever content you put inside <x-button> ... </x-button>
// $attributes → passes extra HTML attributes (class, id, etc.) <x-button class='x' id='y' data-param='z'> ... </x-button>
```

use it

```php
<x-button>Submit</x-button>
<x-button class="bg-green-500">Save</x-button>
```

### class-based:

`php artisan make:component Alert`

```php
// This creates:
// A PHP class: app/View/Components/Alert.php
// A Blade view: resources/views/components/alert.blade.php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public $type;

    public function __construct($type = 'info')
    {
        $this->type = $type;
    }

    public function render()
    {
        return view('components.alert');
    }
}


<div class="p-4 border 
    @if($type === 'error') border-red-500 bg-red-100 text-red-700
    @elseif($type === 'success') border-green-500 bg-green-100 text-green-700
    @else border-blue-500 bg-blue-100 text-blue-700
    @endif">
    {{ $slot }}
</div>

```

use it

```php

<x-alert type="success">
    Data saved successfully!
</x-alert>

<x-alert type="error">
    Something went wrong.
</x-alert>

```

Here, type="error" does not go into $attributes. Instead, Laravel automatically maps it to the constructor argument $type and stores it in the class property $this->type.


### Multiple Slots

```php
// resources/views/components/card.blade.php
<div class="border rounded shadow p-4">
    <div class="font-bold text-lg mb-2">
        {{ $header }}
    </div>

    <div class="mb-4">
        {{ $slot }}  {{-- default slot (main content) --}}
    </div>

    <div class="text-sm text-gray-600">
        {{ $footer }}
    </div>
</div>

```

```php
<x-card>
    <x-slot name="header">
        Card Title
    </x-slot>

    This is the card body (default slot).

    <x-slot name="footer">
        &copy; 2025 My App
    </x-slot>
</x-card>
```

### Pass Props

- Example 1

```php
// resources/views/components/button.blade.php
@props(['type' => 'button', 'color' => 'blue','user' => null,]) //default values

<button 
    user_id="{{$user->id}}" 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "px-4 py-2 rounded bg-$color-500 text-white"]) }}
>
    {{ $slot }}
</button>

```

```php

<x-button type="submit" color="green" :user="$user">Save</x-button>
<x-button color="red">Delete</x-button>
<x-button>Default</x-button>

```

- Example 2

```php
namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public $type;
    public $message;

    public function __construct($type = 'info', $message = '')
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return view('components.alert');
    }
}


<div class="p-4 border 
    @if($type === 'error') border-red-500 bg-red-100 text-red-700
    @elseif($type === 'success') border-green-500 bg-green-100 text-green-700
    @else border-blue-500 bg-blue-100 text-blue-700
    @endif">
    {{ $message }}
</div>

```

```php
<x-alert type="success" message="Data saved successfully!" />
<x-alert type="error" message="Something went wrong." />
<x-alert message="Just info..." />
```

- Example 3:

```php

public $user;
public $settings;

public function __construct($user, $settings = [])
{
    $this->user = $user;
    $this->settings = $settings;
}

<div>
    <h1>{{ $user->name }}</h1>
    <p>Dark Mode: {{ $settings['dark'] ? 'On' : 'Off' }}</p>
</div>

```

```php 

<x-user-profile :user="$user" :settings="['dark' => true]" />

```