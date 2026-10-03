<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EventConsoleMiddleware
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        // Check if feature is enabled and response should be processed
        if (!$this->isEnabled() || !$this->shouldProcess($response)) {
            return $response;
        }

        // Get event logs from session
        $logs = session('event_console_logs', []);

        if (empty($logs)) {
            return $response;
        }

        // Generate console logging script
        $script = $this->generateConsoleScript($logs);

        // Inject script into response
        $html = $response->getContent();
        if (is_string($html)) {
            $html = $this->injectScript($html, $script);
            $response->setContent($html);
        }

        // Clear logs from session after injection if configured
        if (config('event_console.clear_after_injection', true)) {
            session(['event_console_logs' => []]);
        }

        return $response;
    }

    /**
     * Check if event console logging is enabled
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        return config('event_console.enabled', false);
    }

    /**
     * Check if response should be processed (HTML responses only)
     *
     * @param SymfonyResponse $response
     * @return bool
     */
    private function shouldProcess(SymfonyResponse $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return strpos($contentType, 'text/html') !== false;
    }

    /**
     * Generate JavaScript console logging script
     *
     * @param array $logs
     * @return string
     */
    private function generateConsoleScript(array $logs): string
    {
        $logStatements = [];

        foreach ($logs as $event) {
            $logStatements[] = $this->formatEventLog($event);
        }

        $consoleCode = implode("\n        ", $logStatements);

        $template = <<<'SCRIPT'
<script>
(function() {
    try {
        CONSOLE_CODE
    } catch(e) {
        // Silently fail if console unavailable
    }
})();
</script>
SCRIPT;

        return str_replace('CONSOLE_CODE', $consoleCode, $template);
    }

    /**
     * Format a single event for console logging
     *
     * @param array $event
     * @return string
     */
    private function formatEventLog(array $event): string
    {
        $eventName = addslashes($event['event_name'] ?? 'Unknown');

        $lines = [
            "console.log('%cEVENT: {$eventName}', 'font-weight: bold; color: #2563eb; font-size: 14px;');",
        ];

        // Application ID
        if (isset($event['application_id'])) {
            $appId = $event['application_id'];
            $lines[] = "console.log('%cApplication ID:%c {$appId}', 'font-weight: bold;', 'color: #059669;');";
        }

        // Student name
        if (isset($event['student_name'])) {
            $studentName = addslashes($event['student_name']);
            $lines[] = "console.log('%cStudent:%c {$studentName}', 'font-weight: bold;', 'color: #059669;');";
        }

        // Scholarship name
        if (isset($event['scholarship_name'])) {
            $scholarshipName = addslashes($event['scholarship_name']);
            $lines[] = "console.log('%cScholarship:%c {$scholarshipName}', 'font-weight: bold;', 'color: #059669;');";
        }

        // Status (if applicable)
        if (isset($event['old_status']) && isset($event['new_status'])) {
            $oldStatus = addslashes($event['old_status']);
            $newStatus = addslashes($event['new_status']);
            $lines[] = "console.log('%cStatus:%c {$oldStatus} → {$newStatus}', 'font-weight: bold;', 'color: #dc2626;');";
        }

        // Handlers
        if (isset($event['handlers']) && is_array($event['handlers']) && !empty($event['handlers'])) {
            $handlers = implode(', ', array_map('addslashes', $event['handlers']));
            $lines[] = "console.log('%cHandlers:%c {$handlers}', 'font-weight: bold;', 'color: #7c3aed;');";
        }

        // Visual separator
        $lines[] = "console.log('');";

        return implode("\n        ", $lines);
    }

    /**
     * Inject script into HTML response before closing body tag
     *
     * @param string $html
     * @param string $script
     * @return string
     */
    private function injectScript(string $html, string $script): string
    {
        $closingBodyTag = '</body>';

        if (strpos($html, $closingBodyTag) !== false) {
            return str_replace($closingBodyTag, $script . "\n" . $closingBodyTag, $html);
        }

        // If no closing body tag, append at end
        return $html . "\n" . $script;
    }
}
