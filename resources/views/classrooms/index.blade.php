@extends('layouts.app')

@section('title', 'Classes - Madrasa ERP')

@section('content')
    @if(session('success'))
        <div style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div>
                <h2 style="margin:0">Classes</h2>
                <p style="margin:0;color:var(--muted);font-size:13px">Add and manage class structure & fees</p>
            </div>
            <span class="class-badge">
                {{ $classrooms->count() }} Classes
            </span>
        </div>
        <a href="{{ route('classrooms.create') }}" class="btn">+ Add Class</a>
    </div>

    {{-- Table --}}
    <table id="datatable">
        <thead>
            <tr>
                <th>SL</th>
                <th>Class</th>
                <th>Sections</th>
                <th>Max / Section</th>
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
                    <td><strong>{{ $classroom->name }}</strong></td>
                    <td>
                        @foreach(explode(',', $classroom->sections_string) as $sec)
                            <span class="section-chip">{{ trim($sec) }}</span>
                        @endforeach
                    </td>
                    <td>{{ $classroom->max_students_per_section }}</td>
                    <td><span class="fee-cell">৳ {{ number_format($classroom->admission_fee, 0) }}</span></td>
                    <td style="font-size:12px;color:var(--muted);max-width:220px">{{ $classroom->fees_string }}</td>
                    <td><span class="fee-total-cell">৳ {{ number_format($classroom->total_fee, 0) }}</span></td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('classrooms.edit', $classroom) }}" class="icon-btn icon-btn-edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('classrooms.destroy', $classroom) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this class?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn icon-btn-delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="color:var(--muted);padding:50px;text-align:center;font-size:14px">
                        No classes found. <a href="{{ route('classrooms.create') }}" style="color:var(--accent)">Add your first class</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('extra-styles')
<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .page-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .class-badge {
        background: rgba(33,150,243,0.15);
        color: #2196f3;
        border: 1px solid rgba(33,150,243,0.4);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    th {
        background: rgba(255,255,255,0.08);
        padding: 14px 16px;
        font-size: 14px;
        text-align: center;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    td {
        padding: 14px 16px;
        font-size: 15px;
        text-align: center;
        vertical-align: middle;
    }
    #classTable tr:nth-child(odd)  { background: rgba(255,255,255,0.03); }
    #classTable tr:nth-child(even) { background: rgba(255,255,255,0.07); }
    #classTable tr:hover           { background: rgba(227,120,20,0.12); transition: 0.15s; }

    .section-chip {
        display: inline-block;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        color: var(--text);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin: 2px;
    }
    .fee-cell {
        color: var(--accent);
        font-weight: 600;
    }
    .fee-total-cell {
        color: #4caf50;
        font-weight: 700;
    }

    /* Action buttons */
    .action-group {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
    }
    .icon-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid;
        background: transparent;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s, transform 0.1s;
        white-space: nowrap;
        line-height: 1;
    }
    .icon-btn:hover { transform: translateY(-1px); }
    .icon-btn-edit   { border-color: #ffc107; color: #ffc107; }
    .icon-btn-edit:hover { background: rgba(255,193,7,0.15); }
    .icon-btn-delete { border-color: var(--danger); color: var(--danger); }
    .icon-btn-delete:hover { background: rgba(255,78,78,0.15); }

    /* DataTables */
    .dataTables_wrapper { color: var(--text); font-size: 13px; margin-top: 10px; }
    .dataTables_length select,
    .dataTables_filter input {
        background: #1b1f22;
        color: var(--text);
        border: 1px solid rgba(255,255,255,0.15);
        padding: 5px 8px;
        border-radius: 4px;
        margin-left: 6px;
    }
    .dataTables_info { color: var(--muted) !important; margin-top: 10px; }
    .dataTables_paginate { margin-top: 10px; }
    .dataTables_paginate .paginate_button {
        color: var(--text) !important;
        padding: 5px 11px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 4px;
        margin: 0 2px;
        cursor: pointer;
    }
    .dataTables_paginate .paginate_button.current { background: var(--accent); color: #000 !important; border-color: var(--accent); }
    .dataTables_paginate .paginate_button:hover   { background: rgba(255,255,255,0.08) !important; color: var(--text) !important; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { color: var(--muted); }
    table.dataTable tbody tr { background-color: transparent; }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('scripts')
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
        "order": [],
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "columnDefs": [{ "orderable": false, "targets": "no-sort" }],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search classes...",
            "paginate": { "previous": "Prev", "next": "Next" }
        }
    });
});
</script>
@endsection
