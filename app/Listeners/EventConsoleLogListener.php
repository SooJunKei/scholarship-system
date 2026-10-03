<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Events\ScholarshipCreated;
use App\Events\StudentRegistered;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

class EventConsoleLogListener
{
    /**
     * Handle the event
     *
     * @param mixed $event
     * @return void
     */
    public function handle(mixed $event): void
    {
        // Check if feature is enabled
        if (!config('event_console.enabled')) {
            return;
        }

        // Capture event details
        $eventData = $this->captureEventDetails($event);

        if ($eventData !== null) {
            $this->storeInSession($eventData);
        }
    }

    /**
     * Capture event-specific details
     *
     * @param mixed $event
     * @return array|null
     */
    private function captureEventDetails(mixed $event): ?array
    {
        $eventClass = get_class($event);
        $eventName = class_basename($event);

        // Common data for all events
        $data = [
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'event_class' => $eventClass,
            'event_name' => $eventName,
            'handlers' => $this->getEventHandlers($eventClass),
        ];

        // Event-specific data extraction
        if ($event instanceof ApplicationStatusChanged) {
            $data = array_merge($data, [
                'application_id' => $event->application->id ?? null,
                'student_name' => $event->application->student->full_name ?? 'Unknown',
                'scholarship_name' => $event->application->scholarship->scholarship_name ?? 'Unknown',
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);
        } elseif ($event instanceof ApplicationSubmitted) {
            $data = array_merge($data, [
                'application_id' => $event->application->id ?? null,
                'student_name' => $event->application->student->full_name ?? 'Unknown',
                'scholarship_name' => $event->application->scholarship->scholarship_name ?? 'Unknown',
            ]);
        } elseif ($event instanceof ScholarshipCreated) {
            // For other event types, just store base data for now
            // Can be extended in future
        } elseif ($event instanceof StudentRegistered) {
            // For other event types, just store base data for now
            // Can be extended in future
        }

        return $data;
    }

    /**
     * Get list of handlers/listeners for the event
     *
     * @param string $eventClass
     * @return array
     */
    private function getEventHandlers(string $eventClass): array
    {
        try {
            $listeners = Event::getListeners($eventClass);
            $handlers = [];

            foreach ($listeners as $listener) {
                $name = $this->extractHandlerName($listener);
                if ($name && $name !== 'EventConsoleLogListener') {
                    $handlers[] = $name;
                }
            }

            return $handlers;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract handler class name from listener
     *
     * @param mixed $listener
     * @return string|null
     */
    private function extractHandlerName(mixed $listener): ?string
    {
        // String format: "ClassName@method" or "ClassName"
        if (is_string($listener)) {
            return explode('@', $listener)[0];
        }

        // Array format: [object|class, 'method']
        if (is_array($listener) && count($listener) >= 1) {
            $first = $listener[0];
            $method = $listener[1] ?? null;
            
            if (is_object($first)) {
                $class = class_basename($first);
            } elseif (is_string($first)) {
                $class = class_basename($first);
            } else {
                return null;
            }
            
            // If method is specified and different from __invoke, include it
            if ($method && $method !== '__invoke') {
                return "{$class}@{$method}";
            }
            
            return $class;
        }

        // Closure or callable - skip lambda functions, return null instead of "Closure"
        if (is_callable($listener)) {
            if (is_object($listener)) {
                $class = class_basename($listener);
                // Skip closures and lambda functions
                if ($class === 'Closure' || get_class($listener) === 'Closure') {
                    return null;
                }
                return $class;
            }
        }

        return null;
    }

    /**
     * Store event data in session with limit enforcement
     *
     * @param array $eventData
     * @return void
     */
    private function storeInSession(array $eventData): void
    {
        // Get existing logs or start new array
        $logs = session('event_console_logs', []);

        // Add new event
        $logs[] = $eventData;

        // Enforce log limit (FIFO - remove oldest events)
        $limit = config('event_console.log_limit', 50);
        if (count($logs) > $limit) {
            $logs = array_slice($logs, -$limit);
        }

        // Store back in session
        session(['event_console_logs' => $logs]);
    }
}
