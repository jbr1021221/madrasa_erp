<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ isset($classroom) ? 'Edit' : 'Add' }} Class – Madrasa ERP</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root{
  --bg:#0b0d0f;--panel:#111316;--card:#0f1416;--text:#e6eef3;--muted:#98a0a6;
  --accent:#e37814;--danger:#ff4e4e;--radius:6px;
}
body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;
  display:flex;justify-content:center;padding:22px;}
.container{width:100%;max-width:1400px;display:grid;grid-template-columns:240px 1fr;gap:18px;}
.sidebar{background:var(--panel);padding:16px;border-radius:var(--radius);
  height:calc(100vh - 44px);display:flex;flex-direction:column;
  border:1px solid rgba(255,255,255,0.04);}
.logo{width:42px;height:42px;border-radius:var(--radius);background:var(--accent);color:#041617;
  display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;}
.nav{display:flex;flex-direction:column;gap:4px;margin-top:18px}
.nav a{padding:10px 14px;border-radius:var(--radius);text-decoration:none;font-size:14px;color:var(--muted);}
.nav a:hover,.nav a.active{background:rgba(227,120,20,0.12);border-left:3px solid var(--accent);color:var(--accent);}
.panel{background:var(--panel);padding:20px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.05);}
.btn{background:var(--accent);color:#041617;border:0;padding:7px 14px;border-radius:var(--radius);
  cursor:pointer;font-size:14px;text-decoration:none;display:inline-block;}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
input,select{
  background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);
  padding:8px 12px;border-radius:var(--radius);font-size:14px;width:100%;max-width:400px;
}
label{display:block;margin-bottom:6px;margin-top:16px;font-size:14px;}
.fees-row{
  display:flex;
  align-items:center;
  gap:12px;
  margin-top:10px;
}
.fees-row input,
.fees-row select{
  max-width:200px;
}
.fees-row button{
  padding:6px 10px;
  font-size:12px;
  height:35px;
}
.error{color:var(--danger);font-size:12px;margin-top:4px;}
</style>
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div style="display:flex;align-items:center;gap:10px">
      <div class="logo">M</div><strong>Madrasa</strong>
    </div>
    <nav class="nav">
      <a href="{{ route('dashboard') }}">Dashboard</a>
      <a href="{{ route('students.index') }}">Students</a>
      <a class="active" href="{{ route('classrooms.index') }}">Classes</a>
      <a href="{{ route('accounts.index') }}">Accounts</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="panel">

    <h2 style="margin:0 0 20px 0">{{ isset($classroom) ? 'Edit' : 'Add' }} Class</h2>

    <form action="{{ isset($classroom) ? route('classrooms.update', $classroom) : route('classrooms.store') }}" method="POST">
      @csrf
      @if(isset($classroom))
        @method('PUT')
      @endif

      <label>Class Name *</label>
      <input type="text" name="name" placeholder="Class 1" value="{{ old('name', $classroom->name ?? '') }}" required>
      @error('name')
        <div class="error">{{ $message }}</div>
      @enderror

      <label>Sections (comma separated) *</label>
      <input type="text" name="sections" placeholder="A,B,C" value="{{ old('sections', isset($classroom) ? implode(',', $classroom->sections) : 'A,B') }}" required>
      @error('sections')
        <div class="error">{{ $message }}</div>
      @enderror

      <label>Max Students Per Section *</label>
      <input type="number" name="max_students_per_section" value="{{ old('max_students_per_section', $classroom->max_students_per_section ?? 50) }}" required min="1">
      @error('max_students_per_section')
        <div class="error">{{ $message }}</div>
      @enderror

      <div style="margin-top:16px">
        <div>
          <label>Admission Fee *</label>
          <input type="number" name="admission_fee" value="{{ old('admission_fee', $classroom->admission_fee ?? '') }}" placeholder="0" required min="0" step="0.01">
          @error('admission_fee')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <label style="margin-top:24px">Additional Fees (Optional)</label>
      <div id="feesContainer">
        @if(isset($classroom) && count($classroom->fees) > 0)
          @foreach($classroom->fees as $index => $fee)
            <div class="fees-row">
              <input type="text" name="fees[{{ $index }}][name]" placeholder="Fee Name" value="{{ $fee['name'] }}" required>
              <input type="number" name="fees[{{ $index }}][amount]" placeholder="Amount" value="{{ $fee['amount'] }}" required min="0" step="0.01">
              <select name="fees[{{ $index }}][type]" required>
                <option value="One Time" {{ $fee['type'] == 'One Time' ? 'selected' : '' }}>One Time</option>
                <option value="Monthly" {{ $fee['type'] == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                 <option value="Quarterly" {{ $fee['type'] == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="Yearly" {{ $fee['type'] == 'Yearly' ? 'selected' : '' }}>Yearly</option>
              </select>
              <button type="button" class="btn ghost" onclick="this.parentElement.remove()">X</button>
            </div>
          @endforeach
        @endif
      </div>
      <button type="button" class="btn ghost" style="margin-top:10px" onclick="addFeeRow()">+ Add Fee</button>

      @error('fees')
        <div class="error">{{ $message }}</div>
      @enderror

      <div style="margin-top:24px;display:flex;gap:10px">
        <a href="{{ route('classrooms.index') }}" class="btn ghost">Cancel</a>
        <button type="submit" class="btn">Save Class</button>
      </div>

    </form>

  </main>
</div>

<script>
let feeIndex = {{ isset($classroom) ? count($classroom->fees) : 0 }};

function addFeeRow() {
  const container = document.getElementById('feesContainer');
  const row = document.createElement('div');
  row.className = 'fees-row';
  row.innerHTML = `
    <input type="text" name="fees[${feeIndex}][name]" placeholder="Fee Name" required>
    <input type="number" name="fees[${feeIndex}][amount]" placeholder="Amount" required min="0" step="0.01">
    <select name="fees[${feeIndex}][type]" required>
      <option value="One Time">One Time</option>
      <option value="Monthly">Monthly</option>
      <option value="Quarterly">Quarterly</option>
      <option value="Yearly">Yearly</option>
    </select>
    <button type="button" class="btn ghost" onclick="this.parentElement.remove()">X</button>
  `;
  container.appendChild(row);
  feeIndex++;
}
</script>

<!-- SweetAlert for success messages -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#e37814',
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

</body>
</html>