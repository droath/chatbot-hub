<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;
use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;
use Droath\ChatbotHub\Memory\Configuration\MemoryConfiguration;
use Droath\ChatbotHub\Memory\MemoryStrategyFactory;
use Droath\ChatbotHub\Plugins\AgentWorkerPluginManager;
use Livewire\LivewireServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChatbotHubServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('chatbot-hub')
            ->hasViews()
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations(['create_chatbot_messages']);
    }

    public function packageRegistered(): void
    {
        $this->app->register(LivewireServiceProvider::class);

        $this->app->singleton(ChatbotHub::class, static function () {
            return new ChatbotHub;
        });

        $this->app->singleton(AgentWorkerPluginManager::class, function () {
            return new AgentWorkerPluginManager();
        });

        // Register memory system components
        $this->app->singleton(MemoryConfiguration::class, function ($app) {
            $config = $app['config']->get('chatbot-hub.memory', []);
            return new MemoryConfiguration($config);
        });

        $this->app->singleton(MemoryStrategyFactory::class, function ($app) {
            return new MemoryStrategyFactory($app->make(MemoryConfiguration::class));
        });

        // Bind default memory interfaces
        $this->app->bind(AgentMemoryInterface::class, function ($app) {
            return $app->make(MemoryStrategyFactory::class)->createDefault();
        });

        $this->app->bind(MemoryStrategyInterface::class, function ($app) {
            return $app->make(MemoryStrategyFactory::class)->createDefault();
        });
    }

    public function packageBooted(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('../public/vendor/chatbot-hub') => public_path('vendor/chatbot-hub'),
            ], 'chatbot-hub-assets');
        }
    }
}
