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
      <label>Mother Name *</label>
      <input type="text" name="mother_name" placeholder="Mother's Name" value="{{ old('mother_name', $student->mother_name) }}" required>
      @error('mother_name')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-group">
    <label>Address *</label>
    <input type="text" name="address" placeholder="Full Address" value="{{ old('address', $student->address) }}" style="width:100%" required>
    @error('address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Mobile *</label>
      <input type="text" name="mobile" placeholder="01XXXXXXXXX" value="{{ old('mobile', $student->mobile) }}" required>
      @error('mobile')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Alt Mobile</label>
      <input type="text" name="alt_mobile" placeholder="01XXXXXXXXX" value="{{ old('alt_mobile', $student->alt_mobile) }}">
      @error('alt_mobile')
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
      <label>Date of Birth *</label>
      <input type="date" name="dob" value="{{ old('dob', $student->dob ? $student->dob->format('Y-m-d') : '') }}" required>
      @error('dob')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Gender *</label>
      <select name="gender" required>
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
    <label>Present Address *</label>
    <textarea name="address" rows="2" placeholder="Current residential address" required>{{ old('address', $student->address) }}</textarea>
    @error('address')
      <div class="error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label>Present District *</label>
    <input type="text" name="present_district" placeholder="e.g., Dhaka" value="{{ old('present_district', $student->present_district) }}" required>
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
      <label>Guardian Occupation *</label>
      <input type="text" name="guardian_occupation" placeholder="e.g., Teacher, Farmer" value="{{ old('guardian_occupation', $student->guardian_occupation) }}" required>
      @error('guardian_occupation')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label>Guardian Nationality *</label>
      <input type="text" name="guardian_nationality" placeholder="e.g., Bangladeshi" value="{{ old('guardian_nationality', $student->guardian_nationality) }}" required>
      @error('guardian_nationality')
        <div class="error">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Guardian Phone *</label>
      <input type="text" name="guardian_phone" placeholder="01XXXXXXXXX" value="{{ old('guardian_phone', $student->guardian_phone) }}" required>
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
    <label>Guardian NID Number *</label>
    <input type="text" name="guardian_nid" placeholder="National ID Number" value="{{ old('guardian_nid', $student->guardian_nid) }}" required>
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
            if (section === currentSection && classId == "{{ $student->class_id }}") {
                option.selected = true;
            }
            sectionSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "A";
        option.textContent = "A";
        if ("A" === currentSection && classId == "{{ $student->class_id }}") {
            option.selected = true;
        }
        sectionSelect.appendChild(option);
    }
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