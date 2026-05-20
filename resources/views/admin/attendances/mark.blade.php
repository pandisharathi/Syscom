@extends('layouts.admin')
@section('title','Mark attendance')
@section('page_title','Mark attendance — '.$batch->name)

@section('content')
<form method="post" action="{{ route('admin.attendances.store', $batch) }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="attendance_date" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="Optional">
        </div>
    </div>
    <div class="card card-soft mt-3">
        <div class="card-body">
            <div class="fw-semibold mb-2">Students (default Present — tick Absent)</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th width="40">Absent</th><th>Name</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td><input class="form-check-input" type="checkbox" name="absent_student_ids[]" value="{{ $s->id }}"></td>
                            <td>{{ $s->full_name }}</td>
                            <td><span class="badge bg-secondary">{{ $s->status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <button class="btn btn-primary mt-3">Save attendance</button>
    <a href="{{ route('admin.attendances.index') }}" class="btn btn-link mt-3">Back</a>
</form>
@endsection
