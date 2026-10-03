<?php

return [
    /*
     * Enable event console logging feature
     * When enabled, event details will be captured and logged to browser console
     */
    'enabled' => env('EVENT_CONSOLE_LOGGING_ENABLED', false),

    /*
     * List of event classes to monitor and log
     */
    'events_to_log' => [
        \App\Events\ApplicationStatusChanged::class,
        \App\Events\ApplicationSubmitted::class,
        \App\Events\ScholarshipCreated::class,
        \App\Events\StudentRegistered::class,
    ],

    /*
     * Maximum number of events to store in session
     * When limit is exceeded, oldest events are removed (FIFO)
     */
    'log_limit' => 50,

    /*
     * Clear logs after injection into HTML response
     * Prevents session bloat by removing logs after they're displayed in browser
     */
    'clear_after_injection' => true,
];
