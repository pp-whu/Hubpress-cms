<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * EventDispatcher
 *
 * Lightweight event/listener system.
 * Supports:
 * - Multiple listeners per event
 * - Priority ordering
 * - Wildcard events (*)
 * - Stopping propagation
 *
 * Usage:
 *   $dispatcher->listen('user.created', fn($event) => doSomething($event));
 *   $dispatcher->dispatch('user.created', $user);
 *
 * @package HuberCMS\Core
 */
final class EventDispatcher
{
    /** @var array<string, array<int, array{callable, int}>> */
    private array $listeners = [];

    // =========================================================
    // Registration
    // =========================================================

    /**
     * Registers a listener for an event.
     *
     * @param int $priority Higher = called first (default 0)
     */
    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][] = [$listener, $priority];

        // Sort by priority descending
        usort(
            $this->listeners[$event],
            fn($a, $b) => $b[1] <=> $a[1]
        );
    }

    /**
     * Registers a listener that executes only once.
     */
    public function once(string $event, callable $listener, int $priority = 0): void
    {
        $wrapper = null;
        $wrapper = function () use ($event, $listener, &$wrapper) {
            $result = $listener(...func_get_args());
            $this->removeListener($event, $wrapper);
            return $result;
        };

        $this->listen($event, $wrapper, $priority);
    }

    /**
     * Removes a specific listener from an event.
     */
    public function removeListener(string $event, callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_values(
            array_filter(
                $this->listeners[$event],
                fn($entry) => $entry[0] !== $listener
            )
        );
    }

    /**
     * Removes all listeners for an event (or all events if null).
     */
    public function forget(?string $event = null): void
    {
        if ($event === null) {
            $this->listeners = [];
        } else {
            unset($this->listeners[$event]);
        }
    }

    // =========================================================
    // Dispatch
    // =========================================================

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param mixed $payload Data passed to each listener
     */
    public function dispatch(string $event, mixed $payload = null): void
    {
        $listeners = $this->getListenersFor($event);

        foreach ($listeners as [$callable]) {
            $result = $callable($payload, $event);

            // Returning false stops propagation
            if ($result === false) {
                break;
            }
        }
    }

    /**
     * Dispatches and returns aggregated results from all listeners.
     *
     * @param mixed $payload
     * @return mixed[]
     */
    public function filter(string $event, mixed $payload): array
    {
        $results = [];

        foreach ($this->getListenersFor($event) as [$callable]) {
            $results[] = $callable($payload, $event);
        }

        return $results;
    }

    /**
     * Checks whether any listener is registered for an event.
     */
    public function hasListeners(string $event): bool
    {
        return !empty($this->getListenersFor($event));
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Returns all listeners for an event, including wildcard (*) listeners.
     *
     * @return array<int, array{0:callable, 1:int}>
     */
    private function getListenersFor(string $event): array
    {
        $direct   = $this->listeners[$event] ?? [];
        $wildcard = $this->listeners['*'] ?? [];

        $all = array_merge($direct, $wildcard);

        usort($all, fn($a, $b) => $b[1] <=> $a[1]);

        return $all;
    }
}
