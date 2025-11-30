<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PaluwaganRepositoryInterface;
use App\Repositories\PaluwaganRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\OrderRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaluwaganRepositoryInterface::class, PaluwaganRepository::class);
         $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(
        \App\Services\UserManagementServiceInterface::class,
        \App\Services\UserManagementService::class
    );

    // Required dependencies
    $this->app->bind(
        \App\Repositories\UserRepositoryInterface::class,
        \App\Repositories\UserRepository::class
    );

    $this->app->bind(
        \App\Factories\UserFactoryInterface::class,
        \App\Factories\ConcreteUserFactory::class
    );

    $this->app->bind(
        \App\Repositories\RolePrivilegeRepositoryInterface::class,
        \App\Repositories\RolePrivilegeRepository::class
    );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
