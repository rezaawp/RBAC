<?php

declare(strict_types=1);

namespace App\Extensions\RBAC\System;

use App\Domains\Marketplace\Contracts\UninstallExtensionServiceProviderInterface;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Extensions\RBAC\System\Http\Controllers\RoleController;

class RBACServiceProvider extends ServiceProvider implements UninstallExtensionServiceProviderInterface
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerBindings();
    }

    function registerBindings(): void
    {
        $this->app->bind(
            \App\Extensions\RBAC\System\Repositories\AuthorizationRepository::class,
            \App\Extensions\RBAC\System\Repositories\Implementations\AuthorizationRepositoryImpl::class
        );
        $this->app->bind(
            \App\Extensions\RBAC\System\Repositories\RoleRepository::class,
            \App\Extensions\RBAC\System\Repositories\Implementations\RoleRepositoryImpl::class
        );
        $this->app->bind(
            \App\Extensions\RBAC\System\Repositories\PermissionRepository::class,
            \App\Extensions\RBAC\System\Repositories\Implementations\PermissionRepositoryImpl::class
        );
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerRoutes()
            ->registerMigrations()
            ->publishAssets()
            ->registerComponents();
            // ->registerCommand()
    }

    public function registerComponents(): static
    {
        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/rbac'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rbac.php', 'rbac');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'rbac');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'rbac');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    private function registerRoutes(): static
    {
        $this->router()
            ->group([
                'prefix' => 'rbac',
                'middleware' => ['web', 'auth'],
            ], function (Router $router) {
                $router
                    ->name('dashboard.admin.rbac.')
                    ->group(function (Router $router) {
                        $router->resource('roles', RoleController::class)->except(['destroy']);
                        $router->get("roles/{role}/delete", [RoleController::class, 'destroy'])->name('roles.destroy');
                        $router->post("roles/permissions/save", [RoleController::class, 'rolePermissionSave'])->name('roles.permissions.save');
                    });
            });

        return $this;
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }

    public static function uninstall(): void
    {
        // TODO: Implement uninstall() method.
    }
}
