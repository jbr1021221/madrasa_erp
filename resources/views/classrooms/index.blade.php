@extends('layouts.app')

@section('title', 'Classes - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:center;">
  <div>
    <h2 style="margin:0">Classes</h2>
    <div style="color:var(--muted);font-size:13px">Add and manage class structure & fees</div>
  </div>
  <a href="{{ route('classrooms.create') }}" class="btn">+ Add Class</a>
</div>

<table>
  <thead>
    <tr>
      <th>Class</th>
      <th>Sections</th>
      <th>Max/Section</th>
      <th>Fees</th>
      <th>Total Fee</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($classrooms as $classroom)
    <tr>
      <td>{{ $classroom->name }}</td>
      <td>{{ $classroom->sections_string }}</td>
      <td>{{ $classroom->max_students_per_section }}</td>
      <td style="font-size:12px">{{ $classroom->fees_string }}</td>
      <td>৳ {{ number_format($classroom->total_fee, 0) }}</td>
      <td style="display:flex;gap:6px;justify-content:center">
        <a href="{{ route('classrooms.edit', $classroom) }}" class="action-btn">Edit</a>
        <form action="{{ route('classrooms.destroy', $classroom) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this class?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="action-btn delete">Delete</button>
        </form>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="6" style="color:var(--muted);padding:40px">No classes found. Add your first class!</td>
    </tr>
    @endforelse
  </tbody>
</table>
@endsection

@section('extra-styles')
<style>
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;cursor:pointer;font-size:13px;
  text-decoration:none;display:inline-block;margin:2px
}
.action-btn.delete{border-color:var(--danger);color:var(--danger)}
</style>
@endsection