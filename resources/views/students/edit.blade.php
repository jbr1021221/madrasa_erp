@extends('layouts.app')

@section('title', 'Add Student - Madrasa ERP')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <h2 style="margin:0">Add New Student</h2>
</div>

<form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
  @csrf

  <div class="form-row">
    <div class="form-group">
      <label>Student ID</label>
      <input type="text" name="student_id" value="{{ $student_id }}" readonly style="background:#0f1416">
    </div>
    <div class="form-group">
      <label>Student Name *</label>
      <input type="text" name="name" placeholder="Student Name" value="{{ old('name') }}" required>
      @error('name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Father Name *</label>
      <input type="text" name="father_name" placeholder="Father's Name" value="{{ old('father_name') }}" required>
      @error('father_name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Mother Name *</label>
      <input type="text" name="mother_name" placeholder="Mother's Name" value="{{ old('mother_name') }}" required>
      @error('mother_name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Address *</label>
    <input type="text" name="address" placeholder="Full Address" value="{{ old('address') }}" style="width:100%" required>
    @error('address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Mobile *</label>
      <input type="text" name="mobile" placeholder="01XXXXXXXXX" value="{{ old('mobile') }}" required>
      @error('mobile')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Alt Mobile</label>
      <input type="text" name="alt_mobile" placeholder="01XXXXXXXXX" value="{{ old('alt_mobile') }}">
      @error('alt_mobile')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>NID Upload (Father/Mother)</label>
    <input type="file" name="nid_file" accept=".jpg,.jpeg,.png,.pdf" style="width:100%">
    @error('nid_file')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Select Class *</label>
      <select name="class_id" required>
        <option value="">Select Class</option>
        @foreach($classrooms as $classroom)
          <option value="{{ $classroom->id }}" {{ old('class_id') == $classroom->id ? 'selected' : '' }}>
            {{ $classroom->name }}
          </option>
        @endforeach
      </select>
      @error('class_id')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Select Section *</label>
      <select name="section" required>
        <option value="">Select Section</option>
        @foreach($sections as $section)
          <option value="{{ $section }}" {{ old('section') == $section ? 'selected' : '' }}>
            Section {{ $section }}
          </option>
        @endforeach
      </select>
      @error('section')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <h4 style="margin-top:24px;margin-bottom:12px">Admission Fees</h4>
  <div style="background:rgba(255,255,255,0.04);padding:16px;border-radius:6px">
    <table style="width:100%">
      <tr><td>Admission Fee</td><td style="text-align:right">৳ 2,000</td></tr>
      <tr><td>Dress Fee</td><td style="text-align:right">৳ 1,500</td></tr>
      <tr><td>Book Fee</td><td style="text-align:right">৳ 1,200</td></tr>
      <tr><td>Development Fee</td><td style="text-align:right">৳ 3,000</td></tr>
      <tr style="border-top:1px solid rgba(255,255,255,0.15)">
        <td style="font-weight:bold;padding-top:10px">Total</td>
        <td style="text-align:right;font-weight:bold;color:var(--accent);padding-top:10px">৳ 7,700</td>
      </tr>
    </table>
  </div>

  <label style="margin-top:16px">Payment Mode *</label>
  <select name="payment_mode" style="width:50%" required>
    <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
    <option value="Bkash" {{ old('payment_mode') == 'Bkash' ? 'selected' : '' }}>Bkash</option>
    <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
    <option value="Rocket" {{ old('payment_mode') == 'Rocket' ? 'selected' : '' }}>Rocket</option>
    <option value="Upay" {{ old('payment_mode') == 'Upay' ? 'selected' : '' }}>Upay</option>
    <option value="Others" {{ old('payment_mode') == 'Others' ? 'selected' : '' }}>Others</option>
  </select>
  @error('payment_mode')
    <div class="error">{{ $message }}</div>
  @enderror

  <label style="margin-top:16px">Note (optional)</label>
  <textarea name="payment_note" rows="3" style="width:90%;resize:vertical" placeholder="Additional notes...">{{ old('payment_note') }}</textarea>

  <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px">
    <a href="{{ route('students.index') }}" class="btn ghost">Cancel</a>
    <button type="submit" class="btn">Pay & Save</button>
  </div>
</form>
@endsection

@section('extra-styles')
<style>
label{display:block;margin-bottom:6px;margin-top:8px;font-size:13px;color:var(--muted)}
input,select,textarea{
  background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);
  padding:8px 12px;border-radius:var(--radius);font-size:14px;width:100%
}
.form-row{display:flex;gap:12px;margin-top:10px}
.form-group{flex:1;display:flex;flex-direction:column}
.form-group input,.form-group select{width:100%}
.error{color:var(--danger);font-size:12px;margin-top:4px}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
</style>
@endsection