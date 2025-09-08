<?php

namespace App\Providers;

use App\Models\Visit;
use App\Policies\VisitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Visit::class => VisitPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
