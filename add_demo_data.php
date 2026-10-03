<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Scholarship;
use App\Models\Application;

echo "Adding demo data...\n\n";

// Create students
echo "Creating students...\n";
$student1 = Student::firstOrCreate(
    ['email' => 'juan.delacruz@slsu.edu.ph'],
    [
        'student_number' => 'SLSU-2024-001',
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'date_of_birth' => '2004-03-15',
        'gender' => 'Male',
        'address' => 'Brgy. San Isidro, Sogod, Southern Leyte',
        'phone' => '09171234567',
        'course' => 'BS Computer Science',
        'year_level' => '3rd Year',
        'department' => 'College of Engineering',
    ]
);
echo "✓ Created: {$student1->full_name}\n";

$student2 = Student::firstOrCreate(
    ['email' => 'maria.reyes@slsu.edu.ph'],
    [
        'student_number' => 'SLSU-2024-002',
        'first_name' => 'Maria',
        'middle_name' => 'Garcia',
        'last_name' => 'Reyes',
        'date_of_birth' => '2003-07-22',
        'gender' => 'Female',
        'address' => 'Brgy. Poblacion, Maasin City, Southern Leyte',
        'phone' => '09187654321',
        'course' => 'BS Information Technology',
        'year_level' => '4th Year',
        'department' => 'College of Engineering',
    ]
);
echo "✓ Created: {$student2->full_name}\n";

$student3 = Student::firstOrCreate(
    ['email' => 'pedro.santos@slsu.edu.ph'],
    [
        'student_number' => 'SLSU-2024-003',
        'first_name' => 'Pedro',
        'middle_name' => 'Luna',
        'last_name' => 'Santos',
        'date_of_birth' => '2005-01-10',
        'gender' => 'Male',
        'address' => 'Brgy. Tunga-tunga, Maasin City, Southern Leyte',
        'phone' => '09199876543',
        'course' => 'BS Computer Engineering',
        'year_level' => '2nd Year',
        'department' => 'College of Engineering',
    ]
);
echo "✓ Created: {$student3->full_name}\n\n";

// Create scholarships
echo "Creating scholarships...\n";
$scholarship1 = Scholarship::firstOrCreate(
    ['scholarship_name' => 'Academic Excellence Scholarship'],
    [
        'description' => 'For students with outstanding academic performance (GPA 1.5 and above)',
        'amount' => 30000.00,
        'eligibility_criteria' => 'Must have a GPA of 1.5 or higher, Full-time student, Filipino citizen',
        'deadline' => now()->addMonths(3),
        'status' => 'Open',
    ]
);
echo "✓ Created: {$scholarship1->scholarship_name} (₱" . number_format($scholarship1->amount, 2) . ")\n";

$scholarship2 = Scholarship::firstOrCreate(
    ['scholarship_name' => 'SLSU Merit Scholarship'],
    [
        'description' => 'Merit-based scholarship for deserving students',
        'amount' => 25000.00,
        'eligibility_criteria' => 'Must have a GPA of 2.0 or higher, Active in extracurricular activities',
        'deadline' => now()->addMonths(2),
        'status' => 'Open',
    ]
);
echo "✓ Created: {$scholarship2->scholarship_name} (₱" . number_format($scholarship2->amount, 2) . ")\n";

$scholarship3 = Scholarship::firstOrCreate(
    ['scholarship_name' => 'Financial Assistance Grant'],
    [
        'description' => 'Need-based scholarship for financially challenged students',
        'amount' => 20000.00,
        'eligibility_criteria' => 'Must provide proof of financial need, Good moral character',
        'deadline' => now()->addMonths(4),
        'status' => 'Open',
    ]
);
echo "✓ Created: {$scholarship3->scholarship_name} (₱" . number_format($scholarship3->amount, 2) . ")\n\n";

// Create applications
echo "Creating applications...\n";
$app1 = Application::firstOrCreate(
    [
        'student_id' => $student1->id,
        'scholarship_id' => $scholarship1->id,
    ],
    [
        'date_applied' => now()->subDays(10),
        'status' => 'Pending',
    ]
);
echo "✓ Application: {$student1->full_name} → {$scholarship1->scholarship_name} (Status: {$app1->status})\n";

$app2 = Application::firstOrCreate(
    [
        'student_id' => $student2->id,
        'scholarship_id' => $scholarship2->id,
    ],
    [
        'date_applied' => now()->subDays(8),
        'status' => 'Under Review',
    ]
);
echo "✓ Application: {$student2->full_name} → {$scholarship2->scholarship_name} (Status: {$app2->status})\n";

$app3 = Application::firstOrCreate(
    [
        'student_id' => $student3->id,
        'scholarship_id' => $scholarship3->id,
    ],
    [
        'date_applied' => now()->subDays(5),
        'status' => 'Pending',
    ]
);
echo "✓ Application: {$student3->full_name} → {$scholarship3->scholarship_name} (Status: {$app3->status})\n\n";

echo "==========================================\n";
echo "✅ Demo data created successfully!\n";
echo "==========================================\n";
echo "🎓 Students: 3\n";
echo "💰 Scholarships: 3\n";
echo "📝 Applications: 3\n\n";
echo "You can now test the delegate pattern!\n";
echo "Visit: http://127.0.0.1:8000/test-delegates\n";
