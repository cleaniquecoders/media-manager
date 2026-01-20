<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Auto-login for development when serving the workbench app
        if (! app()->runningInConsole() && ! Auth::check()) {
            $this->autoLogin();
        }
    }

    protected function autoLogin(): void
    {
        $user = User::first();

        if ($user) {
            Auth::login($user);
        }
    }
}
