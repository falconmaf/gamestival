<?php

namespace Wave\Plugins\Discussions;

use Livewire\Livewire;
use Wave\Plugins\Plugin;
use Illuminate\Support\Facades\File;

class DiscussionsPlugin extends Plugin
{
    protected $name = 'Discussions';

    protected $description = 'A forum discussions package built to seamlessly plug-n-play into your application.';

    public function register()
    {
        
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'discussions');
        $this->loadTranslationsFrom(__DIR__ . '/src/Lang', 'discussions');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
      
        $this->publishes([
            __DIR__ . '/config/discussions.php' => config_path('discussions.php'),
        ], 'discussions_config');

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
        ], 'discussions_migrations');

        $this->publishes([
            __DIR__ . '/database/seeders/' => database_path('seeders'),
        ], 'discussions_seeders');

        $this->publishes([
            __DIR__ . '/src/Lang' => resource_path('lang/discussions'),
        ], 'discussions_lang');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        Livewire::component('discussion', \Wave\Plugins\Discussions\Components\Discussion::class);
        Livewire::component('discussions', \Wave\Plugins\Discussions\Components\Discussions::class);
        Livewire::component('discussion-posts', \Wave\Plugins\Discussions\Components\DiscussionPosts::class);
    }

    public function getPluginInfo(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->getPluginVersion()
        ];
    }

    public function getPluginVersion(): array
    {
        return File::json(__DIR__ . '/version.json');
    }

    public function getPostActivationCommands(): array
    {
        return [
            'migrate --path=' . $this->getMigrationsPath(),
            function() {
                // You can also include closure for complex operations
                // For example, create necessary directories, etc.
            },
        ];
    }

    private function getMigrationsPath(): string
    {
        return 'plugins/discussions/database/migrations';
    }
}