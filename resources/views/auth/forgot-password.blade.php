@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="erp-auth-heading">
        <h1>Reset password</h1>
        <p>Enter your email and we will send a secure reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="erp-auth-form js-loading-form">
        @csrf

        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-primary w-100 erp-auth-submit" type="submit">
            <span class="js-submit-label"><i class="bi bi-send me-1"></i> Send reset link</span>
            <span class="js-submit-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span> Sending</span>
        </button>

        <a class="btn btn-sm btn-link w-100" href="{{ route('login') }}">Back to login</a>
    </form>
@endsection
