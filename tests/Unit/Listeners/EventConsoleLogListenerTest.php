<?php

namespace Tests\Unit\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Listeners\EventConsoleLogListener;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventConsoleLogListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session()->flush();
    }

    /** @test */
    public function listener_ignores_events_when_feature_disabled()
    {
        // Feature disabled by default
        $this->assertFalse(config('event_console.enabled'));

        // Create test event
        $event = $this->createApplicationStatusChangedEvent();

        // Dispatch event
        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        // Session should be empty
        $this->assertEmpty(session('event_console_logs', []));
    }

    /** @test */
    public function listener_captures_application_status_changed_event()
    {
        config(['event_console.enabled' => true]);

        $event = $this->createApplicationStatusChangedEvent(
            applicationId: 123,
            studentName: 'Juan Dela Cruz',
            scholarshipName: 'SLSU Merit',
            oldStatus: 'Pending',
            newStatus: 'Approved'
        );

        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);

        $this->assertCount(1, $logs);
        $log = $logs[0];

        $this->assertEquals('ApplicationStatusChanged', $log['event_name']);
        $this->assertEquals('App\Events\ApplicationStatusChanged', $log['event_class']);
        $this->assertEquals(123, $log['application_id']);
        $this->assertEquals('Juan Dela Cruz', $log['student_name']);
        $this->assertEquals('SLSU Merit', $log['scholarship_name']);
        $this->assertEquals('Pending', $log['old_status']);
        $this->assertEquals('Approved', $log['new_status']);
        $this->assertNotEmpty($log['timestamp']);
        $this->assertIsArray($log['handlers']);
    }

    /** @test */
    public function listener_captures_application_submitted_event()
    {
        config(['event_console.enabled' => true]);

        $event = $this->createApplicationSubmittedEvent(
            applicationId: 456,
            studentName: 'Maria Santos',
            scholarshipName: 'DOST Scholarship'
        );

        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);

        $this->assertCount(1, $logs);
        $log = $logs[0];

        $this->assertEquals('ApplicationSubmitted', $log['event_name']);
        $this->assertEquals(456, $log['application_id']);
        $this->assertEquals('Maria Santos', $log['student_name']);
        $this->assertEquals('DOST Scholarship', $log['scholarship_name']);
        $this->assertArrayNotHasKey('old_status', $log);
        $this->assertArrayNotHasKey('new_status', $log);
    }

    /** @test */
    public function listener_stores_timestamp_in_correct_format()
    {
        config(['event_console.enabled' => true]);

        $event = $this->createApplicationStatusChangedEvent();
        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);
        $timestamp = $logs[0]['timestamp'];

        // Check format: Y-m-d H:i:s
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp);
    }

    /** @test */
    public function listener_enforces_log_limit_with_fifo()
    {
        config(['event_console.enabled' => true, 'event_console.log_limit' => 3]);

        // Dispatch 5 events
        for ($i = 1; $i <= 5; $i++) {
            $event = $this->createApplicationStatusChangedEvent(applicationId: $i);
            $listener = new EventConsoleLogListener();
            $listener->handle($event);
        }

        $logs = session('event_console_logs', []);

        // Should only have 3 events (oldest 2 removed)
        $this->assertCount(3, $logs);
        $this->assertEquals(3, $logs[0]['application_id']); // First should be 3rd event
        $this->assertEquals(4, $logs[1]['application_id']);
        $this->assertEquals(5, $logs[2]['application_id']);
    }

    /** @test */
    public function listener_handles_events_without_handlers_gracefully()
    {
        config(['event_console.enabled' => true]);

        $event = $this->createApplicationStatusChangedEvent();
        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);

        // Should not throw error, handlers can be empty or populated
        $this->assertCount(1, $logs);
        $this->assertIsArray($logs[0]['handlers']);
    }

    /** @test */
    public function listener_retrieves_event_handlers()
    {
        config(['event_console.enabled' => true]);

        // Register a test listener
        Event::listen(ApplicationStatusChanged::class, function () {
            // Dummy listener for testing
        });

        $event = $this->createApplicationStatusChangedEvent();
        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);

        // Should have handlers (at least from registered listeners)
        $this->assertIsArray($logs[0]['handlers']);
    }

    /** @test */
    public function listener_handles_missing_relationships_gracefully()
    {
        config(['event_console.enabled' => true]);

        // Create event with minimal data
        $application = new Application();
        $application->id = 789;

        $event = new ApplicationStatusChanged(
            application: $application,
            oldStatus: 'Pending',
            newStatus: 'Approved'
        );

        $listener = new EventConsoleLogListener();
        $listener->handle($event);

        $logs = session('event_console_logs', []);

        $this->assertCount(1, $logs);
        $this->assertEquals(789, $logs[0]['application_id']);
        $this->assertEquals('Unknown', $logs[0]['student_name']);
        $this->assertEquals('Unknown', $logs[0]['scholarship_name']);
    }

    /**
     * Helper method to create ApplicationStatusChanged event
     */
    private function createApplicationStatusChangedEvent(
        int $applicationId = 1,
        string $studentName = 'Test Student',
        string $scholarshipName = 'Test Scholarship',
        string $oldStatus = 'Pending',
        string $newStatus = 'Approved'
    ): ApplicationStatusChanged {
        $student = new Student();
        $student->name = $studentName;

        $scholarship = new Scholarship();
        $scholarship->name = $scholarshipName;

        $application = new Application();
        $application->id = $applicationId;
        $application->setRelation('student', $student);
        $application->setRelation('scholarship', $scholarship);

        return new ApplicationStatusChanged(
            application: $application,
            oldStatus: $oldStatus,
            newStatus: $newStatus
        );
    }

    /**
     * Helper method to create ApplicationSubmitted event
     */
    private function createApplicationSubmittedEvent(
        int $applicationId = 1,
        string $studentName = 'Test Student',
        string $scholarshipName = 'Test Scholarship'
    ): ApplicationSubmitted {
        $student = new Student();
        $student->name = $studentName;

        $scholarship = new Scholarship();
        $scholarship->name = $scholarshipName;

        $application = new Application();
        $application->id = $applicationId;
        $application->setRelation('student', $student);
        $application->setRelation('scholarship', $scholarship);

        return new ApplicationSubmitted(application: $application);
    }
}
