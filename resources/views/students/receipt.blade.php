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
            padding: 0 110px; /* Space for logo and date */
            padding-top: 10px;
        }
        
        .banner-center img {
            max-width: 100%;
            height: auto;
            max-height: 90px;
            display: block;
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
            letter-spacing: 1px;
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
            top: 100px;
            right: 0;
            width: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .date-box {
            font-size: 12px;
            font-weight: bold;
            color: #51272f;
            text-align: center;
            width: 100%;
            padding:0 15px;
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
            align-items: center;   /* FIX alignment */
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
                <div class="academy-info">
                    <div class="academy-subtitle">International Academy</div>
                    <div class="academy-address">House #9, Road #41, Sobhanbagh, Dhanmondi, Dhaka</div>
                    <div class="academy-contacts">
                        <span class="contact-item">Tel: +880 1729-649017</span>
                        <span class="contact-item">Web: www.alakhirahacademy.com</span>
                        <span class="contact-item">FB: /alakhirahacademy</span>
                    </div>
                </div>
            </div>
      <!-- Date Right -->
            <div class="date-right">
                <div class="date-box">
                    Date:{{ $admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date)->format('d/m/Y') : date('d/m/Y') }}
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
                            <th style="text-align: right;">Amount (Tk)</th>
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
                    <div class="payment-row">
                        
                        <div class="amount-box"><span  style="float:left;" >Subtotal:</span>{{ number_format($student->classroom->total_fee ?? 0) }}</div>
                    </div>
                    
                    <div class="payment-row">
                       
                        <div class="amount-box"> <span style="float:left;">Discount:</span>{{ number_format(($student->classroom->total_fee ?? 0) - ($admissionPayment->amount ?? 0)) }}</div>
                    </div>
                    
                    <div class="payment-row">                       
                        <div class="amount-box"> <span style="float:left;">Total Paid:</span>{{ number_format($admissionPayment->amount ?? 0) }}</div>
                    </div>
                </div>
                <!-- Payment Method and In Word moved here -->
                <div class="payment-method" style="margin-top: 15px;">
                    <div class="payment-method-label"><label>Pay Method:</label>{{ $admissionPayment->payment_mode ?? 'Cash' }}</div>
                </div>
                
                <div class="payment-method">
                    <div class="payment-method-label"><label>In Word:</label> {{ ucwords($amountInWords) }} Taka Only</div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            @if(file_exists(public_path('footer-logos.png')))
                <img src="{{ public_path('footer-logos.png') }}" alt="Affiliated Partners" class="footer-image">
            @endif
        </div>
    </div>
</body>
</html>