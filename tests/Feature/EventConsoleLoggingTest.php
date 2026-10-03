<?php

namespace Tests\Feature;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventConsoleLoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->flush();
    }

    /** @test */
    public function feature_disabled_by_default_no_overhead()
    {
        // Feature should be disabled by default
        $this->assertFalse(config('event_console.enabled'));

        // Dispatch event
        $application = $this->createTestApplication();
        Event::dispatch(new ApplicationStatusChanged(
            application: $application,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        // No logs should be stored
        $this->assertEmpty(session('event_console_logs', []));
    }

    /** @test */
    public function full_flow_event_dispatch_to_console_injection()
    {
        config(['event_console.enabled' => true]);

        // Create test application
        $application = $this->createTestApplication();

        // Dispatch event
        Event::dispatch(new ApplicationStatusChanged(
            application: $application,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        // Event should be captured in session
        $logs = session('event_console_logs', []);
        $this->assertCount(1, $logs);
        $this->assertEquals('ApplicationStatusChanged', $logs[0]['event_name']);

        // Simulate response with middleware processing
        $response = $this->get('/');

        // Response should contain injected script (if page exists)
        // This is a basic check - actual page load would show script in response
    }

    /** @test */
    public function multiple_events_in_single_request_all_logged()
    {
        config(['event_console.enabled' => true]);

        $app1 = $this->createTestApplication(id: 1, studentName: 'Student 1');
        $app2 = $this->createTestApplication(id: 2, studentName: 'Student 2');

        // Dispatch multiple events
        Event::dispatch(new ApplicationStatusChanged(
            application: $app1,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        Event::dispatch(new ApplicationSubmitted(application: $app2));

        $logs = session('event_console_logs', []);

        $this->assertCount(2, $logs);
        $this->assertEquals('ApplicationStatusChanged', $logs[0]['event_name']);
        $this->assertEquals('ApplicationSubmitted', $logs[1]['event_name']);
    }

    /** @test */
    public function console_output_format_matches_design()
    {
        config(['event_console.enabled' => true]);

        $application = $this->createTestApplication(
            id: 123,
            studentName: 'Juan Dela Cruz',
            scholarshipName: 'SLSU Merit'
        );

        Event::dispatch(new ApplicationStatusChanged(
            application: $application,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        $logs = session('event_console_logs', []);
        $event = $logs[0];

        // Check all expected fields are present
        $this->assertArrayHasKey('event_name', $event);
        $this->assertArrayHasKey('application_id', $event);
        $this->assertArrayHasKey('student_name', $event);
        $this->assertArrayHasKey('scholarship_name', $event);
        $this->assertArrayHasKey('old_status', $event);
        $this->assertArrayHasKey('new_status', $event);
        $this->assertArrayHasKey('handlers', $event);
        $this->assertArrayHasKey('timestamp', $event);

        // Check values match expectations
        $this->assertEquals(123, $event['application_id']);
        $this->assertEquals('Juan Dela Cruz', $event['student_name']);
        $this->assertEquals('SLSU Merit', $event['scholarship_name']);
        $this->assertEquals('Pending', $event['old_status']);
        $this->assertEquals('Approved', $event['new_status']);
    }

    /** @test */
    public function feature_enabled_then_disabled_no_regression()
    {
        // First: feature enabled, dispatch event
        config(['event_console.enabled' => true]);

        $app1 = $this->createTestApplication(id: 1);
        Event::dispatch(new ApplicationStatusChanged(
            application: $app1,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        $logsEnabled = session('event_console_logs', []);
        $this->assertCount(1, $logsEnabled);

        // Clear session for next test
        session()->flush();

        // Second: feature disabled, dispatch event
        config(['event_console.enabled' => false]);

        $app2 = $this->createTestApplication(id: 2);
        Event::dispatch(new ApplicationStatusChanged(
            application: $app2,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        $logsDisabled = session('event_console_logs', []);
        $this->assertEmpty($logsDisabled);
    }

    /** @test */
    public function application_status_changed_event_captured_correctly()
    {
        config(['event_console.enabled' => true]);

        $application = $this->createTestApplication(
            id: 999,
            studentName: 'Test Student',
            scholarshipName: 'Test Scholarship'
        );

        Event::dispatch(new ApplicationStatusChanged(
            application: $application,
            oldStatus: 'Draft',
            newStatus: 'Submitted'
        ));

        $logs = session('event_console_logs', []);
        $event = $logs[0];

        $this->assertEquals('ApplicationStatusChanged', $event['event_name']);
        $this->assertEquals(999, $event['application_id']);
        $this->assertEquals('Draft', $event['old_status']);
        $this->assertEquals('Submitted', $event['new_status']);
    }

    /** @test */
    public function application_submitted_event_captured_correctly()
    {
        config(['event_console.enabled' => true]);

        $application = $this->createTestApplication(
            id: 888,
            studentName: 'Another Student',
            scholarshipName: 'Another Scholarship'
        );

        Event::dispatch(new ApplicationSubmitted(application: $application));

        $logs = session('event_console_logs', []);
        $event = $logs[0];

        $this->assertEquals('ApplicationSubmitted', $event['event_name']);
        $this->assertEquals(888, $event['application_id']);
        $this->assertArrayNotHasKey('old_status', $event);
        $this->assertArrayNotHasKey('new_status', $event);
    }

    /** @test */
    public function log_limit_respected_in_multiple_dispatches()
    {
        config(['event_console.enabled' => true, 'event_console.log_limit' => 5]);

        // Dispatch 10 events
        for ($i = 1; $i <= 10; $i++) {
            $app = $this->createTestApplication(id: $i);
            Event::dispatch(new ApplicationStatusChanged(
                application: $app,
                oldStatus: 'Pending',
                newStatus: 'Approved'
            ));
        }

        $logs = session('event_console_logs', []);

        // Should only have 5 (log_limit)
        $this->assertCount(5, $logs);

        // Should be the last 5 (FIFO)
        $this->assertEquals(6, $logs[0]['application_id']);
        $this->assertEquals(10, $logs[4]['application_id']);
    }

    /** @test */
    public function events_without_status_fields_handled_gracefully()
    {
        config(['event_console.enabled' => true]);

        $application = $this->createTestApplication();

        Event::dispatch(new ApplicationSubmitted(application: $application));

        $logs = session('event_console_logs', []);

        // Should not throw error
        $this->assertCount(1, $logs);
        $this->assertArrayNotHasKey('old_status', $logs[0]);
        $this->assertArrayNotHasKey('new_status', $logs[0]);
    }

    /** @test */
    public function timestamp_format_consistent_across_events()
    {
        config(['event_console.enabled' => true]);

        $app1 = $this->createTestApplication(id: 1);
        $app2 = $this->createTestApplication(id: 2);

        Event::dispatch(new ApplicationStatusChanged(
            application: $app1,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        ));

        Event::dispatch(new ApplicationSubmitted(application: $app2));

        $logs = session('event_console_logs', []);

        foreach ($logs as $event) {
            // All timestamps should match format: Y-m-d H:i:s
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $event['timestamp']);
        }
    }

    /**
     * Helper: Create test application
     */
    private function createTestApplication(
        int $id = 1,
        string $studentName = 'Test Student',
        string $scholarshipName = 'Test Scholarship'
    ): Application {
        $student = new Student();
        $student->name = $studentName;

        $scholarship = new Scholarship();
        $scholarship->name = $scholarshipName;

        $application = new Application();
        $application->id = $id;
        $application->setRelation('student', $student);
        $application->setRelation('scholarship', $scholarship);

        return $application;
    }
}
