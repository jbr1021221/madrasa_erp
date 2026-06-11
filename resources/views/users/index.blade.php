@extends('layouts.app')

@section('title', 'Users - Madrasa ERP')

@section('content')
    @if (session('success'))
        <div class="alert success"
            style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert error"
            style="background:rgba(239,68,68,0.2);color:#f87171;padding:12px;border-radius:6px;margin-bottom:16px">
            {{ session('error') }}
        </div>
    @endif

    <div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
        <div>
            <h2 style="margin:0">Users</h2>
            <p style="margin:0;color:var(--muted);font-size:13px">Manage system users and roles</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn">Add New User</a>
    </div>

    <table id="datatable">
        <thead>
            <tr>
                <th style="width:50px">SL</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th class="no-sort">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span
                            style="padding:4px 8px;border-radius:4px;font-size:12px;text-transform:uppercase;background:{{ $user->role === 'admin' ? 'rgba(227,120,20,0.2)' : 'rgba(255,255,255,0.1)' }};color:{{ $user->role === 'admin' ? 'var(--accent)' : 'var(--text)' }}">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('users.edit', $user) }}" class="action-btn action-btn-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit
                            </a>
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6l-1 14H6L5 6"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M9 6V4h6v2"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('extra-styles')
    <style>
        .dataTables_filter {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px
        }

        th {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            font-size: 14px;
            text-align: center
        }

        td {
            padding: 10px;
            font-size: 14px;
            text-align: left
        }

        #datatable tr:nth-child(odd) {
            background: rgba(255, 255, 255, 0.04)
        }

        #datatable tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.09)
        }

        #datatable tr:hover {
            background: rgba(227, 120, 20, 0.18);
            transition: 0.2s
        }

        /* Action group */
        .action-group {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            line-height: 1;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .action-btn:focus-visible {
            outline: 2px solid;
            outline-offset: 2px;
        }

        .action-btn svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            margin-left: -4px;
        }

        /* Soft button */
        .action-btn-soft {
            background-color: rgba(255, 193, 7, 0.1);
            color: #f59e0b;
        }

        .action-btn-soft:hover {
            background-color: rgba(255, 193, 7, 0.2);
        }

        .action-btn-soft:focus-visible {
            outline-color: #f59e0b;
        }

        /* Danger button - Soft red */
        .action-btn-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .action-btn-danger:hover {
            background-color: rgba(239, 68, 68, 0.2);
        }

        .action-btn-danger:focus-visible {
            outline-color: #ef4444;
        }

        /* DataTables Customization */
        .dataTables_wrapper {
            margin-top: 20px;
            color: var(--text);
            font-size: 14px;
        }

        .dataTables_length select {
            background: #1b1f22;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 4px;
            border-radius: 4px;
        }

        .dataTables_filter input {
            background: #1b1f22;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 6px;
            border-radius: 4px;
            margin-left: 8px;
        }

        .dataTables_info {
            color: var(--muted) !important;
            margin-top: 10px;
        }

        .dataTables_paginate {
            margin-top: 10px;
        }

        .dataTables_paginate .paginate_button {
            color: var(--text) !important;
            padding: 6px 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin: 0 2px;
            cursor: pointer;
        }

        .dataTables_paginate .paginate_button.current {
            background: var(--accent);
            color: #000 !important;
            border-color: var(--accent);
        }

        .dataTables_paginate .paginate_button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text) !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--muted);
        }

        table.dataTable tbody tr {
            background-color: transparent;
        }
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
                "columnDefs": [{
                    "orderable": false,
                    "targets": "no-sort"
                }],
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search users...",
                    "paginate": {
                        "previous": "Prev",
                        "next": "Next"
                    }
                }
            });
        });
    </script>
@endsection
