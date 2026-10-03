<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Student Number</label>
        <input type="text" name="student_number" class="form-control" value="{{ old('student_number', $student->student_number ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Middle Name</label>
        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $student->middle_name ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select" required>
            <option value="">Select</option>
            @foreach(['Male','Female'] as $g)
            <option value="{{ $g }}" {{ old('gender', $student->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', isset($student) ? $student->date_of_birth : '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Year Level</label>
        <input type="number" name="year_level" class="form-control" min="1" max="6" value="{{ old('year_level', $student->year_level ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Course</label>
        <input type="text" name="course" class="form-control" value="{{ old('course', $student->course ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="{{ old('department', $student->department ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2" required>{{ old('address', $student->address ?? '') }}</textarea>
    </div>
</div>
