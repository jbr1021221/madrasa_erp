@extends('layouts.app')

@section('title', 'Accounts / Earnings - Madrasa ERP')

@section('content')
    @if (session('success'))
        <div style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="pay-page-header">
        <div class="pay-header-left">
            <div>
                <h2 style="margin:0">Accounts / Earnings</h2>
                <p style="margin:0;color:var(--muted);font-size:13px">Overview of student fee collections</p>
            </div>
            @if (request('status') !== 'unpaid')
                <span class="earnings-badge">
                    ৳ {{ number_format($totalEarnings, 2) }} Collected
                </span>
            @else
                <span class="unpaid-badge">
                    Unpaid — {{ request('month') ?: date('F') }}, {{ request('year') ?: date('Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="pay-filter-bar">
        <form method="GET" action="{{ route('payments.index') }}" class="pay-filter-form" id="payFilterForm">

            <div class="pay-filter-group">
                <label class="pay-filter-label">Fee Type</label>
                <div style="position:relative;">
                    <button type="button" onclick="toggleFeeDropdown()" id="feeDropdownBtn" class="pay-filter-ctrl pay-dropdown-btn">
                        @php $selectedFees = (array) request('fee_name', []); $count = count($selectedFees); @endphp
                        {{ $count > 0 ? $count . ' Selected' : 'All Fee Types' }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="feeDropdownContent" class="pay-dropdown-panel">
                        @foreach ($feeTypes as $ft)
                            <label class="pay-dropdown-item">
                                <input type="checkbox" name="fee_name[]" value="{{ $ft }}"
                                    {{ in_array($ft, $selectedFees) ? 'checked' : '' }}
                                    style="accent-color:var(--accent);margin-right:8px;">
                                {{ $ft }}
                            </label>
                        @endforeach
                        <div class="pay-dropdown-footer">
                            <button type="button" onclick="clearFeeSelection()" class="pay-dropdown-clear">Clear</button>
                            <button type="submit" class="pay-dropdown-apply">Apply</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">Class</label>
                <select name="class_id" id="class_id" onchange="updateSections(); this.form.submit()" class="pay-filter-ctrl">
                    <option value="">All Classes</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">Section</label>
                <select name="section" id="section" onchange="this.form.submit()" class="pay-filter-ctrl">
                    <option value="">All Sections</option>
                </select>
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">Month</label>
                <select name="month" onchange="this.form.submit()" class="pay-filter-ctrl">
                    <option value="">All Months</option>
                    @foreach ($months as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">Year</label>
                <select name="year" onchange="this.form.submit()" class="pay-filter-ctrl">
                    <option value="">All Years</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">From</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" class="pay-filter-ctrl">
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">To</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" class="pay-filter-ctrl">
            </div>

            <div class="pay-filter-group">
                <label class="pay-filter-label">Status</label>
                <select name="status" onchange="this.form.submit()" class="pay-filter-ctrl">
                    <option value="paid"   {{ request('status') == 'paid'   ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>

            @if (request()->hasAny(['student_search','fee_name','class_id','section','month','year','start_date','end_date','status']))
                <div class="pay-filter-group" style="justify-content:flex-end">
                    <label class="pay-filter-label">&nbsp;</label>
                    <a href="{{ route('payments.index') }}" class="btn ghost" style="padding:7px 16px;font-size:13px">Clear</a>
                </div>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <table id="datatable">
        <thead>
            <tr>
                @if (request('status') == 'unpaid')
                    <th>SL</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Father Mobile</th>
                    <th class="no-sort">Action</th>
                @else
                    <th>SL</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Receipt No</th>
                    <th class="no-sort">Action</th>
                @endif
            </tr>
        </thead>
        <tbody id="earnTable">
            @if (request('status') == 'unpaid')
                @foreach ($unpaidStudents as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->classroom->name ?? 'N/A' }}</td>
                        <td>{{ $student->section }}</td>
                        <td>{{ $student->father_mobile }}</td>
                        <td>
                            <a href="{{ route('students.show', $student->id) }}"
                               class="pay-action-btn pay-action-view">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $payment->student?->name ?? 'Deleted Student' }}</td>
                        <td>{{ $payment->student?->student_id ?? 'N/A' }}</td>
                        <td>{{ $payment->student?->classroom?->name ?? 'N/A' }}</td>
                        <td>{{ $payment->student?->section ?? '-' }}</td>
                        <td><span class="amount-cell">৳ {{ number_format($payment->amount_display ?? $payment->amount, 2) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                        <td style="font-family:monospace;font-size:12px;color:var(--muted)">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank"
                               class="pay-action-btn pay-action-receipt">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                Receipt
                            </a>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

@endsection

@section('extra-styles')
<style>
    /* Page Header */
    .pay-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    .pay-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .earnings-badge {
        background: rgba(76,175,80,0.15);
        color: #4caf50;
        border: 1px solid rgba(76,175,80,0.45);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    .unpaid-badge {
        background: rgba(231,76,60,0.15);
        color: #e74c3c;
        border: 1px solid rgba(231,76,60,0.45);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Filter Bar */
    .pay-filter-bar {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: var(--radius);
        padding: 14px 16px;
        margin-bottom: 18px;
    }
    .pay-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    .pay-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .pay-filter-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
    }
    .pay-filter-ctrl {
        background: #1b1f22;
        color: var(--text);
        border: 1px solid rgba(255,255,255,0.15);
        padding: 7px 10px;
        border-radius: var(--radius);
        font-size: 13px;
        width: 140px;
        transition: border-color 0.2s;
    }
    .pay-filter-ctrl:focus { outline: none; border-color: var(--accent); }

    /* Fee dropdown button */
    .pay-dropdown-btn {
        display: flex;
        align-items: center;
        cursor: pointer;
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .pay-dropdown-panel {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        background: #1b1f22;
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: var(--radius);
        z-index: 1000;
        padding: 10px;
        width: 210px;
        max-height: 280px;
        overflow-y: auto;
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }
    .pay-dropdown-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        cursor: pointer;
        font-size: 13px;
        color: var(--text);
    }
    .pay-dropdown-footer {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .pay-dropdown-clear {
        background: transparent;
        border: none;
        color: var(--muted);
        font-size: 12px;
        cursor: pointer;
    }
    .pay-dropdown-apply {
        background: var(--accent);
        color: #000;
        border: none;
        padding: 4px 14px;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        font-weight: 600;
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
    }
    #earnTable tr:nth-child(odd)  { background: rgba(255,255,255,0.03); }
    #earnTable tr:nth-child(even) { background: rgba(255,255,255,0.07); }
    #earnTable tr:hover           { background: rgba(227,120,20,0.12); transition: 0.15s; }

    .amount-cell {
        color: #4caf50;
        font-weight: 600;
        font-size: 13px;
    }

    /* Action Buttons */
    .pay-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.18s, transform 0.1s;
        white-space: nowrap;
    }
    .pay-action-btn:hover { transform: translateY(-1px); }
    .pay-action-view    { border-color: #2196f3; color: #2196f3; }
    .pay-action-view:hover { background: rgba(33,150,243,0.15); }
    .pay-action-receipt { border-color: #4caf50; color: #4caf50; }
    .pay-action-receipt:hover { background: rgba(76,175,80,0.15); }

    /* DataTables overrides */
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
    .dataTables_paginate .paginate_button.current {
        background: var(--accent); color: #000 !important; border-color: var(--accent);
    }
    .dataTables_paginate .paginate_button:hover {
        background: rgba(255,255,255,0.08) !important; color: var(--text) !important;
    }
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
                    "searchPlaceholder": "Search payments...",
                    "paginate": { "previous": "Prev", "next": "Next" },
                    "emptyTable": "No payment records found",
                    "zeroRecords": "No matching payments found"
                }
            });
        });

        function toggleFeeDropdown() {
            const d = document.getElementById('feeDropdownContent');
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }
        function clearFeeSelection() {
            document.querySelectorAll('#feeDropdownContent input[type="checkbox"]').forEach(i => i.checked = false);
        }
        document.addEventListener('click', function(e) {
            const d = document.getElementById('feeDropdownContent');
            const b = document.getElementById('feeDropdownBtn');
            if (!d.contains(e.target) && !b.contains(e.target)) d.style.display = 'none';
        });
    </script>

    <script>
        const classroomData = @json($classroomData);
        const allSections   = @json($allSections);
        const currentSection = "{{ request('section') }}";

        function updateSections() {
            const classId = document.getElementById('class_id').value;
            const sectionSelect = document.getElementById('section');
            sectionSelect.innerHTML = '<option value="">All Sections</option>';

            let sectionsToShow = new Set();
            if (classId && classroomData[classId]) {
                const data = classroomData[classId];
                (data.sections && data.sections.length > 0)
                    ? data.sections.forEach(s => sectionsToShow.add(s))
                    : sectionsToShow.add('A');
            } else {
                if (allSections && allSections.length > 0) allSections.forEach(s => sectionsToShow.add(s));
                Object.values(classroomData).forEach(cls => {
                    if (cls.sections) cls.sections.forEach(s => sectionsToShow.add(s));
                });
                if (sectionsToShow.size === 0) { sectionsToShow.add('A'); sectionsToShow.add('B'); }
            }

            Array.from(sectionsToShow).sort().forEach(s => {
                const o = document.createElement('option');
                o.value = s; o.textContent = `Section ${s}`;
                if (s === currentSection) o.selected = true;
                sectionSelect.appendChild(o);
            });
        }

        document.addEventListener('DOMContentLoaded', updateSections);
    </script>
@endsection
