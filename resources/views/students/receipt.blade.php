<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Receipt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Header Section */
        .header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .banner-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        /* Invoice Title */
        .invoice-title {
            text-align: center;
            font-size: 28px;
            color: #003366;
            margin: 30px 0;
            font-weight: bold;
        }
        
        /* Main Content */
        .content {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        /* Student Details */
        .student-details {
            flex: 1;
        }
        
        .detail-row {
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .detail-label {
            color: #333;
            font-weight: normal;
            display: inline-block;
            width: 130px;
        }
        
        .detail-value {
            color: #000;
            font-weight: 500;
        }
        
        /* Fee Table */
        .fee-section {
            flex: 1;
        }
        
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .fee-table th {
            background-color: #f0f0f0;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            color: #333;
        }
        
        .fee-table td {
            padding: 12px;
            border: 1px solid #ddd;
            background-color: white;
        }
        
        .fee-table .amount-cell {
            text-align: right;
            font-weight: 500;
        }
        
        /* Empty Rows */
        .empty-row {
            height: 50px;
            background-color: #fafafa;
        }
        
        /* Payment Summary */
        .payment-summary {
            margin-top: 20px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .payment-method {
            margin-bottom: 20px;
        }
        
        .payment-method-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .amount-box {
            border: 1px solid #ddd;
            padding: 10px;
            min-width: 150px;
            text-align: right;
            background-color: white;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
        }
        
        .footer img {
            height: 40px;
            object-fit: contain;
        }
        
        .watermark {
            position: absolute;
            left: 50px;
            bottom: 100px;
            opacity: 0.05;
            width: 200px;
            height: 200px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('academy-banner.png') }}" alt="Al Akhirah International Academy" class="banner-image">
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">Invoice</div>
        
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
                    <span class="detail-value">{{ $student->classroom->name ?? 'N/A' }}</span>
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
                    <span class="detail-label">Phone no:</span>
                    <span class="detail-value">{{ $student->mobile }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $student->address }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date)->format('d M Y') : date('d M Y') }}</span>
                </div>
            </div>
            
            <!-- Fee Section -->
            <div class="fee-section">
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $fees = $student->classroom->fees ?? [];
                        @endphp
                        
                        @foreach($fees as $feeItem)
                            <tr>
                                <td>{{ $feeItem['name'] ?? 'Fee' }}</td>
                                <td class="amount-cell">{{ number_format($feeItem['amount'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        
                        <!-- Add empty rows to match the design -->
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
                        <div class="payment-method-label">Pay Method</div>
                        <div>{{ $admissionPayment->payment_mode ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="payment-method">
                        <div class="payment-method-label">In Word:</div>
                        <div>{{ ucwords($amountInWords) }} Taka Only</div>
                    </div>
                    
                    <div class="payment-row">
                        <span>Subtotal</span>
                        <div class="amount-box">{{ number_format($student->classroom->total_fee ?? 0) }}</div>
                    </div>
                    
                    <div class="payment-row">
                        <span>Discount</span>
                        <div class="amount-box">{{ number_format(($student->classroom->total_fee ?? 0) - ($admissionPayment->amount ?? 0)) }}</div>
                    </div>
                    
                    <div class="payment-row total-row">
                        <span>Total</span>
                        <div class="amount-box">{{ number_format($admissionPayment->amount ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Partner Logos -->
        <div class="footer">
            <div style="color: #999; font-size: 12px;">Affiliated with:</div>
            <!-- Add your partner logos here -->
            <div style="display: flex; gap: 20px; align-items: center;">
                <span style="color: #0066cc; font-weight: bold;">BRITISH COUNCIL</span>
                <span style="color: #0066cc; font-weight: bold;">Pearson</span>
                <span style="color: #0066cc; font-weight: bold;">edexcel</span>
            </div>
        </div>
        
        <!-- Watermark -->
        <div class="watermark">
            <svg viewBox="0 0 100 100" fill="#8B0000">
                <rect x="20" y="40" width="60" height="50"/>
                <path d="M50 10 L80 35 L20 35 Z"/>
            </svg>
        </div>
    </div>
</body>
</html>