@extends('layouts.app')

@section('title', 'Student Details - Madrasa ERP')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <h2 style="margin:0">{{ $student->name }}</h2>
  <div>
    <a href="{{ route('students.edit', $student) }}" class="btn ghost">Edit</a>
    <a href="{{ route('students.index') }}" class="btn ghost">Back to List</a>
  </div>
</div>

<div class="info-grid">
  <div class="info-item">
    <div class="info-label">Student ID</div>
    <div class="info-value">{{ $student->student_id }}</div>
  </div>
  <div class="info-item">
    <div class="info-label">Class & Section</div>
    <div class="info-value">{{ $student->classroom->name ?? 'N/A' }} ({{ $student->section }})</div>
  </div>
  <div class="info-item">
    <div class="info-label">Father's Name</div>
    <div class="info-value">{{ $student->father_name }}</div>
  </div>
  <div class="info-item">
    <div class="info-label">Mobile</div>
    <div class="info-value">{{ $student->mobile }}</div>
  </div>
</div>

<h3 style="margin-top:30px;margin-bottom:10px">Class Fees Structure</h3>
<table>
  <thead>
    <tr>
      <th>Fee Name</th>
      <th>Type</th>
      <th>Amount</th>
    </tr>
  </thead>
  <tbody>
    @if($student->classroom && $student->classroom->fees)
      @foreach($student->classroom->fees as $fee)
      <tr>
        <td>{{ $fee['name'] }}</td>
        <td>{{ $fee['type'] }}</td>
        <td>৳ {{ number_format($fee['amount'], 2) }}</td>
      </tr>
      @endforeach
      <tr style="background:rgba(227,120,20,0.1)">
        <td colspan="2" style="font-weight:bold;text-align:right">Total Class Fee</td>
        <td style="font-weight:bold;color:var(--accent)">৳ {{ number_format($student->classroom->total_fee, 2) }}</td>
      </tr>
    @else
      <tr>
        <td colspan="3" style="color:var(--muted);padding:20px">No fees defined for this class</td>
      </tr>
    @endif
  </tbody>
</table>

<h3 style="margin-top:30px;margin-bottom:10px">Payment History</h3>
<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Type</th>
      <th>Month</th>
      <th>Amount</th>
      <th>Mode</th>
      <th>Note</th>
    </tr>
  </thead>
  <tbody>
    @forelse($student->payments as $payment)
    <tr>
      <td>{{ $payment->payment_date->format('d M, Y') }}</td>
      <td>{{ $payment->payment_type }}</td>
      <td>{{ $payment->month }}</td>
      <td>৳ {{ number_format($payment->amount, 2) }}</td>
      <td>{{ ucfirst($payment->payment_mode) }}</td>
      <td>{{ $payment->note ?? '-' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="6" style="color:var(--muted);padding:20px">No payments recorded</td>
    </tr>
    @endforelse
  </tbody>
</table>
@endsection

@section('extra-styles')
<style>
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent);margin-left:8px}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px}
.info-item{background:rgba(255,255,255,0.03);padding:16px;border-radius:var(--radius);border:1px solid rgba(255,255,255,0.05)}
.info-label{font-size:12px;color:var(--muted);margin-bottom:4px}
.info-value{font-size:16px;font-weight:500}
.status-badge{padding:4px 10px;border-radius:4px;font-size:12px;font-weight:500}
.status-pending{background:rgba(255,193,7,0.2);color:#ffc107}
.status-paid{background:rgba(76,175,80,0.2);color:#4caf50}
.status-partial{background:rgba(33,150,243,0.2);color:#2196f3}
</style>
@endsection
