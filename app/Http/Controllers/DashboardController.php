<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'totalStudents'       => Student::count(),
            'totalScholarships'   => Scholarship::count(),
            'totalApplications'   => Application::count(),
            'pendingApplications' => Application::where('status', 'Pending')->count(),
            'activityLogs'        => ActivityLog::with('user')->latest()->take(15)->get(),
        ]);
    }
}
