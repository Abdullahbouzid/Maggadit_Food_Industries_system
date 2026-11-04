<?php

declare(strict_types=1);

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UnitOfWork
{
    /**
     * Execute a callback within a transaction and fire events after commit.
     *
     * @param Closure(): mixed $callback
     * @param array<int, array{event: object, listeners?: array<int, Closure>}> $deferredEvents
     * @return mixed
     */
    public static function execute(Closure $callback, array $deferredEvents = []): mixed
    {
        return DB::transaction(function () use ($callback, $deferredEvents) {
            try {
                $result = $callback();
            } catch (\Throwable $e) {
                throw $e;
            }

            DB::afterCommit(function () use ($deferredEvents) {
                foreach ($deferredEvents as $deferred) {
                    Event::dispatch($deferred['event']);
                }
            });

            return $result;
        });
    }

    /**
     * Execute a callback within a transaction, registering events
     * that will only fire if the transaction commits successfully.
     *
     * @param Closure $callback Receives a callback to register events: registerEvent(object $event)
     * @return mixed
     */
    public static function transactional(Closure $callback): mixed
    {
        $events = [];

        $registerEvent = function (object $event) use (&$events) {
            $events[] = $event;
        };

        return DB::transaction(function () use ($callback, $registerEvent, &$events) {
            try {
                $result = $callback($registerEvent);
            } catch (\Throwable $e) {
                throw $e;
            }

            DB::afterCommit(function () use ($events) {
                foreach ($events as $event) {
                    Event::dispatch($event);
                }
            });

            return $result;
        });
    }
}
