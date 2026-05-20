@extends('layouts.admin')
@section('title','Create institution')
@section('page_title','Create institution')

@section('content')
<div class="card card-soft" style="max-width:720px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.institutions.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Public code (URL segment)</label><input name="code" class="form-control" required placeholder="e.g. DEMO001"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <div class="mb-3"><label class="form-label">Subscription plan</label><input name="subscription_plan" class="form-control"></div>
            <div class="row">
                <div class="col mb-3"><label class="form-label">Starts</label><input type="date" name="subscription_starts_at" class="form-control"></div>
                <div class="col mb-3"><label class="form-label">Ends</label><input type="date" name="subscription_ends_at" class="form-control"></div>
            </div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="ia"><label class="form-check-label" for="ia">Active</label></div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="enquiry_enabled" value="1" checked id="ie"><label class="form-check-label" for="ie">Public enquiry enabled</label></div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.institutions.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
