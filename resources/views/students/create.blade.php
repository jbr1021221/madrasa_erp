<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Add Student – Madrasa ERP</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root{
  --bg:#0b0d0f;--panel:#111316;--card:#0f1416;--text:#e6eef3;--muted:#98a0a6;
  --accent:#e37814;--danger:#ff4e4e;--radius:6px;
}
body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;
  display:flex;justify-content:center;padding:22px;}
.container{width:100%;max-width:1400px;display:grid;grid-template-columns:240px 1fr;gap:18px;}
.sidebar{background:var(--panel);padding:16px;border-radius:var(--radius);
  height:calc(100vh - 44px);display:flex;flex-direction:column;
  border:1px solid rgba(255,255,255,0.04);}
.logo{width:42px;height:42px;border-radius:var(--radius);object-fit:cover;
  border:1px solid rgba(255,255,255,0.1);}
.nav{display:flex;flex-direction:column;gap:4px;margin-top:18px}
.nav a{padding:10px 14px;border-radius:var(--radius);text-decoration:none;font-size:14px;color:var(--muted);}
.nav a:hover,.nav a.active{background:rgba(227,120,20,0.12);border-left:3px solid var(--accent);color:var(--accent);}
.panel{background:var(--panel);padding:20px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.05);}
.btn{background:var(--accent);color:#041617;border:0;padding:7px 14px;border-radius:var(--radius);
  cursor:pointer;font-size:14px;text-decoration:none;display:inline-block;}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
input,select,textarea{
  background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);
  padding:8px 12px;border-radius:var(--radius);font-size:14px;width:100%;max-width:100%;
  box-sizing: border-box;
}
label{display:block;margin-bottom:6px;margin-top:16px;font-size:14px;color:var(--muted)}
.error{color:var(--danger);font-size:12px;margin-top:4px;}

/* Custom grid for form */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:10px}
.form-group{display:flex;flex-direction:column}

/* Fee Table */
.fee-table {width: 100%;border-collapse: collapse;margin-top: 10px;}
.fee-table th {background: rgba(255,255,255,0.15);padding: 10px;font-size: 14px;text-align: left;}
.fee-table td {padding: 10px;font-size: 14px;border-bottom: 1px solid rgba(255,255,255,0.05);}
.fee-table tr:nth-child(odd) {background: rgba(255,255,255,0.04);}
.total-fee-display {
  margin-top: 14px;font-size: 18px;font-weight: 700;text-align: right;
  color: #fff;padding: 12px;background: rgba(53, 52, 51, 0.1);
  border-radius: var(--radius);
}
</style>

<style>
/* Fee Panel Styles */
.fee-row {
    display: grid;
    grid-template-columns: 40px 1fr 100px 120px 120px 60px 140px;
    align-items: center;
    gap: 12px;
    padding: 12px;
    margin-bottom: 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    transition: 0.2s;
}
.fee-row:hover {
    background: rgba(255,165,0,0.15);
    border-color: orange;
}
.fee-row input[type="number"] {
    width: 100%;
    padding: 5px;
    height: 32px;
    text-align: right;
    border: 1px solid #666;
    border-radius: 4px;
    background: #1b1f22;
    color: #fff;
}
.total-box {
    text-align: right;
    margin-top: 20px;
    padding: 12px;
    font-size: 20px;
    background: rgba(255,165,0,0.15);
    border: 2px solid orange;
    border-radius: 6px;
    color: #fff;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(4px);
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: var(--panel);
    border: 2px solid var(--accent);
    border-radius: var(--radius);
    width: 90%;
    max-width: 1000px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 30px;
    position: relative;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 28px;
    cursor: pointer;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--accent);
}

.review-section {
    margin-bottom: 24px;
}

.review-section h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.review-detail-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.review-item {
    display: flex;
    flex-direction: column;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 4px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: background 0.2s;
}

.review-item:hover {
    background: rgba(255,255,255,0.05);
}

.review-item-label {
    font-size: 12px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
    margin-bottom: 6px;
}

.review-item-value {
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
}
</style>
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  @include('components.sidebar')

  <!-- MAIN CONTENT -->
  <main class="panel">

    <h2 style="margin:0 0 20px 0">Add New Student</h2>

    {{-- Display validation errors --}}
    @if($errors->any())
      <div style="background:rgba(255,0,0,0.1);border:1px solid rgba(255,0,0,0.3);padding:15px;border-radius:6px;margin-bottom:20px">
        <h4 style="margin:0 0 10px 0;color:#ff4444">Please fix the following errors:</h4>
        <ul style="margin:0;padding-left:20px;color:#ff6666">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" id="studentForm">
      @csrf

      <!-- Student ID Display
      <div class="form-group" style="margin-bottom:16px;">
        <label>Student ID (Auto-generated)</label>
        <input type="text" name="student_id" id="student_id" value="{{ $student_id ?? old('student_id') }}" readonly style="background:#262a2e;cursor:not-allowed;font-weight:bold;color:var(--accent)">
        <small style="color:var(--muted);font-size:12px">This ID is automatically generated based on the class selection.</small>
      </div> -->

      <!-- Full width name field -->
      <div class="form-group">
        <label>Student Name *</label>
        <input type="text" name="name" placeholder="Student Name" value="{{ old('name') }}" required>
        @error('name')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Father Name *</label>
          <input type="text" name="father_name" placeholder="Father's Name" value="{{ old('father_name') }}" required>
          @error('father_name')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Mother Name</label>
          <input type="text" name="mother_name" placeholder="Mother's Name" value="{{ old('mother_name') }}">
          @error('mother_name')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      

      <div class="form-row">
        <div class="form-group">
          <label>Mobile *</label>
          <input type="text" name="mobile" placeholder="01XXXXXXXXX" value="{{ old('mobile') }}" required>
          @error('mobile')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Alt Mobile</label>
          <input type="text" name="alt_mobile" placeholder="01XXXXXXXXX" value="{{ old('alt_mobile') }}">
          @error('alt_mobile')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-group">
        <label>NID Upload (Father/Mother)</label>
        <input type="file" name="nid_file" accept=".jpg,.jpeg,.png,.pdf">
        @error('nid_file')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label>Student Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png">
        <small style="color: var(--muted); font-size: 12px; margin-top: 4px; display: block;">Upload a passport-size photo (JPG, JPEG, or PNG)</small>
        @error('photo')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;text-align:center">Student Details</h4>

      <div class="form-row">
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="dob" value="{{ old('dob') }}">
          @error('dob')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
          </select>
          @error('gender')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Blood Group</label>
          <select name="blood_group">
            <option value="">Select Blood Group</option>
            <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
            <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
            <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
            <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
            <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
            <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
            <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
            <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
          </select>
          @error('blood_group')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Last School Attended</label>
          <input type="text" name="last_school" placeholder="Previous School Name" value="{{ old('last_school') }}">
          @error('last_school')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Total Number of Children in Family</label>
          <input type="number" name="siblings_count" min="1" placeholder="e.g., 3" value="{{ old('siblings_count') }}">
          @error('siblings_count')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Position Among Offspring</label>
          <input type="number" name="birth_order" min="1" placeholder="e.g., 2 (if 2nd child)" value="{{ old('birth_order') }}">
          @error('birth_order')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;text-align:center">Address Information</h4>

      <div class="form-group">
        <label>Present Address</label>
        <textarea name="address" rows="2" placeholder="Current residential address">{{ old('address') }}</textarea>
        @error('address')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <!-- <div class="form-group">
        <label>Present District</label>
        <input type="text" name="present_district" placeholder="e.g., Dhaka" value="{{ old('present_district') }}">
        @error('present_district')
          <div class="error">{{ $message }}</div>
        @enderror
      </div> -->

      <div class="form-group">
        <label>Permanent Address</label>
        <textarea name="permanent_address" rows="2" placeholder="Permanent residential address">{{ old('permanent_address') }}</textarea>
        @error('permanent_address')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <!-- <div class="form-group">
        <label>Permanent District</label>
        <input type="text" name="permanent_district" placeholder="e.g., Chittagong" value="{{ old('permanent_district') }}">
        @error('permanent_district')
          <div class="error">{{ $message }}</div>
        @enderror
      </div> -->

      <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;text-align:center">Guardian Details</h4>

      <div class="form-row">
        <div class="form-group">
          <label>Occupation</label>
          <input type="text" name="guardian_occupation" placeholder="e.g., Teacher, Farmer" value="{{ old('guardian_occupation') }}">
          @error('guardian_occupation')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Nationality</label>
          <input type="text" name="guardian_nationality" placeholder="e.g., Bangladeshi" value="{{ old('guardian_nationality') }}">
          @error('guardian_nationality')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="guardian_phone" placeholder="01XXXXXXXXX" value="{{ old('guardian_phone') }}">
          @error('guardian_phone')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="guardian_email" placeholder="guardian@example.com" value="{{ old('guardian_email') }}">
          @error('guardian_email')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-group">
        <label>NID Number</label>
        <input type="text" name="guardian_nid" placeholder="National ID Number" value="{{ old('guardian_nid') }}">
        @error('guardian_nid')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;text-align:center">Class & Section</h4>

      <div class="form-row">
        <div class="form-group">
          <label>Select Class *</label>
          <select name="class_id" id="class_id" required onchange="updateClassInfo()">
            <option value="">Select Class</option>
            @foreach($classrooms as $classroom)
              <option value="{{ $classroom->id }}" {{ old('class_id') == $classroom->id ? 'selected' : '' }}>
                {{ $classroom->name }}
              </option>
            @endforeach
          </select>
          @error('class_id')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Select Section *</label>
          <select name="section" id="section" required>
            <option value="">Select Class First</option>
          </select>
          @error('section')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Program Type * (Select one or both)</label>
          <div style="display:flex;gap:20px;margin-top:8px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="program_type[]" value="Hifz" {{ is_array(old('program_type')) && in_array('Hifz', old('program_type')) ? 'checked' : '' }}>
              <span>Hifz</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="program_type[]" value="Schooling" {{ is_array(old('program_type')) && in_array('Schooling', old('program_type')) ? 'checked' : '' }}>
              <span>Schooling</span>
            </label>
          </div>
          @error('program_type')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Shift *</label>
          <select name="shift" required>
            <option value="">Select Shift</option>
            <option value="Morning" {{ old('shift') == 'Morning' ? 'selected' : '' }}>Morning</option>
            <option value="Evening" {{ old('shift') == 'Evening' ? 'selected' : '' }}>Evening</option>
          </select>
          @error('shift')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>

       <div class="row" style="display:flex;gap:20px;margin-top:24px;">
          <!-- Available Monthly Fees Column -->
          <div style="flex:1;">
            <h4 style="margin-top:0;margin-bottom:12px;color:var(--text);text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;">Available Fees</h4>
            <div id="availableFeesContainer" style="background:rgba(255,255,255,0.04);padding:10px;border-radius:6px;min-height:150px;max-height:500px;overflow-y:auto;">
              <p style="color:var(--muted);text-align:center;margin-top:20px;"><em>Select a class first</em></p>
            </div>
          </div>

          <!-- Student Fees Column -->
          <div style="flex:1.5;">
            <h4 style="margin-top:0;margin-bottom:12px;color:var(--accent);text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;">Student Fees</h4>
            <div style="background:rgba(255,255,255,0.04);padding:10px;border-radius:6px;min-height:150px;">
                <!-- Header -->
                <div style="display:grid;grid-template-columns:30px 2fr 1.5fr 1fr 1fr 30px;gap:8px;padding:0 8px 8px;border-bottom:1px solid rgba(255,255,255,0.05);color:var(--muted);font-size:11px;text-transform:uppercase;">
                    <div>Pay</div>
                    <div>Fee Name</div>
                    <div>Months</div>
                    <div>Discount</div>
                    <div style="text-align:right">Amount</div>
                    <div></div>
                </div>
                
                <div id="studentFeesContainer">
                   <p style="color:var(--muted);text-align:center;margin-top:20px;" id="emptyStudentFeesMsg"><em>Select a class to load fees</em></p>
                </div>

                <!-- Total -->
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.1);display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:600;">Total Payable</span>
                    <span id="studentFeesTotal" style="font-size:18px;font-weight:bold;color:var(--accent)">৳ 0.00</span>
                </div>
                
                <!-- Partial Payment Option -->
                <div style="margin-top:16px;padding:16px;background:rgba(255,193,7,0.1);border:1px solid rgba(255,193,7,0.3);border-radius:8px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
                        <input type="checkbox" id="partialPaymentCheck" onchange="togglePartialPayment()" style="width:18px;height:18px;cursor:pointer;">
                        <span style="font-weight:600;color:var(--text);">Pay Partial Admission Fee (Remaining will be due)</span>
                    </label>
                    
                    <div id="partialPaymentInput" style="display:none;">
                        <label style="display:block;margin-bottom:4px;font-size:13px;color:var(--muted);">Amount Paying Now</label>
                        <input type="number" id="partialAmount" name="partial_amount" min="0" step="0.01" 
                               placeholder="Enter amount" 
                               oninput="updatePartialPayment()"
                               style="width:100%;padding:8px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.1);color:var(--text);border-radius:4px;font-size:14px;">
                        <div id="remainingAmount" style="margin-top:8px;font-size:13px;color:var(--muted);"></div>
                    </div>
                </div>
            </div>
          </div>
       </div>
       <input type="hidden" id="total_admission_fee" name="total_admission_fee" value="0">
       <input type="hidden" id="is_partial_payment" name="is_partial_payment" value="0">




      <h4 style="margin-top:24px;margin-bottom:12px;color:var(--text);text-align:center">Payment Information</h4>
      <div class="form-row">
        <div class="form-group">
          <label>Payment Mode *</label>
          <select name="payment_mode" required>
            <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
            <option value="Bkash" {{ old('payment_mode') == 'Bkash' ? 'selected' : '' }}>Bkash</option>
            <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
            <option value="Rocket" {{ old('payment_mode') == 'Rocket' ? 'selected' : '' }}>Rocket</option>
            <option value="Upay" {{ old('payment_mode') == 'Upay' ? 'selected' : '' }}>Upay</option>
            <option value="Others" {{ old('payment_mode') == 'Others' ? 'selected' : '' }}>Others</option>
          </select>
          @error('payment_mode')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label>Note (optional)</label>
          <input type="text" name="payment_note" placeholder="Additional notes..." value="{{ old('payment_note') }}">
          @error('payment_note')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
      <!-- Hidden field to store selected admission fees for receipt -->
      <input type="hidden" name="selected_admission_fees" id="selectedAdmissionFees" value="{{ old('selected_admission_fees') }}">
      <input type="hidden" name="student_assigned_fees" id="studentAssignedFees" value="{{ old('student_assigned_fees') }}">

      <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('students.index') }}" class="btn ghost">Cancel</a>
        <button type="button" class="btn" id="reviewBtn" onclick="showReview()">Preview</button>
        <button type="submit" class="btn" id="savePayBtn" style="display:none">Save and Pay</button>
      </div>

    </form>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal-overlay">
      <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        
        <h3 style="margin:0 0 8px 0;color:var(--text);font-size:24px;font-weight:600">Student Information Preview</h3>
        <p style="margin:0 0 25px 0;color:var(--muted);font-size:14px">Please review all information before submitting</p>
        
        <div id="reviewContent"></div>
        
        <div style="margin-top:30px;display:flex;gap:12px;justify-content:center;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1)">
          <button type="button" class="btn ghost" onclick="editForm()">← Edit Information</button>
          <button type="button" class="btn" onclick="confirmAndSubmit()">Confirm and Pay →</button>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
// Old input data for restoration
const oldDiscounts = @json(old('fee_discounts', []));
const oldPermanent = @json(old('fee_permanent', []));

// Classroom data from controller
const classroomData = @json($classroomData);
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

let availableClassFees = [];
let feeRowCounter = 0;

// Generate Student ID dynamically
function generateStudentId(classId) {
    if (!classId) return;
    
    fetch(`/students/generate-id/${classId}`)
        .then(response => response.json())
        .then(data => {
            const el = document.getElementById('student_id');
            if (el) el.value = data.student_id;
        })
        .catch(error => {
            console.error('Error generating student ID:', error);
        });
}

function updateClassInfo() {
    const classSelect = document.getElementById('class_id');
    const classId = classSelect.value;
    const sectionSelect = document.getElementById('section');
    
    // Reset UI
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    // Reset UI
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    const availContainer = document.getElementById('availableFeesContainer');
    if(availContainer) availContainer.innerHTML = '<p style="color:var(--muted);text-align:center;margin-top:20px;"><em>Select a class first</em></p>';
    
    const studentContainer = document.getElementById('studentFeesContainer');
    if(studentContainer) studentContainer.innerHTML = '<p style="color:var(--muted);text-align:center;margin-top:20px;" id="emptyStudentFeesMsg"><em>Select a class to load fees</em></p>';
    
    updateTotal(0);
    
    if (!classId || !classroomData[classId]) {
        sectionSelect.innerHTML = '<option value="">Select Class First</option>';
        return;
    }
    
    const data = classroomData[classId];
    
    // Populate sections
    if (data.sections && data.sections.length > 0) {
        data.sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            sectionSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "A";
        option.textContent = "A";
        sectionSelect.appendChild(option);
    }
    
    generateStudentId(classId);
    
    // Prepare Data
    availableClassFees = [];
    const admissionFee = parseFloat(data.admission_fee) || 0;
    const additionalFees = data.fees || [];
    
    if (admissionFee > 0) {
        availableClassFees.push({
            name: "Admission Fee",
            type: "One Time",
            amount: admissionFee,
            is_admission: true
        });
    }
    
    if (additionalFees.length > 0) {
        additionalFees.forEach(f => {
            availableClassFees.push({
                name: f.name,
                type: f.type || "Monthly",
                amount: parseFloat(f.amount)
            });
        });
    }

    // Auto-add non-monthly and admission fees
    const studentContainerEl = document.getElementById('studentFeesContainer');
    if(studentContainerEl) studentContainerEl.innerHTML = ''; // Clear empty message
    const emptyMsg = document.getElementById('emptyStudentFeesMsg');
    if(emptyMsg) emptyMsg.style.display = 'none';

    availableClassFees.forEach((fee, index) => {
        if (!['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type)) {
            addFeeToStudent(index);
        }
    });

    renderAvailableFees();
    
    // Restore logic
    const assignedFeesJson = document.getElementById('studentAssignedFees')?.value;
    const paymentFeesJson = document.getElementById('selectedAdmissionFees')?.value;
    const feesJsonToRestore = assignedFeesJson || paymentFeesJson; 

    if (feesJsonToRestore) {
        try {
            const oldFees = JSON.parse(feesJsonToRestore);
            // Parse payment fees once if available for lookup
            let paymentFees = [];
            if (paymentFeesJson) {
                try { paymentFees = JSON.parse(paymentFeesJson); } catch(e) {}
            }

            oldFees.forEach(of => {
                // Handle legacy restoration or mixed sources
                // Ideally of.name is the base name (no suffix)
                let baseName = of.name;
                
                // If we are restoring from paymentFees (fallback), name has suffix
                if (['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(of.type) && of.name.includes(' - ')) {
                    baseName = of.name.split(' - ')[0]; // Approximate base name
                } else if (!of.type && of.name.includes(' - ')) {
                     // Try to guess if it was supposed to be monthly
                     baseName = of.name.split(' - ')[0];
                }

                let matchIndex = availableClassFees.findIndex(af => af.name === baseName);
                
                if (matchIndex !== -1) {
                    const rowId = addFeeToStudent(matchIndex);
                    const row = document.getElementById(rowId);
                    if(row) {
                        const payCheck = row.querySelector('.pay-now-check');
                        const discInput = row.querySelector('.fee-discount-input');
                        const permCheck = row.querySelector('.perm-check');
                        
                        let isPaid = false;
                        
                        if (paymentFees.length > 0) {
                            if (['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(of.type)) {
                                // For monthly, check if ANY payment exists starting with "Name - "
                                // Safe check: included space-dash-space
                                isPaid = paymentFees.some(pf => pf.name.startsWith(baseName + ' - '));
                            } else {
                                isPaid = paymentFees.some(pf => pf.name === baseName);
                            }
                        } else {
                            // If no paymentFees json (legacy), assume true if it was in the list
                            isPaid = true;
                        }
                        
                        // Force check if paying (or uncheck if not)
                        if(payCheck) payCheck.checked = isPaid;
                        if(discInput) discInput.value = of.discount || 0;
                        if(permCheck) permCheck.checked = of.is_permanent || false;
                        
                        // Restore Months if monthly
                        // We must look at paymentFees to find which months were selected
                        if (['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(of.type)) {
                             let monthsToSelect = [];
                             
                             if (paymentFees.length > 0) {
                                  // Find all payment entries for this fee
                                  const relatedPayments = paymentFees.filter(pf => pf.name.startsWith(baseName + ' - '));
                                  monthsToSelect = relatedPayments.map(rp => rp.month);
                             } else if (of.month) {
                                  // Fallback if 'of' came from payment list directly
                                  monthsToSelect.push(of.month);
                             }
                             
                             if(monthsToSelect.length > 0) {
                                  const checkboxes = row.querySelectorAll('.month-dropdown-menu input[type="checkbox"]');
                                  checkboxes.forEach(cb => {
                                      // Check strict match or containment
                                      if (monthsToSelect.some(m => cb.value === m || cb.value.startsWith(m))) {
                                          cb.checked = true;
                                      } else {
                                          cb.checked = false;
                                      }
                                  });
                             }
                        }
                        
                        updateFeeRow(rowId);
                    }
                }
            });
        } catch (e) {
            console.error('Restore error', e);
        }
    }
}

// Auto-trigger on load if class is selected (e.g. restoration)
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    if(classSelect && classSelect.value) {
        updateClassInfo();
    }
});

function renderAvailableFees() {
    const container = document.getElementById('availableFeesContainer');
    if(!container) return;
    container.innerHTML = '';
    
    // Show Monthly, Quarterly, and Half Yearly fees in this list
    const periodicFees = availableClassFees.filter(f => ['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(f.type));
    
    if (periodicFees.length === 0) {
        container.innerHTML = '<p style="color:var(--muted);text-align:center;">No periodic fees available</p>';
        return;
    }

    // Note: We need original index from availableClassFees to pass to addFeeToStudent
    availableClassFees.forEach((fee, index) => {
        if (!['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type)) return;

        const div = document.createElement('div');
        div.id = `avail_fee_div_${index}`;
        div.style.cssText = 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);margin-bottom:8px;padding:10px;border-radius:4px;display:flex;justify-content:space-between;align-items:center;transition:background 0.2s;';
        div.onmouseover = () => div.style.background = 'rgba(255,255,255,0.06)';
        div.onmouseout = () => div.style.background = 'rgba(255,255,255,0.03)';
        
        div.innerHTML = `
            <div>
                <div style="font-weight:600;font-size:13px;color:var(--text);">${fee.name}</div>
                <div style="font-size:11px;color:var(--muted);">${fee.type} • ৳ ${fee.amount}</div>
            </div>
            <button type="button" onclick="addFeeToStudent(${index})" style="background:var(--accent);color:white;border:none;border-radius:4px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;">+</button>
        `;
        container.appendChild(div);
    });
}

function addFeeToStudent(feeIndex) {
    const fee = availableClassFees[feeIndex];
    if (!fee) return;
    
    const container = document.getElementById('studentFeesContainer');
    const emptyMsg = document.getElementById('emptyStudentFeesMsg');
    if (emptyMsg) emptyMsg.style.display = 'none';

    const type = (fee.type || '').toLowerCase();
    const isMonthly = type === 'monthly';
    const isQuarterly = type === 'quarterly';
    const isHalfYearly = ['half yearly', 'half-yearly', 'half_yearly'].includes(type);
    const isPeriodic = isMonthly || isQuarterly || isHalfYearly;

    if (isPeriodic) {
         const existing = Array.from(container.querySelectorAll('.student-fee-row')).find(r => r.dataset.name === fee.name);
         if (existing) {
             Swal.fire({ toast: true, icon: 'warning', title: 'Fee already added', position: 'top-end', showConfirmButton: false, timer: 1500 });
             return existing.id;
         }
    } else {
        // One Time or others
        const existing = Array.from(container.querySelectorAll('.student-fee-row')).find(r => r.dataset.name === fee.name);
        if (existing) {
             Swal.fire({ toast: true, icon: 'warning', title: 'Fee already added', position: 'top-end', showConfirmButton: false, timer: 1500 });
             return existing.id;
        }
    }

    const rowId = 'fee_row_' + (feeRowCounter++);
    
    const row = document.createElement('div');
    row.className = 'student-fee-row';
    row.id = rowId;
    row.dataset.baseAmount = fee.amount;
    row.dataset.originalIndex = feeIndex;
    row.dataset.type = fee.type;
    row.dataset.name = fee.name;
    row.dataset.isPeriodic = isPeriodic;
    // Updated Grid: added 30px col at start for Checkbox
    row.style.cssText = 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);margin-bottom:8px;padding:8px;border-radius:4px;display:grid;grid-template-columns:30px 2fr 1.5fr 1fr 1fr 30px;gap:8px;align-items:center;';

    let monthHtml = '<span style="color:var(--muted);font-size:11px;">N/A</span>';
    if (isPeriodic) {
        let optionsHtml = '';
        if (isMonthly) {
            const d = new Date();
            const mIdx = d.getMonth();
            const yr = d.getFullYear();
            optionsHtml = generateMonthOptions(rowId, mIdx, yr);
            
            monthHtml = `
                <div style="position:relative;">
                    <div onclick="toggleFeeMonth('${rowId}')" style="background:rgba(0,0,0,0.2);padding:4px 8px;border-radius:3px;font-size:11px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;border:1px solid rgba(255,255,255,0.1);user-select:none;">
                        <span class="month-display" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70px;">...</span>
                        <span>▼</span>
                    </div>
                    <div id="dd_${rowId}" class="month-dropdown-menu" style="display:none;position:absolute;top:100%;left:0;width:160px;max-height:200px;overflow-y:auto;background:#050607;border:1px solid rgba(255,255,255,0.1);z-index:999;box-shadow:0 4px 12px rgba(0,0,0,0.3);border-radius:4px;padding:4px;">
                        ${optionsHtml}
                    </div>
                </div>
            `;
        } else {
            // Quarterly or Half-Yearly -> Visible Select
            if (isQuarterly) {
                optionsHtml = generateQuarterOptions(rowId);
            } else if (isHalfYearly) {
                optionsHtml = generateHalfYearlyOptions(rowId);
            }
            monthHtml = `<div style="width:100%;">${optionsHtml}</div>`;
        }
    }

    row.innerHTML = `
        <div style="display:flex;justify-content:center;">
             <input type="checkbox" class="pay-now-check" ${fee.is_admission ? 'checked' : ''} onchange="updateFeeRow('${rowId}')" title="Pay Now">
        </div>
        <div style="font-size:12px;font-weight:500;">${fee.name}</div>
        <div>${monthHtml}</div>
        <div style="display:flex;align-items:center;gap:4px;">
            <input type="number" class="fee-discount-input" placeholder="0" min="0" max="${fee.amount}" 
                   style="width:60px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.1);color:var(--text);padding:4px;border-radius:3px;font-size:12px;"
                   oninput="updateFeeRow('${rowId}')">
            <label style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:2px;cursor:pointer;" title="Permanent Discount">
                <input type="checkbox" class="perm-check" onclick="updateFeeRow('${rowId}')"> P
            </label>
        </div>
        <div style="text-align:right;font-weight:600;font-size:12px;color:var(--accent);" class="fee-row-total">
            ৳ ${fee.amount}
        </div>
        <div style="text-align:right;">
            ${isPeriodic ? `<button type="button" onclick="removeFeeRow('${rowId}')" style="background:none;border:none;color:#ff4444;cursor:pointer;font-size:16px;line-height:1;">&times;</button>` : ''}
        </div>
    `;
    
    container.appendChild(row);
    updateFeeRow(rowId);
    
    // Hide from available list
    const availDiv = document.getElementById(`avail_fee_div_${feeIndex}`);
    if(availDiv) availDiv.style.display = 'none';

    return rowId;
}

function generateMonthOptions(rowId, startM, startY) {
    let html = '';
    for(let i=0; i<12; i++) {
        let idx = (startM + i) % 12;
        let yAdd = Math.floor((startM + i) / 12);
        let yr = startY + yAdd;
        let val = `${monthNames[idx]}, ${yr.toString().slice(-2)}`;
        let chk = (i === 0) ? 'checked' : '';
        html += `<label style="display:flex;align-items:center;justify-content:space-between;padding:4px;cursor:pointer;font-size:11px;white-space:nowrap;">${val} <input type="checkbox" value="${val}" ${chk} onchange="updateFeeRow('${rowId}')"></label>`;
    }
    return html;
}

function generateQuarterOptions(rowId) {
    const quarters = ['1st Quater', '2nd Quater', '3rd Quater', '4th Quater'];
    let html = '<select class="form-control" onchange="updateFeeRow(\'' + rowId + '\')" style="width:100%;padding:4px;background:#2b2f33;color:#fff;border:1px solid #444;border-radius:4px;font-size:11px;">';
    html += '<option value="">Select Part</option>';
    quarters.forEach((q, i) => {
        let sel = (i === 0) ? 'selected' : ''; // Default select 1st
        html += `<option value="${q}" ${sel}>${q}</option>`;
    });
    html += '</select>';
    return html;
}

function generateHalfYearlyOptions(rowId) {
    const halves = ['1st Half', '2nd Half'];
    let html = '<select class="form-control" onchange="updateFeeRow(\'' + rowId + '\')" style="width:100%;padding:4px;background:#2b2f33;color:#fff;border:1px solid #444;border-radius:4px;font-size:11px;">';
    html += '<option value="">Select Part</option>';
    halves.forEach((h, i) => {
        let sel = (i === 0) ? 'selected' : ''; // Default select 1st
        html += `<option value="${h}" ${sel}>${h}</option>`;
    });
    html += '</select>';
    return html;
}

function toggleFeeMonth(rowId) {
    document.querySelectorAll('.month-dropdown-menu').forEach(d => {
        if(d.id !== `dd_${rowId}`) d.style.display = 'none';
    });
    const dd = document.getElementById(`dd_${rowId}`);
    if(dd) dd.style.display = (dd.style.display === 'none' ? 'block' : 'none');
}

document.addEventListener('click', (e) => {
    if(!e.target.closest('.student-fee-row')) {
        document.querySelectorAll('.month-dropdown-menu').forEach(d => d.style.display = 'none');
    }
});

function removeFeeRow(rowId) {
    const row = document.getElementById(rowId);
    if(row) {
        const idx = row.dataset.originalIndex;
        if(idx !== undefined) {
             const availDiv = document.getElementById(`avail_fee_div_${idx}`);
             if(availDiv) availDiv.style.display = 'flex';
        }
        row.remove();
    }
    
    const c = document.getElementById('studentFeesContainer');
    if(c.children.length === 0 || (c.children.length === 1 && c.children[0].id === 'emptyStudentFeesMsg')) {
        document.getElementById('emptyStudentFeesMsg').style.display = 'block';
    }
    updateTotal();
}

function updateFeeRow(rowId) {
    const row = document.getElementById(rowId);
    if(!row) return;
    
    const base = parseFloat(row.dataset.baseAmount);
    // Use the flag we set on creation to be safe
    const isPeriodic = row.dataset.isPeriodic === 'true';
    const payNow = row.querySelector('.pay-now-check').checked;
    
    // Style update based on checkbox
    if(payNow) {
        row.style.opacity = '1';
    } else {
        row.style.opacity = '0.5';
    }
    
    let mul = 1;
    if(isPeriodic) {
        // Check for Select element (direct child of 3rd div or inside menu)
        // Since we are changing structure, let's look for select anywhere in the row
        const select = row.querySelector('select'); // Simplest check
        if (select) {
            const val = select.value;
            mul = val ? 1 : 0;
            // No display element to update for select
            row.dataset.selectedMonths = val;
        } else {
            // Checkboxes logic (legacy/monthly)
            const chk = row.querySelectorAll('.month-dropdown-menu input[type="checkbox"]:checked');
            mul = chk.length;
            const disp = row.querySelector('.month-display');
            
            let label = 'Months';
            if (row.dataset.type === 'Quarterly') label = 'Qtrs';
            if (['Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(row.dataset.type)) label = 'Halves';

            if (disp) {
                if(mul === 0) disp.innerText = 'None';
                else if(mul === 1) disp.innerText = chk[0].value.split(',')[0];
                else disp.innerText = `${mul} ${label}`;
            }
            
            row.dataset.selectedMonths = Array.from(chk).map(c => c.value).join('|');
        }
    }
    
    const discInput = row.querySelector('.fee-discount-input');
    let disc = parseFloat(discInput.value) || 0;
    if(disc > base) { disc = base; discInput.value = disc; }
    
    const perm = row.querySelector('.perm-check').checked;
    discInput.style.background = perm ? 'rgba(0,255,0,0.1)' : 'rgba(0,0,0,0.2)';
    row.dataset.isPermanent = perm;
    
    const finalUnit = base - disc;
    const total = finalUnit * (mul > 0 ? mul : 0); 
    
    row.querySelector('.fee-row-total').innerText = '৳ ' + total.toFixed(2);
    row.dataset.finalAmount = total;
    row.dataset.unitDiscount = disc;
    
    updateTotal();
}

function updateTotal(override) {
    if(override !== undefined) {
        document.getElementById('studentFeesTotal').innerText = '৳ ' + override.toFixed(2);
        document.getElementById('total_admission_fee').value = override;
        updatePartialPayment(); // Update partial payment display
        return;
    }
    let t = 0;
    let admissionFeesTotal = 0; // Track admission fees separately
    
    document.querySelectorAll('.student-fee-row').forEach(r => {
        // Only include if "Pay Now" is checked
        if (r.querySelector('.pay-now-check').checked) {
            const amount = parseFloat(r.dataset.finalAmount) || 0;
            t += amount;
            
            // Track admission fees (One Time fees) separately for partial payment
            const feeType = r.dataset.type;
            if (feeType && !['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(feeType)) {
                admissionFeesTotal += amount;
            }
        }
    });
    
    document.getElementById('studentFeesTotal').innerText = '৳ ' + t.toFixed(2);
    document.getElementById('total_admission_fee').value = admissionFeesTotal; // Store only admission fees for partial payment
    updatePartialPayment(); // Update partial payment display
}

function togglePartialPayment() {
    const checkbox = document.getElementById('partialPaymentCheck');
    const inputDiv = document.getElementById('partialPaymentInput');
    const isPartialInput = document.getElementById('is_partial_payment');
    
    if (checkbox.checked) {
        inputDiv.style.display = 'block';
        isPartialInput.value = '1';
        updatePartialPayment();
    } else {
        inputDiv.style.display = 'none';
        isPartialInput.value = '0';
        document.getElementById('partialAmount').value = '';
        document.getElementById('remainingAmount').innerHTML = '';
    }
}

function updatePartialPayment() {
    const checkbox = document.getElementById('partialPaymentCheck');
    if (!checkbox.checked) return;
    
    const totalAmount = parseFloat(document.getElementById('total_admission_fee').value) || 0;
    const partialAmount = parseFloat(document.getElementById('partialAmount').value) || 0;
    const remainingDiv = document.getElementById('remainingAmount');
    
    if (partialAmount > totalAmount) {
        document.getElementById('partialAmount').value = totalAmount;
        remainingDiv.innerHTML = '<span style="color:#4caf50;">✓ Full payment</span>';
        return;
    }
    
    if (partialAmount > 0) {
        const remaining = totalAmount - partialAmount;
        remainingDiv.innerHTML = `
            <div style="display:flex;justify-content:space-between;padding:8px;background:rgba(0,0,0,0.2);border-radius:4px;">
                <span>Paying Now:</span>
                <span style="color:#4caf50;font-weight:600;">৳${partialAmount.toFixed(2)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:rgba(255,78,78,0.1);border-radius:4px;margin-top:4px;">
                <span>Remaining Due:</span>
                <span style="color:#ff4e4e;font-weight:600;">৳${remaining.toFixed(2)}</span>
            </div>
        `;
    } else {
        remainingDiv.innerHTML = '';
    }
}


function collectAdmissionFees(event) {
    const paymentFees = [];
    const assignedFees = [];
    
    document.querySelectorAll('.student-fee-row').forEach(row => {
        const name = row.dataset.name;
        const type = row.dataset.type;
        const disc = parseFloat(row.dataset.unitDiscount) || 0;
        const base = parseFloat(row.dataset.baseAmount);
        const perm = row.dataset.isPermanent === 'true';
        const unitAmount = base - disc;
        const isPaying = row.querySelector('.pay-now-check').checked;
        
        // Add to assigned list (All fees in the list)
        assignedFees.push({
            name: name,
            type: type,
            amount: base,
            discount: disc,
            is_permanent: perm
        });
        
        if (!isPaying) return;

        // Add to payment list (Only checked fees)
        // Add to payment list (Only checked fees)
        if (['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(type)) {
            const months = (row.dataset.selectedMonths || '').split('|').filter(m => m);
            months.forEach(m => {
                paymentFees.push({
                    name: `${name} - ${m}`,
                    type: type,
                    amount: unitAmount,
                    original_amount: base,
                    discount: disc,
                    is_permanent: perm,
                    month: m
                });
            });
        } else {
            paymentFees.push({
                name: name,
                type: type,
                amount: unitAmount,
                original_amount: base,
                discount: disc,
                is_permanent: perm
            });
        }
    });

    console.log('Collecting Payment Fees:', paymentFees);
    console.log('Collecting Assigned Fees:', assignedFees);
    
    // Save payment fees for receipt
    document.getElementById('selectedAdmissionFees').value = JSON.stringify(paymentFees);
    
    // Debug: Log what's being saved
    console.log('=== ADMISSION FEE DEBUG ===');
    console.log('Payment Fees JSON:', JSON.stringify(paymentFees, null, 2));
    console.log('Total fees to pay:', paymentFees.length);
    paymentFees.forEach((fee, idx) => {
        console.log(`Fee ${idx + 1}:`, {
            name: fee.name,
            amount: fee.amount,
            original_amount: fee.original_amount,
            discount: fee.discount
        });
    });
    console.log('=========================');
    
    // Create/Update hidden input for assigned fees (Student Subscription)
    let assignedInput = document.getElementById('studentAssignedFees');
    if(!assignedInput) {
        assignedInput = document.createElement('input');
        assignedInput.type = 'hidden';
        assignedInput.id = 'studentAssignedFees';
        assignedInput.name = 'student_assigned_fees';
        // Append to form
        const form = document.getElementById('studentForm');
        if(form) form.appendChild(assignedInput);
    }
    assignedInput.value = JSON.stringify(assignedFees);
    
    return true;
}

function showReview() {
    const form = document.querySelector('#studentForm');
    if (!form.reportValidity()) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill in all required fields.',
            confirmButtonColor: '#e37814'
        });
        return;
    }
    
    const reviewContent = document.getElementById('reviewContent');
    let html = '';
    
    function section(title, items) {
        let h = '<div class="review-section"><h4>'+title+'</h4><div class="review-detail-list">';
        items.forEach(i => {
            h += '<div class="review-item"><div class="review-item-label">'+i.l+'</div><div class="review-item-value">'+(i.v || '<span style="color:var(--muted)">-</span>')+'</div></div>';
        });
        h += '</div></div>';
        return h;
    }
    
    function val(name) {
        const el = form.querySelector('[name="'+name+'"]');
        return el ? el.value : '';
    }
    
    html += section('Basic Details', [
        {l:'Student Name', v:val('name')},
        {l:'Student ID', v:document.getElementById('student_id') ? document.getElementById('student_id').value : 'Auto-generated'},
        {l:'Date of Birth', v:val('dob')},
        {l:'Gender', v:val('gender')},
        {l:'Blood Group', v:val('blood_group')},
        {l:'Mobile', v:val('mobile')},
        {l:'Father Name', v:val('father_name')},
        {l:'Mother Name', v:val('mother_name')}
    ]);

    html += section('Academic Info', [
        {l:'Class', v: form.querySelector('[name="class_id"] option:checked').text},
        {l:'Section', v:val('section')},
        {l:'Program Type', v: Array.from(form.querySelectorAll('[name="program_type[]"]:checked')).map(c => c.value).join(', ')},
        {l:'Shift', v:val('shift')},
        {l:'Last School', v:val('last_school')}
    ]);

    html += section('Address', [
        {l:'Present Address', v:val('address')},
        {l:'Permanent Address', v:val('permanent_address')}
    ]);

    html += section('Guardian Details', [
        {l:'Occupation', v:val('guardian_occupation')},
        {l:'Nationality', v:val('guardian_nationality')},
        {l:'Phone', v:val('guardian_phone')},
        {l:'Email', v:val('guardian_email')},
        {l:'NID', v:val('guardian_nid')}
    ]);
    
    collectAdmissionFees();
    const fees = JSON.parse(document.getElementById('selectedAdmissionFees').value || '[]');
    let feeItems = fees.map(f => {
        return {l: f.name, v: '৳ ' + parseFloat(f.amount).toFixed(2)};
    });
    
    // Add info about UNCHECKED fees? No, preview usually shows what you are paying.
    
    const total = document.getElementById('total_admission_fee').value;
    feeItems.push({l:'TOTAL PAYABLE', v: '<strong style="color:var(--accent)">৳ '+parseFloat(total).toFixed(2)+'</strong>'});
    
    html += section('Fees Breakdown', feeItems);

    html += section('Payment Info', [
        {l:'Payment Mode', v:val('payment_mode')},
        {l:'Note', v:val('payment_note')}
    ]);
    
    reviewContent.innerHTML = html;
    document.getElementById('reviewModal').classList.add('active');
}

function closeModal() {
    document.getElementById('reviewModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function editForm() {
    closeModal();
}

// Refresh CSRF token periodically to prevent expiration
function refreshCsrfToken() {
    fetch('/students/create', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract CSRF token from response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;
        
        if (newToken) {
            // Update meta tag
            document.querySelector('meta[name="csrf-token"]').content = newToken;
            // Update form token
            const formToken = document.querySelector('input[name="_token"]');
            if (formToken) {
                formToken.value = newToken;
            }
            console.log('CSRF token refreshed successfully');
        }
    })
    .catch(error => {
        console.error('Failed to refresh CSRF token:', error);
    });
}

// Refresh token every 5 minutes (300000 ms)
setInterval(refreshCsrfToken, 300000);

// Also refresh when user interacts with the form after being idle
let lastInteraction = Date.now();
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#studentForm');
    if (form) {
        form.addEventListener('input', function() {
            const now = Date.now();
            // If more than 10 minutes since last interaction, refresh token
            if (now - lastInteraction > 600000) {
                refreshCsrfToken();
            }
            lastInteraction = now;
        });
    }
});

// Prevent duplicate submissions
let isSubmitting = false;

function confirmAndSubmit() {
    // Prevent duplicate submissions
    if (isSubmitting) {
        console.log('Form is already being submitted, ignoring duplicate request');
        return;
    }
    
    // Collect admission fees BEFORE closing modal
    collectAdmissionFees();
    
    closeModal();
    
    // Get the form element
    const form = document.querySelector('#studentForm');
    
    if (!form) {
        console.error('Form not found!');
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Form not found. Please refresh the page and try again.',
            confirmButtonColor: '#e37814'
        });
        return;
    }
    
    // Check CSRF token
    const csrfToken = form.querySelector('input[name="_token"]');
    if (!csrfToken || !csrfToken.value) {
        console.error('CSRF token missing!');
        Swal.fire({
            icon: 'error',
            title: 'Session Expired',
            text: 'Your session has expired. Please refresh the page and log in again.',
            confirmButtonColor: '#e37814',
            confirmButtonText: 'Refresh Page'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
        return;
    }
    
    // Trigger the actual submission flow via the submit button
    // This ensures the 'submit' event listener is fired, which handles:
    // 1. Duplicate prevention
    // 2. Fee collection
    // 3. UI feedback
    const submitBtn = document.getElementById('savePayBtn');
    if (submitBtn) {
        console.log('Triggering submit button click...');
        submitBtn.click();
    } else {
        // Fallback (should not happen)
        console.error('Submit button not found, falling back to unsafe submit');
        form.submit();
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    // Unified Form Submit Handler
    const form = document.getElementById('studentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // 1. Check if already submitting (Prevent Duplicate)
            if (isSubmitting) {
                console.log('Preventing duplicate submission');
                e.preventDefault();
                return false;
            }
            
            // 2. Set Flag Immediately
            isSubmitting = true;
            
            // 3. Collect Data
            collectAdmissionFees();
            
            // 4. UI Feedback
            // Disable button
            const submitBtn = document.getElementById('savePayBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
            
            // Show Loading Spinner
            Swal.fire({
                title: 'Submitting...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            console.log('Form submission pipeline active. Sending request...');
            // Allow default submission to proceed
            return true;
        });
    }
});

</script>

<!-- SweetAlert for success messages -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#e37814',
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

</body>
</html>