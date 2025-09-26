<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->singleton(\App\Services\Clients\StudentPortalClient::class, function () {
        return new \App\Services\Clients\StudentPortalClient(
            baseUrl: config('services.student_portal.url'),
            token:   config('services.student_portal.token')

        );
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

     Gate::before(function ($user, string $ability = null) {
        return $user->hasRole('super-admin') ? true : null;
    });


    }
}
