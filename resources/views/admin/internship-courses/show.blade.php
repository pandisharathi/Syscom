@extends('layouts.admin')
@section('title','Internship Course Details')
@section('page_title','Internship Course Details')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <div class="row g-4">
            @if($internship_course->course_image)
            <div class="col-md-4 text-center">
                <img src="{{ Storage::url($internship_course->course_image) }}" class="img-fluid rounded shadow-sm" style="max-height:200px">
            </div>
            @endif
            <div class="col-md-8">
                <table class="table table-borderless mb-0">
                    <tr><th style="width:140px" class="text-muted">Code</th><td><strong>{{ $internship_course->code }}</strong></td></tr>
                    <tr><th class="text-muted">Name</th><td>{{ $internship_course->name }}</td></tr>
                    <tr><th class="text-muted">Duration</th><td>{{ $internship_course->duration ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Fees</th><td>{{ $internship_course->fees ? '$'.number_format($internship_course->fees, 2) : '—' }}</td></tr>
                    <tr><th class="text-muted">Start Date</th><td>{{ $internship_course->start_date?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">End Date</th><td>{{ $internship_course->end_date?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Status</th><td><span class="badge bg-{{ $internship_course->status==='active'?'success':'secondary' }}">{{ $internship_course->status }}</span></td></tr>
                </table>
            </div>
            @if($internship_course->description)
            <div class="col-12">
                <label class="form-label fw-semibold text-muted">Description</label>
                <p class="mb-0">{{ $internship_course->description }}</p>
            </div>
            @endif
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.internship-courses.edit', $internship_course) }}" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i>Edit</a>
            <a href="{{ route('admin.internship-courses.index') }}" class="btn btn-link">Back to List</a>
        </div>
    </div>
</div>
@endsection
