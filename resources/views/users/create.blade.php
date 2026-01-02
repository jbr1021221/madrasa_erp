@extends('layouts.app')

@section('title', 'Add User - Madrasa ERP')

@section('content')
<div style="max-width:600px;margin:auto">
  <div style="margin-bottom:20px">
    <a href="{{ route('users.index') }}" style="color:var(--muted);text-decoration:none;font-size:14px">← Back to Users</a>
    <h2 style="margin:10px 0 0 0">Add New User</h2>
  </div>

  <form action="{{ route('users.store') }}" method="POST" style="background:var(--card);padding:24px;border-radius:var(--radius);border:1px solid rgba(255,255,255,0.05)">
    @csrf

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Full Name</label>
      <input type="text" name="name" value="{{ old('name') }}" required style="width:100%">
      @error('name') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Email Address</label>
      <input type="email" name="email" value="{{ old('email') }}" required style="width:100%">
      @error('email') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Role</label>
      <select name="role" required style="width:100%">
        <option value="admission_office" {{ old('role') == 'admission_office' ? 'selected' : '' }}>Admission Office</option>
        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
      </select>
      @error('role') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Password</label>
      <input type="password" name="password" required style="width:100%">
      @error('password') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:24px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Confirm Password</label>
      <input type="password" name="password_confirmation" required style="width:100%">
    </div>

    <button type="submit" class="btn" style="width:100%">Create User</button>
  </form>
</div>
@endsection
