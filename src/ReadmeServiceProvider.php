<?php

declare(strict_types=1);

namespace Diviky\Readme;

use Diviky\Readme\Component\Indexes;
use Diviky\Readme\Console\Commands\IndexDocuments;
use Diviky\Readme\Livewire\Docs\Search;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ReadmeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootRoutes();
        $this->bootViews();
        $this->bootMigrations();
        $this->bootLivewire();

        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->path() . '/config/readme.php', 'readme');
        $this->mergeConfigFrom($this->path() . '/config/markdown.php', 'markdown');

        Blade::component('readme::indexes', Indexes::class);
    }

    protected function bootLivewire(): void
    {
        Livewire::component('readme.docs.search', Search::class);
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

        $this->commands([
            IndexDocuments::class,
        ]);
    }

    protected function bootMigrations(): void
    {
        $this->loadMigrationsFrom($this->path() . '/database/migrations');
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
