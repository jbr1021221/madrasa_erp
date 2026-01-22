@extends('layouts.app')

@section('title', 'Accounts / Earnings - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px">
  <h2 style="margin:0">Accounts / Earnings</h2>
  <div style="color:var(--muted);font-size:13px">Overview of student fee collections</div>
</div>

<form method="GET" action="{{ route('payments.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
  
  {{-- Student Dropdown --}}
  <!-- <select name="student_search" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Students</option>
    @foreach($allStudents as $std)
      <option value="{{ $std->student_id }}" {{ request('student_search') == $std->student_id ? 'selected' : '' }}>
        {{ $std->name }} ({{ $std->student_id }})
      </option>
    @endforeach
  </select> -->

  {{-- Fee Type Dropdown --}}
  {{-- Custom Multi-select Checkbox Dropdown --}}
  <div style="position:relative;display:inline-block;">
    <button type="button" onclick="toggleFeeDropdown()" id="feeDropdownBtn" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px;text-align:left;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
      @php
        $selectedFees = (array)request('fee_name', []);
        $count = count($selectedFees);
      @endphp
      @if($count > 0)
        {{ $count }} Selected
      @else
        All Fee Types
      @endif
    </button>
    
    <div id="feeDropdownContent" style="display:none;position:absolute;top:100%;left:0;background:#1b1f22;border:1px solid rgba(255,255,255,0.15);border-radius:var(--radius);z-index:1000;padding:10px;width:220px;max-height:300px;overflow-y:auto;box-shadow:0 4px 6px rgba(0,0,0,0.3);">
      @foreach($feeTypes as $ft)
        <label style="display:flex;align-items:center;margin-bottom:8px;cursor:pointer;font-size:13px;color:var(--text);">
          <input type="checkbox" name="fee_name[]" value="{{ $ft }}" {{ in_array($ft, $selectedFees) ? 'checked' : '' }} style="margin-right:8px;accent-color:var(--primary);">
          {{ $ft }}
        </label>
      @endforeach
      
      <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.1);display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" onclick="clearFeeSelection()" style="background:transparent;border:none;color:var(--muted);font-size:12px;cursor:pointer;">Clear</button>
        <button type="submit" style="background:var(--primary, #4caf50);color:#fff;border:none;padding:4px 12px;border-radius:4px;font-size:12px;cursor:pointer;">Apply</button>
      </div>
    </div>
  </div>

  <script>
    function toggleFeeDropdown() {
      const dropdown = document.getElementById('feeDropdownContent');
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }
    
    function clearFeeSelection() {
      const inputs = document.querySelectorAll('#feeDropdownContent input[type="checkbox"]');
      inputs.forEach(input => input.checked = false);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('feeDropdownContent');
      const button = document.getElementById('feeDropdownBtn');
      if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.style.display = 'none';
      }
    });
  </script>

  <select name="class_id" id="class_id" onchange="updateSections(); this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Classes</option>
    @foreach($classrooms as $classroom)
      <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
        {{ $classroom->name }}
      </option>
    @endforeach
  </select>

  <select name="section" id="section" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Sections</option>
  </select>

  <select name="month" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Months</option>
    @foreach($months as $month)
      <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
        {{ $month }}
      </option>
    @endforeach
  </select>

  <select name="year" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Years</option>
    @foreach($years as $year)
      <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
        {{ $year }}
      </option>
    @endforeach
  </select>

  <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
  
  <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
  
  @if(request()->hasAny(['student_search', 'fee_name', 'class_id', 'section', 'month', 'year', 'start_date', 'end_date']))
    <a href="{{ route('payments.index') }}" class="btn ghost">Clear</a>
  @endif
</form>

<h3 style="margin:16px 0">
  Total Earnings: <span style="color:var(--accent)">৳ {{ number_format($totalEarnings, 2) }}</span>
</h3>

<table id="datatable">
  <thead>
    <tr>
      <th>SL</th>
      <th>Name</th>
      <th>ID</th>
      <th>Class</th>
      <th>Section</th>
      <th>Amount</th>
      <th>Date</th>
      <th>Receipt No</th>
      <th class="no-sort">Action</th>
    </tr>
  </thead>
  <tbody id="earnTable">
    @foreach($payments as $payment)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $payment->student?->name ?? 'Deleted Student' }}</td>
      <td>{{ $payment->student?->student_id ?? 'N/A' }}</td>
      <td>{{ $payment->student?->classroom?->name ?? 'N/A' }}</td>
      <td>{{ $payment->student?->section ?? '-' }}</td>
      <td>৳ {{ number_format($payment->amount_display ?? $payment->amount, 2) }}</td>
      <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
      <td>
        {{ \Carbon\Carbon::parse($payment->payment_date)->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}
      </td>
      <td>
          <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="action-btn">View</a>
      </td>
    </tr>
    @endforeach
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
        "order": [], // Respect server-side ordering (latest first)
        "dom": '<"top"f>rt<"bottom"lip><"clear">', // Search top, table, then length/info/pagination at bottom
        "columnDefs": [
            { "orderable": false, "targets": "no-sort" } // Disable sort on Action column
        ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search payments...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            },
            "emptyTable": "No payment records found",
            "zeroRecords": "No matching payments found"
        }
    });
});
</script>

<script>
const classroomData = @json($classroomData);
const allSections = @json($allSections);
const currentSection = "{{ request('section') }}";

function updateSections() {
    const classSelect = document.getElementById('class_id');
    const classId = classSelect.value;
    const sectionSelect = document.getElementById('section');
    
    // Reset section dropdown
    sectionSelect.innerHTML = '<option value="">All Sections</option>';
    
    let sectionsToShow = new Set();
    
    if (classId && classroomData[classId]) {
        // Show sections for specific class
        const data = classroomData[classId];
        if (data.sections && data.sections.length > 0) {
            data.sections.forEach(s => sectionsToShow.add(s));
        } else {
            sectionsToShow.add('A'); // Fallback
        }
    } else {
        // Show all unique sections from database
        if (allSections && allSections.length > 0) {
            allSections.forEach(s => sectionsToShow.add(s));
        }

        // Also add from classroom definitions
        Object.values(classroomData).forEach(cls => {
            if (cls.sections && cls.sections.length > 0) {
                cls.sections.forEach(s => sectionsToShow.add(s));
            }
        });

        // If no sections found, default to A, B
        if (sectionsToShow.size === 0) {
            sectionsToShow.add('A');
            sectionsToShow.add('B');
        }
    }
    
    // Populate dropdown
    Array.from(sectionsToShow).sort().forEach(section => {
        const option = document.createElement('option');
        option.value = section;
        option.textContent = `Section ${section}`;
        if (section === currentSection) {
            option.selected = true;
        }
        sectionSelect.appendChild(option);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSections();
});


</script>
@endsection