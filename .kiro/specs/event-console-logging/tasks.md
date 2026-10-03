# Event Console Logging - Tasks

## Overview
Implementation tasks for the event-console-logging feature. Each task is focused, actionable, and includes specific file paths, method signatures, and acceptance criteria tied to requirements.

**Estimated Duration:** 2-2.5 hours total
**Estimated Per-Task Duration:** 20-35 minutes each

---

## Task 1: Setup - Create Configuration File

**Objective:** Create the configuration file that controls the event console logging feature.

**File to Create:** `config/event_console.php`

**Acceptance Criteria:**
- ✅ Configuration file exists at `config/event_console.php`
- ✅ `enabled` option is controlled by `EVENT_CONSOLE_LOGGING_ENABLED` environment variable with default value `false`
- ✅ `events_to_log` array includes all four events: ApplicationStatusChanged, ApplicationSubmitted, ScholarshipCreated, StudentRegistered
- ✅ `log_limit` is set to 50 (maximum events in session)
- ✅ `clear_after_injection` is set to true (prevents session bloat)
- ✅ Configuration can be accessed via `config('event_console.enabled')` and similar

**Configuration Structure (from Design):**
```php
return [
    'enabled' => env('EVENT_CONSOLE_LOGGING_ENABLED', false),
    'events_to_log' => [
        \App\Events\ApplicationStatusChanged::class,
        \App\Events\ApplicationSubmitted::class,
        \App\Events\ScholarshipCreated::class,
        \App\Events\StudentRegistered::class,
    ],
    'log_limit' => 50,
    'clear_after_injection' => true,
];
```

**References:**
- Requirements 6: Conditional Enable/Disable
- Design: Configuration File section

**Validation:**
- Config file is syntactically valid PHP
- All event classes can be resolved
- Config loads without errors via `php artisan config:cache` (optional)

---

## Task 2: Listener - Create EventConsoleLogListener

**Objective:** Create the event listener that captures event data and stores it in session.

**File to Create:** `app/Listeners/EventConsoleLogListener.php`

**Class Structure:**
```php
namespace App\Listeners;

class EventConsoleLogListener
{
    public function handle(mixed $event): void { }
    
    private function captureEventDetails(mixed $event): array { }
    
    private function getEventHandlers(string $eventClass): array { }
    
    private function storeInSession(array $data): void { }
}
```

**Acceptance Criteria:**
- ✅ Listener class exists with `handle(mixed $event): void` method
- ✅ `handle()` method extracts event details via `captureEventDetails()`
- ✅ For ApplicationStatusChanged: captures application_id, student_name, scholarship_name, old_status, new_status
- ✅ For ApplicationSubmitted: captures application_id, student_name, scholarship_name
- ✅ All events capture: event_name (via `class_basename()`), timestamp (ISO 8601 format)
- ✅ All events capture handler list via `Event::getListeners()` and class_basename extraction
- ✅ Session storage uses key `event_console_logs` with array structure
- ✅ Log limit is enforced: when count exceeds `config('event_console.log_limit')`, oldest events removed (FIFO)
- ✅ Listener imports `Carbon`, `Event` facade, and uses proper namespace

**Session Storage Format (from Design):**
```php
session('event_console_logs') => [
    [
        'timestamp' => '2024-01-15 14:30:45',
        'event_class' => 'App\Events\ApplicationStatusChanged',
        'event_name' => 'ApplicationStatusChanged',
        'application_id' => 123,
        'student_name' => 'Juan Dela Cruz',
        'scholarship_name' => 'SLSU Merit',
        'old_status' => 'Pending',
        'new_status' => 'Approved',
        'handlers' => ['SendStatusNotification', 'UpdateApplicationStatistics', ...]
    ],
    // ... more events up to log_limit
]
```

**Implementation Notes:**
- Use `Event::getListeners(get_class($event))` to retrieve handlers
- Extract handler names using `class_basename()` for class references
- Handle closures gracefully (may show "Closure" or similar)
- Use Carbon for timestamp: `Carbon::now()->format('Y-m-d H:i:s')`
- Handle both ApplicationStatusChanged (has oldStatus, newStatus) and ApplicationSubmitted (no status) events

**References:**
- Requirements 1: Event Data Collection
- Requirements 2: Session Storage
- Design: EventConsoleLogListener section, Event Data Capture section

**Validation:**
- Listener can be instantiated without errors
- Can be registered via `Event::listen()`
- Session storage works in tests with in-memory session

---

## Task 3: Middleware - Create EventConsoleMiddleware

**Objective:** Create middleware that injects JavaScript console logging into HTML responses.

**File to Create:** `app/Http/Middleware/EventConsoleMiddleware.php`

**Class Structure:**
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventConsoleMiddleware
{
    public function handle(Request $request, Closure $next): Response { }
    
    private function isEnabled(): bool { }
    
    private function shouldProcess(Response $response): bool { }
    
    private function generateConsoleScript(array $logs): string { }
    
    private function injectScript(string $html, string $script): string { }
    
    private function formatEventLog(array $event): string { }
}
```

**Acceptance Criteria:**
- ✅ Middleware class exists with `handle()` method following standard signature
- ✅ `isEnabled()` checks `config('event_console.enabled')`
- ✅ If not enabled, response passes through unchanged
- ✅ `shouldProcess()` checks if response Content-Type contains 'text/html'
- ✅ Retrieves session event logs via `session('event_console_logs', [])`
- ✅ `generateConsoleScript()` creates wrapped JavaScript with try-catch block
- ✅ Generated script uses console.log with `%c` styling for color-coded output
- ✅ Each event displays formatted console output with emoji (🚀), event name, and all fields
- ✅ `injectScript()` inserts JavaScript before `</body>` tag using `str_replace()`
- ✅ If `</body>` tag not found, script is appended at end of response
- ✅ If `config('event_console.clear_after_injection')` is true, clears session logs after injection
- ✅ Event names, IDs, and handler names are properly escaped in JavaScript strings

**JavaScript Output Format (from Design):**
```javascript
<script>
(function() {
    try {
        console.log('%c🚀 EVENT: ApplicationStatusChanged', 'font-weight: bold; color: #2563eb; font-size: 14px;');
        console.log('%cApplication ID:%c 123', 'font-weight: bold;', 'color: #059669;');
        console.log('%cStudent:%c Juan Dela Cruz', 'font-weight: bold;', 'color: #059669;');
        console.log('%cScholarship:%c SLSU Merit', 'font-weight: bold;', 'color: #059669;');
        console.log('%cStatus:%c Pending → Approved', 'font-weight: bold;', 'color: #dc2626;');
        console.log('%cHandlers:%c SendStatusNotification, UpdateApplicationStatistics, ...', 'font-weight: bold;', 'color: #7c3aed;');
        console.log('');
    } catch(e) {
        // Silently fail if console unavailable
    }
})();
</script>
```

**Implementation Notes:**
- Use `addslashes()` or similar to escape JavaScript strings
- Use IIFE (Immediately Invoked Function Expression) for scope isolation
- Include try-catch for browser console availability
- Color scheme: blue (#2563eb) for event name, green (#059669) for values, red (#dc2626) for status changes, purple (#7c3aed) for handlers
- Format status changes as "Pending → Approved"
- Handlers as comma-separated list
- Each event ends with empty console.log for visual separation
- Handle response body extraction carefully (may not always have string content)

**References:**
- Requirements 3: JavaScript Console Output Injection
- Design: EventConsoleMiddleware section, JavaScript Console Output section

**Validation:**
- Middleware can be instantiated without errors
- Script generation produces valid JavaScript (testable)
- HTML injection correctly places script before closing body tag
- String escaping prevents XSS vulnerabilities

---

## Task 4: Registration - Register Components in Framework

**Objective:** Register the listener and middleware with Laravel framework.

**Files to Modify:**
1. `app/Providers/AppServiceProvider.php` (register listener)
2. `app/Http/Middleware/Kernel.php` (register middleware)

**Acceptance Criteria for AppServiceProvider:**
- ✅ `boot()` method checks if `config('event_console.enabled')` is true
- ✅ If enabled, registers listener using `Event::listen()` with all events from `config('event_console.events_to_log')`
- ✅ Registration uses: `Event::listen(array_values(config('event_console.events_to_log', [])), EventConsoleLogListener::class)`
- ✅ Imports `Event` facade at top of file
- ✅ Imports `EventConsoleLogListener` class

**Code Snippet (from Design):**
```php
use Illuminate\Support\Facades\Event;
use App\Listeners\EventConsoleLogListener;

public function boot(): void
{
    if (config('event_console.enabled')) {
        Event::listen(
            array_values(config('event_console.events_to_log', [])),
            EventConsoleLogListener::class
        );
    }
}
```

**Acceptance Criteria for Kernel:**
- ✅ `EventConsoleMiddleware::class` is added to `$middleware` array (global HTTP middleware)
- ✅ Middleware is positioned appropriately (typically toward end of array)
- ✅ Middleware is imported at top of Kernel file

**References:**
- Design: Middleware Registration, Event Listener Registration sections
- Design: Implementation Checklist (items 4-5)

**Validation:**
- Application boots without errors
- `php artisan config:clear` and server restart work without issues
- Event listener is registered when enabled
- Middleware is in global middleware stack

---

## Task 5: Testing - Comprehensive Feature Testing

**Objective:** Verify the complete event console logging feature works end-to-end and doesn't break existing functionality.

**Testing Scope:**

### 5.1 Unit Tests - Listener (create `tests/Unit/Listeners/EventConsoleLogListenerTest.php`)
- ✅ Test listener captures ApplicationStatusChanged event data correctly
- ✅ Test listener captures ApplicationSubmitted event data correctly
- ✅ Test timestamp is stored in correct format (Y-m-d H:i:s)
- ✅ Test event_name extracted via class_basename
- ✅ Test handlers list is populated from Event::getListeners()
- ✅ Test session storage respects log_limit configuration
- ✅ Test old events removed when limit exceeded (FIFO)
- ✅ Test events without handlers handled gracefully

**References:**
- Requirements 1: Event Data Collection
- Requirements 2: Session Storage

### 5.2 Unit Tests - Middleware (create `tests/Unit/Middleware/EventConsoleMiddlewareTest.php`)
- ✅ Test middleware disabled by default (no processing when enabled=false)
- ✅ Test middleware skips non-HTML responses (Content-Type not text/html)
- ✅ Test script injection before </body> tag
- ✅ Test graceful handling when </body> missing (appends to end)
- ✅ Test session logs cleared after injection (when clear_after_injection=true)
- ✅ Test script is valid JavaScript with try-catch wrapper
- ✅ Test event data properly escaped in JavaScript strings
- ✅ Test emoji and colors present in output

**References:**
- Requirements 3: JavaScript Console Output Injection
- Requirements 6: Conditional Enable/Disable

### 5.3 Integration Tests (create `tests/Feature/EventConsoleLoggingTest.php`)
- ✅ Test full flow: dispatch event → listener captures → middleware injects → response includes script
- ✅ Test multiple events in single request → all appear in console output
- ✅ Test console output format matches design (emoji, colors, field names)
- ✅ Test feature disabled doesn't add any overhead (no session logs, no script injection)
- ✅ Test with feature enabled then disabled → no regression

**References:**
- Requirements 3: JavaScript Console Output Injection
- Requirements 5: Non-Interference with Core Functionality

### 5.4 Browser/Manual Testing
- ✅ Enable feature in `.env`: `EVENT_CONSOLE_LOGGING_ENABLED=true`
- ✅ Load a page that triggers ApplicationStatusChanged event (e.g., dashboard or application page)
- ✅ Open browser DevTools (F12) → Console tab
- ✅ Verify console output displays with correct emoji (🚀) and styling
- ✅ Verify event name, application ID, student name, and status change all visible
- ✅ Verify handler list displays correctly
- ✅ Repeat with ApplicationSubmitted event
- ✅ Disable feature and verify no console script appears

**Acceptance Criteria:**
- ✅ All unit tests pass
- ✅ All integration tests pass
- ✅ Browser console displays formatted output correctly
- ✅ No errors in browser console
- ✅ No functionality broken (existing pages load, forms submit, data persists)
- ✅ No performance degradation

**References:**
- Requirements 5: Non-Interference with Core Functionality
- Design: Testing Strategy section

---

## Verification Checklist

After completing all tasks:

- [x] Config file loads without errors
- [x] Listener captures all event types
- [x] Middleware injects JavaScript correctly
- [x] Framework registrations complete without errors
- [x] All tests pass
- [x] Browser console displays output as expected
- [x] Feature can be enabled/disabled via config
- [x] Disabled feature has zero overhead
- [x] No breaking changes to existing functionality
- [x] All acceptance criteria from requirements met

---

## Task Dependencies

```
Task 1 (Config) →
  ├→ Task 2 (Listener) ↘
  │                       → Task 4 (Registration) → Task 5 (Testing)
  └→ Task 3 (Middleware) ↗
```

**Task 1** must be completed before Tasks 2, 3, and 4 (they all depend on config).
**Tasks 2 and 3** can be completed in parallel.
**Task 4** must be completed after Tasks 2 and 3.
**Task 5** should be completed last to validate entire feature.

---

## Notes for Implementer

- Review existing listeners (e.g., `app/Listeners/LogApplicationActivity.php`) for code style consistency
- Review existing middleware (e.g., `app/Http/Middleware/AdminMiddleware.php`) for structural consistency
- Refer to `app/Events/ApplicationStatusChanged.php` to understand event structure and how to access properties
- Use existing event classes as reference for handler discovery via `Event::getListeners()`
- Follow Laravel conventions for namespace organization and method naming
- Ensure all classes have proper DocBlock comments
- Test in both local development and with feature disabled

