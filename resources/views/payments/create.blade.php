@extends('layouts.app')

@section('title', 'Record Payment - Madrasa ERP')

@section('content')
<div style="margin-bottom:20px">
  <h2 style="margin:0">Record New Payment</h2>
</div>

<form action="{{ route('payments.store') }}" method="POST">
  @csrf

  <div class="form-row">
    <div class="form-group">
      <label>Select Student *</label>
      <select name="student_id" required>
        <option value="">Choose Student</option>
        @foreach($students as $student)
          <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
            {{ $student->name }} - {{ $student->student_id }} ({{ $student->classroom->name ?? 'N/A' }})
          </option>
        @endforeach
      </select>
      @error('student_id')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label>Amount (৳) *</label>
      <input type="number" name="final_amount" step="0.01" min="0" value="{{ old('final_amount') }}" required>
      @error('final_amount')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Payment Date *</label>
      <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
      @error('payment_date')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label>Month *</label>
      <select name="month" required>
        <option value="">Select Month</option>
        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
          <option value="{{ $month }}" {{ old('month', date('F')) == $month ? 'selected' : '' }}>
            {{ $month }}
          </option>
        @endforeach
      </select>
      @error('month')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Payment Type *</label>
      <select name="payment_type" required>
        <option value="">Select Type</option>
        <option value="Tuition" {{ old('payment_type') == 'Tuition' ? 'selected' : '' }}>Tuition Fee</option>
        <option value="Admission" {{ old('payment_type') == 'Admission' ? 'selected' : '' }}>Admission Fee</option>
        <option value="Meal" {{ old('payment_type') == 'Meal' ? 'selected' : '' }}>Meal Fee</option>
        <option value="Transport" {{ old('payment_type') == 'Transport' ? 'selected' : '' }}>Transport Fee</option>
        <option value="Exam" {{ old('payment_type') == 'Exam' ? 'selected' : '' }}>Exam Fee</option>
        <option value="Others" {{ old('payment_type') == 'Others' ? 'selected' : '' }}>Others</option>
      </select>
      @error('payment_type')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label>Payment Mode *</label>
      <select name="payment_mode" required>
        <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
        <option value="Bkash" {{ old('payment_mode') == 'Bkash' ? 'selected' : '' }}>Bkash</option>
        <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
        <option value="Rocket" {{ old('payment_mode') == 'Rocket' ? 'selected' : '' }}>Rocket</option>
        <option value="Upay" {{ old('payment_mode') == 'Upay' ? 'selected' : '' }}>Upay</option>
        <option value="Nagad" {{ old('payment_mode') == 'Nagad' ? 'selected' : '' }}>Nagad</option>
        <option value="Others" {{ old('payment_mode') == 'Others' ? 'selected' : '' }}>Others</option>
      </select>
      @error('payment_mode')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Note (optional)</label>
    <textarea name="note" rows="3" style="width:100%;resize:vertical">{{ old('note') }}</textarea>
    @error('note')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px">
    <a href="{{ route('payments.index') }}" class="btn ghost">Cancel</a>
    <button type="submit" class="btn">Record Payment</button>
  </div>
</form>
@endsection