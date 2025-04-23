<?php

namespace App\Providers;

use App\Models\Reply;
use App\Models\Thread;
use App\Policies\ThreadPolicy;
use App\Policies\ReplyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Thread::class => ThreadPolicy::class,
        Reply::class => ReplyPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
