# Event Console Logging - Requirements

## Feature Overview
Enable server-side Laravel event dispatch details to be automatically logged to the browser's JavaScript console. This provides developers with real-time visibility into event execution, handlers invoked, and event parameters without requiring separate debugging tools or server logs.

## Problem Statement
Currently, event listener output (e.g., from `echo` statements in listeners) only displays in test routes or server logs, not in the browser where developers are actively debugging. This makes it difficult to:
- Track which event handlers are executing during user actions
- Debug event flow in real-time from the browser
- Correlate backend events with frontend interactions
- Inspect detailed event parameters (status changes, IDs, etc.)

## Goals
1. Provide real-time visibility into event dispatch and handler execution
2. Display event metadata (name, IDs, parameters) in browser console
3. Enable developers to trace the complete event flow from action to handlers
4. Use session-based approach to avoid database overhead
5. Ensure non-invasive implementation that doesn't interfere with existing functionality

---

## Acceptance Criteria

### 1. Event Data Collection
**When an ApplicationStatusChanged event is dispatched, the system collects:**
- Event name (e.g., "ApplicationStatusChanged")
- Application ID
- Student name
- Scholarship name
- Old status (pre-change value)
- New status (post-change value)
- List of handler/listener class names that are configured for this event
- Timestamp of event dispatch

**Validates:** Core event inspection capability

### 2. Session Storage
**The system stores collected event details in the user's session:**
- Event data persists across the HTTP response cycle
- Data includes all information required by acceptance criterion #1
- Session key is organized to prevent conflicts with other session data
- Old events are retained or cleared based on configuration (TBD by design)

**Validates:** Reliable data persistence between event dispatch and response rendering

### 3. JavaScript Console Output Injection
**When an HTTP response is sent to the browser, the system injects JavaScript code that:**
- Reads event data from the response (via script tag or data attribute)
- Logs to browser console with clear, readable formatting
- Displays event type/name prominently
- Shows all collected event data (IDs, names, statuses)
- Lists all handlers that were executed or configured to handle the event
- Uses color-coded output for visual organization (e.g., different colors for event name, handlers, parameters)
- Groups related information together for easy scanning

**Validates:** User-facing console output meets readability and usability standards

### 4. Support for Multiple Event Types
**The system logs to console for:**
- ApplicationStatusChanged events
- ApplicationSubmitted events
- Any other application-related events

**Validates:** Architecture is event-agnostic and extensible

### 5. Non-Interference with Core Functionality
**The feature does NOT:**
- Break normal application functionality (page loads, form submissions, etc.)
- Interfere with database operations or data persistence
- Prevent event listeners from executing normally
- Interfere with existing server-side logging (ActivityLog records, etc.)
- Impact application performance when console logging is enabled
- Require authentication or authorization checks

**Validates:** Zero side effects on existing system behavior

### 6. Conditional Enable/Disable
**The feature can be toggled:**
- Enabled/disabled via configuration or environment variable
- Default state is disabled (non-breaking)
- Can be enabled during development or debugging sessions
- Disabling prevents any session storage or output overhead

**Validates:** Feature doesn't impose overhead when not in use

---

## Non-Functional Requirements

### Performance
- Event data collection adds minimal overhead (<5ms per event)
- Session storage is lightweight (uses native session mechanism)
- JavaScript injection occurs only when feature is enabled
- No database queries added for event logging

### Security
- Event data contains no sensitive information (e.g., passwords, tokens)
- Session data follows Laravel's standard session security model
- JavaScript output is properly escaped to prevent XSS
- Feature respects authentication state (available to authenticated users only)

### Compatibility
- Works with existing Laravel event system without modifications
- Compatible with all existing listeners and handlers
- Works with all currently implemented events (ApplicationStatusChanged, ApplicationSubmitted, etc.)
- No dependency on third-party packages beyond Laravel

### Browser Support
- Console logging works in all modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation if browser console is not available

---

## Scope: In/Out

### In Scope
- Capturing and storing event dispatch details
- Injecting JavaScript console logging into HTTP responses
- Supporting ApplicationStatusChanged and ApplicationSubmitted events
- Configuration to enable/disable the feature
- Session-based storage approach

### Out of Scope
- Persistent storage of events in database
- Event replay or history UI
- Real-time WebSocket/Livewire updates
- Third-party event aggregation services
- Mobile app console integration

---

## Implementation Approach (Informational)

The design will use:
1. **Event Listener(s):** Capture event dispatch and listener metadata
2. **Session Storage:** Store collected event data in `$_SESSION['event_console_logs']` or similar
3. **Response Middleware:** Inject JavaScript before sending response to browser
4. **JavaScript Console API:** Use `console.log()` with `%c` styling for color-coded output
5. **Configuration:** `.env` or `config/` file to control enable/disable state

---

## Related Documentation
- See DELEGATE_PATTERN_DOCUMENTATION.md for current event system context
- See app/Listeners/ for current listener implementation
- See app/Events/ for current event structure

