@extends('layouts.app')

@section('title', 'Student Details - Madrasa ERP')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <div>
    <h2 style="margin:0">{{ $student->name }}</h2>
    <p style="margin:4px 0 0 0;color:var(--muted);font-size:14px">Student ID: {{ $student->student_id }}</p>
  </div>
  <div>
    <a href="{{ route('students.edit', $student) }}" class="btn" style="margin-right:8px">Edit Student</a>
    <a href="{{ route('students.index') }}" class="btn ghost">Back to List</a>
  </div>
</div>

<div style="background:var(--card);border-radius:var(--radius);padding:24px;border:1px solid rgba(255,255,255,0.1);margin-bottom:30px">
  <h3 style="margin:0 0 20px 0;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.1)">Student Information</h3>
  
  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Basic Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Student ID</div>
      <div class="detail-value">{{ $student->student_id }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Student Name</div>
      <div class="detail-value">{{ $student->name }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Father Name</div>
      <div class="detail-value">{{ $student->father_name }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Mother Name</div>
      <div class="detail-value">{{ $student->mother_name ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Mobile</div>
      <div class="detail-value">{{ $student->mobile }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Alt Mobile</div>
      <div class="detail-value">{{ $student->alt_mobile ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Student Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Date of Birth</div>
      <div class="detail-value">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Gender</div>
      <div class="detail-value">{{ $student->gender ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Blood Group</div>
      <div class="detail-value">{{ $student->blood_group ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Last School Attended</div>
      <div class="detail-value">{{ $student->last_school ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Total Children in Family</div>
      <div class="detail-value">{{ $student->siblings_count ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Position Among Offspring</div>
      <div class="detail-value">{{ $student->birth_order ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Program Type</div>
      <div class="detail-value">{{ $student->program_type ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Shift</div>
      <div class="detail-value">{{ $student->shift ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Address Information</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Present Address</div>
      <div class="detail-value">{{ $student->address ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Permanent Address</div>
      <div class="detail-value">{{ $student->permanent_address ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Guardian Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Occupation</div>
      <div class="detail-value">{{ $student->guardian_occupation ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Nationality</div>
      <div class="detail-value">{{ $student->nationality ?? $student->guardian_nationality ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Guardian Phone</div>
      <div class="detail-value">{{ $student->guardian_mobile ?? $student->guardian_phone ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Guardian Email</div>
      <div class="detail-value">{{ $student->guardian_email ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">NID Number</div>
      <div class="detail-value">{{ $student->guardian_nid ?? 'N/A' }}</div>
    </div>
  </div>
</div>

<h3 style="margin-top:30px;margin-bottom:10px">Payment History</h3>
<table id="paymentTable">
  <thead>
    <tr>
      <th>SL</th>
      <th>Payment Date</th>
      <th>Type</th>
      <th>Period</th>
      <th>Amount</th>
      <th>Mode</th>
      <th>Note</th>
      <th>Receipt</th>
      <th>Created At</th>
    </tr>
  </thead>
  <tbody>
    @forelse($student->payments as $payment)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $payment->payment_date->format('d M, Y') }}</td>
      <td>{{ $payment->payment_type }}</td>
      <td>{{ $payment->month }}</td>
      <td>৳ {{ number_format($payment->amount, 2) }}</td>
      <td>{{ ucfirst($payment->payment_mode) }}</td>
      <td>{{ $payment->note ?? '-' }}</td>
      <td>
        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" style="color:var(--accent);text-decoration:none;font-size:13px;border:1px solid var(--accent);padding:2px 8px;border-radius:4px;transition:all 0.2s" onmouseover="this.style.background='rgba(227,120,20,0.1)'" onmouseout="this.style.background='transparent'">
           View
        </a>
      </td>
      <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="9" style="color:var(--muted);padding:20px">No payments recorded</td>
    </tr>
    @endforelse
  </tbody>
</table>
@endsection

@section('extra-styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
.btn{padding:8px 16px;border-radius:var(--radius);border:none;cursor:pointer;font-size:14px;background:var(--accent);color:#000;text-decoration:none;display:inline-block}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
.btn:hover{opacity:0.9}

/* Detail List Styles */
.detail-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:12px}
.detail-row{display:flex;flex-direction:column;padding:12px;background:rgba(255,255,255,0.03);border-radius:4px;border:1px solid rgba(255,255,255,0.05);transition:background 0.2s}
.detail-row:hover{background:rgba(255,255,255,0.05)}
.detail-label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:500;margin-bottom:6px}
.detail-value{font-size:14px;font-weight:500;color:var(--text)}

/* Table Styles */
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:rgba(255,255,255,0.15);padding:10px;font-size:14px;text-align:center}
td{padding:10px;font-size:14px;text-align:center}
#paymentTable tbody tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#paymentTable tbody tr:nth-child(even){background:rgba(255,255,255,0.09)}
#paymentTable tbody tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}

/* DataTables Customization */
.dataTables_wrapper {margin-top: 20px; color: var(--text); font-size: 14px;}
.dataTables_wrapper .top {margin-bottom: 16px;}
.dataTables_wrapper .bottom {display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 16px; flex-wrap: wrap;}
.dataTables_length {text-align: center;}
.dataTables_length select {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 4px; border-radius: 4px;}
.dataTables_filter input {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 6px; border-radius: 4px; margin-left: 8px;}
.dataTables_info {color: var(--muted) !important;}
.dataTables_paginate {display: flex; gap: 4px;}
.dataTables_paginate .paginate_button {color: var(--text) !important; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; margin: 0 2px; cursor: pointer;}
.dataTables_paginate .paginate_button.current {background: var(--accent); color: #000 !important; border-color: var(--accent);}
.dataTables_paginate .paginate_button:hover {background: rgba(255,255,255,0.1); color: var(--text) !important; border-color: rgba(255,255,255,0.2);}
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {color: var(--muted);}
table.dataTable tbody tr {background-color: transparent;}
</style>
@endsection

@section('scripts')
<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#paymentTable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[7, "desc"]], // Sort by Created At (newest first)
        "dom": '<"top"f>rt<"bottom"lip><"clear">', // Search top, table, then length/info/pagination at bottom
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search payments...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            }
        }
    });
});
</script>
@endsection
