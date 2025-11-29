@extends('layouts.app')

@section('title', 'Student Admission Confirmed - Madrasa ERP')

@section('content')
<div style="max-width:800px;margin:0 auto">
    @if(session('success'))
    <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:16px;border-radius:8px;margin-bottom:24px;border-left:4px solid #4caf50">
        <strong>✓ {{ session('success') }}</strong>
    </div>
    @endif

    <div style="background:var(--card);border-radius:var(--radius);padding:32px;border:1px solid rgba(255,255,255,0.15);text-align:center">
        <div style="margin-bottom:24px">
            <div style="width:80px;height:80px;background:rgba(76,175,80,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2 style="color:var(--text);margin-bottom:8px">Student Successfully Enrolled!</h2>
            <p style="color:var(--muted);font-size:14px">Admission receipt is ready for download</p>
        </div>

        <div style="background:rgba(255,255,255,0.05);padding:24px;border-radius:8px;margin-bottom:24px;text-align:left">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Student Name</label>
                    <p style="color:var(--text);font-weight:600;margin:0">{{ $student->name }}</p>
                </div>
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Student ID</label>
                    <p style="color:var(--accent);font-weight:600;margin:0">{{ $student->student_id }}</p>
                </div>
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Class</label>
                    <p style="color:var(--text);font-weight:600;margin:0">{{ $student->classroom->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Section</label>
                    <p style="color:var(--text);font-weight:600;margin:0">{{ $student->section }}</p>
                </div>
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Father's Name</label>
                    <p style="color:var(--text);font-weight:600;margin:0">{{ $student->father_name }}</p>
                </div>
                <div>
                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Mobile</label>
                    <p style="color:var(--text);font-weight:600;margin:0">{{ $student->mobile }}</p>
                </div>
            </div>

            @php
                $admissionPayment = $student->payments()->where('payment_type', 'Admission')->first();
            @endphp

            @if($admissionPayment)
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1)">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Admission Fee Paid</label>
                        <p style="color:#4caf50;font-weight:700;font-size:24px;margin:0">৳{{ number_format($admissionPayment->amount, 2) }}</p>
                    </div>
                    <div style="text-align:right">
                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment Mode</label>
                        <p style="color:var(--text);font-weight:600;margin:0">{{ $admissionPayment->payment_mode }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('students.receipt.view', $student) }}" target="_blank" class="btn" style="display:inline-flex;align-items:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                View Receipt
            </a>
            <a href="{{ route('students.receipt.download', $student) }}" class="btn" style="display:inline-flex;align-items:center;gap:8px;background:var(--accent)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download PDF
            </a>
        </div>

        <div style="margin-top:24px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.1)">
            <a href="{{ route('students.index') }}" class="btn ghost" style="margin-right:8px">
                ← Back to Students
            </a>
            <a href="{{ route('students.create') }}" class="btn ghost">
                + Add Another Student
            </a>
        </div>
    </div>

    <div style="background:rgba(230,126,34,0.1);border:1px solid rgba(230,126,34,0.3);border-radius:8px;padding:16px;margin-top:24px">
        <h4 style="color:var(--accent);margin:0 0 8px 0;font-size:14px">📋 Next Steps</h4>
        <ul style="color:var(--muted);font-size:13px;margin:0;padding-left:20px">
            <li>Print and keep the admission receipt for your records</li>
            <li>Monthly fees are due on the 1st of each month</li>
            <li>You can view payment history anytime from the student profile</li>
            <li>Contact administration for any queries or concerns</li>
        </ul>
    </div>
</div>
@endsection

@section('extra-styles')
<style>
.alert.success {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(230,126,34,0.3);
}

.btn.ghost {
    background: transparent;
    border: 1px solid var(--accent);
    color: var(--accent);
}

.btn.ghost:hover {
    background: rgba(230,126,34,0.1);
}
</style>
@endsection
