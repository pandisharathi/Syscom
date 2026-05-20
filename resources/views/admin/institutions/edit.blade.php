@extends('layouts.admin')
@section('title','Edit institution')
@section('page_title','Edit institution')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.institutions.update', $institution) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ $institution->name }}" required></div>
            <div class="mb-3"><label class="form-label">Public code</label><input name="code" class="form-control" value="{{ $institution->code }}" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ $institution->email }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ $institution->phone }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ $institution->address }}</textarea></div>
            <div class="mb-3"><label class="form-label">Subscription plan</label><input name="subscription_plan" value="{{ $institution->subscription_plan }}" class="form-control"></div>
            <div class="row">
                <div class="col mb-3"><label class="form-label">Starts</label><input type="date" name="subscription_starts_at" value="{{ optional($institution->subscription_starts_at)->format('Y-m-d') }}" class="form-control"></div>
                <div class="col mb-3"><label class="form-label">Ends</label><input type="date" name="subscription_ends_at" value="{{ optional($institution->subscription_ends_at)->format('Y-m-d') }}" class="form-control"></div>
            </div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked($institution->is_active)><label class="form-check-label" for="ia">Active</label></div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="enquiry_enabled" value="1" id="ie" @checked($institution->enquiry_enabled)><label class="form-check-label" for="ie">Public enquiry enabled</label></div>

            <hr>
            <div class="fw-semibold mb-2">Modules (enable/disable)</div>
            @php $keys = ['dashboard','institutions','students','internship','attendance','expense','reports','settings','users']; @endphp
            @foreach($keys as $key)
                @php $mod = $institution->modules->firstWhere('module_key',$key); @endphp
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="modules[{{ $key }}]" value="1" id="m{{ $key }}" @checked($mod ? $mod->enabled : true)>
                    <label class="form-check-label" for="m{{ $key }}">{{ ucfirst($key) }}</label>
                </div>
            @endforeach

            <div class="mt-3">
                <button class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.institutions.index') }}" class="btn btn-link">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
