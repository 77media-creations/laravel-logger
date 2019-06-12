<?php

namespace jeremykenedy\LaravelLogger;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use jeremykenedy\LaravelLogger\App\Http\Middleware\LogActivity;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The event listener mappings for the applications auth scafolding.
     *
     * @var array
     */
    protected $listeners = [

        'Illuminate\Auth\Events\Attempting' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogAuthenticationAttempt',
        ],

        'Illuminate\Auth\Events\Authenticated' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogAuthenticated',
        ],

        'Illuminate\Auth\Events\Login' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogSuccessfulLogin',
        ],

        'Illuminate\Auth\Events\Failed' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogFailedLogin',
        ],

        'Illuminate\Auth\Events\Logout' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogSuccessfulLogout',
        ],

        'Illuminate\Auth\Events\Lockout' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogLockout',
        ],

        'Illuminate\Auth\Events\PasswordReset' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogPasswordReset',
        ],

    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->middlewareGroup('activity', [LogActivity::class]);
        $this->loadTranslationsFrom(__DIR__.'/resources/lang/', 'LaravelLogger');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        // $this->loadViewsFrom(__DIR__.'/resources/views/', 'LaravelLogger');
        $this->loadViewsFrom(resource_path('views/vendor/laravellogger'), 'LaravelLogger');
        // $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        if (file_exists(config_path('laravel-logger.php'))) {
            $this->mergeConfigFrom(config_path('laravel-logger.php'), 'LaravelLogger');
        } else {
            $this->mergeConfigFrom(__DIR__.'/config/laravel-logger.php', 'LaravelLogger');
        }
        // My custom - to publish database migration (needed to change integer to uuid format for userId column) part 1 (part 2 down there)
        if (file_exists(database_path('migrations/2017_11_04_103444_create_laravel_logger_activity_table.php'))) {
            $this->loadMigrationsFrom(database_path('migrations'));
        } else {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        }
        // My custom end
        $this->registerEventListeners();
        $this->publishFiles();
    }

    /**
     * Get the list of listeners and events.
     *
     * @return array
     */
    private function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Register the list of listeners and events.
     *
     * @return void
     */
    private function registerEventListeners()
    {
        $listeners = $this->getListeners();
        foreach ($listeners as $listenerKey => $listenerValues) {
            foreach ($listenerValues as $listenerValue) {
                \Event::listen($listenerKey,
                    $listenerValue
                );
            }
        }
    }

    /**
     * Publish files for Laravel Logger.
     *
     * @return void
     */
    private function publishFiles()
    {
        $publishTag = 'laravellogger';

        $this->publishes([
            __DIR__.'/config/laravel-logger.php' => base_path('config/laravel-logger.php'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/'.$publishTag),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/resources/lang' => base_path('resources/lang/vendor/'.$publishTag),
        ], $publishTag);


        // My custom - to publish database migration (needed to change integer to uuid format for userId column) part 2
        $this->publishes([
            __DIR__.'/database/migrations' => base_path('database/migrations'),
        ], $publishTag);
    }
}
