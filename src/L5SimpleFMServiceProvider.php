<?php

namespace L5SimpleFM;

use Illuminate\Support\ServiceProvider;
use L5SimpleFM\L5SimpleFM;
use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\HostConnection;

class L5SimpleFMServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->constructAndBindL5SimpleFM();
    }

    protected function constructAndBindL5SimpleFM()
    {
        $this->app->bind('L5SimpleFM\Contracts\FileMakerInterface', function ($app) {
            $username = env('FM_USERNAME', 'myFileMakerDatabaseUsername');
            $password = env('FM_PASSWORD', 'myFileMakerDatabasePassword');
            $host = env('FM_HOST', '127.0.0.1');
            $database = env('FM_DATABASE', 'myFileMakerDatabaseFileName');

            $hostConnection = new HostConnection($host, $database, $username, $password);
            return new L5SimpleFM(new Adapter($hostConnection));
        });
    }
}
