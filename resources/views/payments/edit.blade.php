@extends('layouts.app')

@section('title', 'Edit Payment - Madrasa ERP')

@section('content')
<div style="margin-bottom:20px">
  <h2 style="margin:0">Edit Payment</h2>
  <p style="color:var(--muted);margin:8px 0 0 0">Update payment details. Note: Amounts cannot be modified for accounting integrity.</p>
</div>

<form action="{{ route('payments.update', $payment) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="form-row">
    <div class="form-group">
      <label>Student</label>
      <input type="text" value="{{ $payment->student->name }} - {{ $payment->student->student_id }}" readonly
        style="background:#15181a;cursor:not-allowed">
      <input type="hidden" name="student_id" value="{{ $payment->student_id }}">
    </div>

    <div class="form-group">
      <label>Amount (৳)</label>
      <input type="number" value="{{ number_format($payment->final_amount, 2) }}" readonly
        style="background:#15181a;cursor:not-allowed">
      <small style="color:var(--muted);font-size:12px">Amounts cannot be modified</small>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Payment Date *</label>
      <input type="date" name="payment_date" value="{{ $payment->payment_date?->format('Y-m-d') }}" required>
      @error('payment_date')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label>Month *</label>
      <input type="text" name="month" value="{{ old('month', $payment->month) }}" required>
      <small style="color:var(--muted);font-size:12px">Format: "January, 26" or "February, 26"</small>
      @error('month')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Payment Type *</label>
      <select name="payment_type" required>
        <option value="Monthly" {{ $payment->payment_type === 'Monthly' ? 'selected' : '' }}>Monthly</option>
        <option value="Admission" {{ $payment->payment_type === 'Admission' ? 'selected' : '' }}>Admission Fee</option>
        <option value="Quarterly" {{ $payment->payment_type === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
        <option value="Half-Yearly" {{ $payment->payment_type === 'Half-Yearly' ? 'selected' : '' }}>Half-Yearly</option>
        <option value="Yearly" {{ $payment->payment_type === 'Yearly' ? 'selected' : '' }}>Yearly</option>
        <option value="Others" {{ $payment->payment_type === 'Others' ? 'selected' : '' }}>Others</option>
      </select>
      @error('payment_type')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label>Payment Mode *</label>
      <select name="payment_mode" required>
        <option value="Cash" {{ $payment->payment_mode === 'Cash' ? 'selected' : '' }}>Cash</option>
        <option value="Bkash" {{ $payment->payment_mode === 'Bkash' ? 'selected' : '' }}>Bkash</option>
        <option value="Bank" {{ $payment->payment_mode === 'Bank' ? 'selected' : '' }}>Bank</option>
        <option value="Rocket" {{ $payment->payment_mode === 'Rocket' ? 'selected' : '' }}>Rocket</option>
        <option value="Upay" {{ $payment->payment_mode === 'Upay' ? 'selected' : '' }}>Upay</option>
        <option value="Others" {{ $payment->payment_mode === 'Others' ? 'selected' : '' }}>Others</option>
      </select>
      @error('payment_mode')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Note (optional)</label>
    <textarea name="note" rows="3" style="width:100%;resize:vertical">{{ old('note', $payment->note) }}</textarea>
    @error('note')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <!-- Payment Details Preview (Read-only) -->
  @if($payment->fee_details)
    <div class="form-group">
      <label>Payment Details (Read-only)</label>
      <div style="background:rgba(255,255,255,0.05);padding:16px;border-radius:8px;border:1px solid rgba(255,255,255,0.1)">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.1)">
              <th style="text-align:left;padding:8px;color:var(--muted);font-size:13px">Fee Name</th>
              <th style="text-align:right;padding:8px;color:var(--muted);font-size:13px">Amount</th>
            </tr>
          </thead>
          <tbody>
            @foreach($payment->fee_details as $fee)
              <tr style="border-bottom:1px solid rgba(255,255,255,0.05)">
                <td style="padding:8px;font-size:14px">{{ $fee['name'] ?? 'N/A' }}</td>
                <td style="text-align:right;padding:8px;font-size:14px">৳{{ number_format($fee['amount'] ?? 0, 2) }}</td>
              </tr>
            @endforeach
            <tr style="background:rgba(227,120,20,0.1);font-weight:bold">
              <td style="padding:8px">Total</td>
              <td style="text-align:right;padding:8px">৳{{ number_format($payment->final_amount, 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <input type="hidden" name="redirect_to" value="payments.show">

  <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px">
    <a href="{{ route('payments.show', $payment->student_id) }}" class="btn ghost">Cancel</a>
    <button type="submit" class="btn">Update Payment</button>
  </div>
</form>
@endsection
