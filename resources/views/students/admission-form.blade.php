<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form - {{ $student->name }}</title>
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
            font-size: 12px;
            line-height: 1.2;
        }
        
        .form-wrapper {
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
            display: flex;
            flex-direction: column;
            align-items: center;
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
            font-size: 16px;
            font-weight: bold;
            color: #51272f;
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .academy-address {
            font-size: 11px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .academy-contacts {
            font-size: 10px;
            color: #333;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .contact-item {
            white-space: nowrap;
        }

        .photo-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .photo-box {
            width: 100px;
            height: 100px;
            border: 1px dashed #51272f;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            color: #51272f;
            background: #fff;
            overflow: hidden;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .date-box {
            font-size: 11px;
            font-weight: bold;
            color: #51272f;
            margin-top: 5px;
            text-align: center;
            width: 100%;
        }
        .checkboxes {
    display: block;
    text-align: center;
    margin-bottom: 8px;
    padding: 8px 0;
}

.checkbox-wrapper {
    text-align: center;
    padding: 4px 0;
}

.checkboxes label {
    display: inline-block;
    margin: 0 15px;
    font-size: 11px;
    font-weight: normal;
    vertical-align: middle;
}

.checkbox-box {
    width: 18px;
    height: 18px;
    border: 1.5px solid #51272f;
    display: inline-block;
    text-align: center;
    vertical-align: middle;
    background: white;
    font-size: 20px;
    line-height: 2px;
    font-weight: bold;
    margin-right: 5px;
    font-family: 'DejaVu Sans', sans-serif;
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
        
        .form-label {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 2px;
            color: #333;
        }

        .form-value {
            border-bottom: 1px dotted #51272f;
            padding: 2px 0;
            min-height: 18px;
            font-size: 12px;
            display: block;
            color: #000;
        }
        

        .row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .col-md-4, .col-md-6, .col-md-8, .col-12 {
            display: table-cell;
            padding: 2px 4px;
            vertical-align: top;
        }

        .col-md-4 {
            width: 33.33%;
        }

        .col-md-6 {
            width: 50%;
        }

        .col-md-8 {
            width: 66.66%;
        }

        .col-12 {
            width: 100%;
            display: block;
        }

        .mb-2 {
            margin-bottom: 4px;
        }

        .mb-3 {
            margin-bottom: 6px;
        }

        .d-block {
            display: block;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin: 8px 0 6px 0;
            padding: 3px;
            background: #f5f5f5;
            border-top: 1px dashed #51272f;
            border-bottom: 1px dashed #51272f;
            color: #51272f;
        }

        .address-box {
            border: 1px dashed #51272f;
            padding: 6px;
            margin-bottom: 6px;
            min-height: 50px;
        }

        .address-box-title {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
            color: #51272f;
        }

        .radio-group {
    display: inline-block;
    margin-left: 8px;
    vertical-align: middle;
    margin-top: 2px;
}
        .radio-group label {
    display: inline-block;
    margin-right: 15px;
    font-weight: normal;
    font-size: 10px;
    vertical-align: middle;
}

.radio-group input[type="radio"] {
    vertical-align: middle;
    margin-top: -2px;
    margin-right: 3px;
}
        .declaration-box {
            border: 1px solid #51272f;
            padding: 6px;
            margin: 8px 0;
            font-size: 10px;
            line-height: 1.3;
            text-align: justify;
        }

        .declaration-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
            color: #51272f;
        }

        .signature-section {
            margin-top: 15px;
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #51272f;
            margin-top: 25px;
            padding-top: 3px;
            text-align: center;
            font-size: 10px;
        }

        @media print {
            body {
                padding: 0;
            }
            
            .form-wrapper {
                border: 1px solid #51272f;
                padding: 10px;
            }

            .form-value {
                border-bottom: 1px dotted #51272f;
            }
        }

        @page {
            size: A4;
            margin: 8mm;
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        
        <div class="header-container">
            <!-- Logo Left -->
            <div class="logo-left">
                <img src="{{ public_path('madrasa-logo.jpeg') }}" alt="Logo">
            </div>

            <!-- Banner Center -->
          <!-- Banner Center -->
          <div class="banner-center">
                <img src="{{ public_path('academy-banner.png') }}" alt="Al Akhirah International Academy">
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
            <!-- Photo Right -->
            <div class="photo-right">
                <div class="photo-box">
                    @if($student->photo && file_exists(storage_path('app/public/' . $student->photo)))
                        <img src="{{ storage_path('app/public/' . $student->photo) }}" alt="Student Photo">
                    @else
                        <br>Attach<br>Photo<br>Here
                    @endif
                </div>

                <div class="date-box">
                    ID: {{ $student->student_id }}
                </div>
                <div class="date-box">
                    Date: {{ $student->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
        
        <h1>ADMISSION FORM</h1>

        <!-- Academic Year, Class, Shift -->

         <div class="checkboxes">
                <div class="checkbox-wrapper">
                    <label>
                        <span class="checkbox-box">{{ str_contains($student->program_type, 'Hifz') ? '✓' : '' }}</span> Hifz
                    </label>
                    <label>
                        <span class="checkbox-box">{{ str_contains($student->program_type, 'Schooling') ? '✓' : '' }}</span> Schooling
                    </label>
                </div>
            </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <span class="form-label">Academic Year:</span>
                <span class="form-value">{{ date('Y') }}</span>
            </div>
            <div class="col-md-4">
                <span class="form-label">Class:</span>
                <span class="form-value">{{ $student->classroom->name ?? '' }}</span>
            </div>
            <div class="col-md-4">
                <span class="form-label">Shift:</span>
                <span class="form-value">{{ $student->shift ?? 'Morning' }}</span>
            </div>
        </div>

        <!-- Student Name -->
        <div class="row mb-2">
            <div class="col-12">
                <span class="form-label">Student's Name:</span>
                <span class="form-value d-block">{{ $student->name }}</span>
            </div>
        </div>

        <!-- Father's Name -->
        <div class="row mb-2">
            <div class="col-12">
                <span class="form-label">Father's Name:</span>
                <span class="form-value d-block">{{ $student->father_name }}</span>
            </div>
        </div>

        <!-- Mother's Name -->
        <div class="row mb-2">
            <div class="col-12">
                <span class="form-label">Mother's Name:</span>
                <span class="form-value d-block">{{ $student->mother_name ?? '' }}</span>
            </div>
        </div>

        <!-- Birth Date & Gender -->
        <div class="row mb-2">
            <div class="col-md-6">
                <span class="form-label">Birth Date:</span>
                <span class="form-value">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d / m / Y') : '' }}</span>
                <small style="color: #999; font-size: 7px;">(Please Attach Birth Certificate)</small>
            </div>
           <div class="col-md-6">
                 <span class="form-label"style="margin-bottom:-35px !important; display:block;">Gender:</span><div class="radio-group">
                     <div style="margin-left:40px">
                    <label ><input type="radio" {{ $student->gender == 'Male' ? 'checked' : '' }} disabled> Male</label>
                    <label><input type="radio" {{ $student->gender == 'Female' ? 'checked' : '' }} disabled> Female</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Children & Position -->
        <div class="row mb-2">
            <div class="col-md-6">
                <span class="form-label">Total Number of children in family:</span>
                <span class="form-value">{{ $student->siblings_count ?? '' }}</span>
            </div>
            <div class="col-md-6">
                <span class="form-label">Position among offspring:</span>
                <span class="form-value">{{ $student->birth_order ?? '' }}</span>
            </div>
        </div>

        <!-- Last School Attended -->
        <div class="row mb-3">
            <div class="col-12">
                <span class="form-label">Name of the school last attended:</span>
                <span class="form-value d-block">{{ $student->last_school ?? '' }}</span>
            </div>
        </div>

        <!-- Present Address -->
        <div class="address-box">
            <div class="address-box-title">Present Address</div>
            <div class="row">
                <div class="col-md-8 mb-2">
                    <span class="form-label">Address:</span>
                    <span class="form-value d-block">{{ $student->address ?? '' }}</span>
                </div>
                <div class="col-md-4 mb-2">
                    <span class="form-label">District:</span>
                    <span class="form-value d-block">{{ $student->present_district ?? '' }}</span>
                </div>
            </div>
        </div>

        <!-- Permanent Address -->
        <div class="address-box">
            <div class="address-box-title">Permanent Address</div>
            <div class="row">
                <div class="col-md-8 mb-2">
                    <span class="form-label">Address:</span>
                    <span class="form-value d-block">{{ $student->permanent_address ?? $student->address ?? '' }}</span>
                </div>
                <div class="col-md-4 mb-2">
                    <span class="form-label">District:</span>
                    <span class="form-value d-block">{{ $student->permanent_district ?? $student->present_district ?? '' }}</span>
                </div>
            </div>
        </div>

        <!-- Guardian's Details Section -->
        <div class="section-title">GUARDIAN'S DETAILS</div>

        <!-- Occupation & Nationality -->
        <div class="row mb-2">
            <div class="col-md-6">
                <span class="form-label">Occupation:</span>
                <span class="form-value d-block">{{ $student->guardian_occupation ?? '' }}</span>
            </div>
            <div class="col-md-6">
                <span class="form-label">Nationality:</span>
                <span class="form-value d-block">{{ $student->guardian_nationality ?? '' }}</span>
            </div>
        </div>

        <!-- Phone & Email -->
        <div class="row mb-2">
            <div class="col-md-6">
                <span class="form-label">Phone Number:</span>
                <span class="form-value d-block">{{ $student->guardian_phone ?? $student->mobile }}</span>
            </div>
            <div class="col-md-6">
                <span class="form-label">Email Address:</span>
                <span class="form-value d-block">{{ $student->guardian_email ?? '' }}</span>
            </div>
        </div>

        <!-- NID & Blood Group -->
        <div class="row mb-2">
            <div class="col-md-6">
                <span class="form-label">NID Number:</span>
                <span class="form-value d-block">{{ $student->guardian_nid ?? '' }}</span>
            </div>
            <div class="col-md-6">
                <span class="form-label">Blood Group:</span>
                <span class="form-value d-block">{{ $student->blood_group ?? '__________' }}</span>
            </div>
        </div>

        <!-- Declaration -->
        <div class="declaration-box">
            <div class="declaration-title">DECLARATION</div>
            <p>I hereby declare that the particulars stated above are true and my child does not suffer from any contagious disease. I pledge that I shall abide by all the rules and regulations of Al Akhirah International Academy.</p>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-col">
                <div class="signature-line">Guardian's Signature</div>
            </div>
            <div class="signature-col">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

    </div>
</body>
</html>