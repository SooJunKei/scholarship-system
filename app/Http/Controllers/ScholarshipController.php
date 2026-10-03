<?php

namespace App\Http\Controllers;

use App\Events\ScholarshipCreated;
use App\Models\Requirement;
use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::withCount('applications')->latest()->paginate(15);
        return view('scholarships.index', compact('scholarships'));
    }

    public function create()
    {
        $requirements = Requirement::all();
        return view('scholarships.create', compact('requirements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scholarship_name'     => 'required|string|max:200',
            'description'          => 'required|string',
            'sponsor'              => 'required|string|max:200',
            'amount'               => 'required|numeric|min:0',
            'application_deadline' => 'required|date',
            'requirements'         => 'nullable|array',
            'requirements.*'       => 'exists:requirements,id',
        ]);

        $scholarship = Scholarship::create($data);

        if (!empty($data['requirements'])) {
            $scholarship->requirements()->sync($data['requirements']);
        }

        ScholarshipCreated::dispatch($scholarship);

        return redirect()->route('scholarships.index')->with('success', 'Scholarship created successfully.');
    }

    public function show(Scholarship $scholarship)
    {
        $scholarship->load('requirements', 'applications.student');
        return view('scholarships.show', compact('scholarship'));
    }

    public function edit(Scholarship $scholarship)
    {
        $requirements = Requirement::all();
        $selected = $scholarship->requirements->pluck('id')->toArray();
        return view('scholarships.edit', compact('scholarship', 'requirements', 'selected'));
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $data = $request->validate([
            'scholarship_name'     => 'required|string|max:200',
            'description'          => 'required|string',
            'sponsor'              => 'required|string|max:200',
            'amount'               => 'required|numeric|min:0',
            'application_deadline' => 'required|date',
            'requirements'         => 'nullable|array',
            'requirements.*'       => 'exists:requirements,id',
        ]);

        $scholarship->update($data);
        $scholarship->requirements()->sync($data['requirements'] ?? []);

        return redirect()->route('scholarships.index')->with('success', 'Scholarship updated successfully.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();
        return redirect()->route('scholarships.index')->with('success', 'Scholarship deleted.');
    }
}
