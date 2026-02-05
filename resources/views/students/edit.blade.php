@extends('layouts.app')

@section('title', 'Edit Student - Madrasa ERP')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <h2 style="margin:0">Edit Student</h2>
</div>

<form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="form-row">
    <div class="form-group">
      <label>Student ID</label>
      <input type="text" name="student_id" value="{{ $student->student_id }}" readonly style="background:#0f1416;cursor:not-allowed">
    </div>
    <div class="form-group">
      <label>Student Name *</label>
      <input type="text" name="name" placeholder="Student Name" value="{{ old('name', $student->name) }}" required>
      @error('name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Father Name *</label>
      <input type="text" name="father_name" placeholder="Father's Name" value="{{ old('father_name', $student->father_name) }}" required>
      @error('father_name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Mother Name</label>
      <input type="text" name="mother_name" placeholder="Mother's Name" value="{{ old('mother_name', $student->mother_name) }}">
      @error('mother_name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Address</label>
    <input type="text" name="address" placeholder="Full Address" value="{{ old('address', $student->address) }}" style="width:100%">
    @error('address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Father/Main Mobile *</label>
      <input type="text" name="father_mobile" placeholder="01XXXXXXXXX" value="{{ old('father_mobile', $student->father_mobile) }}" required>
      @error('father_mobile')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Mother/Alt Mobile</label>
      <input type="text" name="mother_mobile" placeholder="01XXXXXXXXX" value="{{ old('mother_mobile', $student->mother_mobile) }}">
      @error('mother_mobile')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>NID Upload (Father/Mother)</label>
    <input type="file" name="nid_file" accept=".jpg,.jpeg,.png,.pdf" style="width:100%">
    @if($student->nid_file_path)
      <small style="color:var(--muted);margin-top:4px;display:block">Current file: {{ basename($student->nid_file_path) }}</small>
    @endif
    @error('nid_file')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">Student Details</h4>

  <div class="form-row">
    <div class="form-group">
      <label>Date of Birth</label>
      <input type="date" name="dob" value="{{ old('dob', $student->dob ? $student->dob->format('Y-m-d') : '') }}">
      @error('dob')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Gender</label>
      <select name="gender">
        <option value="">Select Gender</option>
        <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
        <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
        <option value="Other" {{ old('gender', $student->gender) == 'Other' ? 'selected' : '' }}>Other</option>
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
        <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
        <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
        <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
        <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
        <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
        <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
        <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
        <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
      </select>
      @error('blood_group')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Last School Attended</label>
      <input type="text" name="last_school" placeholder="Previous School Name" value="{{ old('last_school', $student->last_school) }}">
      @error('last_school')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Total Number of Children in Family</label>
      <input type="number" name="siblings_count" min="1" placeholder="e.g., 3" value="{{ old('siblings_count', $student->siblings_count) }}">
      @error('siblings_count')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Position Among Offspring</label>
      <input type="number" name="birth_order" min="1" placeholder="e.g., 2 (if 2nd child)" value="{{ old('birth_order', $student->birth_order) }}">
      @error('birth_order')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">Address Information</h4>

  <div class="form-group">
    <label>Present Address</label>
    <textarea name="address" rows="2" placeholder="Current residential address">{{ old('address', $student->address) }}</textarea>
    @error('address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label>Present District</label>
    <input type="text" name="present_district" placeholder="e.g., Dhaka" value="{{ old('present_district', $student->present_district) }}">
    @error('present_district')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label>Permanent Address</label>
    <textarea name="permanent_address" rows="2" placeholder="Permanent residential address">{{ old('permanent_address', $student->permanent_address) }}</textarea>
    @error('permanent_address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label>Permanent District</label>
    <input type="text" name="permanent_district" placeholder="e.g., Chittagong" value="{{ old('permanent_district', $student->permanent_district) }}">
    @error('permanent_district')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">Guardian Details</h4>

  <div class="form-row">
    <div class="form-group">
      <label>Guardian Occupation</label>
      <input type="text" name="guardian_occupation" placeholder="e.g., Teacher, Farmer" value="{{ old('guardian_occupation', $student->guardian_occupation) }}">
      @error('guardian_occupation')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Guardian Nationality</label>
      <input type="text" name="guardian_nationality" placeholder="e.g., Bangladeshi" value="{{ old('guardian_nationality', $student->guardian_nationality) }}">
      @error('guardian_nationality')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Guardian Phone</label>
      <input type="text" name="guardian_phone" placeholder="01XXXXXXXXX" value="{{ old('guardian_phone', $student->guardian_phone) }}">
      @error('guardian_phone')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Guardian Email</label>
      <input type="email" name="guardian_email" placeholder="guardian@example.com" value="{{ old('guardian_email', $student->guardian_email) }}">
      @error('guardian_email')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Guardian NID Number</label>
    <input type="text" name="guardian_nid" placeholder="National ID Number" value="{{ old('guardian_nid', $student->guardian_nid) }}">
    @error('guardian_nid')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">Class & Section</h4>

    <div class="form-row">
    <div class="form-group">
      <label>Select Class *</label>
      <select name="class_id" id="class_id" required onchange="updateSections()">
        <option value="">Select Class</option>
        @foreach($classrooms as $classroom)
          <option value="{{ $classroom->id }}" {{ old('class_id', $student->class_id) == $classroom->id ? 'selected' : '' }}>
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

  <h4 style="margin-top:28px;margin-bottom:12px;color:var(--text);border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">Fee Management</h4>

  <div class="row" style="display:flex;gap:20px;margin-top:24px;">
    <!-- Available Fees Column -->
    <div style="flex:1;">
      <h4 style="margin-top:0;margin-bottom:12px;color:var(--text);text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;">Available Fees</h4>
      <div id="availableFeesContainer" style="background:rgba(255,255,255,0.04);padding:10px;border-radius:6px;min-height:150px;max-height:400px;overflow-y:auto;">
        <p style="color:var(--muted);text-align:center;margin-top:20px;"><em>Select a class first</em></p>
      </div>
    </div>

    <!-- Student Fees Column -->
    <div style="flex:1.5;">
      <h4 style="margin-top:0;margin-bottom:12px;color:var(--accent);text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px;">Student's Fees</h4>
      <div style="background:rgba(255,255,255,0.04);padding:10px;border-radius:6px;min-height:150px;">
        <!-- Header -->
        <div style="display:grid;grid-template-columns:2fr 1.5fr 1fr 1fr 30px;gap:8px;padding:0 8px 8px;border-bottom:1px solid rgba(255,255,255,0.05);color:var(--muted);font-size:11px;text-transform:uppercase;">
          <div>Fee Name</div>
          <div>Type</div>
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
      </div>
    </div>
  </div>

  <input type="hidden" name="student_assigned_fees" id="studentAssignedFees" value="">

  <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px">
    <a href="{{ route('students.index') }}" class="btn ghost">Cancel</a>
    <button type="submit" class="btn">Update Student</button>
  </div>
</form>
@endsection

@section('extra-styles')
<style>
label{display:block;margin-bottom:6px;margin-top:8px;font-size:13px;color:var(--muted)}
input,select,textarea{
  background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);
  padding:8px 12px;border-radius:var(--radius);font-size:14px;width:100%
}
.form-row{display:flex;gap:12px;margin-top:10px}
.form-group{flex:1;display:flex;flex-direction:column}
.form-group input,.form-group select{width:100%}
.error{color:var(--danger);font-size:12px;margin-top:4px}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
</style>
@endsection

@section('scripts')
<script>
const classroomData = @json($classroomData);
const currentSection = "{{ old('section', $student->section) }}";
const studentData = {
    selectedFees: @json($student->selected_fees ?? []),
    discounts: @json($student->discounts ?? []),
    classId: "{{ $student->class_id }}"
};

let availableClassFees = [];
let feeRowCounter = 0;

function updateSections() {
    const classSelect = document.getElementById('class_id');
    const classId = classSelect.value;
    const sectionSelect = document.getElementById('section');

    // Reset section dropdown
    sectionSelect.innerHTML = '<option value="">Select Section</option>';

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
            if (section === currentSection && classId == studentData.classId) {
                option.selected = true;
            }
            sectionSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "A";
        option.textContent = "A";
        if ("A" === currentSection && classId == studentData.classId) {
            option.selected = true;
        }
        sectionSelect.appendChild(option);
    }

    // Load fees for the selected class
    loadClassFees(classId);
}

function loadClassFees(classId) {
    if (!classId || !classroomData[classId]) return;

    const data = classroomData[classId];
    availableClassFees = [];

    // Add admission fee
    const admissionFee = parseFloat(data.admission_fee) || 0;
    if (admissionFee > 0) {
        availableClassFees.push({
            name: "Admission Fee",
            type: "One Time",
            amount: admissionFee,
            is_admission: true
        });
    }

    // Add other fees
    const additionalFees = data.fees || [];
    additionalFees.forEach(f => {
        availableClassFees.push({
            name: f.name,
            type: f.type || "Monthly",
            amount: parseFloat(f.amount)
        });
    });

    // Clear containers
    // Clear containers
    const studentContainer = document.getElementById('studentFeesContainer');
    studentContainer.innerHTML = '';
    const emptyMsg = document.getElementById('emptyStudentFeesMsg');
    if (emptyMsg) emptyMsg.style.display = 'none';

    // Render available fees first
    renderAvailableFees();

    // Auto-add non-monthly fees and restore student's selected fees
    if (classId == studentData.classId) {
        // Restore existing fees
        restoreStudentFees();
    } else {
        // New class - auto-add non-monthly fees
        availableClassFees.forEach((fee, index) => {
            if (!['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type)) {
                addFeeToStudent(index);
            }
        });
    }
}

function restoreStudentFees() {
    const selectedFees = studentData.selectedFees;
    const discounts = studentData.discounts;

    // If no selected fees, add all fees
    if (!selectedFees || selectedFees.length === 0) {
        availableClassFees.forEach((fee, index) => {
            addFeeToStudent(index);
            const rowId = `fee_row_${feeRowCounter - 1}`;
            const row = document.getElementById(rowId);
            if (row && discounts[fee.name]) {
                const discInput = row.querySelector('.fee-discount-input');
                const permCheck = row.querySelector('.perm-check');
                if (discInput) discInput.value = discounts[fee.name].amount || 0;
                if (permCheck) permCheck.checked = discounts[fee.name].permanent || false;
                updateFeeRow(rowId);
            }
        });
    } else {
        // Add only selected fees
        selectedFees.forEach(sf => {
            const index = availableClassFees.findIndex(f => f.name === sf.name);
            if (index !== -1) {
                addFeeToStudent(index);
                const rowId = `fee_row_${feeRowCounter - 1}`;
                const row = document.getElementById(rowId);
                if (row && discounts[sf.name]) {
                    const discInput = row.querySelector('.fee-discount-input');
                    const permCheck = row.querySelector('.perm-check');
                    if (discInput) discInput.value = discounts[sf.name].amount || 0;
                    if (permCheck) permCheck.checked = discounts[sf.name].permanent || false;
                    updateFeeRow(rowId);
                }
            }
        });
    }
}

function renderAvailableFees() {
    const container = document.getElementById('availableFeesContainer');
    if (!container) return;
    container.innerHTML = '';

    if (availableClassFees.length === 0) {
        container.innerHTML = '<p style="color:var(--muted);text-align:center;">No fees available</p>';
        return;
    }

    availableClassFees.forEach((fee, index) => {
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

    // Check if already added
    const existing = Array.from(container.querySelectorAll('.student-fee-row')).find(r => r.dataset.name === fee.name);
    if (existing) {
        alert('Fee already added');
        return existing.id;
    }

    const rowId = 'fee_row_' + (feeRowCounter++);
    const isPeriodic = ['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type);

    const row = document.createElement('div');
    row.className = 'student-fee-row';
    row.id = rowId;
    row.dataset.baseAmount = fee.amount;
    row.dataset.originalIndex = feeIndex;
    row.dataset.type = fee.type;
    row.dataset.name = fee.name;
    row.dataset.isPeriodic = isPeriodic;
    row.style.cssText = 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);margin-bottom:8px;padding:8px;border-radius:4px;display:grid;grid-template-columns:2fr 1.5fr 1fr 1fr 30px;gap:8px;align-items:center;';

    row.innerHTML = `
        <div style="font-size:12px;font-weight:500;">${fee.name}</div>
        <div style="font-size:11px;color:var(--muted);">${fee.type}</div>
        <div style="display:flex;align-items:center;gap:4px;">
            <input type="number" class="fee-discount-input" placeholder="0" min="0" max="${fee.amount}"
                   style="width:60px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.1);color:var(--text);padding:4px;border-radius:3px;font-size:12px;"
                   oninput="updateFeeRow('${rowId}')">
            <label style="font-size:10px;color:var(--muted);display:flex;align-items:center;gap:2px;cursor:pointer;" title="Permanent Discount">
                <input type="checkbox" class="perm-check" onclick="updateFeeRow('${rowId}')"> P
            </label>
        </div>
        <div style="text-align:right;font-weight:600;font-size:12px;color:var(--accent);" class="fee-row-total">
            ৳ ${fee.amount.toFixed(2)}
        </div>
        <div style="text-align:right;">
            <button type="button" onclick="removeFeeRow('${rowId}')" style="background:none;border:none;color:#ff4444;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>
    `;

    container.appendChild(row);
    updateFeeRow(rowId);

    // Hide from available list
    const availDiv = document.getElementById(`avail_fee_div_${feeIndex}`);
    if (availDiv) availDiv.style.display = 'none';

    return rowId;
}

function removeFeeRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        const idx = row.dataset.originalIndex;
        if (idx !== undefined) {
            const availDiv = document.getElementById(`avail_fee_div_${idx}`);
            if (availDiv) availDiv.style.display = 'flex';
        }
        row.remove();
    }

    // Update total first
    updateTotal();

    // Then check if container is empty
    const c = document.getElementById('studentFeesContainer');
    const feeRows = c.querySelectorAll('.student-fee-row');
    if (feeRows.length === 0) {
        const emptyMsg = document.getElementById('emptyStudentFeesMsg');
        if (emptyMsg) emptyMsg.style.display = 'block';
    }
}

function updateFeeRow(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const base = parseFloat(row.dataset.baseAmount);
    const discInput = row.querySelector('.fee-discount-input');
    const discount = parseFloat(discInput.value) || 0;
    const total = Math.max(0, base - discount);

    const totalEl = row.querySelector('.fee-row-total');
    if (totalEl) totalEl.textContent = `৳ ${total.toFixed(2)}`;

    updateTotal();
}

function updateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('.student-fee-row');
    rows.forEach(row => {
        const base = parseFloat(row.dataset.baseAmount);
        const discInput = row.querySelector('.fee-discount-input');
        const discount = parseFloat(discInput.value) || 0;
        total += Math.max(0, base - discount);
    });

    const totalEl = document.getElementById('studentFeesTotal');
    if (totalEl) totalEl.textContent = `৳ ${total.toFixed(2)}`;

    // Update hidden field with fee data
    updateHiddenField();
}

function updateHiddenField() {
    const fees = [];
    const rows = document.querySelectorAll('.student-fee-row');
    rows.forEach(row => {
        const name = row.dataset.name;
        const type = row.dataset.type;
        const amount = parseFloat(row.dataset.baseAmount);
        const discInput = row.querySelector('.fee-discount-input');
        const permCheck = row.querySelector('.perm-check');
        const discount = parseFloat(discInput.value) || 0;
        const isPermanent = permCheck.checked;

        fees.push({
            name: name,
            type: type,
            amount: amount,
            discount: discount,
            is_permanent: isPermanent
        });
    });

    const hiddenField = document.getElementById('studentAssignedFees');
    if (hiddenField) hiddenField.value = JSON.stringify(fees);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('class_id').value) {
        updateSections();
        // If the class changed (not the original class), we might not want to select the old section,
        // but for simplicity, the logic above handles the original class case.
        // If it's a validation error redirect, we might want to re-select the old('section').
        const oldSection = "{{ old('section') }}";
        if (oldSection && oldSection !== "{{ $student->section }}") {
            setTimeout(() => {
                document.getElementById('section').value = oldSection;
            }, 100);
        }
    }
});
</script>
@endsection
