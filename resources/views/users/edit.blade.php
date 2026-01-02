@extends('layouts.app')

@section('title', 'Edit User - Madrasa ERP')

@section('content')
<div style="max-width:600px;margin:auto">
  <div style="margin-bottom:20px">
    <a href="{{ route('users.index') }}" style="color:var(--muted);text-decoration:none;font-size:14px">← Back to Users</a>
    <h2 style="margin:10px 0 0 0">Edit User</h2>
  </div>

  <form action="{{ route('users.update', $user) }}" method="POST" style="background:var(--card);padding:24px;border-radius:var(--radius);border:1px solid rgba(255,255,255,0.05)">
    @csrf
    @method('PUT')

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Full Name</label>
      <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%">
      @error('name') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Email Address</label>
      <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%">
      @error('email') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Role</label>
      <select name="role" required style="width:100%">
        <option value="admission_office" {{ old('role', $user->role) == 'admission_office' ? 'selected' : '' }}>Admission Office</option>
        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
      </select>
      @error('role') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
    </div>

    @php
      $userPermissions = old('permissions', $user->permissions ?? []);
    @endphp

    <div style="margin-bottom:16px;padding:16px;background:rgba(255,255,255,0.03);border-radius:6px;border:1px solid rgba(255,255,255,0.05)">
      <h3 style="margin:0 0 16px 0;font-size:16px;color:var(--text)">Permissions</h3>
      <p style="margin:0 0 16px 0;font-size:13px;color:var(--muted)">Admins have all permissions automatically. Select specific permissions for other users.</p>
      
      <!-- Students Section -->
      <div style="margin-bottom:20px">
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:0.5px">Students Management</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="students.create" {{ in_array('students.create', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Create Students</span>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="students.edit" {{ in_array('students.edit', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Edit Students</span>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="students.delete" {{ in_array('students.delete', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Delete Students</span>
          </label>
        </div>
      </div>

      <!-- Classes Section -->
      <div style="margin-bottom:20px">
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:0.5px">Classes Management</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="classes.create" {{ in_array('classes.create', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Create Classes</span>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="classes.edit" {{ in_array('classes.edit', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Edit Classes</span>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="classes.delete" {{ in_array('classes.delete', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Delete Classes</span>
          </label>
        </div>
      </div>

      <!-- Payments Section -->
      <div style="margin-bottom:0">
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:0.5px">Payments Management</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="payments.view" {{ in_array('payments.view', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">View Payments</span>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(255,255,255,0.02);border-radius:4px;cursor:pointer;transition:0.2s" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
            <input type="checkbox" name="permissions[]" value="payments.update" {{ in_array('payments.update', $userPermissions) ? 'checked' : '' }}>
            <span style="font-size:13px">Update Payments</span>
          </label>
        </div>
      </div>
    </div>

    <div style="margin-top:24px;margin-bottom:16px;border-top:1px solid rgba(255,255,255,0.1);padding-top:16px">
      <h3 style="margin:0 0 12px 0;font-size:16px">Change Password <span style="font-weight:normal;font-size:13px;color:var(--muted)">(Leave blank to keep current)</span></h3>
      
      <div style="margin-bottom:16px">
        <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">New Password</label>
        <input type="password" name="password" style="width:100%">
        @error('password') <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
      </div>

      <div style="margin-bottom:16px">
        <label style="display:block;margin-bottom:6px;color:var(--muted);font-size:14px">Confirm New Password</label>
        <input type="password" name="password_confirmation" style="width:100%">
      </div>
    </div>

    <button type="submit" class="btn" style="width:100%">Update User</button>
  </form>
</div>
@endsection
