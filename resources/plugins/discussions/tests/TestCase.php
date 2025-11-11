<?php

namespace Wave\Plugins\Discussions\Tests;

use Wave\Plugins\Discussions\DiscussionsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            DiscussionsServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('discussions.user.namespace', 'Wave\Plugins\Discussions\Models\User');

        $app['config']->set('discussions.categories', [
            'general' => [
                'title' => 'General',
                'icon' => 'fas fa-comment',
            ],
            'bug' => [
                'title' => 'Bug',
                'icon' => 'fas fa-bug',
            ],
            'feature' => [
                'title' => 'Feature',
                'icon' => 'fas fa-lightbulb',
            ],
        ]);
    }


    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/pestphp/pest-plugin-laravel/resources/database/migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        // php artisan tall:install
    }
}
