<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Student</label>
        <select name="student_id" class="form-select" required>
            <option value="">Select student</option>
            @foreach($students as $s)
            <option value="{{ $s->id }}" {{ old('student_id', $application->student_id ?? '') == $s->id ? 'selected' : '' }}>
                {{ $s->full_name }} ({{ $s->student_number }})
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Scholarship</label>
        <select name="scholarship_id" class="form-select" required>
            <option value="">Select scholarship</option>
            @foreach($scholarships as $sc)
            <option value="{{ $sc->id }}" {{ old('scholarship_id', $application->scholarship_id ?? '') == $sc->id ? 'selected' : '' }}>
                {{ $sc->scholarship_name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Date Applied</label>
        <input type="datetime-local" name="date_applied" class="form-control"
            value="{{ old('date_applied', isset($application) ? $application->date_applied->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            @foreach(['Pending','Under Review','Approved','Rejected'] as $st)
            <option value="{{ $st }}" {{ old('status', $application->status ?? 'Pending') == $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>
    </div>
</div>
