@extends('layouts.vali')

@section('title', 'Create User')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
    <h1 style="margin-bottom: 2rem;">Create New User</h1>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div style="color: #dc3545; margin-top: 0.5rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div style="color: #dc3545; margin-top: 0.5rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="8">
            @error('password')
                <div style="color: #dc3545; margin-top: 0.5rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="enroll_id">Enroll ID (Device PIN) *</label>
            <input type="text" id="enroll_id" name="enroll_id" value="{{ old('enroll_id') }}" required placeholder="e.g., 1, 2, 3..." pattern="[0-9]+" maxlength="9">
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <strong>Important:</strong> This must be a numeric value (1-65535) and will be used as the user ID on the ZKTeco device. 
                Each user must have a unique Enroll ID.
            </small>
            @error('enroll_id')
                <div style="color: #dc3545; margin-top: 0.5rem;">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="{{ route('users.index') }}" class="btn" style="background: #6c757d; color: white;">Cancel</a>
        </div>
    </form>
        </div>
    </div>
</div>
@endsection

