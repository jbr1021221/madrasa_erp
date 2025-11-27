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
  <select name="class_id" id="class_id" onchange="updateSections()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Classes</option>
    @foreach($classrooms as $classroom)
      <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
        {{ $classroom->name }}
      </option>
    @endforeach
  </select>

  <select name="section" id="section" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Sections</option>
  </select>

  <select name="month" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Months</option>
    @foreach($months as $month)
      <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
        {{ $month }}
      </option>
    @endforeach
  </select>

  <select name="year" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
    <option value="">All Years</option>
    @foreach($years as $year)
      <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
        {{ $year }}
      </option>
    @endforeach
  </select>

  <input type="date" name="start_date" value="{{ request('start_date') }}" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">
  
  <input type="date" name="end_date" value="{{ request('end_date') }}" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:180px">

  <button type="submit" class="btn">Filter</button>
  
  @if(request()->hasAny(['class_id', 'section', 'month', 'year', 'start_date', 'end_date']))
    <a href="{{ route('payments.index') }}" class="btn ghost">Clear</a>
  @endif
</form>

<h3 style="margin:16px 0">
  Total Earnings: <span style="color:var(--accent)">৳ {{ number_format($totalEarnings, 2) }}</span>
</h3>

<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>ID</th>
      <th>Class</th>
      <th>Section</th>
      <th>Amount</th>
      <th>Date</th>
      <th>Month</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="earnTable">
    @forelse($payments as $payment)
    <tr>
      <td>{{ $payment->student->name }}</td>
      <td>{{ $payment->student->student_id }}</td>
      <td>{{ $payment->student->classroom->name ?? 'N/A' }}</td>
      <td>{{ $payment->student->section }}</td>
      <td>৳ {{ number_format($payment->amount, 2) }}</td>
      <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
      <td>{{ $payment->month }}</td>
      <td>
        <button class="action-btn" onclick="viewPaymentHistory({{ $payment->student->id }})">View</button>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="8" style="color:var(--muted);padding:40px;text-align:center">No payment records found</td>
    </tr>
    @endforelse
  </tbody>
</table>

<!-- PAYMENT HISTORY MODAL -->
<div class="modal" id="viewModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="vName"></h3>
      <span class="close" onclick="closeView()">✕</span>
    </div>

    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Month</th>
          <th>Amount</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody id="viewPayHistory"></tbody>
    </table>
  </div>
</div>

@endsection

@section('extra-styles')
<style>
table{width:100%;border-collapse:collapse;margin-top:14px;text-align:center}
th,td{padding:10px;font-size:14px;border-bottom:1px solid rgba(255,255,255,0.07)}
#earnTable tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#earnTable tr:nth-child(even){background:rgba(255,255,255,0.09)}
#earnTable tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;font-size:12px;cursor:pointer
}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}

/* MODAL */
.modal{
  position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);
  display:none;justify-content:center;align-items:center;z-index:9999;
}
.modal-content{
  width:650px;background:var(--card);padding:20px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.15);
}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.close{cursor:pointer;color:var(--muted);font-size:18px}
</style>
@endsection

@section('scripts')
<script>
const classroomData = @json($classroomData);
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
        // Show all unique sections from all classes
        Object.values(classroomData).forEach(cls => {
            if (cls.sections && cls.sections.length > 0) {
                cls.sections.forEach(s => sectionsToShow.add(s));
            }
        });
        // If no sections found in any class, default to A, B
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

function viewPaymentHistory(studentId) {
  fetch(`/payments/student/${studentId}/history`)
    .then(response => response.json())
    .then(data => {
      const student = data.student;
      const payments = data.payments;
      
      document.getElementById('vName').innerText = 
        `${student.name} — ${student.classroom.name} (${student.section})`;
      
      const tbody = document.getElementById('viewPayHistory');
      tbody.innerHTML = '';
      
      if (payments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="color:var(--muted);padding:20px">No payment history</td></tr>';
      } else {
        payments.forEach(payment => {
          const date = new Date(payment.payment_date).toLocaleDateString('en-GB');
          tbody.innerHTML += `
            <tr>
              <td>${date}</td>
              <td>${payment.month}</td>
              <td>৳ ${parseFloat(payment.amount).toFixed(2)}</td>
              <td>${payment.note || '-'}</td>
            </tr>
          `;
        });
      }
      
      document.getElementById('viewModal').style.display = 'flex';
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to load payment history');
    });
}

function closeView() {
  document.getElementById('viewModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('viewModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeView();
  }
});
</script>
@endsection