@extends('layouts.app')

@section('title', 'Student Details - Madrasa ERP')

@section('content')
@php
    $className = $student->classroom->name ?? 'N/A';
    $allClassFees = $student->classroom->fees ?? [];
    $recurringFees = $student->selected_fees ?? ($student->fees ?? []);
    
    // Calculate Paid Fees Tracker for this student
    $paidFeeTracker = [];
    foreach($student->payments as $payment) {
        if($payment->fee_details && is_array($payment->fee_details)) {
             foreach($payment->fee_details as $feeDetail) {
                 $fType = strtolower($feeDetail['type'] ?? '');
                 if($fType === 'monthly') {
                     $monthName = $feeDetail['month'] ?? '';
                     $year = $feeDetail['year'] ?? date('y');
                     $monthKey = (strpos($monthName, ', ') !== false) ? $monthName : ($monthName . ', ' . $year);
                     
                     if(!isset($paidFeeTracker[$monthKey])) $paidFeeTracker[$monthKey] = [];
                     
                     $name = $feeDetail['name'];
                     if(strpos($name, ' - ') !== false) {
                         $parts = explode(' - ', $name);
                         $name = trim($parts[0]);
                     }
                     $paidFeeTracker[$monthKey][] = $name;
                 } else {
                     $rawName = $feeDetail['name'] ?? '';
                     if (strpos($rawName, ' - ') !== false) {
                        $separatorPos = strpos($rawName, ' - ');
                        $baseName = trim(substr($rawName, 0, $separatorPos));
                        $partsStr = substr($rawName, $separatorPos + 3);
                        $parts = explode(',', $partsStr);
                        foreach($parts as $p) {
                            $p = trim($p);
                            if(!empty($p)) $paidFeeTracker[$baseName . ' - ' . $p] = true;
                        }
                     } else {
                        $names = explode(',', $rawName);
                        foreach($names as $n) $paidFeeTracker[trim($n)] = true;
                     }
                 }
             }
        } elseif ($payment->month && strtolower($payment->payment_type) === 'monthly') {
             preg_match_all('/([A-Za-z]+,\s*\d{2})/', $payment->month, $matches);
             if(!empty($matches[0])) {
                 foreach($matches[0] as $m) {
                     if(!isset($paidFeeTracker[$m])) $paidFeeTracker[$m] = ['__ALL__'];
                 }
             }
        }
    }
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <div>
    <h2 style="margin:0">{{ $student->name }}</h2>
    <p style="margin:4px 0 0 0;color:var(--muted);font-size:14px">Student ID: {{ $student->student_id }}</p>
  </div>
  <div>
     <button onclick='openPayModal({{ $student->id }}, "{{ $student->name }} ({{ $className }})", "{{ $student->father_name }}", @json($recurringFees), @json($student->discounts ?? []), @json($paidFeeTracker), @json($allClassFees ?? []), @json($student->partial_payments ?? []))' class="btn" style="margin-right:8px;">Make Payment</button>
    <a href="{{ route('students.edit', $student) }}" class="btn" style="margin-right:8px">Edit Student</a>
    <a href="{{ route('students.index') }}" class="btn ghost">Back to List</a>
  </div>
</div>

<div style="background:var(--card);border-radius:var(--radius);padding:24px;border:1px solid rgba(255,255,255,0.1);margin-bottom:30px">
  <h3 style="margin:0 0 20px 0;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.1)">Student Information</h3>
  
  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Basic Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Student ID</div>
      <div class="detail-value">{{ $student->student_id }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Student Name</div>
      <div class="detail-value">{{ $student->name }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Father Name</div>
      <div class="detail-value">{{ $student->father_name }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Mother Name</div>
      <div class="detail-value">{{ $student->mother_name ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Mobile</div>
      <div class="detail-value">{{ $student->mobile }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Alt Mobile</div>
      <div class="detail-value">{{ $student->alt_mobile ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Student Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Date of Birth</div>
      <div class="detail-value">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Gender</div>
      <div class="detail-value">{{ $student->gender ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Blood Group</div>
      <div class="detail-value">{{ $student->blood_group ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Last School Attended</div>
      <div class="detail-value">{{ $student->last_school ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Total Children in Family</div>
      <div class="detail-value">{{ $student->siblings_count ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Position Among Offspring</div>
      <div class="detail-value">{{ $student->birth_order ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Program Type</div>
      <div class="detail-value">{{ $student->program_type ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Shift</div>
      <div class="detail-value">{{ $student->shift ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Address Information</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Present Address</div>
      <div class="detail-value">{{ $student->address ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Permanent Address</div>
      <div class="detail-value">{{ $student->permanent_address ?? 'N/A' }}</div>
    </div>
  </div>

  <h4 style="margin:20px 0 12px 0;font-size:14px;color:var(--accent);text-transform:uppercase;letter-spacing:1px">Guardian Details</h4>
  <div class="detail-list">
    <div class="detail-row">
      <div class="detail-label">Occupation</div>
      <div class="detail-value">{{ $student->guardian_occupation ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Nationality</div>
      <div class="detail-value">{{ $student->nationality ?? $student->guardian_nationality ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Guardian Phone</div>
      <div class="detail-value">{{ $student->guardian_mobile ?? $student->guardian_phone ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">Guardian Email</div>
      <div class="detail-value">{{ $student->guardian_email ?? 'N/A' }}</div>
    </div>
    <div class="detail-row">
      <div class="detail-label">NID Number</div>
      <div class="detail-value">{{ $student->guardian_nid ?? 'N/A' }}</div>
    </div>
  </div>
</div>

<div style="background:var(--card);border-radius:var(--radius);padding:24px;border:1px solid rgba(255,255,255,0.1);margin-bottom:30px">
  <h3 style="margin:0 0 20px 0;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.1)">Payment History</h3>
<table id="paymentTable">
  <thead>
    <tr>
      <th>SL</th>
      <th>Payment Date</th>
      <th>Type</th>
      <th>Period</th>
      <th>Amount</th>
      <th>Mode</th>
      <th>Note</th>
      <th>Receipt</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($student->payments as $payment)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $payment->payment_date->format('d M, Y') }}</td>
      <td>{{ $payment->payment_type }}</td>
      <td>{{ $payment->month }}</td>
      <td>৳ {{ number_format($payment->amount, 2) }}</td>
      <td>{{ ucfirst($payment->payment_mode) }}</td>
      <td>{{ $payment->note ?? '-' }}</td>
      <td>
        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="action-btn">
           View
        </a>
      </td>
      <td style="display:flex;gap:6px;justify-content:center">
        <button type="button" class="action-btn edit" onclick='openEditPaymentModal(@json($payment))' title="Edit Payment">Edit</button>
        <form action="{{ route('payments.destroy', $payment) }}" method="POST" style="display:inline-block;margin:0" class="delete-payment-form">
            @csrf
            @method('DELETE')
            <button type="button" class="action-btn delete" title="Delete Payment" onclick="confirmDelete(this)">
                Delete
            </button>
        </form>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="9" style="color:var(--muted);padding:20px">No payments recorded</td>
    </tr>
    @endforelse
  </tbody>
  </table>
</div>

<!-- PAYMENT MODAL PARTIAL -->
@include('students.partials.payment-modal')
@endsection

@section('extra-styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
.btn{padding:8px 16px;border-radius:var(--radius);border:none;cursor:pointer;font-size:14px;background:var(--accent);color:#000;text-decoration:none;display:inline-block}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}
.btn:hover{opacity:0.9}

/* Detail List Styles */
.detail-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:12px}
.detail-row{display:flex;flex-direction:column;padding:12px;background:rgba(255,255,255,0.03);border-radius:4px;border:1px solid rgba(255,255,255,0.05);transition:background 0.2s}
.detail-row:hover{background:rgba(255,255,255,0.05)}
.detail-label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:500;margin-bottom:6px}
.detail-value{font-size:14px;font-weight:500;color:var(--text)}

/* Table Styles */
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:rgba(255,255,255,0.15);padding:10px;font-size:14px;text-align:center}
td{padding:10px;font-size:14px;text-align:center}
#paymentTable tbody tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#paymentTable tbody tr:nth-child(even){background:rgba(255,255,255,0.09)}
#paymentTable tbody tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}

/* DataTables Customization */
.dataTables_wrapper {margin-top: 20px; color: var(--text); font-size: 14px;}
.dataTables_wrapper .top {margin-bottom: 16px;}
.dataTables_wrapper .bottom {display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 16px; flex-wrap: wrap;}
.dataTables_length {text-align: center;}
.dataTables_length select {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 4px; border-radius: 4px;}
.dataTables_filter input {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 6px; border-radius: 4px; margin-left: 8px;}
.dataTables_info {color: var(--muted) !important;}
.dataTables_paginate {display: flex; gap: 4px;}
.dataTables_paginate .paginate_button {color: var(--text) !important; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; margin: 0 2px; cursor: pointer;}
.dataTables_paginate .paginate_button.current {background: var(--accent); color: #000 !important; border-color: var(--accent);}
.dataTables_paginate .paginate_button:hover {background: rgba(255,255,255,0.1); color: var(--text) !important; border-color: rgba(255,255,255,0.2);}
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {color: var(--muted);}
table.dataTable tbody tr {background-color: transparent;}
</style>
@endsection

@section('scripts')
<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
// Global Student Data for Payment Modal
const studentData = {
    id: {{ $student->id }},
    name: '{{ $student->name }} ({{ $className }})',
    fatherName: '{{ $student->father_name }}',
    fees: @json($recurringFees),
    discounts: @json($student->discounts ?? []),
    paidTracker: @json($paidFeeTracker),
    classFees: @json($allClassFees ?? []),
    partialPayments: @json($student->partial_payments ?? [])
};

$(document).ready(function() {
    $('#paymentTable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[7, "desc"]], 
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search payments...",
            "paginate": { "previous": "Prev", "next": "Next" }
        }
    });
});

function openEditPaymentModal(payment) {
    // Set Redirect to current student page
    const redirectInput = document.querySelector('input[name="redirect_to"]');
    if(redirectInput) redirectInput.value = 'students.show';

    // 0. Parse Details
    let details = payment.fee_details;
    if (typeof details === 'string') {
        try { details = JSON.parse(details); } catch(e) { details = []; }
    }
    if (details && !Array.isArray(details) && details.fee_details) details = details.fee_details;
    details = details || [];

    // 1. Clean Paid Tracker (Unlock edited fees so they are selectable)
    const cleanTracker = JSON.parse(JSON.stringify(studentData.paidTracker));
    
    details.forEach(d => {
         // Non-Monthly Keys check
         if (cleanTracker[d.name]) delete cleanTracker[d.name];
         
         // Monthly Keys check
         Object.keys(cleanTracker).forEach(key => {
             if (Array.isArray(cleanTracker[key])) { 
                 let matchesMonth = false;
                 if(d.month && key.includes(d.month)) matchesMonth = true;
                 else if(d.name.includes(key) || d.name.includes(key.split(',')[0])) matchesMonth = true; // Heuristic
                 
                 if(matchesMonth) {
                     const cleanName = d.name.split(' - ')[0]; // Extract base name "Tuition"
                     cleanTracker[key] = cleanTracker[key].filter(f => f !== cleanName && f !== '__ALL__');
                 }
             }
         });
    });

    // 2. Initialize Base State with Clean Tracker
    // Edit Mode: Need to extract the SPECIFIC discounts saved in this transaction,
    // otherwise the modal defaults to the student's current global discounts.
    const editDiscounts = {};
    let totalDetailsAmount = 0;
    
    details.forEach(d => {
        // Map discount to fee (base name logic handled inside modal usually, but here we prep specific keys)
        // If the fee has a discount value > 0, we record it.
        // For monthly fees like "Tuition Fee - January", the modal expects "Tuition Fee".
        // If different months have different discounts, the modal UI only supports one per row (base name).
        // We will take the first non-zero discount we find for a base name.
        
        // Extract base name logic (similar to cleanTracker above)
        let baseName = d.name;
        if(d.month && d.name.includes(d.month)) {
             // likely "Tuition Fee - January 2026" or similar
             // We can try splitting by " - "
             const parts = d.name.split(' - ');
             if(parts.length > 1) {
                 // Check if suffix matches month
                 const suffix = parts[parts.length-1];
                 if(suffix.includes(',') || d.month.includes(suffix)) {
                     parts.pop();
                     baseName = parts.join(' - ');
                 }
             }
        } else if (d.name.includes(' - ')) {
              // Check for quarter/half parts
              const parts = d.name.split(' - ');
              if(parts.length > 1) {
                  const suffix = parts[parts.length-1];
                  if(['1st Quater', '2nd Quater', '3rd Quater', '4th Quater', '1st Half', '2nd Half'].some(s => suffix.includes(s))) {
                       parts.pop();
                       baseName = parts.join(' - ');
                  }
              }
        }
        
        if (d.discount > 0) {
            if (!editDiscounts[baseName]) {
                 editDiscounts[baseName] = parseFloat(d.discount);
            }
            // Also store exact name just in case
            editDiscounts[d.name] = parseFloat(d.discount);
        }
        
        // Sum up net amount for manual discount calc
        const amt = parseFloat(d.amount) || 0;
        totalDetailsAmount += amt;
    });
    
    // Merge defaults? No, if we are editing, we usually want exactly what was saved.
    // However, if the user adds a NEW fee during edit, they might expect the default discount.
    // Let's merge: editDiscounts takes precedence.
    const mergedDiscounts = { ...studentData.discounts, ...editDiscounts };

    openPayModal(
        studentData.id, 
        studentData.name, 
        studentData.fatherName, 
        studentData.fees, 
        mergedDiscounts, // <--- Pass Merged Discounts
        cleanTracker, 
        studentData.classFees, 
        studentData.partialPayments
    );
    
    // Check for Manual Global Discount (Payment Amount < Sum of Details)
    const storedTotal = parseFloat(payment.amount) || 0;
    // Note: totalDetailsAmount is sum of fee item Net Amounts.
    // If storedTotal < totalDetailsAmount, difference is Manual Discount.
    
    // Slight tolerance for float precision
    if (totalDetailsAmount - storedTotal > 0.01) {
        const manualDisc = totalDetailsAmount - storedTotal;
        setTimeout(() => {
             const input = document.getElementById('manualDiscountInput');
             if(input) {
                 input.value = manualDisc.toFixed(0); // Usually integer
                 // Trigger recalc
                 if(typeof calculateTotal === 'function') calculateTotal();
             }
        }, 500); // reduced timeout slightly
    }
    
    // 3. Override Form Action for UPDATE
    const form = document.getElementById('paymentForm');
    form.action = `/payments/${payment.id}`;
    
    // Inject PUT method
    let methodContainer = document.getElementById('methodSpoofContainer');
    if(!methodContainer) {
        methodContainer = document.createElement('div');
        methodContainer.id = 'methodSpoofContainer';
        form.appendChild(methodContainer);
    }
    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    // UI Updates
    document.getElementById('payModalTitle').innerText = 'Edit Payment';
    const submitBtn = form.querySelector('button[type="submit"]');
    if(submitBtn) submitBtn.textContent = 'Update Payment';

    // 4. Populate Basic Fields
    if(payment.payment_date) {
        document.getElementById('paymentDateInput').value = payment.payment_date.substring(0, 10);
    }
    
    const category = payment.payment_type || 'Monthly';
    document.getElementById('feeCategorySelect').value = category;
    document.getElementById('paymentType').value = category;
    
    // Note & Mode
    const modeSelect = document.querySelector('select[name="payment_mode"]');
    if(modeSelect && payment.payment_mode) modeSelect.value = payment.payment_mode;
    
    const noteInput = document.querySelector('input[name="note"]');
    if(noteInput) noteInput.value = payment.note || '';

    // 5. Hydrate Fees & Months
    if(category === 'Monthly') {
        const uniqueMonths = new Set();
        const baseFeeNames = new Set();
        
        details.forEach(d => {
            if(d.month) {
                uniqueMonths.add(d.month);
                let name = d.name;
                if(name.includes(' - ')) {
                    const parts = name.split(' - ');
                    const suffix = parts[parts.length-1];
                    if(suffix.includes(',') || d.month.includes(suffix)) {
                        parts.pop();
                        name = parts.join(' - ');
                    }
                }
                baseFeeNames.add(name);
            } else {
                // Fallback parsing
                const parts = d.name.split(' - ');
                if(parts.length > 1) {
                     const suffix = parts[parts.length-1];
                     if(suffix.match(/[A-Za-z]+, \d{2}/)) {
                         uniqueMonths.add(suffix);
                         parts.pop();
                         baseFeeNames.add(parts.join(' - '));
                     } else {
                         baseFeeNames.add(d.name);
                     }
                } else {
                    baseFeeNames.add(d.name);
                }
            }
        });
        
        // Reconstruct allFees (Active list)
        allFees = Array.from(baseFeeNames).map(name => {
             const sub = studentData.fees.find(f => f.name === name);
             const det = details.find(d => d.name.includes(name));
             return {
                 name: name,
                 // Prioritize historical amount
                 amount: parseFloat(det ? (det.original_amount || det.amount) : (sub ? sub.amount : 0)),
                 type: 'Monthly',
                 is_partial_completion: false
             };
        });
        
        updateFeeViews(); 
        
        // Tick Months
        updatePeriod(); 
        document.querySelectorAll('.month-checkbox').forEach(cb => {
            if(uniqueMonths.has(cb.dataset.displayText) || uniqueMonths.has(cb.value)) {
                cb.checked = true;
                cb.disabled = false; // Should satisfy via cleanTracker, but forcing ensures safety
                cb.parentElement.style.opacity = '1';
                cb.parentElement.style.cursor = 'pointer';
            }
        });
        updateSelectedMonths();
        
    } else {
        // Non-Monthly & Parts handling
        // 1. Set allFees based on base names logic or just raw details?
        // For Quarterly with parts, the "Base Fee" is "Tuition Fee". The "Detail" is "Tuition Fee - 1st Quater".
        // updateFeeViews renders "Tuition Fee" with dropdown.
        // So we need allFees to contain the BASE FEE.
        
        const baseFeeNames = new Set();
        details.forEach(d => {
             // Try to extract base name if it has a part suffix
             let name = d.name;
             // Check common suffixes
             [' - 1st Quater', ' - 2nd Quater', ' - 3rd Quater', ' - 4th Quater', ' - 1st Half', ' - 2nd Half'].forEach(suffix => {
                 if(name.includes(suffix)) {
                     name = name.replace(suffix, '');
                 }
             });
             baseFeeNames.add(name);
        });

        allFees = Array.from(baseFeeNames).map(name => {
             const sub = studentData.fees.find(f => f.name === name);
             const det = details.find(d => d.name.includes(name));
             return {
                 name: name,
                 amount: parseFloat(det ? (det.original_amount || det.amount) : (sub ? sub.amount : 0)),
                 type: category, // Force category
                 is_partial_completion: false
             };
        });
        
        updateFeeViews();
        
        // 2. Select the specific parts in Dropdowns
        details.forEach(d => {
             const parts = d.name.split(' - ');
             if(parts.length > 1) {
                 const partName = parts.pop();
                 const feeName = parts.join(' - ');
                 
                 const tr = document.querySelector(`.main-fee-row[data-fee-name="${feeName}"]`);
                 if(tr) {
                     const dropdown = tr.querySelector('.part-dropdown-menu');
                     if(dropdown) {
                         const cb = dropdown.querySelector(`input[value="${partName}"]`);
                         if(cb) {
                             cb.checked = true;
                             cb.disabled = false;
                         }
                         const anyCb = dropdown.querySelector('input');
                         if(anyCb) {
                             const menuId = dropdown.id.replace('menu_', '');
                             updatePartSelection(anyCb, menuId);
                         }
                     }
                 }
             }
        });
    }
    
    calculateTotal();
}

function confirmDelete(button) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e37814', // Matches accent color
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    })
}
</script>
@endsection
