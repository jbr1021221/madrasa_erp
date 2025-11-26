@extends('layouts.app')

@section('content')
<style>
:root{
  --bg:#0b0d0f;
  --panel:#111316;
  --card:#0f1416;
  --text:#e6eef3;
  --muted:#98a0a6;
  --accent:#e37814;
  --danger:#ff4e4e;
  --radius:6px;
}

.modal-content-custom {
  background: var(--card);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: var(--radius);
  color: var(--text);
}

.form-label {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 4px;
}

.form-control, .form-select {
  background: #1b1f22;
  color: var(--text);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: var(--radius);
  font-size: 14px;
}

.form-control:focus, .form-select:focus {
  background: #1b1f22;
  color: var(--text);
  border-color: var(--accent);
  box-shadow: 0 0 0 0.2rem rgba(227,120,20,0.25);
}

.btn-primary {
  background: var(--accent);
  color: #041617;
  border: 0;
  font-weight: 600;
}

.btn-secondary {
  background: transparent;
  border: 1px solid var(--accent);
  color: var(--accent);
}

.fee-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.fee-table th {
  background: rgba(255,255,255,0.15);
  padding: 10px;
  font-size: 14px;
  text-align: left;
}

.fee-table td {
  padding: 10px;
  font-size: 14px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.fee-table tr:nth-child(odd) {
  background: rgba(255,255,255,0.04);
}

.total-fee-display {
  margin-top: 14px;
  font-size: 18px;
  font-weight: 700;
  text-align: right;
  color: var(--accent);
  padding: 12px;
  background: rgba(227,120,20,0.1);
  border-radius: var(--radius);
}
</style>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="modal-content-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1" style="color: var(--text);">Add New Student</h3>
                        <p class="mb-0" style="color: var(--muted); font-size: 13px;">Fill in student details and admission fees</p>
                    </div>
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">← Back to List</a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" id="studentForm">
                    @csrf

                    <!-- Student Information Section -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" value="{{ old('student_id', $student_id) }}" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="father_name" class="form-label">Father Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror" id="father_name" name="father_name" value="{{ old('father_name') }}" required>
                            @error('father_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mother_name" class="form-label">Mother Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mother_name') is-invalid @enderror" id="mother_name" name="mother_name" value="{{ old('mother_name') }}" required>
                            @error('mother_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" required>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" required>
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="alt_mobile" class="form-label">Alternative Mobile</label>
                            <input type="text" class="form-control @error('alt_mobile') is-invalid @enderror" id="alt_mobile" name="alt_mobile" value="{{ old('alt_mobile') }}">
                            @error('alt_mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nid_file" class="form-label">NID Upload (Father/Mother)</label>
                        <input type="file" class="form-control @error('nid_file') is-invalid @enderror" id="nid_file" name="nid_file" accept=".jpg,.jpeg,.png,.pdf">
                        @error('nid_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="class_id" class="form-label">Select Class <span class="text-danger">*</span></label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                <option value="">Select</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" 
                                            data-class-name="{{ $classroom->name }}"
                                            data-total-fee="{{ $classroom->total_fee }}"
                                            data-fees='@json($classroom->fees)'
                                            {{ old('class_id') == $classroom->id ? 'selected' : '' }}>
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="section" class="form-label">Select Section <span class="text-danger">*</span></label>
                            <select class="form-select @error('section') is-invalid @enderror" id="section" name="section" required>
                                <option value="">Select</option>
                                @foreach($sections as $sect)
                                    <option value="{{ $sect }}" {{ old('section') == $sect ? 'selected' : '' }}>{{ $sect }}</option>
                                @endforeach
                            </select>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Admission Fees Section -->
                    <h4 class="mt-4 mb-3" style="color: var(--text);">Admission Fees</h4>
                    <div id="admissionFeesDisplay">
                        <p style="color: var(--muted);"><em>Please select a class to view fees</em></p>
                    </div>

                    <input type="hidden" id="total_admission_fee" name="total_admission_fee" value="0">

                    <!-- Payment Information -->
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode" required>
                                <option value="">Select</option>
                                <option value="Cash" {{ old('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bkash" {{ old('payment_mode') == 'Bkash' ? 'selected' : '' }}>Bkash</option>
                                <option value="Bank" {{ old('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                <option value="Rocket" {{ old('payment_mode') == 'Rocket' ? 'selected' : '' }}>Rocket</option>
                                <option value="Upay" {{ old('payment_mode') == 'Upay' ? 'selected' : '' }}>Upay</option>
                                <option value="Others" {{ old('payment_mode') == 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                            @error('payment_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_note" class="form-label">Note (Optional)</label>
                            <input type="text" class="form-control @error('payment_note') is-invalid @enderror" id="payment_note" name="payment_note" value="{{ old('payment_note') }}">
                            @error('payment_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location='{{ route('students.index') }}'">Cancel</button>
                        <button type="submit" class="btn btn-primary">Pay & Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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

// Update class info and regenerate ID
function updateClassInfo() {
    const classSelect = document.getElementById('class_id');
    const selectedOption = classSelect.options[classSelect.selectedIndex];
    const feesDisplay = document.getElementById('admissionFeesDisplay');
    const totalFeeInput = document.getElementById('total_admission_fee');
    
    if (!selectedOption.value) {
        feesDisplay.innerHTML = '<p style="color: var(--muted);"><em>Please select a class to view fees</em></p>';
        totalFeeInput.value = 0;
        return;
    }
    
    // Regenerate Student ID based on selected class
    generateStudentId(selectedOption.value);
    
    const totalFee = parseFloat(selectedOption.dataset.totalFee) || 0;
    const fees = JSON.parse(selectedOption.dataset.fees || '[]');
    
    totalFeeInput.value = totalFee;
    
    let html = '<table class="fee-table">';
    html += '<thead><tr><th>Fee Name</th><th>Type</th><th style="text-align:right;">Amount</th></tr></thead>';
    html += '<tbody>';
    
    if (fees && fees.length > 0) {
        fees.forEach(fee => {
            html += `<tr>
                <td>${fee.name}</td>
                <td><small style="color: var(--muted);">${fee.type}</small></td>
                <td style="text-align:right;">৳ ${parseFloat(fee.amount).toFixed(2)}</td>
            </tr>`;
        });
    } else {
        html += '<tr><td colspan="3" style="text-align:center; color: var(--muted);">No fees available</td></tr>';
    }
    
    html += '</tbody></table>';
    html += `<div class="total-fee-display">Admission Fee Total: ৳ ${totalFee.toFixed(2)}</div>`;
    
    feesDisplay.innerHTML = html;
}

// Event listener for class selection
document.getElementById('class_id').addEventListener('change', updateClassInfo);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    if (classSelect.value) {
        updateClassInfo();
    }
});
</script>

<style>
.gap-2 {
    gap: 0.5rem;
}
</style>
@endsection