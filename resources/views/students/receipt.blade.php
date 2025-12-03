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
            font-family: Arial, sans-serif;
            background-color: #fff;
            padding: 8px;
            font-size: 10px;
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
            margin-bottom: 10px;
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
            padding: 0 110px; /* Space for logo and date */
            padding-top: 10px;
        }
        
        .banner-center img {
            max-width: 100%;
            height: auto;
            max-height: 90px;
        }

        .date-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .date-box {
            font-size: 10px;
            font-weight: bold;
            color: #51272f;
            text-align: center;
            width: 100%;
            padding: 5px;
            border: 1px dashed #51272f;
            background: #fff;
        }
        
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #51272f;
            border-bottom: 4px solid #51272f;
            padding-bottom: 5px;
            margin-top: 5px;
        }
        
        .content {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .student-details, .fee-section {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
        }
        
        .student-details {
            width: 50%;
            padding-right: 15px;
        }
        
        .fee-section {
            width: 50%;
            padding-left: 15px;
        }
        
        .detail-row {
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .detail-label {
            color: #333;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
        }
        
        .detail-value {
            color: #000;
            font-weight: normal;
        }
        
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .fee-table th {
            background-color: #f5f5f5;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #51272f;
            color: #51272f;
            font-size: 10px;
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
        }
        
        .payment-method {
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        .payment-method-label {
            font-weight: bold;
            margin-bottom: 3px;
            color: #51272f;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .amount-box {
            border: 1px solid #51272f;
            padding: 5px 10px;
            min-width: 120px;
            text-align: right;
            background-color: white;
            font-weight: 500;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 11px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #51272f;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #51272f;
            text-align: center;
            font-size: 9px;
        }
        
        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }
        
        .footer-label {
            color: #666;
            font-size: 9px;
        }
        
        .footer-partners {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .footer-partners span {
            color: #51272f;
            font-weight: bold;
            font-size: 9px;
        }

        @media print {
            body {
                padding: 0;
            }
            
            .receipt-container {
                border: 1px solid #51272f;
                padding: 10px;
            }
        }

        @page {
            size: A4;
            margin: 8mm;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        
        <div class="header-container">
            <!-- Logo Left -->
            <div class="logo-left">
                <img src="{{ public_path('madrasa-logo.jpeg') }}" alt="Logo">
            </div>

            <!-- Banner Center -->
            <div class="banner-center">
                <img src="{{ public_path('academy-banner.png') }}" alt="Al Akhirah International Academy">
            </div>

            <!-- Date Right -->
            <div class="date-right">
                <div class="date-box">
                    Date:<br>{{ $admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date)->format('d/m/Y') : date('d/m/Y') }}
                </div>
            </div>
        </div>
        
        <h1>PAYMENT RECEIPT</h1>

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
                    <span class="detail-value">{{ $student->mobile }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $student->address }}</span>
                </div>
            </div>
            
            <!-- Fee Section -->
            <div class="fee-section">
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $fees = $student->classroom->fees ?? [];
                            $admissionFee = $student->classroom->admission_fee ?? 0;
                        @endphp
                        
                        @if($admissionFee > 0)
                            <tr>
                                <td>Admission Fee</td>
                                <td class="amount-cell">{{ number_format($admissionFee) }}</td>
                            </tr>
                        @endif
                        
                        @foreach($fees as $feeItem)
                            <tr>
                                <td>{{ $feeItem['name'] ?? 'Fee' }}</td>
                                <td class="amount-cell">{{ number_format($feeItem['amount'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        
                        <!-- Add empty rows -->
                        @for($i = count($fees); $i < 3; $i++)
                            <tr class="empty-row">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                
                <!-- Payment Summary -->
                <div class="payment-summary">
                    <div class="payment-method">
                        <div class="payment-method-label">Pay Method:</div>
                        <div>{{ $admissionPayment->payment_mode ?? 'Cash' }}</div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="payment-method-label">In Word:</div>
                        <div>{{ ucwords($amountInWords) }} Taka Only</div>
                    </div>
                    
                    <div class="payment-row">
                        <span>Subtotal:</span>
                        <div class="amount-box">{{ number_format($student->classroom->total_fee ?? 0) }}</div>
                    </div>
                    
                    <div class="payment-row">
                        <span>Discount:</span>
                        <div class="amount-box">{{ number_format(($student->classroom->total_fee ?? 0) - ($admissionPayment->amount ?? 0)) }}</div>
                    </div>
                    
                    <div class="payment-row total-row">
                        <span>Total Paid:</span>
                        <div class="amount-box">{{ number_format($admissionPayment->amount ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <span class="footer-label">Affiliated with:</span>
                <div class="footer-partners">
                    <span>BRITISH COUNCIL</span>
                    <span>Pearson</span>
                    <span>edexcel</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>