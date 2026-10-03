# Event Console Logging - Design Document

## Overview
The Event Console Logging feature provides real-time visibility into Laravel event dispatch and handler execution by automatically logging event details to the browser's JavaScript console. This is implemented through a non-invasive listener and middleware approach that captures event metadata and injects formatted console output into HTTP responses.

---

## Architecture

### Component Diagram
```
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Request                             │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────┐
        │   Event is Dispatched    │
        │ (ApplicationStatusChanged)│
        └──────────────┬───────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │   EventConsoleLogListener fires       │
        │   - Captures event details           │
        │   - Gets handlers list                │
        │   - Stores in session                │
        └──────────────┬──────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │   HTTP Response Generated            │
        └──────────────┬──────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │   EventConsoleMiddleware processes   │
        │   response (if enabled)              │
        │   - Reads session event logs         │
        │   - Generates JavaScript code       │
        │   - Injects before closing body tag  │
        │   - Clears session logs (optional)   │
        └──────────────┬──────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │   Response sent to browser           │
        │   with injected console logging      │
        └──────────────────────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │   Browser logs to console:           │
        │   🚀 EVENT: ApplicationStatusChanged │
        │   Application ID: 123                │
        │   Student: Juan Dela Cruz            │
        │   ...                                │
        └──────────────────────────────────────┘
```

### Components

#### 1. **EventConsoleLogListener** (`app/Listeners/EventConsoleLogListener.php`)
Captures event dispatch details and stores them in session.

**Responsibilities:**
- Listen to all events in the `events_to_log` list from configuration
- Extract event metadata (event name, timestamp)
- For ApplicationStatusChanged events: extract application_id, student_name, scholarship_name, old_status, new_status
- For ApplicationSubmitted events: extract application_id, student_name, scholarship_name
- Query `Event::getListeners()` to get list of handlers/listeners for the current event
- Store all data in session under `event_console_logs` key
- Maintain a maximum event limit based on `log_limit` configuration

**Key Methods:**
- `handle(mixed $event): void` - Main event handler
- `captureEventDetails(mixed $event): array` - Extract event-specific data
- `getEventHandlers(string $eventClass): array` - Get list of listeners for event
- `storeInSession(array $data): void` - Add to session log with limit enforcement

#### 2. **EventConsoleMiddleware** (`app/Http/Middleware/EventConsoleMiddleware.php`)
Injects JavaScript console logging into HTML responses.

**Responsibilities:**
- Check if event_console_logging is enabled in config
- Retrieve session event logs
- Only process HTML responses (Content-Type: text/html)
- Generate JavaScript code with formatted console.log statements
- Inject script before closing `</body>` tag
- Optionally clear session logs after injection
- Handle cases where `</body>` tag is missing (graceful degradation)

**Key Methods:**
- `handle(Request $request, Closure $next): Response` - Middleware pipeline
- `isEnabled(): bool` - Check configuration
- `shouldProcess(Response $response): bool` - Determine if response qualifies
- `generateConsoleScript(array $logs): string` - Create JavaScript code
- `injectScript(string $html, string $script): string` - Insert before closing body tag
- `formatEventLog(array $event): string` - Format single event for console output

#### 3. **Configuration File** (`config/event_console.php`)
Controls feature behavior and scope.

**Configuration Options:**
```php
return [
    // Enable/disable console logging feature
    'enabled' => env('EVENT_CONSOLE_LOGGING_ENABLED', false),
    
    // List of event classes to monitor
    'events_to_log' => [
        \App\Events\ApplicationStatusChanged::class,
        \App\Events\ApplicationSubmitted::class,
        \App\Events\ScholarshipCreated::class,
        \App\Events\StudentRegistered::class,
    ],
    
    // Maximum number of events to store in session
    'log_limit' => 50,
    
    // Clear logs after injection (prevents session bloat)
    'clear_after_injection' => true,
];
```

#### 4. **Registration in AppServiceProvider** (`app/Providers/AppServiceProvider.php`)
Registers the listener to handle events.

**Implementation:**
```php
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

---

## Event Data Capture

### Session Storage Structure
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
        'handlers' => [
            'SendStatusNotification',
            'UpdateApplicationStatistics',
            'CreateAwardForApprovedApplication',
            'LogApplicationActivity'
        ]
    ],
    [
        'timestamp' => '2024-01-15 14:30:46',
        'event_class' => 'App\Events\ApplicationSubmitted',
        'event_name' => 'ApplicationSubmitted',
        'application_id' => 124,
        'student_name' => 'Maria Santos',
        'scholarship_name' => 'DOST Scholarship',
        'handlers' => [
            'LogApplicationActivity'
        ]
    ],
    // ... more events, limited by log_limit config
]
```

### Event-Specific Data Extraction

#### ApplicationStatusChanged
```php
[
    'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
    'event_class' => get_class($event),
    'event_name' => class_basename($event),
    'application_id' => $event->application->id,
    'student_name' => $event->application->student->name,
    'scholarship_name' => $event->application->scholarship->name,
    'old_status' => $event->oldStatus,
    'new_status' => $event->newStatus,
    'handlers' => [ /* list of listener names */ ]
]
```

#### ApplicationSubmitted
```php
[
    'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
    'event_class' => get_class($event),
    'event_name' => class_basename($event),
    'application_id' => $event->application->id,
    'student_name' => $event->application->student->name,
    'scholarship_name' => $event->application->scholarship->name,
    'handlers' => [ /* list of listener names */ ]
]
```

### Handler Discovery
Using `Event::getListeners($eventClass)`:
```php
$listeners = Event::getListeners(get_class($event));
$handlers = array_map(function($listener) {
    // Handle closures and class@method format
    if (is_string($listener)) {
        return explode('@', $listener)[0];
    }
    if (is_array($listener) && isset($listener[0])) {
        return class_basename($listener[0]);
    }
    return 'Unknown';
}, $listeners);
```

---

## JavaScript Console Output

### Console Output Format
Each event logs with formatted output showing all details:

```javascript
console.log('%c🚀 EVENT: ApplicationStatusChanged', 'font-weight: bold; color: #2563eb; font-size: 14px;');
console.log('%cApplication ID:%c 123', 'font-weight: bold;', 'color: #059669;');
console.log('%cStudent:%c Juan Dela Cruz', 'font-weight: bold;', 'color: #059669;');
console.log('%cScholarship:%c SLSU Merit', 'font-weight: bold;', 'color: #059669;');
console.log('%cStatus:%c Pending → Approved', 'font-weight: bold;', 'color: #dc2626;');
console.log('%cHandlers:%c SendStatusNotification, UpdateApplicationStatistics, CreateAwardForApprovedApplication, LogApplicationActivity', 'font-weight: bold;', 'color: #7c3aed;');
console.log('');
```

### Console Output Appearance
```
🚀 EVENT: ApplicationStatusChanged
Application ID: 123
Student: Juan Dela Cruz
Scholarship: SLSU Merit
Status: Pending → Approved
Handlers: SendStatusNotification, UpdateApplicationStatistics, CreateAwardForApprovedApplication, LogApplicationActivity

🚀 EVENT: ApplicationSubmitted
Application ID: 124
Student: Maria Santos
Scholarship: DOST Scholarship
Handlers: LogApplicationActivity
```

### Generated JavaScript Template
```javascript
<script>
(function() {
    try {
        // Event 1
        console.log('%c🚀 EVENT: ApplicationStatusChanged', 'font-weight: bold; color: #2563eb; font-size: 14px;');
        console.log('%cApplication ID:%c 123', 'font-weight: bold;', 'color: #059669;');
        console.log('%cStudent:%c Juan Dela Cruz', 'font-weight: bold;', 'color: #059669;');
        console.log('%cScholarship:%c SLSU Merit', 'font-weight: bold;', 'color: #059669;');
        console.log('%cStatus:%c Pending → Approved', 'font-weight: bold;', 'color: #dc2626;');
        console.log('%cHandlers:%c SendStatusNotification, UpdateApplicationStatistics, CreateAwardForApprovedApplication, LogApplicationActivity', 'font-weight: bold;', 'color: #7c3aed;');
        console.log('');
        
        // Event 2
        console.log('%c🚀 EVENT: ApplicationSubmitted', 'font-weight: bold; color: #2563eb; font-size: 14px;');
        console.log('%cApplication ID:%c 124', 'font-weight: bold;', 'color: #059669;');
        console.log('%cStudent:%c Maria Santos', 'font-weight: bold;', 'color: #059669;');
        console.log('%cScholarship:%c DOST Scholarship', 'font-weight: bold;', 'color: #059669;');
        console.log('%cHandlers:%c LogApplicationActivity', 'font-weight: bold;', 'color: #7c3aed;');
        console.log('');
    } catch(e) {
        // Silently fail if console unavailable
    }
})();
</script>
```

---

## Implementation Details

### Middleware Registration

**File:** `app/Http/Middleware/Kernel.php`

Add to `$middleware` array (global HTTP middleware):
```php
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\EventConsoleMiddleware::class,
];
```

### Event Listener Registration

**File:** `app/Providers/AppServiceProvider.php`

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

### Session Storage Management

**Strategy:**
1. Each event is appended to session array `event_console_logs`
2. Array is checked against `log_limit` config
3. If count exceeds limit, oldest events are removed (FIFO)
4. After middleware injects script, logs are cleared (if `clear_after_injection` is true)
5. This prevents session bloat on pages with many requests

**Implementation in Listener:**
```php
private function storeInSession(array $eventData): void
{
    $logs = session('event_console_logs', []);
    $logs[] = $eventData;
    
    $limit = config('event_console.log_limit', 50);
    if (count($logs) > $limit) {
        $logs = array_slice($logs, -$limit);
    }
    
    session(['event_console_logs' => $logs]);
}
```

### Response Injection Logic

**File:** `app/Http/Middleware/EventConsoleMiddleware.php`

```php
private function injectScript(string $html, string $script): string
{
    $closingBodyTag = '</body>';
    
    if (strpos($html, $closingBodyTag) !== false) {
        return str_replace($closingBodyTag, $script . "\n" . $closingBodyTag, $html);
    }
    
    // If no closing body tag, append at end
    return $html . "\n" . $script;
}
```

---

## Configuration

### Environment Variable
```
EVENT_CONSOLE_LOGGING_ENABLED=true    # Local development
EVENT_CONSOLE_LOGGING_ENABLED=false   # Production (default)
```

### Config File Publishing (Optional)
Developers can publish the config file:
```bash
php artisan vendor:publish --tag=event-console-config
```

### Default Configuration Values
```php
'enabled' => env('EVENT_CONSOLE_LOGGING_ENABLED', false),
'events_to_log' => [
    \App\Events\ApplicationStatusChanged::class,
    \App\Events\ApplicationSubmitted::class,
    \App\Events\ScholarshipCreated::class,
    \App\Events\StudentRegistered::class,
],
'log_limit' => 50,
'clear_after_injection' => true,
```

---

## Security Considerations

### Data Sanitization
- Event names are extracted using `class_basename()` (safe)
- Database values (student_name, scholarship_name) come from Model attributes (already sanitized by Laravel ORM)
- JavaScript string values are properly escaped before output
- Console.log does not execute untrusted code

### XSS Prevention
- All string values are escaped using `addslashes()` or similar
- JavaScript is wrapped in IIFE (Immediately Invoked Function Expression) for scope isolation
- No `eval()` or `innerHTML` usage - only script tag injection

### Information Disclosure
- Only application-related metadata is logged (no passwords, tokens, or sensitive PII)
- Console logging only visible to developers on local/dev environments (disabled by default)
- Logs are session-scoped and cleared after injection

---

## Testing Strategy

### Unit Tests
- Test EventConsoleLogListener event capture
- Test session storage and log limit enforcement
- Test EventConsoleMiddleware script injection
- Test configuration loading

### Integration Tests
- Test full flow: event dispatch → listener → session → middleware → HTML injection
- Test multiple events in single request cycle
- Test with and without configuration enabled

### Browser Testing
- Verify console output displays correctly in browser console
- Verify formatting with color codes applies correctly
- Verify multiple events display in correct order

---

## Compatibility & Non-Breaking Changes

### No Changes Required To:
- Existing Event classes (ApplicationStatusChanged, ApplicationSubmitted, etc.)
- Existing Listener classes (SendStatusNotification, LogApplicationActivity, etc.)
- Existing Controllers and their behavior
- Database schema or migrations
- API endpoints or response structure
- Authentication or authorization logic

### Implementation is Non-Invasive Because:
- Feature is disabled by default (enabled via config)
- Listener uses passive event observation (doesn't modify events)
- Middleware only processes HTML responses
- No modifications to application request/response cycle
- Can be completely disabled without side effects

---

## Performance Impact

### When Disabled (Default)
- Zero overhead (configuration check prevents any execution)

### When Enabled
- **Event Listener**: ~1-2ms per event dispatch (minimal session storage)
- **Middleware**: ~2-5ms per response (string manipulation for HTML injection)
- **Total**: <10ms additional latency per request
- **Memory**: ~5-20KB per session (50 events × ~100-400 bytes each)

### Optimization Strategies
- `log_limit` config controls session size (default 50 events)
- `clear_after_injection` clears logs after each response to prevent bloat
- Event filtering via `events_to_log` config allows monitoring specific events only
- Feature disabled by default for production environments

---

## Future Extensions

Possible future enhancements (not in initial scope):
- Database persistence for event history UI
- Real-time WebSocket display of events
- Event filtering/search in console
- Performance profiling (event execution time)
- Export event logs for analysis
- Integration with browser DevTools extensions

---

## Files to Create/Modify

### New Files:
- `app/Listeners/EventConsoleLogListener.php`
- `app/Http/Middleware/EventConsoleMiddleware.php`
- `config/event_console.php`

### Modified Files:
- `app/Providers/AppServiceProvider.php` (add listener registration)
- `app/Http/Middleware/Kernel.php` (add middleware registration)

### No Changes To:
- Database migrations
- Existing models or controllers
- Existing listeners or events
- Routes or configuration files (except those listed above)

---

## Implementation Checklist

- [ ] Create `config/event_console.php`
- [ ] Create `app/Listeners/EventConsoleLogListener.php`
- [ ] Create `app/Http/Middleware/EventConsoleMiddleware.php`
- [ ] Register listener in `AppServiceProvider::boot()`
- [ ] Register middleware in `Kernel::$middleware`
- [ ] Write unit tests for listener
- [ ] Write unit tests for middleware
- [ ] Write integration tests
- [ ] Manual browser testing
- [ ] Verify no side effects with disabled config
- [ ] Document usage in README or wiki
