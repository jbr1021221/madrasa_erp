<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Receipt - {{ $student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial;
            background-color: #fff;
            padding: 8px;
            font-size: 12px;
            line-height: 1.2;
        }

        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 12px;
            border: 1px solid #51272f;
            position: relative;
        }

        /* ... other styles ... */

        @page {
            size: A4 landscape;
            margin: 10mm;
            /* Hide browser default headers and footers */
            @top-left { content: none; }
        }

        .header-container {
            position: relative;
            min-height: 130px;
            max-width: 95%;
            margin: 0px 0px 0px 15px;
        }

        .logo-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 100px;
            height: 100px;
        }

        .logo-left img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .banner-center {
            text-align: center;
            padding: 0 110px;
            padding-top: 10px;
        }

        .banner-center img {
            max-width: 100%;
            height: auto;
            max-height: 90px;
            display: block;
            margin: 0 auto;
        }

        .academy-info {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            margin-top: 8px;
            width: 100%;
        }

        .academy-subtitle {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-size: 16px;
            font-weight: bold;
            color: #51272f;
            letter-spacing: 4px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .academy-address {
            font-size: 10px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .academy-contacts {
            font-size: 9px;
            color: #333;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .contact-item {
            white-space: nowrap;
        }

        .date-right {
            position: absolute;
            top: 45px;
            right: 0;
            width: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

.date-wrapper {
    display: flex;
    flex-direction: column;   /* stack vertically */
    align-items: flex-end;    /* push to the right */
}

.date-box {
    font-size: 11px;
    font-weight: bold;
    color: #51272f;
    margin-top: 5px;
    text-align: right;
    width: auto;
}


        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #51272f;
            border-bottom: 4px solid #51272f;
            padding-bottom: 5px;
        }

        .content {
            display: table;
            width: 98%;
            margin-top: 5px;

        }

        .student-details, .fee-section {
            display: table-cell;
            vertical-align: top;
            padding: 2px;
        }

        .student-details {
            width: 35%;
            padding-right: 8px;
        }

        .fee-section {
            width: 65%;
            padding-left: 8px;
        }

            .detail-row {
            font-size: 9px;
            padding: 1px 0;
            width: 80%;
            display: block;
            align-items: center;   /* FIX alignment */
            }

            .detail-label {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            color: #51272f;
            font-weight: 600;
            min-width: 70px;
            }

            .detail-value {
            color: #000;
            font-weight: normal;
            margin-bottom:-2px !important;
            padding-left:3px;
            }

        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .fee-table th {
            background-color: #f5f5f5;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #51272f;
            color: #51272f;
            font-size: 12px;
        }

        .fee-table td {
            padding: 8px;
            border: 1px solid #51272f;
            background-color: white;
        }

        .fee-table .amount-cell {
            text-align: right;
            font-weight: 500;
        }

        .empty-row {
            height: 35px;
            background-color: #fafafa;
        }

        .payment-summary {
            margin-top: 5px;
            width: 50%;
            margin-left: auto;
        }

        .payment-method {
            margin-bottom: 3px;
            font-size: 9px;
        }

        .payment-method-label label{
            font-weight: bold;
            margin-bottom: 3px;
            color: #51272f;
        }


        .payment-method-label {
            font-weight: normal;
            margin-bottom: 3px;
            color: #000;
        }

        .payment-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 3px;
            font-size: 9px;
            gap: 5px;
        }

        .payment-row span {
            text-align: right;
        }

        .amount-box {
            border: 1px solid #51272f;
            padding: 2px 4px;
            min-width: 80px;
            text-align: right;
            background-color: white;
            font-weight: 500;
        }

        .total-row {
            font-weight: bold;
            font-size: 9px;
            margin-top: 3px;
            padding-top: 3px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #51272f;
            text-align: center;
        }

        .footer-image {
            max-width: 100%;
            height: auto;
            max-height: 20px;
            display: block;
            margin: 0 auto;
        }

        @media print {
            body {
                padding: 0;
            }

            .receipt-container {
                border: 1px solid #51272f;
                padding: 5px;
            }
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="receipt-container">

        <div class="header-container">
            <!-- Logo Left -->
            <div class="logo-left">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('madrasa-logo.jpeg') : asset('madrasa-logo.jpeg') }}" alt="Logo">
            </div>

            <!-- Banner Center -->
            <div class="banner-center">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('academy-banner.png') : asset('academy-banner.png') }}" alt="Al Akhirah International Academy">
                <div class="academy-info">
                    <div class="academy-subtitle">International Academy</div>
                  <div class="academy-address">House #9, Road #14, Sobhanbagh, Dhanmondi, Dhaka</div>
                    <div class="academy-contacts">
                        <span class="contact-item">Tel: +880 1729-649017</span>
                        <span class="contact-item">Web: www.alakhirahacademy.com</span>
                        <span class="contact-item">FB: /alakhirahacademy</span>
                    </div>
                </div>
            </div>
      <!-- Date Right -->
            <div class="date-wrapper">
                <div class="date-box">
                                Receipt No: {{ $receiptNo }}
                            </div>
                <div class="date-box">
                    Date: {{ $student->created_at->format('d/m/Y') }}
                </div>
            </div>


                    <h1>PAYMENT RECEIPT(Admission)</h1>

                    <!-- Main Content -->
                    <div class="content">
                        <!-- Student Details -->
                        <div class="student-details">
                            <div class="detail-row">
                                <span class="detail-label">Student Name:</span>
                                <span class="detail-value">{{ $student->name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Student ID:</span>
                                <span class="detail-value">{{ $student->student_id }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Class:</span>
                                <span class="detail-value">{{ $student->classroom->name ?? '' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Section:</span>
                                <span class="detail-value">{{ $student->section }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Father Name:</span>
                                <span class="detail-value">{{ $student->father_name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Mother Name:</span>
                                <span class="detail-value">{{ $student->mother_name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value">{{ $student->father_mobile }}</span>
                            </div>



</div>

            <!-- Fee Section -->
            <div class="fee-section">
                @php
                    // Get fee details from the payment record in database
                    $feeDetails = $admissionPayment->fee_details ?? [];

                    // Process and group fees
                    $processedFees = [];
                    $monthlyGroups = [];

                    $totalNet = 0;
                    $totalOriginal = 0;
                    $totalDiscount = 0;

                    if (is_array($feeDetails)) {
                        foreach ($feeDetails as $fee) {
                            // Net Amount (Paid Amount)
                            $net = floatval($fee['amount'] ?? 0);

                            // Discount
                            $disc = floatval($fee['discount'] ?? 0);

                            // Original Amount
                            // Check for Partial Payment (Suffix added by controller)
                            $isPartial = (strpos($fee['name'], '(Partial)') !== false);

                            if ($isPartial) {
                                // For receipt math, we only consider the portion processed now (Paid + Discount)
                                $orig = $net + $disc;
                            } elseif (isset($fee['original_amount']) && $fee['original_amount'] > 0) {
                                $orig = floatval($fee['original_amount']);
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
                            if (isset($fee['type']) && $fee['type'] === 'Monthly' && isset($fee['month'])) {
                                // Extract base name by splitting " - "
                                $nameParts = explode(' - ', $fee['name']);
                                $baseName = count($nameParts) > 1 ? trim($nameParts[0]) : $fee['name'];

                                $monthLabel = $fee['month'];
                                if (isset($fee['year']) && strpos($monthLabel, $fee['year']) === false) {
                                    $monthLabel .= ' ' . $fee['year'];
                                } elseif (!isset($fee['year']) && isset($nameParts[1])) {
                                    $monthLabel = trim($nameParts[1]);
                                }

                                if (!isset($monthlyGroups[$baseName])) {
                                    $monthlyGroups[$baseName] = [
                                        'months' => [],
                                        'total_amount' => 0,
                                        'total_original' => 0  // Track original for groups
                                    ];
                                }
                                $monthlyGroups[$baseName]['months'][] = $monthLabel;
                                $monthlyGroups[$baseName]['total_amount'] += $net;
                                $monthlyGroups[$baseName]['total_original'] += $orig;
                            } else {
                                // Store original amount specifically for Admission Fee (Partial) to calculate remaining
                                $item = [
                                    'name' => $fee['name'],
                                    'amount' => $net
                                ];

                                if (strpos($fee['name'], 'Admission Fee (Partial)') !== false) {
                                     if (isset($fee['original_amount'])) {
                                         $item['original'] = floatval($fee['original_amount']);
                                     } elseif ($disc > 0) {
                                         $item['original'] = $net + $disc;
                                     }
                                }

                                $processedFees[] = $item;
                            }
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

                        $description = $baseName . ' (' . $monthRange . ')';

                        $processedFees[] = [
                            'name' => $description,
                            'amount' => $group['total_amount']
                        ];
                    }

                    $totalPaid = $admissionPayment->amount ?? 0;

                    // Final Calculation for Display
                    $subtotal = $totalOriginal;
                    $discount = $totalDiscount;

                    // Fallback: If no discount data found at all, but totalPaid < subtotal (from loop), recalculate
                    // (This handles cases where the loop calculates subtotal based on net only if orig is missing)
                    // But our robust logic above handles orig defaulting to net.
                    // If manual global discount exists (payment amount < sum of items)
                    if ($totalPaid < $totalNet - 0.01) {
                         $manualDiscount = $totalNet - $totalPaid;
                         $discount += $manualDiscount;
                         $subtotal += $manualDiscount; // If we consider the list items as "after discount", this logic might be complex.
                         // Let's trust the fee_details logic primarily.
                    }
                @endphp

                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (Tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($processedFees) > 0)
                            {{-- Display processed fees --}}
                            @foreach($processedFees as $fee)
                                <tr>
                                    <td>
                                        @if(strpos($fee['name'], 'Admission Fee (Partial)') !== false && isset($fee['original']) && $fee['original'] > $fee['amount'])
                                            Admission Fee (<span style="color:#51272f;">{{ number_format($fee['original'], 0) }} TK</span>) - Partial
                                        @else
                                            {{ $fee['name'] ?? 'Fee' }}
                                        @endif
                                    </td>
                                    <td class="amount-cell">{{ number_format($fee['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Fallback: If no fee_details, show basic info --}}
                            <tr>
                                <td>{{ $admissionPayment->payment_type ?? 'Payment' }} - {{ $admissionPayment->month ?? '' }}</td>
                                <td class="amount-cell">{{ number_format($totalPaid, 2) }}</td>
                            </tr>
                        @endif

                        {{-- Add empty rows to fill space --}}
                        @php
                            $rowCount = count($feeDetails) > 0 ? count($feeDetails) : 1;
                        @endphp
                        @for($i = $rowCount; $i < 3; $i++)
                            <tr class="empty-row">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; margin-top: 5px; align-items: flex-start;">
                    {{-- Left Side: Payment Method & In Word --}}
                    <div style="width: 55%;">
                        <div class="payment-method">
                            <div class="payment-method-label"><label>Pay Method:</label> {{ $admissionPayment->payment_mode ?? 'Cash' }}</div>
                        </div>

                        <div class="payment-method">
                            <div class="payment-method-label"><label>In Word:</label> {{ ucwords($amountInWords) }} Taka Only</div>
                        </div>
                    </div>

                    {{-- Right Side: Payment Summary --}}
                    <div class="payment-summary" style="width: 40%; margin-top: 0;">
                 @if($discount > 0)
                        <div class="payment-row">
                            <div class="amount-box"><span style="float:left;">Subtotal:</span>{{ number_format($subtotal, 2) }}</div>
                        </div>

                        <div class="payment-row">
                            <div class="amount-box"><span style="float:left;">Discount:</span>{{ number_format($discount, 2) }}</div>
                        </div>
                        @endif

                        <div class="payment-row">
                            <div class="amount-box"><span style="float:left;">Total:</span>{{ number_format($totalPaid, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top: 20px; margin-bottom: 10px;">
            <div style="border-top: 1px solid #000; width: 100px; text-align: center; font-size: 9px; margin-left: 10px;">
                Accountant
            </div>
        </div>

        <!-- Footer -->

              <div class="footer">
            <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/Aslaf.jpg') : asset('images/Aslaf.jpg') }}" alt="Aslaf" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/BriCou1.png') : asset('images/BriCou1.png') }}" alt="British Council" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/Cambridge.jpg') : asset('images/Cambridge.jpg') }}" alt="Cambridge" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/edexcel.png') : asset('images/edexcel.png') }}" alt="Edexcel" style="height: 25px; width: auto;">

                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/pearson.png') : asset('images/pearson.png') }}" alt="Pearson" style="height: 25px; width: auto;">
            </div>
        </div>

    </div>
</body>
</html>
