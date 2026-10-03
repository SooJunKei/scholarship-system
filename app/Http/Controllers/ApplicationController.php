<?php

namespace App\Http\Controllers;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Student;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with('student', 'scholarship')->latest()->paginate(15);
        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        $students     = Student::orderBy('last_name')->get();
        $scholarships = Scholarship::orderBy('scholarship_name')->get();
        return view('applications.create', compact('students', 'scholarships'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'scholarship_id' => 'required|exists:scholarships,id',
            'date_applied'   => 'required|date',
            'status'         => 'required|in:Pending,Under Review,Approved,Rejected',
        ]);

        $application = Application::create($data);

        ApplicationSubmitted::dispatch($application);

        return redirect()->route('applications.index')->with('success', 'Application submitted.');
    }

    public function show(Application $application)
    {
        $application->load('student', 'scholarship', 'evaluations.evaluator', 'award', 'submittedRequirements.requirement');
        return view('applications.show', compact('application'));
    }

    public function edit(Application $application)
    {
        $students     = Student::orderBy('last_name')->get();
        $scholarships = Scholarship::orderBy('scholarship_name')->get();
        return view('applications.edit', compact('application', 'students', 'scholarships'));
    }

    public function update(Request $request, Application $application)
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'scholarship_id' => 'required|exists:scholarships,id',
            'date_applied'   => 'required|date',
            'status'         => 'required|in:Pending,Under Review,Approved,Rejected',
        ]);

        $oldStatus = $application->status;

        $application->update($data);

        if ($oldStatus !== $data['status']) {
            ApplicationStatusChanged::dispatch($application, $oldStatus, $data['status']);
        }

        return redirect()->route('applications.index')->with('success', 'Application updated.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return redirect()->route('applications.index')->with('success', 'Application deleted.');
    }
}
