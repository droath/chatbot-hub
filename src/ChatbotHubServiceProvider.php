<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Plugins\AgentWorkerPluginManager;
use Droath\ChatbotHub\Services\MemoryManager;
use Droath\ChatbotHub\Services\MemoryCleanupService;
use Droath\ChatbotHub\Console\Commands\MemoryCleanupCommand;
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
            ->discoversMigrations()
            ->hasCommand(MemoryCleanupCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->register(LivewireServiceProvider::class);

        $this->app->singleton(ChatbotHub::class, static function () {
            return new ChatbotHub();
        });

        $this->app->singleton(AgentWorkerPluginManager::class, function () {
            return new AgentWorkerPluginManager();
        });

        $this->app->singleton(MemoryManager::class, function () {
            return new MemoryManager();
        });

        $this->app->singleton(MemoryCleanupService::class, function () {
            return new MemoryCleanupService();
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
