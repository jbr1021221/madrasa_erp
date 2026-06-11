@extends('layouts.app')

@section('title', 'Students - Madrasa ERP')

@section('content')
    @if (session('success'))
        <div class="alert success"
            style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
            {{ session('success') }}
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div>
                <h2 style="margin:0">Students</h2>
                <p style="margin:0;color:var(--muted);font-size:13px">Manage & monitor all enrolled students</p>
            </div>
            <span class="active-badge">
                &#10003; {{ $activeCount }} Active
            </span>
        </div>
        <a href="{{ route('students.create') }}" class="btn">+ Add Student</a>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('students.index') }}" class="filter-form-row">
            <div class="filter-group">
                <label class="filter-label">Class</label>
                <select name="class_id" onchange="this.form.submit()" class="filter-ctrl">
                    <option value="">All Classes</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Section</label>
                <select name="section" onchange="this.form.submit()" class="filter-ctrl">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
                            {{ $section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">From</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-ctrl"
                    title="Start Date">
            </div>

            <div class="filter-group">
                <label class="filter-label">To</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-ctrl"
                    title="End Date">
            </div>

            <div class="filter-group filter-group-search">
                <label class="filter-label">Search</label>
                <input type="text" name="search" placeholder="Name or ID..." value="{{ request('search') }}"
                    class="filter-ctrl">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn ghost" style="padding:6px 16px">Filter</button>
                @if (request()->hasAny(['class_id', 'section', 'search', 'start_date', 'end_date']))
                    <a href="{{ route('students.index') }}" class="btn ghost" style="padding:6px 16px">Clear</a>
                @endif
                @if ($showInactive)
                    <a href="{{ route('students.index', request()->except('show_inactive')) }}" class="btn ghost"
                        style="padding:6px 14px">&#8592; Active Only</a>
                @else
                    <a href="{{ route('students.index', array_merge(request()->all(), ['show_inactive' => '1'])) }}"
                        class="btn ghost" style="padding:6px 14px;border-color:#e74c3c;color:#e74c3c;">Show Inactive</a>
                @endif
            </div>
        </form>

        <div class="filter-bulk">
            <label class="filter-label">Bulk</label>
            <select id="bulkActionDropdown" onchange="handleBulkAction(this.value); this.value='';" class="filter-ctrl">
                <option value="">Bulk Actions</option>
                <option value="delete">Delete Selected</option>
            </select>
        </div>
    </div>

    <form id="bulkActionForm" action="{{ route('students.bulk-destroy') }}" method="POST">
        @csrf
        @method('DELETE')

        <table id="datatable">
            <thead>
                <tr>
                    <th class="no-sort"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                    <th>SL</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
            <tbody id="studentTable">
                @foreach ($students as $index => $student)
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="{{ $student->id }}"
                                class="student-checkbox" onclick="updateBulkAction()"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->classroom->name ?? 'N/A' }}</td>
                        <td>{{ $student->section }}</td>
                        <td>
                            @if ($student->is_active)
                                <span
                                    style="background:rgba(76,175,80,0.2);color:#4caf50;padding:2px 8px;border-radius:3px;font-size:11px;border:1px solid #4caf50;">Active</span>
                            @else
                                <span
                                    style="background:rgba(231,76,60,0.2);color:#e74c3c;padding:2px 8px;border-radius:3px;font-size:11px;border:1px solid #e74c3c;">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $recurringFees = [];
                                $className = 'N/A';
                                if ($student->classroom) {
                                    $className = $student->classroom->name;

                                    // Get all class fees first
                                    $allClassFees = $student->classroom->fees ?? [];

                                    // Check if student has specific selected fees saved
                                    $subscribedFees = $student->selected_fees ?? null;
                                    $subscribedFeeNames = [];

                                    if ($subscribedFees && is_array($subscribedFees)) {
                                        foreach ($subscribedFees as $f) {
                                            if (!empty($f['name'])) {
                                                $subscribedFeeNames[] = $f['name'];
                                            }
                                        }
                                    }
                                    // Fallback: Try to find from Admission payment (Legacy support)
                                    elseif ($student->payments) {
                                        $admissionPayment = $student->payments
                                            ->where('payment_type', 'Admission')
                                            ->first();
                                        if ($admissionPayment && !empty($admissionPayment->fee_details)) {
                                            foreach ($admissionPayment->fee_details as $detail) {
                                                $name = $detail['name'] ?? '';
                                                if (strpos($name, ' - ') !== false) {
                                                    $parts = explode(' - ', $name);
                                                    $name = trim($parts[0]);
                                                }
                                                if (!empty($name)) {
                                                    $subscribedFeeNames[] = $name;
                                                }
                                            }
                                        }
                                    }

                                    if ($allClassFees) {
                                        foreach ($allClassFees as $fee) {
                                            if (isset($fee['type'])) {
                                                // Only include fees that the student is explicitly subscribed to
                                                if (
                                                    count($subscribedFeeNames) > 0 &&
                                                    in_array($fee['name'], $subscribedFeeNames)
                                                ) {
                                                    $recurringFees[] = $fee;
                                                }
                                            }
                                        }
                                    }
                                }
                                // Get paid months from student_months table
                                $paidMonths = $student->paidMonthKeys; // Returns array like ['June, 26', 'July, 26']
                            @endphp
                            <div class="action-group">
                                <button type="button" class="action-btn action-btn-fee"
                                    onclick="openPayModal({{ $student->id }}, '{{ $student->name }} ({{ $className }})', '{{ $student->father_name }}', {{ json_encode($recurringFees) }}, {{ json_encode($student->discounts ?? []) }}, {{ json_encode($paidMonths) }}, {{ json_encode($allClassFees ?? []) }}, {{ json_encode($student->partial_payments ?? []) }}, '{{ $student->created_at->format('Y-m') }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" />
                                        <line x1="1" y1="10" x2="23" y2="10" />
                                    </svg>
                                    Fees
                                </button>
                                <a href="{{ route('students.show', $student) }}" class="action-btn action-btn-view">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    View
                                </a>
                                <div class="dropdown">
                                    <button type="button" class="action-btn action-btn-more" onclick="toggleDropdown(event, '{{ $student->id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="1" />
                                            <circle cx="12" cy="5" r="1" />
                                            <circle cx="12" cy="19" r="1" />
                                        </svg>
                                    </button>
                                    <div id="dropdown-{{ $student->id }}" class="dropdown-content">
                                        <button type="button"
                                            class="dropdown-item {{ $student->is_active ? 'dropdown-item-deactivate' : 'dropdown-item-activate' }}"
                                            onclick="confirmToggleStatus('{{ route('students.toggle-status', $student) }}', {{ $student->is_active ? 'true' : 'false' }}, this)">
                                            @if ($student->is_active)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                                </svg>
                                                Inactive
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                                    <polyline points="22 4 12 14.01 9 11.01" />
                                                </svg>
                                                Active
                                            @endif
                                        </button>
                                        <button type="button" class="dropdown-item dropdown-item-delete"
                                            onclick="confirmDelete('{{ route('students.destroy', $student) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path d="M9 6V4h6v2" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </form>

    <!-- PAY MODAL -->
    @include('students.partials.payment-modal')
@endsection

@section('extra-styles')
    <style>
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .page-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .active-badge {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.45);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Filter Bar */
        .filter-bar {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-end;
            gap: 14px;
            flex-wrap: wrap;
        }

        .filter-form-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            flex: 1;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group-search {
            min-width: 180px;
        }

        .filter-label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
        }

        .filter-ctrl {
            background: #1b1f22;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 7px 10px;
            border-radius: var(--radius);
            font-size: 13px;
            width: 130px;
            transition: border-color 0.2s;
        }

        .filter-ctrl:focus {
            outline: none;
            border-color: var(--accent);
        }

        .filter-group-search .filter-ctrl {
            width: 180px;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 1px;
        }

        .filter-bulk {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-bulk .filter-ctrl {
            width: 140px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px
        }

        th {
            background: rgba(255, 255, 255, 0.15);
            padding: 14px 16px;
            font-size: 15px;
            text-align: center
        }

        td {
            padding: 14px 16px;
            font-size: 16px;
            text-align: left
        }

        #studentTable tr:nth-child(odd) {
            background: rgba(255, 255, 255, 0.04)
        }

        #studentTable tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.09)
        }

        #studentTable tr:hover {
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

        /* Soft buttons */
        .action-btn-fee {
            background-color: rgba(227, 120, 20, 0.1);
            color: #e37814;
        }

        .action-btn-fee:hover {
            background-color: rgba(227, 120, 20, 0.2);
        }

        .action-btn-fee:focus-visible {
            outline-color: #e37814;
        }

        .action-btn-view {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .action-btn-view:hover {
            background-color: rgba(33, 150, 243, 0.2);
        }

        .action-btn-view:focus-visible {
            outline-color: #2196f3;
        }

        /* More button - Minimal */
        .action-btn-more {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text);
            padding: 6px 8px;
        }

        .action-btn-more:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .action-btn-more:focus-visible {
            outline-color: rgba(255, 255, 255, 0.3);
        }

        /* More options dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .icon-btn-more {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text);
            min-width: auto;
            width: 42px;
            min-height: 38px;
            padding: 8px 0;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-btn-more:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .icon-btn-more:focus-visible {
            outline-color: rgba(255, 255, 255, 0.5);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 5px;
            background: #1b1f22;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius);
            min-width: 140px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
            z-index: 1000;
        }

        .dropdown-content.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            padding: 6px 10px;
            border: none;
            background: transparent;
            color: var(--text);
            text-align: left;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            border-radius: 0;
        }

        .dropdown-item:first-child {
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .dropdown-item-activate:hover {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .dropdown-item-deactivate:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .dropdown-item-delete:hover {
            background: rgba(255, 78, 78, 0.1);
            color: var(--danger);
        }

        .dropdown-item svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }

        .btn.ghost {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent)
        }

        /* MODAL */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            width: 800px;
            max-width: 90%;
            background: var(--card);
            padding: 30px;
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px
        }

        .modal-header h3 {
            margin: 0
        }

        .close {
            cursor: pointer;
            color: var(--muted);
            font-size: 18px
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--muted)
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            background: #1b1f22;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text);
            border-radius: 4px
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0
        }

        .pagination li a,
        .pagination li span {
            padding: 6px 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
        }

        .pagination li.active span {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }

        .pagination li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .pagination li.disabled span {
            color: var(--muted);
            cursor: not-allowed;
        }

        /* Checkbox size */
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                "order": [], // Disable initial sort
                "columnDefs": [{
                        "orderable": false,
                        "targets": "no-sort"
                    } // Disable sort on specific columns
                ],
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search records...",
                    "paginate": {
                        "previous": "Prev",
                        "next": "Next"
                    }
                }
            });
        });

        // Force reload on back button to ensure updated payment status
        window.addEventListener("pageshow", function(event) {
            var historyTraversal = event.persisted ||
                (typeof window.performance != "undefined" &&
                    window.performance.navigation.type === 2);
            if (historyTraversal) {
                window.location.reload();
            }
        });




        function closePayModal() {
            document.getElementById('payModal').style.display = 'none';
        }

        // Close on outside click
        document.getElementById('payModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePayModal();
            }
        });

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.getElementsByClassName('student-checkbox');

            for (let checkbox of checkboxes) {
                checkbox.checked = selectAll.checked;
            }
            updateBulkAction();
        }

        function updateBulkAction() {
            // Optional: You can enable/disable the dropdown based on selection if needed
            // For now, we just keep tracking handled by the dropdown's onchange
        }

        function confirmBulkDelete() {
            const checkboxes = document.getElementsByClassName('student-checkbox');
            let checkedCount = 0;
            for (let checkbox of checkboxes) {
                if (checkbox.checked) checkedCount++;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${checkedCount} students. This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulkActionForm').submit();
                }
            })
        }

        // Handle bulk action dropdown
        function handleBulkAction(action) {
            if (!action) return;

            const checkboxes = document.getElementsByClassName('student-checkbox');
            let checkedCount = 0;

            for (let checkbox of checkboxes) {
                if (checkbox.checked) checkedCount++;
            }

            if (checkedCount === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Students Selected',
                    text: 'Please select at least one student to perform bulk actions.',
                    confirmButtonColor: '#e37814'
                });
                return;
            }

            switch (action) {
                case 'delete':
                    confirmBulkDelete();
                    break;

                case 'export-pdf':
                    Swal.fire({
                        icon: 'info',
                        title: 'Export to PDF',
                        text: `Exporting ${checkedCount} selected students to PDF...`,
                        confirmButtonColor: '#e37814',
                        timer: 2000
                    });
                    // TODO: Implement PDF export functionality
                    break;

                case 'export-excel':
                    Swal.fire({
                        icon: 'info',
                        title: 'Export to Excel',
                        text: `Exporting ${checkedCount} selected students to Excel...`,
                        confirmButtonColor: '#e37814',
                        timer: 2000
                    });
                    // TODO: Implement Excel export functionality
                    break;
            }
        }

        function confirmDelete(url) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteForm');
                    form.action = url;
                    form.submit();
                }
            })
        }

        function confirmToggleStatus(url, isActive, btn) {
            const action = isActive ? 'Deactivate' : 'Activate';
            const color = isActive ? '#e74c3c' : '#4caf50';
            const icon = isActive ? 'warning' : 'question';

            Swal.fire({
                title: action + ' Student?',
                text: isActive ?
                    'This student will be hidden from the active student list.' :
                    'This student will be restored to the active student list.',
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#555',
                confirmButtonText: 'Yes, ' + action + '!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    }).then(response => {
                        if (response.ok || response.redirected) {
                            window.location.reload();
                        } else {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        }
                    }).catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                }
            });
        }

        // Toggle dropdown menu
        function toggleDropdown(event, studentId) {
            event.stopPropagation();
            const dropdownContent = document.getElementById('dropdown-' + studentId);

            // Close all other dropdowns
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            allDropdowns.forEach(dropdown => {
                if (dropdown.id !== 'dropdown-' + studentId) {
                    dropdown.classList.remove('show');
                }
            });

            // Toggle current dropdown
            dropdownContent.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        });
    </script>

    <form id="deleteForm" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>
@endsection
