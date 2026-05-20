@extends('layouts.admin')
@section('title','Settings')
@section('page_title','Institution settings')

@section('content')
@if(!$institution)
    <div class="alert alert-warning">Super Admin: institution-specific settings are managed under Institutions.</div>
@else
<form method="post" action="{{ route('admin.settings.institution') }}" class="card card-soft" style="max-width:720px">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ $institution->name }}" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input name="email" value="{{ $institution->email }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ $institution->phone }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ $institution->address }}</textarea></div>
        <button class="btn btn-primary">Save</button>
    </div>
</form>

<div class="card card-soft mt-3" style="max-width:720px">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Public internship enquiry</div>
            <div class="small text-muted">URL: <code>{{ url('/internship-enquiry/'.$institution->code) }}</code></div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="toggleEnquiry">{{ $institution->enquiry_enabled ? 'Disable' : 'Enable' }}</button>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$('#toggleEnquiry').on('click', function(){
    fetch(`{{ route('admin.settings.enquiry-toggle') }}`, {method:'POST', headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(()=>location.reload());
});
</script>
@endpush
