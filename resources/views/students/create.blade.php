<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Add Student – Madrasa ERP</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

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
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  @include('components.sidebar')

  <!-- MAIN CONTENT -->
  <main class="panel">

    <h2 style="margin:0 0 20px 0">Add New Student</h2>

    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="form-row">
        <div class="form-group">
          <label>Student ID</label>
          <input type="text" name="student_id" id="student_id" value="{{ $student_id }}" readonly style="background:#0f1416;cursor:not-allowed">
        </div>
        <div class="form-group">
          <label>Student Name *</label>
          <input type="text" name="name" placeholder="Student Name" value="{{ old('name') }}" required>
          @error('name')
            <div class="error">{{ $message }}</div>
          @enderror
        </div>
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

      <h4 style="margin-top:24px;margin-bottom:12px;color:var(--text);text-align:center">Admission Fees</h4>
      <div style="background:rgba(255,255,255,0.04);padding:16px;border-radius:6px">
        <div id="admissionFeesDisplay">
          <p style="color: var(--muted);text-align:center;margin:0"><em>Please select a class to view fees</em></p>
        </div>
      </div>
      <input type="hidden" id="total_admission_fee" name="total_admission_fee" value="0">

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

      <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('students.index') }}" class="btn ghost">Cancel</a>
        <button type="submit" class="btn">Pay & Save</button>
      </div>

    </form>

  </main>
</div>

<script>
// Classroom data from controller
const classroomData = @json($classroomData);

// Generate Student ID dynamically
function generateStudentId(classId) {
    if (!classId) return;
    
    fetch(`/students/generate-id/${classId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('student_id').value = data.student_id;
        })
        .catch(error => {
            console.error('Error generating student ID:', error);
        });
}

// Update class info (sections, fees) and regenerate ID
function updateClassInfo() {
    const classSelect = document.getElementById('class_id');
    const classId = classSelect.value;
    const sectionSelect = document.getElementById('section');
    const feesDisplay = document.getElementById('admissionFeesDisplay');
    const totalFeeInput = document.getElementById('total_admission_fee');
    
    // Reset section dropdown
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId || !classroomData[classId]) {
        sectionSelect.innerHTML = '<option value="">Select Class First</option>';
        feesDisplay.innerHTML = '<p style="color: var(--muted);text-align:center;margin:0"><em>Please select a class to view fees</em></p>';
        totalFeeInput.value = 0;
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
        option.value = "A"; // Default if no sections defined
        option.textContent = "A";
        sectionSelect.appendChild(option);
    }
    
    // Regenerate Student ID based on selected class
    generateStudentId(classId);
    
    // Display Fees
    const admissionFee = parseFloat(data.admission_fee) || 0;
    const fees = data.fees || [];
    
    // Calculate total dynamically
    let calculatedTotal = admissionFee;
    if (fees && fees.length > 0) {
        fees.forEach(fee => {
            calculatedTotal += parseFloat(fee.amount) || 0;
        });
    }
    
    totalFeeInput.value = calculatedTotal;
    
    let html = '<table class="fee-table">';
    html += '<thead><tr><th>Fee Name</th><th>Type</th><th style="text-align:right;">Amount</th></tr></thead>';
    html += '<tbody>';
    
    // Static Admission Fee
    html += `<tr>
        <td>Admission Fee</td>
        <td><small style="color: var(--muted);">One Time</small></td>
        <td style="text-align:right;">৳ ${admissionFee.toFixed(2)}</td>
    </tr>`;
    
    if (fees && fees.length > 0) {
        fees.forEach(fee => {
            html += `<tr>
                <td>${fee.name}</td>
                <td><small style="color: var(--muted);">${fee.type}</small></td>
                <td style="text-align:right;">৳ ${parseFloat(fee.amount).toFixed(2)}</td>
            </tr>`;
        });
    }
    
    html += '</tbody></table>';
    html += `<div class="total-fee-display">Total: ৳ ${calculatedTotal.toFixed(2)}</div>`;
    
    // Add discount input field
      html += `<div style="margin-top: 15px; display: flex; align-items: center; justify-content: end;">
        <label for="discount_amount" style="font-weight: 500; color: #fff; padding-right: 10px; padding-bottom: 10px; text-align: left;">Discount Amount:</label>
        <input type="number" 
               id="discount_amount" 
               name="discount_amount" 
               min="0" 
               max="${calculatedTotal}" 
               step="0.01" 
               value="" 
               placeholder="Enter discount amount"
               style="width: 200px; height: 35px; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; text-align: right;"
               oninput="calculateFinalAmount(${calculatedTotal})">
    </div>`;
    
    // Add final amount display (white background)
    html += `<div id="final_amount_display" class="total-fee-display" style="background-color:#000; color: #df8705ff; border: 2px solid #28a745; margin-top: 10px;">
        Final Amount: ৳ ${calculatedTotal.toFixed(2)}
    </div>`;
    
    feesDisplay.innerHTML = html;
}

// New function to calculate final amount after discount
function calculateFinalAmount(totalAmount) {
    const discountInput = document.getElementById('discount_amount');
    const finalAmountDisplay = document.getElementById('final_amount_display');
    const totalFeeInput = document.getElementById('total_admission_fee');
    
    let discount = parseFloat(discountInput.value) || 0;
    
    // Ensure discount doesn't exceed total
    if (discount > totalAmount) {
        discount = totalAmount;
        discountInput.value = totalAmount;
    }
    
    if (discount < 0) {
        discount = 0;
        discountInput.value = 0;
    }
    
    const finalAmount = totalAmount - discount;
    
    // Update display
    finalAmountDisplay.innerHTML = `Final Amount: ৳ ${finalAmount.toFixed(2)}`;
    
    // Update hidden input with final amount (if you want to submit final amount instead of total)
    totalFeeInput.value = finalAmount;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    if (classSelect.value) {
        updateClassInfo();
        // Pre-select section if old value exists
        const oldSection = "{{ old('section') }}";
        if (oldSection) {
            setTimeout(() => {
                document.getElementById('section').value = oldSection;
            }, 100);
        }
    }
});

// Image Resizing (600x600)
document.querySelector('input[name="photo"]').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = 600;
                canvas.height = 600;
                const ctx = canvas.getContext('2d');
                
                // Draw and resize to 600x600
                ctx.drawImage(img, 0, 0, 600, 600);
                
                canvas.toBlob(function(blob) {
                    const newFile = new File([blob], file.name, { type: 'image/jpeg', lastModified: Date.now() });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(newFile);
                    e.target.files = dataTransfer.files;
                }, 'image/jpeg', 0.9);
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>