<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(CommandStarting::class, function ($event) {
            if (Str::startsWith($event->command, 'migrate')) {
                $host = DB::connection()->getConfig()['host'];
                Log::channel('stderr')->info('Connecting to ' . $host);
                if (app()->environment() != 'production' && $host != "127.0.0.1") {
                    Log::channel('stderr')->error('Migration command disabled!');
                    die();
                }
            }
        });
        Event::listen(MigrationsStarted::class, function ($event) {
            $host = DB::connection()->getConfig()['host'];
            Log::channel('stderr')->info('Connecting to ' . $host);
            if (app()->environment() != 'production' && $host != "127.0.0.1") {
                Log::channel('stderr')->error('Migration command disabled!');
                die();
            }
        });

        Event::listen(MigrationStarted::class, function ($event) {
            $host = DB::connection()->getConfig()['host'];
            Log::channel('stderr')->info('Connecting to ' . $host);
            if (app()->environment() != 'production' && $host != "127.0.0.1") {
                Log::channel('stderr')->error('Migration command disabled!');
                die();
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
