@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="erp-auth-heading">
        <h1>Sign in</h1>
        <p>Use your Login username or email to continue.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="erp-auth-form js-loading-form">
        @csrf

        <div>
            <label class="form-label" for="login">Username / Email</label>
            <input class="form-control @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}" autocomplete="username" autofocus required>
            @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div>
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="password">Password</label>
                <a class="erp-auth-link" href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror js-password-input" id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="btn btn-outline-secondary js-password-toggle" type="button" title="Show password">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input" id="remember" type="checkbox" name="remember" value="1">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
        </div>

        <button class="btn btn-primary w-100 erp-auth-submit" type="submit">
            <span class="js-submit-label"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
            <span class="js-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span> Signing in</span>
        </button>
    </form>
@endsection
