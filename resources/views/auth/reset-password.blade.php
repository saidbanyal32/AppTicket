@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="erp-auth-heading">
        <h1>New password</h1>
        <p>Create a strong password for your ERP account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="erp-auth-form js-loading-form">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="email" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="password">Password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
        </div>

        <button class="btn btn-primary w-100 erp-auth-submit" type="submit">
            <span class="js-submit-label"><i class="bi bi-shield-check me-1"></i> Update password</span>
            <span class="js-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span> Updating</span>
        </button>
    </form>
@endsection
