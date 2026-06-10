<x-layout title="Admission Confirmed - Madrasa ERP">
    <x-slot:styles>
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
    </x-slot:styles>

    <div style="max-width:800px;margin:0 auto">
        @if(session('success'))
        <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:16px;border-radius:8px;margin-bottom:24px;border-left:4px solid #4caf50">
            <strong>✓ {{ session('success') }}</strong>
        </div>
        @endif

        <div style="background:var(--card);border-radius:var(--radius);padding:32px;border:1px solid rgba(255,255,255,0.15);text-align:center">
            @if(session('success'))
            <div style="margin-bottom:24px">
                <div style="width:80px;height:80px;background:rgba(76,175,80,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h2 style="color:var(--text);margin-bottom:8px">Student Successfully Enrolled!</h2>
                <p style="color:var(--muted);font-size:14px">Admission form and receipt are ready for download</p>
            </div>
            @endif

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
                        <p style="color:var(--text);font-weight:600;margin:0">{{ $student->father_mobile }}</p>
                    </div>
                </div>

                @php
                    // Get the admission payment for this student
                    $admissionPayment = $student->payments()->where('payment_type', 'Admission')->latest('id')->first();
                @endphp

                @if($admissionPayment)
                <div style="margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1)">
                    <h3 style="color:var(--text);margin:0 0 16px 0;font-size:18px">Admission Fee Details</h3>

                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">
                                Admission Fee Paid
                            </label>
                            <p style="color:#4caf50;font-weight:700;font-size:24px;margin:0">৳{{ number_format($admissionPayment->final_amount, 2) }}</p>
                        </div>
                        <div style="text-align:right">
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment Mode</label>
                            <p style="color:var(--text);font-weight:600;margin:0">{{ $admissionPayment->payment_mode }}</p>
                        </div>
                    </div>

                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);display:flex;justify-content:space-between">
                        <div>
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment Date</label>
                            <p style="color:var(--text);font-weight:600;margin:0">{{ \Carbon\Carbon::parse($admissionPayment->payment_date)->format('d M, Y') }}</p>
                        </div>
                        @if($admissionPayment->payment_note)
                        <div style="text-align:right">
                            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Note</label>
                            <p style="color:var(--text);font-weight:600;margin:0">{{ $admissionPayment->payment_note }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                <a href="{{ route('students.admission-form.download', $student) }}" class="btn" style="display:inline-flex;align-items:center;gap:8px;background:#4caf50;color:#fff">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Download Admission Form
                </a>

                @if($admissionPayment)
                <a href="{{ route('payments.receipt', $admissionPayment->id) }}" target="_blank" class="btn" style="display:inline-flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    View Receipt
                </a>
                <button onclick="printReceipt('{{ route('payments.receipt', $admissionPayment->id) }}')" class="btn" style="display:inline-flex;align-items:center;gap:8px;background:var(--accent);border:none;cursor:pointer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Receipt
                </button>
                @endif
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
                <li>Download and print the admission form for your records</li>
                <li>Download and print the admission receipt for payment confirmation</li>
                <li>Monthly fees are due on the 1st of each month</li>
                <li>You can view payment history anytime from the student profile</li>
                <li>Contact administration for any queries or concerns</li>
            </ul>
        </div>
    </div>

    <x-slot:scripts>
        <script>
        function printReceipt(url) {
            // Open receipt in new window
            const printWindow = window.open(url, '_blank');

            // Wait for the window to load, then trigger print
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.print();
                };
            }
        }
        </script>
    </x-slot:scripts>
</x-layout>

