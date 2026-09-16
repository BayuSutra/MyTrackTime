@extends('layouts.auth')

@section('title', 'Login - My TrackTime')

@section('content')
<form method="POST" action="{{ route('login.post') }}">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
               value="{{ old('email', 'admin@gps.com') }}" required placeholder="admin@gps.com">
        @error('email')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
               required placeholder="Enter password">
        @error('password')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="#" class="auth-link" style="font-size: 13px;">Forgot?</a>
    </div>
    
    <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i> Login
    </button>
</form>

<div class="text-center mt-4">
    <p class="text-muted-custom">
        Don't have account? 
        <a href="{{ route('register') }}" class="auth-link">Register</a>
    </p>
</div>

<div class="mt-3 pt-3 border-top text-center">
    <span class="demo-badge">Demo: admin@gps.com / password123</span>
    <span class="demo-badge ms-1">user@test.com / password123</span>
</div>
@endsection