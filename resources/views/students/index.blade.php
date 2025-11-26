@extends('layouts.app')

@section('title', 'Students - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px">
  <h2 style="margin:0">Students</h2>
  <p style="margin:0;color:var(--muted);font-size:13px">Manage & monitor all enrolled students</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <form method="GET" action="{{ route('students.index') }}" style="display:flex;gap:10px;flex:1;flex-wrap:wrap">
    <select name="class_id" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
      <option value="">All Classes</option>
      @foreach($classrooms as $classroom)
        <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
          {{ $classroom->name }}
        </option>
      @endforeach
    </select>

    <select name="section" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
      <option value="">All Sections</option>
      @foreach($sections as $section)
        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
          Section {{ $section }}
        </option>
      @endforeach
    </select>

    <input type="text" name="search" placeholder="Search by Name or ID" value="{{ request('search') }}" 
           style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:200px">
    
    <button type="submit" class="btn ghost" style="padding:6px 14px">Search</button>
    @if(request()->hasAny(['class_id', 'section', 'search']))
      <a href="{{ route('students.index') }}" class="btn ghost" style="padding:6px 14px">Clear</a>
    @endif
  </form>

  <a href="{{ route('students.create') }}" class="btn">+ Add Student</a>
</div>

<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>ID</th>
      <th>Class</th>
      <th>Section</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="studentTable">
    @forelse($students as $student)
    <tr>
      <td>{{ $student->name }}</td>
      <td>{{ $student->student_id }}</td>
      <td>{{ $student->classroom->name ?? 'N/A' }}</td>
      <td>{{ $student->section }}</td>
      <td style="display:flex;gap:6px;justify-content:center">
        <a href="{{ route('students.show', $student) }}" class="action-btn">View</a>
        <a href="{{ route('students.edit', $student) }}" class="action-btn">Edit</a>
        <form action="{{ route('students.destroy', $student) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure to delete this student?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="action-btn delete">Delete</button>
        </form>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="5" style="color:var(--muted);padding:40px;text-align:center">No students found. Add your first student!</td>
    </tr>
    @endforelse
  </tbody>
</table>
@endsection

@section('extra-styles')
<style>
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:rgba(255,255,255,0.15);padding:10px;font-size:14px;text-align:center}
td{padding:10px;font-size:14px;text-align:center}
#studentTable tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#studentTable tr:nth-child(even){background:rgba(255,255,255,0.09)}
#studentTable tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;cursor:pointer;font-size:13px;
  text-decoration:none;display:inline-block;margin:2px
}
.action-btn.delete{border-color:var(--danger);color:var(--danger)}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
</style>
@endsection