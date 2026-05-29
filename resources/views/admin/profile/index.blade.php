@extends('layouts.admin')
@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-md-6 col-lg-5">
        <form method="post" action="{{ route('admin.profile.update') }}" class="card card-soft">
            @csrf
            @method('PUT')
            <div class="card-body">
                <h5 class="card-title mb-4">Personal Details</h5>
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                </div>

                <hr class="my-4">
                <h5 class="card-title mb-3">Change Password</h5>
                <p class="small text-muted mb-3">Leave blank if you don't want to change your password.</p>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-6 col-lg-7 mt-4 mt-md-0">
        <div class="card card-soft bg-light border-0">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-user-circle text-secondary" style="font-size: 80px; opacity: 0.5;"></i>
                <h4 class="mt-4">{{ $user->name }}</h4>
                <div class="text-muted mb-2">{{ $user->email }}</div>
                <div class="badge bg-primary text-uppercase">{{ $user->role->name ?? 'User' }}</div>
                
                @if($user->institution)
                <div class="mt-3 text-muted small">
                    <i class="fa-solid fa-building me-1"></i> {{ $user->institution->name }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
