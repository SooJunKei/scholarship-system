<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        User::firstOrCreate(
            ['email' => 'admin@scholarship.test'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'faculty@scholarship.test'],
            [
                'name'     => 'Faculty User',
                'password' => Hash::make('password'),
                'role'     => 'faculty',
            ]
        );

        // Create students
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

        // Create scholarships
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

        // Create applications
        Application::firstOrCreate(
            [
                'student_id' => $student1->id,
                'scholarship_id' => $scholarship1->id,
            ],
            [
                'date_applied' => now()->subDays(10),
                'status' => 'Pending',
            ]
        );

        Application::firstOrCreate(
            [
                'student_id' => $student2->id,
                'scholarship_id' => $scholarship2->id,
            ],
            [
                'date_applied' => now()->subDays(8),
                'status' => 'Under Review',
            ]
        );

        Application::firstOrCreate(
            [
                'student_id' => $student3->id,
                'scholarship_id' => $scholarship3->id,
            ],
            [
                'date_applied' => now()->subDays(5),
                'status' => 'Pending',
            ]
        );

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('👥 Users: 2 (admin@scholarship.test / password)');
        $this->command->info('🎓 Students: 3');
        $this->command->info('💰 Scholarships: 3');
        $this->command->info('📝 Applications: 3');
    }
}
