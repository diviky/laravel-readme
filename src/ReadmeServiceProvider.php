<?php

declare(strict_types=1);

namespace Diviky\Readme;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReadmeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootRoutes();
        $this->bootViews();

        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->path() . '/config/readme.php', 'readme');
    }

    protected function path()
    {
        return __DIR__ . '/..';
    }

    /**
     * @return array
     */
    protected function routesConfig()
    {
        return [
            'prefix' => config('readme.docs.route'),
            'namespace' => 'Diviky\Readme\Http\Controllers',
            'domain' => config('readme.domain', null),
            'as' => 'readme.',
            'middleware' => config('readme.docs.middleware'),
        ];
    }

    protected function console(): void
    {
        $this->publishes([
            $this->path() . '/config/readme.php' => config_path('readme.php'),
        ], 'config');

        $this->publishes([
            $this->path() . '/resources/views/' => resource_path('views'),
        ], 'views');
    }

    protected function bootRoutes(): self
    {
        Route::group($this->routesConfig(), function (): void {
            $this->loadRoutesFrom($this->path() . '/routes/web.php');
        });

        return $this;
    }

    protected function bootViews(): self
    {
        $this->loadViewsFrom($this->path() . '/resources/views', 'readme');

        return $this;
    }
}
