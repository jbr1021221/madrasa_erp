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

<table id="datatable">
  <thead>
    <tr>
      <th>SL</th>
      <th>Class</th>
      <th>Sections</th>
      <th>Max/Section</th>
      <th>Admission Fee</th>
      <th>Additional Fees</th>
      <th>Total Fee</th>
      <th class="no-sort">Actions</th>
    </tr>
  </thead>
  <tbody id="classTable">
    @forelse($classrooms as $classroom)
    <tr>
      <td>{{ $loop->iteration }}</td>
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
      <td colspan="8" style="color:var(--muted);padding:40px;text-align:center">No classes found. Add your first class!</td>
    </tr>
    @endforelse
  </tbody>
</table>
@endsection

@section('scripts')
<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [], // No initial sort
        "dom": '<"top"f>rt<"bottom"lip><"clear">', // Search top, table, then length/info/pagination at bottom
        "columnDefs": [
            { "orderable": false, "targets": "no-sort" } // Disable sort on Actions column
        ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search classes...",
            
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            }
        }
    });
});
</script>
@endsection