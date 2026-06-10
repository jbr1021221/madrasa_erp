@extends('layouts.app')

@section('title', 'Student Admission Confirmed - Madrasa ERP')

@section('content')
    <div style="max-width:800px;margin:0 auto">
        @if (session('success'))
            <div class="alert success"
                style="background:rgba(76,175,80,0.2);color:#4caf50;padding:16px;border-radius:8px;margin-bottom:24px;border-left:4px solid #4caf50">
                <strong>✓ {{ session('success') }}</strong>
            </div>
        @endif

        <div
            style="background:var(--card);border-radius:var(--radius);padding:32px;border:1px solid rgba(255,255,255,0.15);text-align:center">
            @if (session('success'))
                <div style="margin-bottom:24px">
                    <div
                        style="width:80px;height:80px;background:rgba(76,175,80,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2 style="color:var(--text);margin-bottom:8px">Student Successfully Enrolled!</h2>
                    <p style="color:var(--muted);font-size:14px">Admission receipt is ready for download</p>
                </div>
            @endif

            <div
                style="background:rgba(255,255,255,0.05);padding:24px;border-radius:8px;margin-bottom:24px;text-align:left">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Student
                            Name</label>
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
                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Father's
                            Name</label>
                        <p style="color:var(--text);font-weight:600;margin:0">{{ $student->father_name }}</p>
                    </div>
                    <div>
                        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Mobile</label>
                        <p style="color:var(--text);font-weight:600;margin:0">{{ $student->mobile }}</p>
                    </div>
                </div>

                @php
                    // Get the Admission payment for this student by priority
                    $latestPayment =
                        $student->payments()->where('payment_type', 'Admission')->latest('id')->first() ??
                        $student->payments()->latest('id')->first();

                    // Get payment items from the relationship
                    $paymentItems = $latestPayment ? $latestPayment->payment_items ?? collect() : collect();
                    $isAdmissionPayment = $latestPayment && $latestPayment->payment_type === 'Admission';
                @endphp

                @if ($latestPayment)
                    <div style="margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1)">
                        <h3 style="color:var(--text);margin:0 0 16px 0;font-size:18px">Payment Details</h3>

                        @if ($paymentItems->count() > 0)
                            @php
                                // Process and group fees
                                $processedFees = [];
                                $monthlyGroups = [];

                                $totalNet = 0;
                                $totalOriginal = 0;
                                $totalDiscount = 0;

                                foreach ($paymentItems as $item) {
                                    // Net Amount (Paid Amount)
                                    $net = floatval($item->amount ?? 0);

                                    // Discount
                                    $disc = floatval($item->discount ?? 0);

                                    // Original Amount
                                    // Check for Partial Payment (Suffix added by controller)
                                    $isPartial = strpos($item->fee_name, '(Partial)') !== false;

                                    if ($isPartial) {
                                        // For receipt math, we only consider the portion processed now (Paid + Discount)
                                        // This prevents "Unpaid Due" from being calculated as a Discount
                                        $orig = $net + $disc;
                                    } elseif (isset($item->original_amount) && $item->original_amount > 0) {
                                        $orig = floatval($item->original_amount);
                                    } elseif ($disc > 0) {
                                        $orig = $net + $disc;
                                    } else {
                                        $orig = $net;
                                    }

                                    // Double check discount
                                    if ($disc <= 0 && $orig > $net) {
                                        $disc = $orig - $net;
                                    }

                                    // Accumulate Totals
                                    $totalNet += $net;
                                    $totalOriginal += $orig;
                                    $totalDiscount += $disc;

                                    // Check if this is a monthly fee that should be grouped
                                    if ($item->fee_type === 'Monthly' && $item->month) {
                                        $nameParts = explode(' - ', $item->fee_name);
                                        $baseName = count($nameParts) > 1 ? trim($nameParts[0]) : $item->fee_name;

                                        $monthLabel = $item->month;
                                        if ($item->year && strpos($monthLabel, $item->year) === false) {
                                            $monthLabel .= ' ' . $item->year;
                                        } elseif (!$item->year && isset($nameParts[1])) {
                                            $monthLabel = trim($nameParts[1]);
                                        }

                                        if (!isset($monthlyGroups[$baseName])) {
                                            $monthlyGroups[$baseName] = [
                                                'months' => [],
                                                'total_amount' => 0,
                                                'total_original' => 0,
                                            ];
                                        }
                                        $monthlyGroups[$baseName]['months'][] = $monthLabel;
                                        $monthlyGroups[$baseName]['total_amount'] += $net;
                                        $monthlyGroups[$baseName]['total_original'] += $orig;
                                    } else {
                                        $processedFee = [
                                            'name' => $item->fee_name,
                                            'amount' => $net,
                                        ];

                                        if (
                                            strpos($item->fee_name, 'Admission Fee (Partial)') !== false &&
                                            isset($item->original_amount)
                                        ) {
                                            $processedFee['original'] = floatval($item->original_amount);
                                        } elseif (
                                            strpos($item->fee_name, 'Admission Fee (Partial)') !== false &&
                                            $disc > 0
                                        ) {
                                            $processedFee['original'] = $net + $disc;
                                        }

                                        $processedFees[] = $processedFee;
                                    }
                                }

                                // Process the groups
                                foreach ($monthlyGroups as $baseName => $group) {
                                    $months = $group['months'];
                                    $count = count($months);

                                    $monthRange = '';
                                    if ($count === 1) {
                                        $monthRange = $months[0];
                                    } elseif ($count === 2) {
                                        $monthRange = $months[0] . ' & ' . $months[1];
                                    } elseif ($count >= 3) {
                                        $monthRange = $months[0] . ' - ' . end($months);
                                    }

                                    $processedFees[] = [
                                        'name' => $baseName . ' (' . $monthRange . ')',
                                        'amount' => $group['total_amount'],
                                    ];
                                }

                                $totalPaid = $latestPayment->final_amount ?? 0;

                                // Final Calculation
                                $subtotal = $totalOriginal;
                                $discount = $totalDiscount;

                                if ($totalPaid < $totalNet - 0.01) {
                                    $manualDiscount = $totalNet - $totalPaid;
                                    $discount += $manualDiscount;
                                    // $subtotal += $manualDiscount; // Optional based on manual logic
                                }
                            @endphp

                            <!-- Detailed Payment Breakdown from Database -->
                            <div
                                style="background:rgba(255,255,255,0.03);border-radius:6px;padding:16px;margin-bottom:16px">
                                <table style="width:100%;border-collapse:collapse">
                                    <thead>
                                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1)">
                                            <th style="text-align:left;padding:8px;color:var(--muted);font-size:13px">
                                                Description</th>
                                            <th style="text-align:right;padding:8px;color:var(--muted);font-size:13px">
                                                Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($processedFees as $fee)
                                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05)">
                                                <td style="padding:10px;font-size:14px">
                                                    @if (strpos($fee['name'], 'Admission Fee (Partial)') !== false &&
                                                            isset($fee['original']) &&
                                                            $fee['original'] > $fee['amount']
                                                    )
                                                        Admission Fee(<span
                                                            style="color:#ff4e4e">{{ number_format($fee['original'], 0) }}
                                                            TK</span>) - Partial
                                                    @else
                                                        {{ $fee['name'] ?? 'Fee' }}
                                                    @endif
                                                </td>
                                                <td style="text-align:right;padding:10px;font-size:14px">
                                                    ৳{{ number_format($fee['amount'] ?? 0, 2) }}
                                                </td>
                                            </tr>

                                            @if (strpos($fee['name'], 'Admission Fee (Partial)') !== false &&
                                                    isset($fee['original']) &&
                                                    $fee['original'] > $fee['amount']
                                            )
                                                @php
                                                    $remainingAdm = $fee['original'] - $fee['amount'];
                                                @endphp
                                                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);color:#ff4e4e">
                                                    <td
                                                        style="padding:10px;font-size:13px;font-style:italic;padding-left:24px">
                                                        Remaining Admission Fee</td>
                                                    <td style="text-align:right;padding:10px;font-size:13px">
                                                        ৳{{ number_format($remainingAdm, 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @if ($discount > 0)
                                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05)">
                                                <td style="padding:10px;font-weight:600;font-size:14px;color:var(--text)">
                                                    Subtotal</td>
                                                <td
                                                    style="text-align:right;padding:10px;font-weight:600;font-size:14px;color:var(--text)">
                                                    ৳{{ number_format($subtotal, 2) }}</td>
                                            </tr>
                                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05)">
                                                <td style="padding:10px;font-weight:600;font-size:14px;color:var(--text)">
                                                    Discount</td>
                                                <td
                                                    style="text-align:right;padding:10px;font-weight:600;font-size:14px;color:var(--text)">
                                                    ৳{{ number_format($discount, 2) }}</td>
                                            </tr>
                                        @endif

                                        <tr style="background:rgba(227,120,20,0.1)">
                                            <td style="padding:12px;font-weight:700;font-size:16px;color:var(--text)">Total
                                                Amount</td>
                                            <td
                                                style="text-align:right;padding:12px;font-weight:700;font-size:18px;color:#4caf50">
                                                ৳{{ number_format($totalPaid, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Simple Payment Display (fallback for old payments without payment_items) -->
                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">
                                        {{ $latestPayment->payment_type }} Fee Paid
                                    </label>
                                    <p style="color:#4caf50;font-weight:700;font-size:24px;margin:0">
                                        ৳{{ number_format($latestPayment->final_amount, 2) }}</p>
                                </div>
                                <div style="text-align:right">
                                    <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment
                                        Mode</label>
                                    <p style="color:var(--text);font-weight:600;margin:0">
                                        {{ $latestPayment->payment_mode }}</p>
                                </div>
                            </div>
                        @endif

                        <div
                            style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.05);display:flex;justify-content:space-between">
                            <div>
                                <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment
                                    Mode</label>
                                <p style="color:var(--text);font-weight:600;margin:0">{{ $latestPayment->payment_mode }}
                                </p>
                            </div>
                            <div style="text-align:right">
                                <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:4px">Payment
                                    Date</label>
                                <p style="color:var(--text);font-weight:600;margin:0">
                                    {{ \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                @if ($latestPayment)
                    {{-- View/Print Receipt Button --}}
                    <a href="{{ route('students.receipt.view', $student) }}" target="_blank" class="btn"
                        style="display:inline-flex;align-items:center;gap:8px">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        View Receipt
                    </a>
                    <a href="javascript:void(0)" onclick="printReceipt('{{ route('students.receipt.view', $student) }}')"
                        class="btn" style="display:inline-flex;align-items:center;gap:8px;background:#4caf50;color:#fff">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Print Receipt
                    </a>

                    {{-- Share via WhatsApp --}}
                    <a href="javascript:void(0)" onclick="shareViaWhatsApp()" class="btn"
                        style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                        Share via WhatsApp
                    </a>

                    {{-- Share via Email --}}
                    <a href="javascript:void(0)" onclick="shareViaEmail()" class="btn"
                        style="display:inline-flex;align-items:center;gap:8px;background:#EA4335;color:#fff">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Share via Email
                    </a>

                    {{-- Web Share API (for mobile devices) --}}
                    <a href="javascript:void(0)" onclick="shareReceipt()" id="webShareBtn" class="btn"
                        style="display:none;align-items:center;gap:8px;background:#2196F3;color:#fff">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        Share
                    </a>
                @endif

                {{-- Show admission form download for all students --}}
                <a href="{{ route('students.admission-form.download', $student) }}" class="btn"
                    style="display:inline-flex;align-items:center;gap:8px;background:#2196F3;color:#fff">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Download Admission Form
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

        <div
            style="background:rgba(230,126,34,0.1);border:1px solid rgba(230,126,34,0.3);border-radius:8px;padding:16px;margin-top:24px">
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
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }

        .btn.ghost {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
        }

        .btn.ghost:hover {
            background: rgba(230, 126, 34, 0.1);
        }
    </style>
@endsection

@section('scripts')
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

        // Share via WhatsApp - Download PDF first, then share
        function shareViaWhatsApp() {
            const studentName = "{{ $student->name }}";
            const studentId = "{{ $student->student_id }}";
            const amount = "{{ $latestPayment ? number_format($latestPayment->final_amount, 2) : '0.00' }}";
            const pdfUrl = "{{ route('students.receipt.download', $student) }}";

            // For WhatsApp, we need to download the PDF first
            // Then user can manually attach it
            const message = `🎓 *Student Receipt - Al Akhirah International Academy*\n\n` +
                `👤 Student: ${studentName}\n` +
                `🆔 ID: ${studentId}\n` +
                `💰 Amount Paid: ৳${amount}\n\n` +
                `📎 Please download the receipt PDF and attach it to this message.`;

            // Download the PDF
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = `receipt_${studentId}.pdf`;
            link.click();

            // Open WhatsApp with message
            setTimeout(() => {
                const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, '_blank');

                alert('📥 PDF downloaded! Please attach it to your WhatsApp message.');
            }, 1000);
        }

        // Share via Email with PDF attachment
        function shareViaEmail() {
            const studentName = "{{ $student->name }}";
            const studentId = "{{ $student->student_id }}";
            const amount = "{{ $latestPayment ? number_format($latestPayment->final_amount, 2) : '0.00' }}";
            const pdfUrl = "{{ route('students.receipt.download', $student) }}";

            const subject = `Payment Receipt - ${studentName} (${studentId})`;
            const body = `Dear Parent/Guardian,\n\n` +
                `This is to confirm the payment receipt for ${studentName} (ID: ${studentId}).\n\n` +
                `Amount Paid: ৳${amount}\n\n` +
                `Please find the attached receipt PDF.\n\n` +
                `Thank you,\nAl Akhirah International Academy`;

            // Download the PDF first
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = `receipt_${studentId}.pdf`;
            link.click();

            // Open email client
            setTimeout(() => {
                const mailtoUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                window.location.href = mailtoUrl;

                alert('📥 PDF downloaded! Please attach it to your email.');
            }, 1000);
        }

        // Web Share API - Share actual PDF file (works on mobile)
        async function shareReceipt() {
            const studentName = "{{ $student->name }}";
            const studentId = "{{ $student->student_id }}";
            const amount = "{{ $latestPayment ? number_format($latestPayment->final_amount, 2) : '0.00' }}";
            const pdfUrl = "{{ route('students.receipt.download', $student) }}";

            if (!navigator.share) {
                alert('Web Share API is not supported in your browser. Please use WhatsApp or Email buttons.');
                return;
            }

            try {
                // Show loading message
                const loadingMsg = document.createElement('div');
                loadingMsg.innerHTML = '⏳ Preparing PDF for sharing...';
                loadingMsg.style.cssText =
                    'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#333;color:#fff;padding:20px;border-radius:8px;z-index:9999;';
                document.body.appendChild(loadingMsg);

                // Fetch the PDF file
                const response = await fetch(pdfUrl);
                const blob = await response.blob();

                // Create a File object from the blob
                const file = new File([blob], `receipt_${studentId}.pdf`, {
                    type: 'application/pdf'
                });

                // Remove loading message
                document.body.removeChild(loadingMsg);

                // Check if we can share files
                if (navigator.canShare && navigator.canShare({
                        files: [file]
                    })) {
                    await navigator.share({
                        title: `Payment Receipt - ${studentName}`,
                        text: `Payment receipt for ${studentName} (ID: ${studentId}). Amount: ৳${amount}`,
                        files: [file]
                    });
                    console.log('PDF shared successfully');
                } else {
                    // Fallback: download the file
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `receipt_${studentId}.pdf`;
                    link.click();
                    alert(
                        '📥 PDF downloaded! Your browser doesn\'t support file sharing, but the file has been downloaded.');
                }
            } catch (error) {
                console.error('Error sharing PDF:', error);
                alert('❌ Error sharing PDF: ' + error.message);
            }
        }

        // Check if Web Share API is supported and show the button
        document.addEventListener('DOMContentLoaded', function() {
            if (navigator.share) {
                const webShareBtn = document.getElementById('webShareBtn');
                if (webShareBtn) {
                    webShareBtn.style.display = 'inline-flex';
                }
            }
        });
    </script>
@endsection
