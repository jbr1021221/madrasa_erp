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
                    <td style="display:flex;gap:6px;justify-content:center">
                        <a href="{{ route('users.edit', $user) }}" class="action-btn edit" title="Edit User">Edit</a>
                        @if ($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this user?');"
                                style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" title="Delete User">Delete</button>
                            </form>
                        @endif
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

        .action-btn {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid var(--accent);
            color: var(--accent);
            background: transparent;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            transition: all 0.2s
        }

        .action-btn:hover {
            background: rgba(227, 120, 20, 0.15)
        }

        .action-btn.delete {
            border-color: var(--danger);
            color: var(--danger)
        }

        .action-btn.delete:hover {
            background: rgba(255, 78, 78, 0.15)
        }

        .action-btn.edit {
            border-color: #ffc107;
            color: #ffc107
        }

        .action-btn.edit:hover {
            background: rgba(255, 193, 7, 0.15)
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
