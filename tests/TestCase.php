<?php

namespace BeeInteractive\Snooze\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTemporaryDirectory());

        $this->setUpDatabase($this->app);
    }

    public function getTemporaryDirectory(string $suffix = ''): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'temp'.($suffix == '' ? '' : DIRECTORY_SEPARATOR.$suffix);
    }

    protected function initializeDirectory(string $directory): void
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        File::makeDirectory($directory);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTemporaryDirectory() . '/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:6h5n14Xt8FpY+czGBKB8qbOgRmP2Cv+WxvWuhqAE5kE=');
    }

    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTemporaryDirectory() . '/database.sqlite', null);

        $this->artisan('migrate')->run();

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
