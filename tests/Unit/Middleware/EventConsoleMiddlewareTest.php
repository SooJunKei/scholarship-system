<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EventConsoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class EventConsoleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->flush();
    }

    /** @test */
    public function middleware_disabled_by_default_does_not_process()
    {
        config(['event_console.enabled' => false]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body>Test</body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Content should not have script injected
        $this->assertStringNotContainsString('<script>', $result->getContent());
    }

    /** @test */
    public function middleware_skips_non_html_responses()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('{"status": "ok"}');
        $response->header('Content-Type', 'application/json');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Content should not have script injected
        $this->assertStringNotContainsString('<script>', $result->getContent());
    }

    /** @test */
    public function middleware_injects_script_before_closing_body_tag()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body><h1>Test</h1></body></html>');
        $response->header('Content-Type', 'text/html; charset=UTF-8');

        session(['event_console_logs' => [
            [
                'timestamp' => '2024-01-15 14:30:45',
                'event_name' => 'ApplicationStatusChanged',
                'event_class' => 'App\Events\ApplicationStatusChanged',
                'application_id' => 123,
                'student_name' => 'Juan Dela Cruz',
                'scholarship_name' => 'SLSU Merit',
                'old_status' => 'Pending',
                'new_status' => 'Approved',
                'handlers' => ['SendStatusNotification', 'UpdateApplicationStatistics']
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Script should be injected before closing body tag
        $this->assertStringContainsString('<script>', $content);
        $this->assertStringContainsString('console.log', $content);
        $this->assertStringContainsString('ApplicationStatusChanged', $content);
        $this->assertStringContainsString('</body>', $content);

        // Script should come before </body>
        $scriptPos = strpos($content, '<script>');
        $bodyPos = strpos($content, '</body>');
        $this->assertLessThan($bodyPos, $scriptPos);
    }

    /** @test */
    public function middleware_handles_missing_closing_body_tag()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body><h1>Test</h1>');
        $response->header('Content-Type', 'text/html; charset=UTF-8');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Script should be appended at end
        $this->assertStringContainsString('<script>', $content);
        $this->assertStringEndsWith('</script>', $content);
    }

    /** @test */
    public function middleware_clears_session_logs_after_injection_when_configured()
    {
        config(['event_console.enabled' => true, 'event_console.clear_after_injection' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Session logs should be cleared
        $this->assertEmpty(session('event_console_logs', []));
    }

    /** @test */
    public function middleware_does_not_clear_session_logs_when_configured()
    {
        config(['event_console.enabled' => true, 'event_console.clear_after_injection' => false]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Session logs should still be there
        $logs = session('event_console_logs', []);
        $this->assertCount(1, $logs);
    }

    /** @test */
    public function middleware_generates_valid_javascript()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'timestamp' => '2024-01-15 14:30:45',
                'event_name' => 'ApplicationStatusChanged',
                'event_class' => 'App\Events\ApplicationStatusChanged',
                'application_id' => 123,
                'student_name' => 'Juan Dela Cruz',
                'scholarship_name' => 'SLSU Merit',
                'old_status' => 'Pending',
                'new_status' => 'Approved',
                'handlers' => ['SendStatusNotification']
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Check for JavaScript structure
        $this->assertStringContainsString('(function() {', $content);
        $this->assertStringContainsString('try {', $content);
        $this->assertStringContainsString('} catch(e) {', $content);
        $this->assertStringContainsString('})();', $content);

        // Check for console.log statements
        $this->assertStringContainsString('console.log', $content);

        // Check for emoji and event name
        $this->assertStringContainsString('🚀', $content);
        $this->assertStringContainsString('ApplicationStatusChanged', $content);
    }

    /** @test */
    public function middleware_escapes_strings_properly()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        // Event with special characters that need escaping
        session(['event_console_logs' => [
            [
                'event_name' => "Test'Event",
                'application_id' => 123,
                'student_name' => "Juan'Dela \"Cruz",
                'scholarship_name' => "SLSU\nMerit",
                'handlers' => ["Handler'One", 'Handler"Two']
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Should contain escaped quotes
        $this->assertStringContainsString("\\'", $content);
    }

    /** @test */
    public function middleware_displays_status_changes_correctly()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'student_name' => 'Juan Dela Cruz',
                'scholarship_name' => 'SLSU Merit',
                'old_status' => 'Pending',
                'new_status' => 'Approved',
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Status should be displayed with arrow
        $this->assertStringContainsString('Pending → Approved', $content);
    }

    /** @test */
    public function middleware_handles_multiple_events()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body></body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => [
            [
                'event_name' => 'ApplicationStatusChanged',
                'application_id' => 123,
                'student_name' => 'Juan Dela Cruz',
                'scholarship_name' => 'SLSU Merit',
                'handlers' => []
            ],
            [
                'event_name' => 'ApplicationSubmitted',
                'application_id' => 124,
                'student_name' => 'Maria Santos',
                'scholarship_name' => 'DOST Scholarship',
                'handlers' => []
            ]
        ]]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $content = $result->getContent();

        // Both events should be in output
        $this->assertStringContainsString('ApplicationStatusChanged', $content);
        $this->assertStringContainsString('ApplicationSubmitted', $content);
        $this->assertStringContainsString('Juan Dela Cruz', $content);
        $this->assertStringContainsString('Maria Santos', $content);
    }

    /** @test */
    public function middleware_skips_processing_when_no_logs_in_session()
    {
        config(['event_console.enabled' => true]);

        $middleware = new EventConsoleMiddleware();
        $request = Request::create('/');
        $response = new Response('<html><body>Test</body></html>');
        $response->header('Content-Type', 'text/html');

        session(['event_console_logs' => []]);

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Should return original content without script
        $this->assertStringNotContainsString('<script>', $result->getContent());
    }
}
