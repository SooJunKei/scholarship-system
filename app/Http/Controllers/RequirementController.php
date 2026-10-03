<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    public function index()
    {
        $requirements = Requirement::latest()->paginate(15);
        return view('requirements.index', compact('requirements'));
    }

    public function create()
    {
        return view('requirements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'requirement_name' => 'required|string|max:200',
            'description'      => 'required|string',
        ]);

        Requirement::create($data);
        return redirect()->route('requirements.index')->with('success', 'Requirement added.');
    }

    public function edit(Requirement $requirement)
    {
        return view('requirements.edit', compact('requirement'));
    }

    public function update(Request $request, Requirement $requirement)
    {
        $data = $request->validate([
            'requirement_name' => 'required|string|max:200',
            'description'      => 'required|string',
        ]);

        $requirement->update($data);
        return redirect()->route('requirements.index')->with('success', 'Requirement updated.');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();
        return redirect()->route('requirements.index')->with('success', 'Requirement deleted.');
    }
}
