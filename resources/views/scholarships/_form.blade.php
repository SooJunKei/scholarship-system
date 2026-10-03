<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Scholarship Name</label>
        <input type="text" name="scholarship_name" class="form-control" value="{{ old('scholarship_name', $scholarship->scholarship_name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Sponsor</label>
        <input type="text" name="sponsor" class="form-control" value="{{ old('sponsor', $scholarship->sponsor ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Amount (₱)</label>
        <input type="number" name="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $scholarship->amount ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Application Deadline</label>
        <input type="date" name="application_deadline" class="form-control" value="{{ old('application_deadline', isset($scholarship) ? $scholarship->application_deadline->format('Y-m-d') : '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $scholarship->description ?? '') }}</textarea>
    </div>
    @if($requirements->count())
    <div class="col-12">
        <label class="form-label">Requirements</label>
        <div class="row g-2">
            @foreach($requirements as $req)
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requirements[]" value="{{ $req->id }}" id="req_{{ $req->id }}"
                        {{ in_array($req->id, old('requirements', $selected ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="req_{{ $req->id }}">{{ $req->requirement_name }}</label>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
