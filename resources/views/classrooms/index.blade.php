@extends('layouts.app')

@section('title', 'Classes - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px">
  <h2 style="margin:0">Classes</h2>
  <p style="margin:0;color:var(--muted);font-size:13px">Add and manage class structure & fees</p>
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
  <a href="{{ route('classrooms.create') }}" class="btn">+ Add Class</a>
</div>

<table>
  <thead>
    <tr>
      <th>Class</th>
      <th>Sections</th>
      <th>Max/Section</th>
      <th>Admission Fee</th>
      <th>Additional Fees</th>
      <th>Total Fee</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="classTable">
    @forelse($classrooms as $classroom)
    <tr>
      <td>{{ $classroom->name }}</td>
      <td>{{ $classroom->sections_string }}</td>
      <td>{{ $classroom->max_students_per_section }}</td>
      <td>৳ {{ number_format($classroom->admission_fee, 0) }}</td>
      <td>{{ $classroom->fees_string }}</td>
      <td>৳ {{ number_format($classroom->total_fee, 0) }}</td>
      <td style="display:flex;gap:6px;justify-content:center">
        <a href="{{ route('classrooms.edit', $classroom) }}" class="action-btn">Edit</a>
        <form action="{{ route('classrooms.destroy', $classroom) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure to delete this class?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="action-btn delete">Delete</button>
        </form>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="7" style="color:var(--muted);padding:40px;text-align:center">No classes found. Add your first class!</td>
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
#classTable tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#classTable tr:nth-child(even){background:rgba(255,255,255,0.09)}
#classTable tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;cursor:pointer;font-size:13px;
  text-decoration:none;display:inline-block;margin:2px
}
.action-btn.delete{border-color:var(--danger);color:var(--danger)}
.action-btn:hover{background:rgba(227,120,20,0.1)}
.action-btn.delete:hover{background:rgba(239,68,68,0.1)}
</style>
@endsection