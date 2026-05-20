<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4 mt-5">
                <div class="card-body p-4">
                    <h4 class="mb-3 fw-semibold">Sign in</h4>
                    <p class="text-muted small mb-4">Single login for Super Admin, Institution, Faculty, Staff, and Student roles.</p>
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="post" action="{{ route('login.attempt') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button class="btn btn-primary w-100 rounded-pill py-2" type="submit">Continue</button>
                    </form>
                    <p class="small text-muted mt-3 mb-0">Demo: superadmin@syscom.test / password</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
