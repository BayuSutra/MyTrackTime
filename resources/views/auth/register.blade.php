@extends('layouts.auth')

@section('title', 'Register - My TrackTime')

@section('content')
<form method="POST" action="{{ route('register.post') }}">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
               value="{{ old('name') }}" required placeholder="John Doe">
        @error('name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
               value="{{ old('email') }}" required placeholder="john@example.com">
        @error('email')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
               required placeholder="Min 8 characters">
        @error('password')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" 
               required placeholder="Confirm password">
    </div>
    
    <button type="submit" class="btn-login">
        <i class="bi bi-person-plus me-2"></i> Register
    </button>
</form>

<div class="text-center mt-4">
    <p class="text-muted-custom">
        Already have account? 
        <a href="{{ route('login') }}" class="auth-link">Login</a>
    </p>
</div>
@endsection