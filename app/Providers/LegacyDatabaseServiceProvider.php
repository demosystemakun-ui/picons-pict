<?php

namespace App\Providers;

use App\Database\Grammar\LegacyMySqlGrammar;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\ServiceProvider;

class LegacyDatabaseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['events']->listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            $grammar = new LegacyMySqlGrammar($event->connection);
            $event->connection->setSchemaGrammar($grammar);
        });
    }
}