@extends('layouts.app')

@section('title', 'Students - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px">
  <h2 style="margin:0">Students</h2>
  <p style="margin:0;color:var(--muted);font-size:13px">Manage & monitor all enrolled students</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <form method="GET" action="{{ route('students.index') }}" style="display:flex;gap:10px;flex:1;flex-wrap:wrap">
    <select name="class_id" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
      <option value="">All Classes</option>
      @foreach($classrooms as $classroom)
        <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
          {{ $classroom->name }}
        </option>
      @endforeach
    </select>

    <select name="section" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
      <option value="">All Sections</option>
      @foreach($sections as $section)
        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
          Section {{ $section }}
        </option>
      @endforeach
    </select>

    <input type="text" name="search" placeholder="Search by Name or ID" value="{{ request('search') }}" 
           style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:200px">
    
    <button type="submit" class="btn ghost" style="padding:6px 14px">Search</button>
    @if(request()->hasAny(['class_id', 'section', 'search']))
      <a href="{{ route('students.index') }}" class="btn ghost" style="padding:6px 14px">Clear</a>
    @endif
  </form>

  <a href="{{ route('students.create') }}" class="btn">+ Add Student</a>
</div>

<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>ID</th>
      <th>Class</th>
      <th>Section</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="studentTable">
    @forelse($students as $student)
    <tr>
      <td>{{ $student->name }}</td>
      <td>{{ $student->student_id }}</td>
      <td>{{ $student->classroom->name ?? 'N/A' }}</td>
      <td>{{ $student->section }}</td>
      <td style="display:flex;gap:6px;justify-content:center">
        @php
          $monthlyFee = 0;
          $className = 'N/A';
          if($student->classroom) {
              $className = $student->classroom->name;
              if($student->classroom->fees) {
                  foreach($student->classroom->fees as $fee) {
                      // Check for Monthly type (partial match, case-insensitive)
                      if(isset($fee['type']) && stripos($fee['type'], 'Monthly') !== false) {
                          $monthlyFee += (float)$fee['amount'];
                      }
                  }
              }
          }
        @endphp
        <button class="action-btn" onclick="openPayModal({{ $student->id }}, '{{ $student->name }} ({{ $className }})', {{ $monthlyFee }})" title="Fees: {{ json_encode($student->classroom->fees ?? []) }}">Pay</button>
        <a href="{{ route('students.show', $student) }}" class="action-btn">View</a>
        <a href="{{ route('students.receipt.download', $student) }}" class="action-btn receipt" title="Download Admission Receipt">Receipt</a>
        <a href="{{ route('students.edit', $student) }}" class="action-btn">Edit</a>
        <form action="{{ route('students.destroy', $student) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure to delete this student?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="action-btn delete">Delete</button>
        </form>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="5" style="color:var(--muted);padding:40px;text-align:center">No students found. Add your first student!</td>
    </tr>
    @endforelse
  </tbody>
</table>

<!-- PAY MODAL -->
<div class="modal" id="payModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Monthly Fee</h3>
      <span class="close" onclick="closePayModal()">✕</span>
    </div>
    <form action="{{ route('payments.store') }}" method="POST">
      @csrf
      <input type="hidden" name="student_id" id="payStudentId">
      <input type="hidden" name="payment_type" value="Monthly Fee">
      <input type="hidden" name="redirect_to" value="students.index">
      
      <div style="margin-bottom:12px">
        <label>Student</label>
        <input type="text" id="payStudentName" readonly style="background:#15181a;cursor:not-allowed">
      </div>

      <div style="margin-bottom:12px">
        <label>Date</label>
        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
      </div>

      <div style="margin-bottom:12px">
        <label>Amount</label>
        <input type="number" name="amount" id="payAmount" value="0" required>
      </div>

      <div style="margin-bottom:12px">
        <label>Month</label>
        <select name="month" required>
          @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
            <option value="{{ $month }}" {{ date('F') == $month ? 'selected' : '' }}>{{ $month }}</option>
          @endforeach
        </select>
      </div>

      <div style="margin-bottom:12px">
        <label>Payment Mode</label>
        <select name="payment_mode" required>
          <option value="Cash">Cash</option>
          <option value="Bkash">Bkash</option>
          <option value="Bank">Bank</option>
          <option value="Rocket">Rocket</option>
          <option value="Upay">Upay</option>
          <option value="Others">Others</option>
        </select>
      </div>

      <div style="margin-bottom:16px">
        <label>Note</label>
        <input type="text" name="note" placeholder="Optional note">
      </div>

      <div style="text-align:right">
        <button type="button" class="btn ghost" onclick="closePayModal()">Cancel</button>
        <button type="submit" class="btn">Confirm Payment</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('extra-styles')
<style>
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:rgba(255,255,255,0.15);padding:10px;font-size:14px;text-align:center}
td{padding:10px;font-size:14px;text-align:center}
#studentTable tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#studentTable tr:nth-child(even){background:rgba(255,255,255,0.09)}
#studentTable tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;cursor:pointer;font-size:13px;
  text-decoration:none;display:inline-block;margin:2px
}
.action-btn.delete{border-color:var(--danger);color:var(--danger)}
.action-btn.receipt{border-color:#4caf50;color:#4caf50}
.action-btn.receipt:hover{background:rgba(76,175,80,0.1)}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}

/* MODAL */
.modal{
  position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);
  display:none;justify-content:center;align-items:center;z-index:9999;
}
.modal-content{
  width:400px;background:var(--card);padding:20px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.15);
}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.modal-header h3{margin:0}
.close{cursor:pointer;color:var(--muted);font-size:18px}
label{display:block;margin-bottom:6px;font-size:13px;color:var(--muted)}
input,select{width:100%;padding:8px;background:#1b1f22;border:1px solid rgba(255,255,255,0.15);color:var(--text);border-radius:4px}
</style>
@endsection

@section('scripts')
<script>
function openPayModal(id, name, amount) {
  document.getElementById('payStudentId').value = id;
  document.getElementById('payStudentName').value = name;
  document.getElementById('payAmount').value = amount;
  document.getElementById('payModal').style.display = 'flex';
}

function closePayModal() {
  document.getElementById('payModal').style.display = 'none';
}

// Close on outside click
document.getElementById('payModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closePayModal();
  }
});
</script>
@endsection