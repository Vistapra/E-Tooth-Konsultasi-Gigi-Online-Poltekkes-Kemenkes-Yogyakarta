<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Chatify\ChatifyMessenger;

class ChatifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ChatifyMessenger', function ($app) {
            return new ChatifyMessenger();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views/chatify', 'Chatify');

        // Publish assets and config
        if ($this->app->runningInConsole()) {
            $this->publishAssets();
            $this->publishConfig();
        }
    }

    /**
     * Publish Chatify assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->publishes([
            __DIR__ . '/../../public/chatify' => public_path('chatify'),
        ], 'chatify-assets');
    }

    /**
     * Publish Chatify config.
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/chatify.php' => config_path('chatify.php'),
        ], 'chatify-config');
    }
}