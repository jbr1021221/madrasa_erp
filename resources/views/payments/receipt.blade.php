<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $student?->name ?? 'Deleted Student' }}</title>
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
            top: 75px;
            right: 0;
            width: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
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
            margin-top: 15px;
        }
        
        .student-details, .fee-section {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
        }
        
        .student-details {
            width: 35%;
            padding-right: 15px;
        }
        
        .fee-section {
            width: 65%;
            padding-left: 15px;
        }

        .detail-row {
            font-size: 12px;
            padding: 3px 0;
            width: 80%;
            display: block;
            align-items: center;
        }

        .detail-label {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            color: #51272f;
            font-weight: 600;
            min-width: 100px;
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
            margin-top: 10px;
            width: 50%;
            margin-left: auto;
        }
        
        .payment-method {
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .payment-method-label {
            font-weight: normal;
            margin-bottom: 3px;
            color: #000;
        }
        
        .payment-method-label label {
            font-weight: bold;
            margin-bottom: 3px;
            color: #51272f;
        }
        
        .payment-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 6px;
            font-size: 12px;
            gap: 10px;
        }
        
        .payment-row span {
            text-align: right;
        }
        
        .amount-box {
            border: 1px solid #51272f;
            padding: 4px 8px;
            min-width: 100px;
            text-align: right;
            background-color: white;
            font-weight: 500;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 12px;
            margin-top: 8px;
            padding-top: 8px;
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
            max-height: 35px;
            display: block;
            margin: 0 auto;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .receipt-container {
                border: 1px solid #51272f;
                padding: 10px;
                max-width: 100%;
                page-break-inside: avoid;
            }
            
            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
            /* Hide browser default headers and footers */
            @top-left { content: none; }
            @top-center { content: none; }
            @top-right { content: none; }
            @bottom-left { content: none; }
            @bottom-center { content: none; }
            @bottom-right { content: none; }
        }
    </style>
</head>
<body>

    
    @if(!isset($isPdf) || !$isPdf)
    <div class="no-print" style="text-align:center; padding:10px; background:#f0f0f0; border-bottom:1px solid #ccc; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:8px 16px; background:#51272f; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-right:10px;">
            Print Receipt
        </button>
        <a href="{{ route('payments.receipt.download', $payment->id) }}" style="padding:8px 16px; background:#4caf50; color:white; text-decoration:none; border-radius:4px; font-weight:bold; display:inline-block;">
            Download PDF
        </a>
    </div>
    @endif

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
        Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
    </div>
</div>

        
        <h1>PAYMENT RECEIPT</h1>

        <!-- Main Content -->
        <div class="content">
            <!-- Student Details -->
            <div class="student-details">
                <div class="detail-row">
                    <span class="detail-label">Student Name:</span>
                    <span class="detail-value">{{ $student?->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Student ID:</span>
                    <span class="detail-value">{{ $student?->student_id ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Class:</span>
                    <span class="detail-value">{{ $student?->classroom?->name ?? '' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Section:</span>
                    <span class="detail-value">{{ $student?->section ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Father Name:</span>
                    <span class="detail-value">{{ $student?->father_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mother Name:</span>
                    <span class="detail-value">{{ $student?->mother_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $student?->mobile ?? 'N/A' }}</span>
                </div>
          
            </div>
            
            <div class="fee-section">
                @php
                    // Get fee details from the payment record in database
                    $feeDetails = $payment->fee_details ?? [];

                    // Process and group fees
                    $processedFees = [];
                    $monthlyGroups = [];
                    
                    $totalNet = 0;
                    $totalOriginal = 0;
                    $totalDiscount = 0;

                    if (is_array($feeDetails) && count($feeDetails) > 0) {
                        foreach ($feeDetails as $fee) {
                            $net = floatval($fee['amount'] ?? 0);
                            $orig = floatval($fee['original_amount'] ?? $net);
                            $disc = floatval($fee['discount'] ?? ($orig - $net));
                            
                            $totalNet += $net;
                            $totalOriginal += $orig;
                            $totalDiscount += $disc;

                            // Check if this is a monthly fee that should be grouped
                            if (isset($fee['type']) && $fee['type'] === 'Monthly' && isset($fee['month'])) {
                                // Extract base name logic
                                $nameParts = explode(' - ', $fee['name']);
                                $baseName = count($nameParts) > 1 ? trim($nameParts[0]) : $fee['name'];
                                
                                $monthLabel = $fee['month'];
                                if (isset($fee['year']) && strpos($monthLabel, $fee['year']) === false) {
                                    $monthLabel .= ' ' . $fee['year'];
                                } elseif (isset($nameParts[1])) {
                                    $monthLabel = trim($nameParts[1]);
                                }

                                if (!isset($monthlyGroups[$baseName])) {
                                    $monthlyGroups[$baseName] = [
                                        'months' => [],
                                        'net' => 0,
                                        'original' => 0,
                                        'discount' => 0
                                    ];
                                }
                                $monthlyGroups[$baseName]['months'][] = $monthLabel;
                                $monthlyGroups[$baseName]['net'] += $net;
                                $monthlyGroups[$baseName]['original'] += $orig;
                                $monthlyGroups[$baseName]['discount'] += $disc;
                            } else {
                                // Non-grouped fee
                                $processedFees[] = [
                                    'name' => $fee['name'],
                                    'amount' => $net,
                                    'original_amount' => $orig,
                                    'discount' => $disc
                                ];
                            }
                        }
                    } else {
                        // Legacy fallback if fee_details empty but amount exists
                        $totalNet = $payment->amount;
                        $totalOriginal = $payment->amount;
                        $totalDiscount = 0;
                    }

                    // Process groups
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
                            'amount' => $group['net'],
                            'original_amount' => $group['original'],
                            'discount' => $group['discount']
                        ];
                    }

                    // Reconcile Manual/Global Discount
                    // If the sum of fee items (Net) is greater than the actual Payment Amount, 
                    // it means a global discount was applied (e.g. manual entry)
                    $actualPaid = floatval($payment->amount);
                    $manualDiscount = max(0, $totalNet - $actualPaid);

                    if ($manualDiscount > 0.01) {
                         $processedFees[] = [
                             'name' => 'Additional Discount',
                             'amount' => -$manualDiscount,
                             'original_amount' => 0,
                             'discount' => $manualDiscount
                         ];
                         $totalDiscount += $manualDiscount;
                         $totalNet -= $manualDiscount; // Should match actualPaid now
                    }
                    
                    // Show discount columns if Total Discount > 0
                    $showDiscountCol = $totalDiscount > 0;
                @endphp
                
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            @if($showDiscountCol)
                                <th style="text-align: right;">Amount (Tk)</th>
                                <th style="text-align: right;">Discount</th>
                                <th style="text-align: right;">Net Payable</th>
                            @else
                                <th style="text-align: right;">Amount (Tk)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($processedFees) > 0)
                            @foreach($processedFees as $fee)
                                <tr>
                                    <td>{{ $fee['name'] ?? 'Fee' }}</td>
                                    @if($showDiscountCol)
                                        <td class="amount-cell">{{ number_format($fee['original_amount'], 2) }}</td>
                                        <td class="amount-cell">{{ number_format($fee['discount'], 2) }}</td>
                                        <td class="amount-cell">{{ number_format($fee['amount'], 2) }}</td>
                                    @else
                                        <td class="amount-cell">{{ number_format($fee['amount'], 2) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                            
                            {{-- Fill empty rows --}}
                            @php $emptyRows = max(0, 4 - count($processedFees)); @endphp
                            @for($i = 0; $i < $emptyRows; $i++)
                                <tr class="empty-row">
                                    <td>&nbsp;</td>
                                    @if($showDiscountCol)
                                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                </tr>
                            @endfor
                        @else
                            {{-- Legacy Fallback Row --}}
                            <tr>
                                <td>{{ $payment->payment_type }} - {{ $payment->month }}</td>
                                @if($showDiscountCol)
                                    <td class="amount-cell">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="amount-cell">0.00</td>
                                @endif
                                <td class="amount-cell">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endif
                        
                        {{-- Total Row --}}
                        <tr class="total-row" style="background-color: #f5f5f5; border-top: 2px solid #51272f;">
                            <td style="text-align: right; font-weight: bold;">Total:</td>
                            @if($showDiscountCol)
                                <td class="amount-cell" style="font-weight: bold;">{{ number_format($totalOriginal, 2) }}</td>
                                <td class="amount-cell" style="font-weight: bold;">{{ number_format($totalDiscount, 2) }}</td>
                                <td class="amount-cell" style="font-weight: bold;">{{ number_format($totalNet, 2) }}</td>
                            @else
                                <td class="amount-cell" style="font-weight: bold;">{{ number_format($totalNet, 2) }}</td>
                            @endif
                        </tr>
                    </tbody>
                </table>
                
                <div style="display: flex; justify-content: space-between; margin-top: 10px; align-items: flex-start;">
                    <!-- Left Side: Payment Method & In Word -->
                    <div style="width: 100%;">
                        <div class="payment-method">
                            <div class="payment-method-label"><label>Payment Method:</label> {{ $payment->payment_mode ?? 'Cash' }}</div>
                        </div>
                        
                        @if($payment->note)
                        <div class="payment-method">
                            <div class="payment-method-label"><label>Note:</label> {{ $payment->note }}</div>
                        </div>
                        @endif
                        
                        <div class="payment-method">
                            <div class="payment-method-label"><label>In Word:</label> {{ ucwords($amountInWords) }} Taka Only</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 40px; margin-bottom: 20px;">
            <div style="border-top: 1px solid #000; width: 150px; text-align: center; font-size: 12px; margin-left: 20px;">
                Accountant
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/Aslaf-3.jpg') : asset('images/Aslaf-3.jpg') }}" alt="Aslaf" style="height: 35px; width: 120px;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/BriCou1.png') : asset('images/BriCou1.png') }}" alt="British Council" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/Cambridge.jpg') : asset('images/Cambridge.jpg') }}" alt="Cambridge" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/edexcel.png') : asset('images/edexcel.png') }}" alt="Edexcel" style="height: 25px; width: auto;">
                <img src="{{ isset($isPdf) && $isPdf ? public_path('images/pearson.png') : asset('images/pearson.png') }}" alt="Pearson" style="height: 25px; width: auto;">
            </div>
        </div>
    </div>
</body>
</html>

