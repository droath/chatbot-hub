<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Livewire\Chatbot;
use Livewire\Livewire;
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
            ->hasMigration('create_chatbot_hub_user_messages_table');
    }

    public function packageRegistered(): void
    {
        $this->app->register(LivewireServiceProvider::class);
    }

    public function packageBooted(): void
    {
        Livewire::component('chatbot', Chatbot::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('../public/vendor/chatbot-hub') => public_path('vendor/chatbot-hub'),
            ], 'chatbot-hub-assets');
        }
    }
}
