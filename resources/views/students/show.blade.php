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
    <div class="info-label">Class</div>
    <div class="info-value">{{ $student->classroom->name ?? 'N/A' }}</div>
  </div>

  <div class="info-item">
    <div class="info-label">Roll Number</div>
    <div class="info-value">{{ $student->roll }}</div>
  </div>

  <div class="info-item">
    <div class="info-label">Email</div>
    <div class="info-value">{{ $student->email }}</div>
  </div>
</div>

<h3 style="margin-top:30px;margin-bottom:10px">Fees</h3>
<table>
  <thead>
    <tr>
      <th>Type</th>
      <th>Amount</th>
      <th>Due Date</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse($student->fees as $fee)
    <tr>
      <td>{{ $fee->type }}</td>
      <td>৳ {{ number_format($fee->amount, 2) }}</td>
      <td>{{ $fee->due_date }}</td>
      <td>
        <span class="status-badge status-{{ $fee->status }}">{{ ucfirst($fee->status) }}</span>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="4" style="color:var(--muted);padding:20px">No fees assigned</td>
    </tr>
    @endforelse
  </tbody>
</table>

<h3 style="margin-top:30px;margin-bottom:10px">Payment History</h3>
<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Amount</th>
      <th>Method</th>
      <th>Transaction ID</th>
    </tr>
  </thead>
  <tbody>
    @forelse($student->payments as $payment)
    <tr>
      <td>{{ $payment->payment_date }}</td>
      <td>৳ {{ number_format($payment->amount, 2) }}</td>
      <td>{{ ucfirst($payment->payment_method) }}</td>
      <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="4" style="color:var(--muted);padding:20px">No payments recorded</td>
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
