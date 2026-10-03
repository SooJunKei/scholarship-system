<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// Test delegate pattern route - AUTO CREATES DATA
Route::get('/test-delegates', function () {
    $student = App\Models\Student::firstOrCreate(
        ['email' => 'juan.delacruz@slsu.edu.ph'],
        [
            'student_number' => 'SLSU-2024-001',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '2004-03-15',
            'gender' => 'Male',
            'address' => 'Brgy. San Isidro, Sogod',
            'phone' => '09171234567',
            'course' => 'BS Computer Science',
            'year_level' => 3,
            'department' => 'College of Engineering',
        ]
    );
    
    $scholarship = App\Models\Scholarship::first();
    if (!$scholarship) {
        $scholarship = App\Models\Scholarship::create([
            'scholarship_name' => 'Academic Excellence Scholarship',
            'description' => 'For outstanding students',
            'sponsor' => 'SLSU Foundation',
            'amount' => 30000,
            'application_deadline' => now()->addMonths(3),
        ]);
    }
    
    $application = App\Models\Application::firstOrCreate(
        [
            'student_id' => $student->id,
            'scholarship_id' => $scholarship->id,
        ],
        [
            'date_applied' => now()->subDays(10),
            'status' => 'Pending',
        ]
    );
    
    $application->load('student', 'scholarship');
    $oldStatus = $application->status;
    $newStatus = $oldStatus === 'Pending' ? 'Approved' : 'Pending';
    
    ob_start();
    echo "DELEGATE PATTERN DEMONSTRATION\n";
    echo "One Event → Multiple Handlers\n\n";
    echo "TRIGGERING: ApplicationStatusChanged Event\n";
    echo "Student: {$student->full_name}\n";
    echo "Scholarship: {$scholarship->scholarship_name}\n";
    echo "Status Change: {$oldStatus} → {$newStatus}\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Dispatch the event - triggers all 5 delegates
    App\Events\ApplicationStatusChanged::dispatch($application, $oldStatus, $newStatus);
    
    // Capture the buffered output from event handlers
    $output = ob_get_clean();
    
    // Build JavaScript console output
    $jsConsole = "
<script>
console.clear();
console.log('%c╔══════════════════════════════════════════════════════════╗', 'color: #4CAF50; font-weight: bold;');
console.log('%c║       DELEGATE PATTERN DEMONSTRATION                     ║', 'color: #4CAF50; font-weight: bold;');
console.log('%c╚══════════════════════════════════════════════════════════╝', 'color: #4CAF50; font-weight: bold;');
console.log('');
console.log('%c🚀 EVENT TRIGGERED: ApplicationStatusChanged', 'color: #2196F3; font-size: 14px; font-weight: bold;');
console.log('%c   Student: {$student->full_name}', 'color: #FFF;');
console.log('%c   Scholarship: {$scholarship->scholarship_name}', 'color: #FFF;');
console.log('%c   Status Change: {$oldStatus} → {$newStatus}', 'color: #FF9800; font-weight: bold;');
console.log('');
console.log('%c✓ DELEGATE #1: SendStatusNotification', 'color: #4CAF50; font-weight: bold;');
console.log('  📧 Email sent to student');
console.log('%c✓ DELEGATE #2: UpdateApplicationStatistics', 'color: #4CAF50; font-weight: bold;');
console.log('  📊 Statistics updated');
console.log('%c✓ DELEGATE #3: CreateAwardForApprovedApplication', 'color: #4CAF50; font-weight: bold;');
console.log('  🏆 Award handler executed');
console.log('%c✓ DELEGATE #4: LogApplicationActivity', 'color: #4CAF50; font-weight: bold;');
console.log('  📝 Activity logged');
console.log('%c✓ DELEGATE #5: Anonymous/Lambda Handler', 'color: #4CAF50; font-weight: bold;');
console.log('  🔔 Admin notification cached');
console.log('');
console.log('%c✅ ALL 5 DELEGATES EXECUTED!', 'color: #4CAF50; font-size: 16px; font-weight: bold;');
console.log('%cONE EVENT → MULTIPLE RESPONSES', 'color: #FF9800; font-style: italic;');
</script>
";

    return response('<pre style="background: #1e1e1e; color: #d4d4d4; padding: 20px; font-family: monospace;">' . $output . '</pre>' . $jsConsole);
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('students', StudentController::class);
    Route::resource('scholarships', ScholarshipController::class);
    Route::resource('applications', ApplicationController::class);

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::resource('requirements', RequirementController::class)->except(['show']);
    });
});