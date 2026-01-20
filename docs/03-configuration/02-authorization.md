# Authorization

Media Manager supports Laravel's authorization gates to control access to the media browser.

## Default Behavior

By default, all authenticated users can access the media manager:

```php
'authorization' => [
    'gate' => null,
],
```

## Setting Up a Gate

### Step 1: Define the Gate

In your `AuthServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-media', function ($user) {
            // Allow admins and editors
            return $user->hasRole(['admin', 'editor']);
        });
    }
}
```

### Step 2: Configure Media Manager

Update your `config/media-manager.php`:

```php
'authorization' => [
    'gate' => 'manage-media',
],
```

## Authorization Examples

### Role-Based Access

```php
Gate::define('manage-media', function ($user) {
    return $user->hasRole('admin');
});
```

### Permission-Based Access

If using Spatie Permission:

```php
Gate::define('manage-media', function ($user) {
    return $user->hasPermissionTo('manage media');
});
```

### Team-Based Access

```php
Gate::define('manage-media', function ($user) {
    return $user->currentTeam?->hasFeature('media-library');
});
```

### Subscription-Based Access

```php
Gate::define('manage-media', function ($user) {
    return $user->subscribed('pro');
});
```

## Access Denied Response

When a user fails authorization, they receive a 403 Forbidden response.

To customize this behavior, you can override the middleware.

### Custom Middleware

Create a custom middleware:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuthorizeMediaManager
{
    public function handle(Request $request, Closure $next)
    {
        $gate = config('media-manager.authorization.gate');

        if ($gate && Gate::denies($gate)) {
            // Custom redirect instead of 403
            return redirect()->route('dashboard')
                ->with('error', 'You do not have access to the media manager.');
        }

        return $next($request);
    }
}
```

Register in your service provider:

```php
$this->app->bind(
    \CleaniqueCoders\MediaManager\Http\Middleware\AuthorizeMediaManager::class,
    \App\Http\Middleware\AuthorizeMediaManager::class
);
```

## Component-Level Authorization

You can also add authorization checks at the component level in your Blade views:

```blade
@can('manage-media')
    <livewire:media-manager::uploader :model="$post" collection="gallery" />
@endcan
```

Or conditionally show the browser link:

```blade
@can('manage-media')
    <a href="{{ url('media-manager') }}">Media Library</a>
@endcan
```

## API Authorization

If building an API, use policy methods:

```php
<?php

namespace App\Policies;

use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $user->id === $media->model->user_id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->id === $media->model->user_id
            || $user->hasRole('admin');
    }
}
```

Register in `AuthServiceProvider`:

```php
protected $policies = [
    Media::class => MediaPolicy::class,
];
```

## Related Documentation

- [Configuration Reference](01-configuration-reference.md) - All config options
- [Media Browser](../02-components/01-media-browser.md) - Browser component
