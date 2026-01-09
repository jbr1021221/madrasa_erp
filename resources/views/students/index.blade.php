@extends('layouts.app')

@section('title', 'Students - Madrasa ERP')

@section('content')
@if(session('success'))
  <div class="alert success" style="background:rgba(76,175,80,0.2);color:#4caf50;padding:12px;border-radius:6px;margin-bottom:16px">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:end">
  <div>
    <h2 style="margin:0">Students</h2>
    <p style="margin:0;color:var(--muted);font-size:13px">Manage & monitor all enrolled students</p>
  </div>

</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <div style="display:flex;gap:10px;flex:1;flex-wrap:wrap;align-items:center">
    <!-- Filters Form -->
    <form method="GET" action="{{ route('students.index') }}" style="display:flex;gap:10px;flex-wrap:wrap">
      <select name="class_id" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
        <option value="">All Classes</option>
        @foreach($classrooms as $classroom)
          <option value="{{ $classroom->id }}" {{ request('class_id') == $classroom->id ? 'selected' : '' }}>
            {{ $classroom->name }}
          </option>
        @endforeach
      </select>

      <select name="section" onchange="this.form.submit()" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
        <option value="">All Sections</option>
        @foreach($sections as $section)
          <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
            Section {{ $section }}
          </option>
        @endforeach
      </select>

      <input type="date" name="start_date" value="{{ request('start_date') }}" 
             style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:140px" title="Start Date">
      
      <input type="date" name="end_date" value="{{ request('end_date') }}" 
             style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:140px" title="End Date">

      <input type="text" name="search" placeholder="Search by Name or ID" value="{{ request('search') }}" 
             style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:200px">
      
      <button type="submit" class="btn ghost" style="padding:6px 14px">Filter</button>
      @if(request()->hasAny(['class_id', 'section', 'search', 'start_date', 'end_date']))
        <a href="{{ route('students.index') }}" class="btn ghost" style="padding:6px 14px">Clear</a>
      @endif
    </form>

    <!-- Bulk Actions Dropdown -->
    <select id="bulkActionDropdown" onchange="handleBulkAction(this.value); this.value='';" style="background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);padding:6px 10px;border-radius:var(--radius);font-size:14px;width:150px">
      <option value="">Bulk Actions</option>
      <option value="delete">Delete Selected</option>
      <!--<option value="export-pdf">Export to PDF</option>-->
      <!--<option value="export-excel">Export to Excel</option>-->
    </select>
  </div>

  <a href="{{ route('students.create') }}" class="btn">+ Add Student</a>
</div>

<form id="bulkActionForm" action="{{ route('students.bulk-destroy') }}" method="POST">
  @csrf
  @method('DELETE')
  
  <table id="datatable">
    <thead>
      <tr>
        <th style="width:40px" class="no-sort"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
        <th style="width:50px">SL</th>
        <th>Name</th>
        <th>ID</th>
        <th>Class</th>
        <th>Section</th>
        <th class="no-sort">Actions</th>
      </tr>
    </thead>
    <tbody id="studentTable">
      @foreach($students as $index => $student)
      <tr>
        <td><input type="checkbox" name="selected_ids[]" value="{{ $student->id }}" class="student-checkbox" onclick="updateBulkAction()"></td>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $student->name }}</td>
        <td>{{ $student->student_id }}</td>
        <td>{{ $student->classroom->name ?? 'N/A' }}</td>
        <td>{{ $student->section }}</td>
        <td style="display:flex;gap:6px;justify-content:center">
          @php
            $recurringFees = [];
            $className = 'N/A';
            if($student->classroom) {
                $className = $student->classroom->name;
                
                // Get all class fees first
                $allClassFees = $student->classroom->fees ?? [];
                
                // Check if student has specific selected fees saved
                $subscribedFees = $student->selected_fees ?? null;
                $subscribedFeeNames = [];
                
                if($subscribedFees && is_array($subscribedFees)) {
                    foreach($subscribedFees as $f) {
                        if (!empty($f['name'])) {
                            $subscribedFeeNames[] = $f['name'];
                        }
                    }
                } 
                // Fallback: Try to find from Admission payment (Legacy support)
                elseif($student->payments) {
                     $admissionPayment = $student->payments->where('payment_type', 'Admission')->first();
                     if($admissionPayment && !empty($admissionPayment->fee_details)) {
                        foreach($admissionPayment->fee_details as $detail) {
                             $name = $detail['name'] ?? '';
                             if(strpos($name, ' - ') !== false) {
                                $parts = explode(' - ', $name);
                                $name = trim($parts[0]);
                             }
                             if(!empty($name)) $subscribedFeeNames[] = $name;
                        }
                     }
                }
                
                if($allClassFees) {
                    foreach($allClassFees as $fee) {
                        if(isset($fee['type'])) {
                            // If we have a subscription list (either from selected_fees or legacy admission), use it
                            if (count($subscribedFeeNames) > 0) {
                                if (in_array($fee['name'], $subscribedFeeNames)) {
                                    $recurringFees[] = $fee;
                                }
                            } else {
                                // Fallback: Include all fees if no specific subscription found
                                $recurringFees[] = $fee;
                            }
                        }
                    }
                }
            }
            // Calculate paid months and fees from all payments
            $paidFeeTracker = [];
            if($student->payments) {
                foreach($student->payments as $payment) {
                    if($payment->fee_details && is_array($payment->fee_details)) {
                        foreach($payment->fee_details as $feeDetail) {
                            $fType = strtolower($feeDetail['type'] ?? '');
                            if($fType === 'monthly') {
                                // Build month key in format "MonthName, YY"
                                $monthName = $feeDetail['month'] ?? '';
                                $year = $feeDetail['year'] ?? date('y');
                                
                                if (strpos($monthName, ', ') !== false) {
                                  $monthKey = $monthName;
                                } else {
                                  $monthKey = $monthName . ', ' . $year;
                                }
                                
                                if(!isset($paidFeeTracker[$monthKey])) $paidFeeTracker[$monthKey] = [];
                                
                                // Extract simple fee name
                                $name = $feeDetail['name'];
                                if(strpos($name, ' - ') !== false) {
                                  $parts = explode(' - ', $name);
                                  $name = trim($parts[0]);
                                }
                                
                                $paidFeeTracker[$monthKey][] = $name;
                            } else {
                                // For Quarterly/Half/Other
                                $rawName = $feeDetail['name'] ?? '';
                                
                                // Check if name is in "Fee - Parts" format
                                if (strpos($rawName, ' - ') !== false) {
                                    // Extract Base Name and Parts String
                                    // We need to be careful finding the first valid separator that separates Fee from Parts
                                    // Assuming "Fee Name - Part1, Part2"
                                    $separatorPos = strpos($rawName, ' - ');
                                    $baseName = trim(substr($rawName, 0, $separatorPos));
                                    $partsStr = substr($rawName, $separatorPos + 3);
                                    
                                    $parts = explode(',', $partsStr);
                                    foreach($parts as $p) {
                                        $p = trim($p);
                                        if(!empty($p)) {
                                            $paidFeeTracker[$baseName . ' - ' . $p] = true;
                                        }
                                    }
                                } else {
                                    // Fallback for simple names
                                    $names = explode(',', $rawName);
                                    foreach($names as $n) {
                                        $prioritizedKey = trim($n);
                                        $paidFeeTracker[$prioritizedKey] = true;
                                    }
                                }
                            }
                        }
                    } 
                    // Fallback for legacy payments (if full month was marked paid without details)
                    elseif ($payment->month && strtolower($payment->payment_type) === 'monthly') {
                         preg_match_all('/([A-Za-z]+,\s*\d{2})/', $payment->month, $matches);
                         if(!empty($matches[0])) {
                             foreach($matches[0] as $m) {
                                 // Assume ALL fees paid for this legacy month
                                 if(!isset($paidFeeTracker[$m])) $paidFeeTracker[$m] = ['__ALL__'];
                             }
                         }
                    }
                }
            }
          @endphp
          <button type="button" class="action-btn" onclick="openPayModal({{ $student->id }}, '{{ $student->name }} ({{ $className }})', '{{ $student->father_name }}', {{ json_encode($recurringFees) }}, {{ json_encode($student->discounts ?? []) }}, {{ json_encode($paidFeeTracker) }}, {{ json_encode($allClassFees ?? []) }}, {{ json_encode($student->partial_payments ?? []) }})" title="Pay Fees">Fees</button>
          <a href="{{ route('students.show', $student) }}" class="action-btn" title="View Details">View</a>
          <a href="{{ route('students.receipt.confirm', $student) }}" class="action-btn receipt" title="View Receipt">Receipt</a>
          <button type="button" class="action-btn delete" onclick="confirmDelete('{{ route('students.destroy', $student) }}')" title="Delete Student">Delete</button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</form>

<!-- PAY MODAL -->
<div class="modal" id="payModal">
  <div class="modal-content" style="width: 900px; max-width: 95%;">
    <div class="modal-header">
      <h3 id="payModalTitle">Payment</h3>
      <span class="close" onclick="closePayModal()">✕</span>
    </div>
    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" onsubmit="preparePaymentDetails()" target="_blank">
      @csrf
      <input type="hidden" name="student_id" id="payStudentId">
      <input type="hidden" name="payment_type" id="paymentType" value="Monthly">
      <input type="hidden" name="redirect_to" value="students.receipt.confirm">
      <input type="hidden" name="show_receipt" value="1">
      
      <!-- Hidden inputs for detailed payment breakdown -->
      <input type="hidden" name="payment_details" id="paymentDetailsInput">
      <input type="hidden" name="selected_months" id="selectedMonthsInput">
      <input type="hidden" name="added_fees" id="addedFeesInput">
      
      <div style="display:flex; gap: 16px; margin-bottom:12px; flex-wrap:wrap">
        <div style="flex: 2; min-width: 200px;">
            <label>Student</label>
            <input type="text" id="payStudentName" readonly style="background:#15181a;cursor:not-allowed">
        </div>
        <div style="flex: 2; min-width: 200px;">
            <label>Father's Name</label>
            <input type="text" id="payFatherName" readonly style="background:#15181a;cursor:not-allowed">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label>Date</label>
            <input type="date" name="payment_date" id="paymentDateInput" value="{{ date('Y-m-d') }}" required onchange="updatePeriod()">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label>Category</label>
            <select id="feeCategorySelect" onchange="updateFeeViews()" required>
              <option value="Monthly" selected>Monthly</option>
              <option value="Quarterly">Quarterly</option>
              <option value="Half-Yearly">Half-Yearly</option>
              <option value="Yearly">Yearly</option>
            </select>
        </div>
        
        <!-- Month Selection Dropdown (Visible only for Monthly) -->
        <div style="flex: 1; min-width: 150px; display:none;" id="monthSelectionContainer">
            <label>Select Months</label>
            <div style="position:relative;">
                <div id="monthDropdownBtn" onclick="toggleMonthDropdown()" style="background:#1b1f22; padding:8px; border:1px solid rgba(255,255,255,0.15); border-radius:4px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; height: 35px; box-sizing: border-box;">
                    <span id="selectedMonthsText" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:13px;">Select Months</span>
                    <span style="font-size:10px">▼</span>
                </div>
                <div id="monthDropdownList" style="display:none; position:absolute; top:100%; left:0; width:100%; background:#1b1f22; border:1px solid rgba(255,255,255,0.15); border-radius:4px; z-index:1000; max-height:200px; overflow-y:auto; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                    <!-- Checkboxes generated by JS -->
                </div>
            </div>
        </div>
        
        <input type="hidden" name="month" id="hiddenMonthInput">
      </div>

      <div style="margin-bottom:16px">
        <label>Fees</label>
        <div style="border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; overflow: visible;">
            <table style="width:100%; margin:0;">
                <thead style="background:rgba(255,255,255,0.05)">
                    <tr>
                        <th style="text-align:left; padding: 10px;">Description</th>
                        <th style="text-align:right; padding: 10px;">Actual Fee</th>
                        <th style="text-align:right; padding: 10px;">Discounted Fee</th>
                    </tr>
                </thead>
                <tbody id="monthlyFeeTableBody">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
      </div>

      <div style="display:flex; gap: 20px; flex-wrap:wrap">
        <!-- Left: Other Fees -->
        <div style="flex: 1; min-width: 250px;">
            <label style="margin-bottom: 8px; display:block;">Other Fees</label>
            <div id="otherFeesContainer" style="background:rgba(255,255,255,0.05); padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1);">
                <!-- Checkboxes populated via JS -->
            </div>
        </div>

        <!-- Right: Summary -->
        <div style="width: 300px; min-width: 250px;">
            <div style="background:rgba(255,255,255,0.05); padding: 15px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                    <span style="color:var(--muted)">Subtotal</span>
                    <span id="summarySubtotal" style="font-weight:bold">৳ 0.00</span>
                </div>
                 <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                    <span style="color:var(--muted)">Discount</span>
                    <input type="number" id="manualDiscountInput" value="0" min="0" oninput="calculateTotal()" style="width: 100px; text-align:right; padding: 6px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:var(--text); border-radius:4px;">
                </div>
                <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 8px 0;"></div>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                    <span style="font-weight:bold; font-size: 16px;">Total Amount</span>
                    <input type="number" name="amount" id="payAmountInput" readonly
                           style="width: 120px; text-align:right; font-weight:bold; color:var(--accent); background:rgba(0,0,0,0.2); border:1px solid var(--accent); border-radius:4px; padding:6px;">
                </div>
            </div>
            
            <div style="margin-top: 12px;">
                 <label>Payment Mode</label>
                <select name="payment_mode" required>
                  <option value="Cash">Cash</option>
                  <option value="Bkash">Bkash</option>
                  <option value="Bank">Bank</option>
                  <option value="Rocket">Rocket</option>
                  <option value="Upay">Upay</option>
                  <option value="Others">Others</option>
                </select>
            </div>
             <div style="margin-top: 12px;">
                <label>Note</label>
                <input type="text" name="note" placeholder="Optional note">
            </div>
        </div>
      </div>

      <div style="text-align:right; margin-top: 20px;">
        <button type="button" class="btn ghost" onclick="closePayModal()">Cancel</button>
        <button type="submit" class="btn">Confirm Payment</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('extra-styles')
<style>
table{width:100%;border-collapse:collapse;margin-top:14px}
th{background:rgba(255,255,255,0.15);padding:10px;font-size:14px;text-align:center}
td{padding:10px;font-size:14px;text-align:center}
#studentTable tr:nth-child(odd){background:rgba(255,255,255,0.04)}
#studentTable tr:nth-child(even){background:rgba(255,255,255,0.09)}
#studentTable tr:hover{background:rgba(227,120,20,0.18);transition:0.2s}
.action-btn{
  padding:6px 10px;border-radius:6px;border:1px solid var(--accent);
  color:var(--accent);background:transparent;cursor:pointer;font-size:13px;
  text-decoration:none;display:inline-block;margin:2px;transition:all 0.2s
}
.action-btn:hover{background:rgba(227,120,20,0.15)}
.action-btn.delete{border-color:var(--danger);color:var(--danger)}
.action-btn.delete:hover{background:rgba(255,78,78,0.15)}
.action-btn.receipt{border-color:#4caf50;color:#4caf50}
.action-btn.receipt:hover{background:rgba(76,175,80,0.15)}
.action-btn.admission{border-color:#2196f3;color:#2196f3}
.action-btn.admission:hover{background:rgba(33,150,243,0.15)}
.action-btn.edit{border-color:#ffc107;color:#ffc107}
.action-btn.edit:hover{background:rgba(255,193,7,0.15)}
.btn.ghost{background:transparent;border:1px solid var(--accent);color:var(--accent)}

/* MODAL */
.modal{
  position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);
  display:none;justify-content:center;align-items:center;z-index:9999;
}
.modal-content{
  width: 800px;max-width:90%;background:var(--card);padding:30px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.15);
}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.modal-header h3{margin:0}
.close{cursor:pointer;color:var(--muted);font-size:18px}
label{display:block;margin-bottom:6px;font-size:13px;color:var(--muted)}
input,select{width:100%;padding:8px;background:#1b1f22;border:1px solid rgba(255,255,255,0.15);color:var(--text);border-radius:4px}

/* Pagination Styles */
.pagination {display:flex;justify-content:center;gap:6px;list-style:none;padding:0;margin:0}
.pagination li a, .pagination li span {
  padding: 6px 12px;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px;
  color: var(--text);
  text-decoration: none;
  font-size: 14px;
}
.pagination li.active span {
  background: var(--accent);
  color: #000;
  border-color: var(--accent);
}
.pagination li a:hover {
  background: rgba(255,255,255,0.1);
}
.pagination li.disabled span {
  color: var(--muted);
  cursor: not-allowed;
}
/* Checkbox size */
input[type="checkbox"] {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

/* DataTables Customization */
.dataTables_wrapper {margin-top: 20px; color: var(--text); font-size: 14px;}
.dataTables_length select {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 4px; border-radius: 4px;}
.dataTables_filter input {background: #1b1f22; color: var(--text); border: 1px solid rgba(255,255,255,0.15); padding: 6px; border-radius: 4px; margin-left: 8px;}
.dataTables_info {color: var(--muted) !important; margin-top: 10px;}
.dataTables_paginate {margin-top: 10px;}
.dataTables_paginate .paginate_button {color: var(--text) !important; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; margin: 0 2px; cursor: pointer;}
.dataTables_paginate .paginate_button.current {background: var(--accent); color: #000 !important; border-color: var(--accent);}
.dataTables_paginate .paginate_button:hover {background: rgba(255,255,255,0.1); color: var(--text) !important; border-color: rgba(255,255,255,0.2);}
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {color: var(--muted);}
table.dataTable tbody tr {background-color: transparent;}
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('scripts')
<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [], // Disable initial sort
        "columnDefs": [
            { "orderable": false, "targets": "no-sort" } // Disable sort on specific columns
        ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search records...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            }
        }
    });
});

// Force reload on back button to ensure updated payment status
window.addEventListener("pageshow", function(event) {
    var historyTraversal = event.persisted || 
                           (typeof window.performance != "undefined" && 
                            window.performance.navigation.type === 2);
    if (historyTraversal) {
        window.location.reload();
    }
});

let currentFees = [];
let currentDiscounts = {};
let allFees = [];
let currentSubscribedFees = []; 
let currentNetPayable = 0;
let currentPaidFeeTracker = {};

let availableClassFees = [];

function openPayModal(id, name, fatherName, selectedFees, discounts, paidFeeTracker, classFees, partialPayments) {
  // fees = selectedFees (what student has subscribed to)
  // classFees = all available fees for the class
  // partialPayments = fees with remaining balance
  
  // Store subscriptions globally - these are the 'defaults' 
  currentSubscribedFees = selectedFees || [];
  
  // Initialize 'allFees' (Active Payment Fees)
  // By default, only show Monthly fees in the main table.
  // Other fees (Admission, Quarterly, etc.) will appear in the "Other Fees" section.
  allFees = (selectedFees || []).filter(f => (f.type || 'Monthly').toLowerCase() === 'monthly');
  
  // Ensure all subscribed fees are available in the pool even if not active in table
  availableClassFees = [...(classFees || [])];
  (selectedFees || []).forEach(sf => {
      if(!availableClassFees.some(af => af.name === sf.name)) {
          availableClassFees.push(sf);
      }
  });
  
  currentDiscounts = discounts || {};
  currentPaidFeeTracker = paidFeeTracker || {};
  
  console.log('=== Payment Modal Debug ===');
  console.log('Subscribed Fees:', currentSubscribedFees);
  console.log('Initial Active Fees:', allFees);
  console.log('Partial Payments:', partialPayments);
  
  // Add unpaid admission fees to availableClassFees
  if (partialPayments && typeof partialPayments === 'object') {
    for (let feeName in partialPayments) {
      const partial = partialPayments[feeName];
      if (partial.remaining && partial.remaining > 0) {
        // Add to available fees with remaining amount
        availableClassFees.push({
          name: feeName + ' (Remaining)',
          type: 'One Time',
          amount: partial.remaining,
          is_partial_completion: true,
          original_total: partial.total,
          already_paid: partial.paid
        });
        
        console.log(`Added partial fee: ${feeName} - Remaining: ${partial.remaining}`);
      }
    }
  }
  
  document.getElementById('payStudentId').value = id;
  document.getElementById('payStudentName').value = name;
  document.getElementById('payFatherName').value = fatherName;
  document.getElementById('feeCategorySelect').value = 'Monthly'; // Default
  document.getElementById('feeCategorySelect').setAttribute('onchange', 'handleCategoryChange()'); // Ensure we use custom handler
  document.getElementById('manualDiscountInput').value = 0;
  
  // Set date to today if not set
  if(!document.getElementById('paymentDateInput').value) {
      document.getElementById('paymentDateInput').value = new Date().toISOString().split('T')[0];
  }
  
  updateFeeViews();
  document.getElementById('payModal').style.display = 'flex';
}

function handleCategoryChange() {
    const newCategory = document.getElementById('feeCategorySelect').value;
    const previousCategory = document.getElementById('paymentType').value || 'Monthly';
    
    // Logic: 
    // 1. Remove fees that matched the OLD Main Category (Swap out)
    // 2. Add fees that match the NEW Main Category (Swap in from Subscriptions)
    // 3. Keep "Manually Added" fees (Non-matching types)
    
    // Remove fees belonging to the PREVIOUS main category
    let remainingFees = allFees.filter(f => (f.type || '').toLowerCase() !== previousCategory.toLowerCase());
    
    // Add fees that belong to the NEW main category (from Subscriptions)
    const newMainFees = currentSubscribedFees.filter(f => (f.type || '').toLowerCase() === newCategory.toLowerCase());
    
    // Combine (avoid duplicates)
    newMainFees.forEach(newFee => {
        if (!remainingFees.some(existing => existing.name === newFee.name)) {
            remainingFees.push(newFee);
        }
    });
    
    allFees = remainingFees;
    
    // Trigger render
    updateFeeViews();
}

// Toggle custom part dropdown
function togglePartDropdown(id) {
    const menu = document.getElementById('menu_' + id);
    if(menu) menu.style.display = (menu.style.display === 'none' ? 'block' : 'none');
}

// Global click to close
document.addEventListener('click', function(e) {
    if(!e.target.closest('.part-dropdown-container')) {
        document.querySelectorAll('.part-dropdown-menu').forEach(m => m.style.display = 'none');
    }
});

// Update selection from checkboxes
function updatePartSelection(chk, id) {
    const container = document.getElementById('part_container_' + id);
    const tr = container.closest('tr');
    
    // Get all checked
    const menu = document.getElementById('menu_' + id);
    const checked = menu.querySelectorAll('input[type="checkbox"]:checked');
    const values = Array.from(checked).map(c => c.value);
    
    // Update Display
    const disp = document.getElementById('disp_' + id);
    if(values.length === 0) disp.innerText = 'None';
    else if(values.length === 1) disp.innerText = values[0];
    else disp.innerText = values.length + ' Selected';
    
    // Update Row Data
    tr.dataset.partName = values.join(', '); // Comma separated for display/storage
    
    // Update Amount logic (Multiply unit amount)
    const unitAmount = parseFloat(tr.dataset.unitAmount) || parseFloat(tr.dataset.actual) || 0;
    // ensure unitAmount is saved if missing
    if(!tr.dataset.unitAmount) tr.dataset.unitAmount = unitAmount;
    
    const count = Math.max(0, values.length); // Allow 0 to remove fee effectively?
    const newTotal = unitAmount * count;
    
    tr.dataset.actual = newTotal; // Update actual so calculateTotal picks it up
    
    // Update Text in Row (Actual Fee)
    const tds = tr.querySelectorAll('td');
    if(tds[1]) tds[1].innerText = '৳' + newTotal.toFixed(2);
    
    // Update Discounted Column (3rd TD)
    const discount = parseFloat(tr.dataset.discount) || 0;
    const discounted = Math.max(0, newTotal - discount);
    if(tds[2]) {
        // The display structure is <div><span>Amount</span><button>...</div>
        const span = tds[2].querySelector('span');
        if(span) span.innerText = '৳' + discounted.toFixed(2);
    }
    
    // Recalculate totals
    calculateTotal();
}

// Helper to generate dropdown for split fees (Checkbox Version)
function generatePartDropdown(type, feeName) {
    let options = [];
    const t = (type || '').toLowerCase();
    
    if(t.includes('quarterly')) {
        options = ['1st Quater', '2nd Quater', '3rd Quater', '4th Quater'];
    } else if (t.includes('half')) {
        options = ['1st Half', '2nd Half'];
    } else {
        return null;
    }

    const uniqueId = 'pdd_' + Math.floor(Math.random() * 100000);
    
    let html = `<div id="part_container_${uniqueId}" class="part-dropdown-container" style="position:relative; display:inline-block; margin-left:12px;">
        <div onclick="togglePartDropdown('${uniqueId}')" style="background:#2b2f33; border:1px solid #444; color:white; padding:4px 8px; font-size:11px; border-radius:4px; cursor:pointer; min-width:110px; display:flex; justify-content:space-between; align-items:center;">
            <span id="disp_${uniqueId}">Select Part</span>
            <span>▼</span>
        </div>
        <div id="menu_${uniqueId}" class="part-dropdown-menu" style="display:none; position:absolute; top:100%; left:0; width:100%; min-width:120px; background:#252629; border:1px solid #444; z-index:99999; max-height:150px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.5);">`;
    
    let firstUnpaid = null;

    options.forEach(opt => {
        // Scoped check: Fee Name - Part
        // Use exact key created by PHP parser (with trims)
        const key = feeName.trim() + ' - ' + opt;
        let isPaid = !!currentPaidFeeTracker[key];
        
        // Default select logic: select the first unpaid one
        let checked = '';
        if(!isPaid && !firstUnpaid) {
            firstUnpaid = opt;
            checked = 'checked';
        }
        
        html += `<label style="display:block; padding:6px 8px; cursor:pointer; font-size:11px; border-bottom:1px solid #333; margin:0;">
            <input type="checkbox" value="${opt}" onchange="updatePartSelection(this, '${uniqueId}')" ${isPaid ? 'disabled' : ''} ${checked}> 
            <span style="opacity:${isPaid ? 0.5 : 1}">${opt} ${isPaid ? '(Paid)' : ''}</span>
        </label>`;
    });
    
    html += `</div></div>`;
    
    // We need to inject a script to set initial text logic? 
    // Or we return the default text.
    const defaultText = firstUnpaid || 'Select Part';
    
    // Hack: Replace the placeholder text in the html string
    html = html.replace('Select Part', defaultText);
    
    return { html: html, default: firstUnpaid || '' };
}

function updateFeeViews() {
  const category = document.getElementById('feeCategorySelect').value;
  document.getElementById('paymentType').value = category;
  
  // Update Period based on date and category
  updatePeriod();
  
  // 1. Determine which fees match the PRIMARY selected category
  let activeMainFees;
  if (category === 'Monthly') {
      activeMainFees = allFees.filter(f => (f.type || 'Monthly').toLowerCase() === 'monthly');
  } else {
      activeMainFees = allFees.filter(f => (f.type || '').toLowerCase() === category.toLowerCase());
  }
  
  // Render Table with Remove buttons for monthly fees (ACTIVE only)
  const tbody = document.getElementById('monthlyFeeTableBody');
  tbody.innerHTML = '';
  
  // 1. Render Active Main Fees (Current Category)
  activeMainFees.forEach((fee, index) => {
      const discount = getDiscount(fee.name);
      const actual = parseFloat(fee.amount) || 0;
      const discounted = Math.max(0, actual - discount);
      
      // Check for split fee dropdown
      const splitData = generatePartDropdown(fee.type, fee.name);
      const dropdownHtml = splitData ? splitData.html : '';
      const initialPart = splitData ? splitData.default : '';
      
      const tr = document.createElement('tr');
      tr.className = 'main-fee-row';
      tr.dataset.type = fee.type || category; // Critical for distinguishing logic
      tr.dataset.actual = actual;
      tr.dataset.unitAmount = actual;
      tr.dataset.discount = discount;
      tr.dataset.feeName = fee.name;
      // Set initial part name if applicable
      if(initialPart) tr.dataset.partName = initialPart;
      
      tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
      tr.style.transition = 'all 0.3s ease';
      tr.innerHTML = `
        <td style="text-align:left; padding: 10px;">
            <div style="display:flex; align-items:center; justify-content:flex-start; gap:15px; flex-wrap:nowrap;">
                <span style="font-weight:500;">${fee.name}</span>
                ${dropdownHtml}
            </div>
        </td>
        <td style="text-align:right; padding: 10px;">৳${actual.toFixed(2)}</td>
        <td style="text-align:right; padding: 10px;">
          <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
            <span style="font-weight:bold; color:var(--text);">৳${discounted.toFixed(2)}</span>
            <button type="button" onclick="removeMainFee('${fee.name}')" 
                    class="action-btn delete" style="padding:4px 8px; font-size:11px;">✕ Remove</button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
  });
  
  // 2. Render Active Other Fees (Different Category) - Automatically render them in table
  const activeOtherFees = allFees.filter(f => (f.type || '').toLowerCase() !== category.toLowerCase());

  activeOtherFees.forEach((fee) => {
      const discount = getDiscount(fee.name);
      const actual = parseFloat(fee.amount) || 0;
      const discounted = Math.max(0, actual - discount);
      
      const isPartial = fee.is_partial_completion === true;
      
      // Check for split fee dropdown
      const splitData = generatePartDropdown(fee.type, fee.name);
      const dropdownHtml = splitData ? splitData.html : '';
      const initialPart = splitData ? splitData.default : '';
      
      const tr = document.createElement('tr');
      tr.className = 'added-other-fee';
      tr.setAttribute('data-fee-name', fee.name);
      tr.dataset.actual = actual;
      tr.dataset.discount = discount;
      tr.dataset.unitAmount = actual;
      tr.dataset.isPartial = isPartial ? 'true' : 'false';
      if(initialPart) tr.dataset.partName = initialPart;
      
      tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
      
      const amountDisplay = isPartial 
        ? `<input type="number" value="${discounted}" 
                  oninput="updatePartialAmount(this)" 
                  style="width:80px; background:#1b1f22; color:white; border:1px solid #444; border-radius:4px; padding:4px; text-align:right;"
                  step="0.01" min="0" max="${actual}">`
        : `৳${discounted.toFixed(2)}`;

      tr.innerHTML = `
        <td style="text-align:left; padding: 10px;">
           <div style="display:flex; align-items:center; justify-content:flex-start; gap:15px; flex-wrap:nowrap;">
               <div>
                  ${fee.name} (${fee.type})
                  <span style="background:rgba(227,120,20,0.3); color:var(--accent); padding:2px 6px; border-radius:3px; font-size:11px; margin-left:8px;">ADDED</span>
               </div>
               ${dropdownHtml}
           </div>
        </td>
        <td style="text-align:right; padding: 10px;">৳${actual.toFixed(2)}</td>
        <td style="text-align:right; padding: 10px;">
          <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
            <span style="font-weight:bold; color:var(--text);">${amountDisplay}</span>
            <button type="button" onclick="removeAddedFee('${fee.name}')" 
                    class="action-btn delete" style="padding:4px 8px; font-size:11px;">✕ Remove</button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
  });

  // Check if empty
  if(tbody.children.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--muted)">No fees selected</td></tr>';
  }
  
  // 3. Render Other Fees List
  // We include active fees but HIDE them, so they can be toggled back if removed from table
  const inactiveFees = availableClassFees.filter(af => !allFees.some(f => f.name === af.name));
  
  const combinedOtherFees = [
      ...activeOtherFees.map(f => ({...f, _isActive: true})), 
      ...inactiveFees.map(f => ({...f, _isActive: false}))
  ];
  
  const otherContainer = document.getElementById('otherFeesContainer');
  otherContainer.innerHTML = '';
  
  let visibleCount = 0;
  
  combinedOtherFees.forEach((fee, index) => {
      const discount = getDiscount(fee.name);
      const actual = parseFloat(fee.amount) || 0;
      const discounted = Math.max(0, actual - discount);
      const isChecked = fee._isActive;
      
      const div = document.createElement('div');
      div.style.marginBottom = '8px';
      div.className = 'other-fee-item';
      const isPartialFee = fee.is_partial_completion === true;
      
      div.setAttribute('data-fee-name', fee.name);
      div.setAttribute('data-actual', actual);
      div.setAttribute('data-discount', discount);
      div.setAttribute('data-discounted', discounted);
      div.setAttribute('data-type', fee.type);
      div.setAttribute('data-is-partial', isPartialFee ? 'true' : 'false');
      
      // HIDE if active (because it's in the table)
      if (isChecked) {
          div.style.display = 'none';
      } else {
          div.style.display = 'block';
          visibleCount++;
      }
      
      // Check if this is a partial payment fee
      const partialBadge = isPartialFee ? `<span style="background:rgba(255,193,7,0.3); color:#ffc107; padding:2px 6px; border-radius:3px; font-size:10px; margin-left:4px; font-weight:600;">DUE</span>` : '';
      
      div.innerHTML = `
        <div style="display:flex; align-items:center; gap: 8px; cursor:pointer; padding: 6px 8px; border-radius: 4px; background:${isChecked ? 'rgba(227,120,20,0.15)' : (isPartialFee ? 'rgba(255,193,7,0.08)' : 'rgba(255,255,255,0.02)')}; ${isPartialFee ? 'border: 1px solid rgba(255,193,7,0.3);' : ''} user-select:none;"
             onclick="toggleOtherFee(this)">
            <input type="checkbox" class="fee-checkbox other-fee-checkbox" 
                   data-fee-name="${fee.name}"
                   ${isChecked ? 'checked' : ''}
                   style="opacity:0; position:absolute; pointer-events:none;">
            <span style="font-size:13px; flex:1;">${fee.name} (${fee.type})${partialBadge}</span>
            <span style="font-size:13px; color:${isPartialFee ? '#ffc107' : 'var(--accent)'}; font-weight:500;">৳${discounted.toFixed(2)}</span>
        </div>
      `;
      
      div.onmouseover = function() { 
        const input = this.querySelector('input');
        const innerDiv = this.querySelector('div');
        if(!input.checked) {
          if (isPartialFee) {
            innerDiv.style.background = 'rgba(255,193,7,0.15)';
          } else {
            innerDiv.style.background = 'rgba(255,255,255,0.05)';
          }
        }
      };
      div.onmouseout = function() { 
        const input = this.querySelector('input');
        const innerDiv = this.querySelector('div');
        if(!input.checked) {
          if (isPartialFee) {
            innerDiv.style.background = 'rgba(255,193,7,0.08)';
          } else {
            innerDiv.style.background = 'rgba(255,255,255,0.02)';
          }
        }
      };
      
      otherContainer.appendChild(div);
  });

  if(visibleCount === 0 && combinedOtherFees.length > 0) {
       const msg = document.createElement('div');
       msg.style.color = 'var(--muted)';
       msg.style.textAlign = 'center';
       msg.style.padding = '10px';
       msg.innerText = 'All fees added';
       otherContainer.appendChild(msg);
  } else if (combinedOtherFees.length === 0) {
      otherContainer.innerHTML = '<div style="color:var(--muted); text-align:center; padding:10px">No other fees available</div>';
  }
  
  // Also remove any added fees that are no longer valid for this student context (though modal rebuilds every time)
  // But calculateTotal to be sure
  calculateTotal();
}

// Remove added fee from payment table
function removeAddedFee(feeName) {
  // Update state
  allFees = allFees.filter(f => f.name !== feeName);
  
  const tbody = document.getElementById('monthlyFeeTableBody');
  const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);
  
  if (existingRow) {
    existingRow.remove();
    
    // Uncheck the checkbox in other fees section
    const checkbox = document.querySelector(`.other-fee-checkbox[data-fee-name="${feeName}"]`);
    if (checkbox) {
      checkbox.checked = false;
      const feeItem = checkbox.closest('.other-fee-item');
      if (feeItem) {
        feeItem.style.display = 'block';
        feeItem.querySelector('div').style.background = 'rgba(255,255,255,0.02)';
      }
    }
    calculateTotal();
  }
}

// Remove main fee (monthly fee) from payment table
function removeMainFee(feeName) {
  // Update state
  allFees = allFees.filter(f => f.name !== feeName);

  const tbody = document.getElementById('monthlyFeeTableBody');
  const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);
  
  if (existingRow) {
    existingRow.remove();
    
    // Check if it exists in Other Fees (was hidden)
    const otherContainer = document.getElementById('otherFeesContainer');
    let feeItem = otherContainer.querySelector(`.other-fee-item[data-fee-name="${feeName}"]`);
    
    if (feeItem) {
        const checkbox = feeItem.querySelector('input[type="checkbox"]');
        if(checkbox) checkbox.checked = false;
        feeItem.style.display = 'block';
        feeItem.querySelector('div').style.background = 'rgba(255,255,255,0.02)';
    } else {
        // If it doesn't exist in Other list, we might need to add it there as inactive
        // This usually won't happen if availableClassFees is correctly populated
        addNewInactiveFeeListItem(feeName, existingRow.dataset.actual, existingRow.dataset.discount);
    }
    calculateTotal();
  }
}

// Helper to add a fee to the list if it was removed from table but wasn't in list
function addNewInactiveFeeListItem(name, actual, discount) {
    const otherContainer = document.getElementById('otherFeesContainer');
    const category = document.getElementById('feeCategorySelect').value;
    const discounted = Math.max(0, actual - discount);
    
    const div = document.createElement('div');
    div.style.marginBottom = '8px';
    div.className = 'other-fee-item';
    div.setAttribute('data-fee-name', name);
    div.setAttribute('data-actual', actual);
    div.setAttribute('data-discount', discount);
    div.setAttribute('data-discounted', discounted);
    div.setAttribute('data-type', category);
    
    div.innerHTML = `
      <div style="display:flex; align-items:center; gap: 8px; cursor:pointer; padding: 6px 8px; border-radius: 4px; background:rgba(255,255,255,0.02); user-select:none;"
           onclick="toggleOtherFee(this)">
          <input type="checkbox" class="fee-checkbox other-fee-checkbox" 
                 data-fee-name="${name}"
                 style="opacity:0; position:absolute; pointer-events:none;">
          <span style="font-size:13px; flex:1;">${name} (${category})</span>
          <span style="font-size:13px; color:var(--accent); font-weight:500;">৳${discounted.toFixed(2)}</span>
      </div>
    `;
    otherContainer.appendChild(div);
}          
// Toggle other fee between "Other Fees" and "Payment Table"
// Toggle other fee between "Other Fees" and "Payment Table"
function toggleOtherFee(container) {
  const checkbox = container.querySelector('input[type="checkbox"]');
  const feeName = checkbox.getAttribute('data-fee-name');
  
  // Determine action based on STATE, not just checkbox toggle
  const isAlreadySelected = allFees.some(f => f.name === feeName);
  
  const feeItem = container.closest('.other-fee-item');
  const tbody = document.getElementById('monthlyFeeTableBody');

  if (isAlreadySelected) {
    // IT IS SELECTED -> REMOVE IT
    // This happens when clicking a checked "Active Other Fee"
    
    // 1. Update State
    allFees = allFees.filter(f => f.name !== feeName);
    
    // 2. Update UI (Uncheck)
    checkbox.checked = false;
    if (feeItem) {
        // Fix: Use correct selector (it's a div, not a label)
        const innerDiv = feeItem.querySelector('div');
        if(innerDiv) innerDiv.style.background = 'rgba(255,255,255,0.02)';
    }
    
    // 3. Remove from table if present
    const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);
    if (existingRow) existingRow.remove();

  } else {
    // IT IS NOT SELECTED -> ADD IT
    // This happens when clicking an unchecked "Inactive Fee"

    const actual = parseFloat(feeItem.getAttribute('data-actual')) || 0;
    const discounted = parseFloat(feeItem.getAttribute('data-discounted')) || 0;
    const discount = parseFloat(feeItem.getAttribute('data-discount')) || 0;
    const feeType = (feeItem.getAttribute('data-type') || 'Other');
    const currentCategory = (document.getElementById('feeCategorySelect').value || '').toLowerCase();

    const isPartial = feeItem.getAttribute('data-is-partial') === 'true';
    
    // 1. Update State
    allFees.push({
        name: feeName,
        amount: actual,
        type: feeType,
        is_partial_completion: isPartial
    });

    // 2. Update UI (Check)
    checkbox.checked = true;
    
    // 3. Add to Table
    let tr = document.createElement('tr');
    tr.setAttribute('data-fee-name', feeName);
    tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';

    if (feeType.toLowerCase() === currentCategory) {
      // Check for split fee dropdown
      const splitData = generatePartDropdown(feeType, feeName);
      const dropdownHtml = splitData ? splitData.html : '';
      const initialPart = splitData ? splitData.default : '';

      tr.className = 'main-fee-row'; 
      tr.dataset.actual = actual;
      tr.dataset.unitAmount = actual;
      tr.dataset.discount = discount;
      tr.dataset.feeName = feeName;
      if(initialPart) tr.dataset.partName = initialPart;

      tr.innerHTML = `
        <td style="text-align:left; padding: 10px;">
            <div style="display:flex; align-items:center; justify-content:flex-start; gap:15px; flex-wrap:nowrap;">
                <span style="font-weight:500;">${feeName}</span>
                ${dropdownHtml}
            </div>
        </td>
        <td style="text-align:right; padding: 10px;">৳${actual.toFixed(2)}</td>
        <td style="text-align:right; padding: 10px;">
          <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
            <span style="font-weight:bold; color:var(--text);">৳${discounted.toFixed(2)}</span>
            <button type="button" onclick="removeMainFee('${feeName}')" 
                    class="action-btn delete" style="padding:4px 8px; font-size:11px;">✕ Remove</button>
          </div>
        </td>
      `;
    } else {
      const splitData = generatePartDropdown(feeType, feeName);
      const dropdownHtml = splitData ? splitData.html : '';
      const initialPart = splitData ? splitData.default : '';

      tr.className = 'added-other-fee';
      tr.dataset.actual = actual;
      tr.dataset.discount = discount;
      tr.dataset.isPartial = isPartial ? 'true' : 'false';

      const amountDisplay = isPartial 
        ? `<input type="number" value="${discounted}" 
                  oninput="updatePartialAmount(this)" 
                  style="width:80px; background:#1b1f22; color:white; border:1px solid #444; border-radius:4px; padding:4px; text-align:right;"
                  step="0.01" min="0" max="${actual}">`
        : `৳${discounted.toFixed(2)}`;

      tr.innerHTML = `
        <td style="text-align:left; padding: 10px;">
           <div style="display:flex; align-items:center; justify-content:flex-start; gap:15px; flex-wrap:nowrap;">
               <div>
                  ${feeName} (${feeType})
                  <span style="background:rgba(227,120,20,0.3); color:var(--accent); padding:2px 6px; border-radius:3px; font-size:11px; margin-left:8px;">ADDED</span>
               </div>
               ${dropdownHtml}
           </div>
        </td>
        <td style="text-align:right; padding: 10px;">৳${actual.toFixed(2)}</td>
        <td style="text-align:right; padding: 10px;">
          <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
            <span style="font-weight:bold; color:var(--text);">${amountDisplay}</span>
            <button type="button" onclick="removeAddedFee('${feeName}')" 
                    class="action-btn delete" style="padding:4px 8px; font-size:11px;">✕ Remove</button>
          </div>
        </td>
      `;
    }
    tbody.appendChild(tr);
    
    // 4. Hide from list immediately
    feeItem.style.display = 'none';
  }
  
  calculateTotal();
}

function getDiscount(feeName) {
    if (!currentDiscounts || !feeName) return 0;
    
    const targetName = feeName.toString().trim().toLowerCase();
    
    // Try exact match first
    if (currentDiscounts[feeName]) {
        const d = currentDiscounts[feeName];
        return typeof d === 'object' ? (parseFloat(d.amount) || 0) : (parseFloat(d) || 0);
    }
    
    // Try case-insensitive and trimmed match
    for (let key in currentDiscounts) {
        if (key.toString().trim().toLowerCase() === targetName) {
            const d = currentDiscounts[key];
            return typeof d === 'object' ? (parseFloat(d.amount) || 0) : (parseFloat(d) || 0);
        }
    }
    
    return 0;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const container = document.getElementById('monthSelectionContainer');
    const dropdown = document.getElementById('monthDropdownList');
    const btn = document.getElementById('monthDropdownBtn');
    
    if (container.style.display !== 'none' && !container.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

function toggleMonthDropdown() {
    const dropdown = document.getElementById('monthDropdownList');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

function updatePeriod() {
    const dateInput = document.getElementById('paymentDateInput');
    const category = document.getElementById('feeCategorySelect').value;
    const hiddenMonth = document.getElementById('hiddenMonthInput');
    const monthContainer = document.getElementById('monthSelectionContainer');
    
    if (!dateInput.value) return;
    
    const date = new Date(dateInput.value);
    const monthIndex = date.getMonth(); // 0-11
    const year = date.getFullYear();
    
    let period = '';
    
    if (category === 'Monthly') {
        monthContainer.style.display = 'block';
        
        // Generate months list starting from current month
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        // Reorder months to put current month at top and calculate year for each
        const orderedMonths = [];
        for(let i=0; i<12; i++) {
            const currentMonthIndex = (monthIndex + i) % 12;
            const monthName = months[currentMonthIndex];
            
            // Calculate year: if we've wrapped around (monthIndex + i >= 12), increment year
            const monthYear = year + Math.floor((monthIndex + i) / 12);
            const shortYear = monthYear.toString().slice(-2);
            
            orderedMonths.push({
                name: monthName,
                year: shortYear,
                displayText: `${monthName}, ${shortYear}`
            });
        }
        
        const dropdownList = document.getElementById('monthDropdownList');
        dropdownList.innerHTML = '';
        
        orderedMonths.forEach((monthObj, index) => {
            const displayText = monthObj.displayText;
            const paidFeesForThisMonth = currentPaidFeeTracker[displayText] || [];
            
            // Determine status
            const monthlyFees = allFees.filter(f => (f.type || '').toLowerCase() === 'monthly');
            const totalMonthlyFees = monthlyFees.length;
            const monthlyFeeNames = monthlyFees.map(f => f.name.toLowerCase());
            
            // Count how many of OUR current monthly fees are paid
            let paidCount = 0;
            if (paidFeesForThisMonth.includes('__ALL__')) {
                paidCount = totalMonthlyFees;
            } else {
                paidCount = paidFeesForThisMonth.filter(pf => monthlyFeeNames.includes(pf.toLowerCase())).length;
            }
            
            let status = 'unpaid'; // unpaid, partial, paid
            if (totalMonthlyFees > 0) {
                if (paidCount >= totalMonthlyFees) {
                    status = 'paid';
                } else if (paidCount > 0) {
                    status = 'partial';
                }
            } else if (paidCount > 0 || paidFeesForThisMonth.includes('__ALL__')) {
                // If there are paid fees but no subscribed monthly fees, treat as paid
                status = 'paid';
            }
            
            const isFullyPaid = status === 'paid';
            const isChecked = false; // Don't auto-check
            
            const div = document.createElement('div');
            div.style.padding = '8px';
            div.style.cursor = isFullyPaid ? 'not-allowed' : 'pointer';
            div.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
            div.style.background = isFullyPaid ? 'rgba(255,255,255,0.02)' : 'transparent';
            
            let badge = '';
            if (status === 'paid') {
                badge = '<span style="font-size:10px; background:rgba(76,175,80,0.2); color:#4caf50; padding:1px 4px; border-radius:3px; margin-left:6px; border:1px solid #4caf50;">PAID</span>';
            } else if (status === 'partial') {
                badge = '<span style="font-size:10px; background:rgba(255,193,7,0.2); color:#ffc107; padding:1px 4px; border-radius:3px; margin-left:6px; border:1px solid #ffc107;">PARTIAL</span>';
            }
            
            div.innerHTML = `
                <label style="cursor:${isFullyPaid ? 'not-allowed' : 'pointer'}; display:flex; align-items:center; width:100%; margin:0; ${isFullyPaid ? 'opacity:0.6;' : ''}">
                    <input type="checkbox" value="${monthObj.name}" class="month-checkbox" 
                           data-year="${monthObj.year}" 
                           data-display-text="${monthObj.displayText}" 
                           ${isChecked ? 'checked' : ''} 
                           ${isFullyPaid ? 'disabled' : ''}
                           onchange="updateSelectedMonths()">
                    <span style="margin-left:8px">
                        ${monthObj.displayText}
                        ${badge}
                    </span>
                </label>
            `;
            
            if(!isFullyPaid) {
                div.onmouseover = function() { this.style.background = 'rgba(255,255,255,0.1)'; };
                div.onmouseout = function() { this.style.background = 'transparent'; };
            }
            
            dropdownList.appendChild(div);
        });
        
        updateSelectedMonths(); // Set initial value
        
    } else {
        monthContainer.style.display = 'none';
        
        if (category === 'Quarterly') {
            const quarterIndex = Math.floor(monthIndex / 3);
            const quarters = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
            period = quarters[quarterIndex];
        } else if (category === 'Half-Yearly') {
            const halfIndex = monthIndex < 6 ? 0 : 1;
            const halfYears = ['H1 (Jan-Jun)', 'H2 (Jul-Dec)'];
            period = halfYears[halfIndex];
        } else if (category === 'Yearly') {
            period = year.toString();
        }
        hiddenMonth.value = period;
        calculateTotal(); // Recalculate as multiplier is 1
    }
}

function updateSelectedMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox:checked');
    const selected = Array.from(checkboxes).map(cb => cb.getAttribute('data-display-text'));
    
    document.getElementById('hiddenMonthInput').value = selected.join(', ');
    
    const text = selected.length > 0 ? selected.join(', ') : 'Select Months';
    document.getElementById('selectedMonthsText').textContent = text;
    
    // Hide dropdown after selection as requested
    document.getElementById('monthDropdownList').style.display = 'none';
    
    calculateTotal();
}

function updatePartialAmount(input) {
    const tr = input.closest('tr');
    tr.dataset.actual = input.value;
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    const category = document.getElementById('feeCategorySelect').value;
    
    // Determine multiplier
    let multiplier = 1;
    const isMonthly = category.toLowerCase() === 'monthly';
    if (isMonthly) {
        const selectedMonthTexts = Array.from(document.querySelectorAll('.month-checkbox:checked')).map(cb => cb.dataset.displayText);
        
        console.log('=== Calculate Total Debug ===');
        console.log('Selected Months:', selectedMonthTexts);
        console.log('Paid Fee Tracker:', currentPaidFeeTracker);
        
        // Calculate total for main fees based on selected months and unpaid status
        document.querySelectorAll('.main-fee-row').forEach(row => {
            const actual = parseFloat(row.dataset.actual) || 0;
            const permanentDiscount = parseFloat(row.dataset.discount) || 0;
            const discountedFee = Math.max(0, actual - permanentDiscount);
            const feeName = row.dataset.feeName;
            
            // For each selected month, check if this specific fee is already paid
            let validMonthCount = 0;
            const targetFeeName = feeName.toLowerCase();
            
            // NEW: Only use month loop for fees that are actually type 'Monthly'
            // Non-monthly fees (e.g. Admission, Quarterly) should not depend on month selection
            const feeType = (row.dataset.type || 'Monthly').toLowerCase();
            let totalForThisFee = 0;
            
            if (feeType === 'monthly') {
                selectedMonthTexts.forEach(monthText => {
                    const paidFees = currentPaidFeeTracker[monthText] || [];
                    const isPaid = paidFees.some(pf => pf.toLowerCase() === targetFeeName) || paidFees.includes('__ALL__');
                    
                    if (!isPaid) {
                        validMonthCount++;
                    }
                });
                totalForThisFee = discountedFee * validMonthCount;
            } else {
                // Non-monthly fee: just use the discounted fee amount as is
                totalForThisFee = discountedFee;
                validMonthCount = 1; // Used for UI below
            }
            
            subtotal += totalForThisFee;
            
            // Store calculated amount for submission
            row.dataset.calculatedAmount = totalForThisFee;
            
            // Update Row UI to reflect status (Paid vs Amount)
            const actionCell = row.querySelector('td:nth-child(3)');
            if(actionCell) {
                if(feeType !== 'monthly' || selectedMonthTexts.length > 0) {
                     if(feeType === 'monthly' && validMonthCount === 0) {
                         // Fully paid for all selected months
                         actionCell.innerHTML = '<span style="color:#4caf50; font-weight:bold; background:rgba(76,175,80,0.1); padding:4px 8px; border-radius:4px; border:1px solid #4caf50;">Paid</span>';
                         row.style.opacity = '0.5'; // Visually dim the row
                     } else {
                         // Available to pay (Partially or Fully)
                         row.style.opacity = '1';
                         const isPartial = feeType === 'monthly' && validMonthCount < selectedMonthTexts.length;
                         
                         let badgeHtml = '';
                         if (isPartial) {
                             badgeHtml += `<span style="font-size:10px; background:rgba(255,193,7,0.2); color:#ffc107; padding:1px 4px; border-radius:3px; margin-right:4px; border:1px solid rgba(255,193,7,0.3);">Partial</span>`;
                         }
                         if (feeType === 'monthly' && validMonthCount > 1) {
                             badgeHtml += `<span style="font-size:11px; color:var(--muted); margin-right:4px;">(x${validMonthCount})</span>`;
                         }
                         
                         const removeBtnHtml = `
                            <button type="button" onclick="removeMainFee('${feeName}')" 
                                    style="background:rgba(255,78,78,0.2); color:var(--danger); border:1px solid var(--danger); border-radius:4px; padding:4px 8px; cursor:pointer; font-size:11px; transition:all 0.2s;"
                                    onmouseover="this.style.background='rgba(255,78,78,0.3)'" 
                                    onmouseout="this.style.background='rgba(255,78,78,0.2)'">
                              ✕ Remove
                            </button>`;
                            
                         actionCell.innerHTML = `
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                                ${badgeHtml}
                                <span style="font-weight:bold; color:var(--text);">${totalForThisFee > 0 ? '৳'+totalForThisFee.toFixed(2) : ''}</span>
                                ${removeBtnHtml}
                            </div>`;
                     }
                } else {
                    // Monthly fee with no months selected
                     actionCell.innerHTML = '<span style="color:var(--muted)">Select Month</span>';
                     row.dataset.calculatedAmount = 0;
                     row.style.opacity = '1';
                }
            }
        });
    } else {
        // Non-monthly logic (Quarterly, etc) - use multiplier 1
        const mainFees = document.querySelectorAll('.main-fee-row');
        if (mainFees.length === 0 && subtotal === 0) {
            // No fees at all
        }
        mainFees.forEach(row => {
            const actual = parseFloat(row.dataset.actual) || 0;
            const permanentDiscount = parseFloat(row.dataset.discount) || 0;
            const discountedFee = Math.max(0, actual - permanentDiscount);
            subtotal += discountedFee;
            row.dataset.calculatedAmount = discountedFee; // Store for submission
            row.style.opacity = '1';
        });
    }

    // Dynamically Added Other Fees
    // Dynamically Added Other Fees
    document.querySelectorAll('.added-other-fee').forEach(row => {
        const feeName = row.getAttribute('data-fee-name');
        const feeItem = document.querySelector(`.other-fee-item[data-fee-name="${feeName}"]`);
        
        let amount = 0;
        // Prefer row dataset if updated by multi-select
        if (row.dataset.actual) {
             const actual = parseFloat(row.dataset.actual) || 0;
             const discount = parseFloat(row.dataset.discount) || 0;
             amount = Math.max(0, actual - discount);
        } else if (feeItem) {
            amount = parseFloat(feeItem.getAttribute('data-discounted')) || 0;
        }
        subtotal += amount;
    });
    
    // Get manual discount
    const manualDiscountInput = document.getElementById('manualDiscountInput');
    let manualDiscount = parseFloat(manualDiscountInput.value) || 0;
    
    // Calculate final total (Net Payable)
    currentNetPayable = Math.max(0, subtotal - manualDiscount);
    
    document.getElementById('summarySubtotal').textContent = '৳ ' + subtotal.toFixed(2);
    
    // Update the payment input
    document.getElementById('payAmountInput').value = currentNetPayable;
    
    console.log('Subtotal:', subtotal, 'Manual Discount:', manualDiscount, 'Total:', currentNetPayable);
}

// Prepare detailed payment information before form submission
function preparePaymentDetails() {
  const category = document.getElementById('feeCategorySelect').value;
  const paymentDetails = {
    category: category,
    fee_details: [] // We'll build the full detail list here for the backend
  };
  
  const isMonthly = category.toLowerCase() === 'monthly';
  
  // Get selected months if Monthly category
  if (isMonthly) {
    const selectedMonths = [];
    document.querySelectorAll('.month-checkbox:checked').forEach(cb => {
      selectedMonths.push({
        name: cb.value,
        year: cb.dataset.year
      });
    });
    document.getElementById('selectedMonthsInput').value = JSON.stringify(selectedMonths);
  }
  
  // 1. Process Main Fees (Subscriptions)
  document.querySelectorAll('.main-fee-row').forEach(row => {
    let feeName = row.dataset.feeName || row.querySelector('td').textContent.trim();
    if (row.dataset.partName) {
        feeName = feeName + ' - ' + row.dataset.partName;
    }
    const actual = parseFloat(row.dataset.actual) || 0;
    const discount = parseFloat(row.dataset.discount) || 0;
    const discountedAmount = Math.max(0, actual - discount);
    const targetFeeName = feeName.toLowerCase();

    if (isMonthly) {
        const rowType = (row.dataset.type || 'Monthly').toLowerCase();
        
        if (rowType !== 'monthly') {
            // Non-monthly fees in monthly view (e.g. Admission, Quarterly)
            // They don't need month suffixes or looping
            paymentDetails.fee_details.push({
                name: feeName,
                type: row.dataset.type || 'Other',
                amount: discountedAmount,
                original_amount: actual,
                discount: discount
            });
        } else {
            // Real Monthly fees: Find which selected months are unpaid for THIS specific fee
            document.querySelectorAll('.month-checkbox:checked').forEach(cb => {
                const monthText = cb.dataset.displayText;
                const paidFees = currentPaidFeeTracker[monthText] || [];
                const isPaid = paidFees.some(pf => pf.toLowerCase() === targetFeeName) || paidFees.includes('__ALL__');

                if (!isPaid) {
                    paymentDetails.fee_details.push({
                        name: feeName + ' - ' + monthText,
                        type: 'Monthly',
                        amount: discountedAmount,
                        original_amount: actual,
                        discount: discount,
                        month: cb.value,
                        year: cb.dataset.year
                    });
                }
            });
        }
    } else {
        // Non-monthly fees (Quarterly, Yearly, etc.)
        paymentDetails.fee_details.push({
            name: feeName,
            type: category,
            amount: discountedAmount,
            original_amount: actual,
            discount: discount
        });
    }
  });
  
  // 2. Process Dynamically Added "Other Fees"
  document.querySelectorAll('.added-other-fee').forEach(row => {
    const originalFeeName = row.getAttribute('data-fee-name');
    let feeName = originalFeeName;
    if (row.dataset.partName) {
        feeName = originalFeeName + ' - ' + row.dataset.partName;
    }
    const feeItem = document.querySelector(`.other-fee-item[data-fee-name="${originalFeeName}"]`);
    
    let actual = 0;
    let discount = 0;
    let valid = false;

    if (row.dataset.actual) {
        actual = parseFloat(row.dataset.actual) || 0;
        discount = parseFloat(row.dataset.discount) || 0;
        valid = true;
    } else if (feeItem) {
        actual = parseFloat(feeItem.getAttribute('data-actual')) || 0;
        discount = parseFloat(feeItem.getAttribute('data-discount')) || 0;
        valid = true;
    }

    if (valid) {
        const discountedAmount = Math.max(0, actual - discount);
        const type = (feeItem ? feeItem.getAttribute('data-type') : '') || row.dataset.type || 'Other';
        paymentDetails.fee_details.push({
            name: feeName,
            type: type,
            amount: discountedAmount,
            original_amount: actual,
            discount: discount
        });
    }
  });

  // Update hidden input for backend consumption
  // Fix for validation error when paying only Other Fees (e.g. Quarterly) without selecting month
  const hiddenMonth = document.getElementById('hiddenMonthInput');
  if (hiddenMonth && !hiddenMonth.value && paymentDetails.fee_details.length > 0) {
      hiddenMonth.value = 'Other Fees'; // Placeholder to satisfy required validation
  }

  document.getElementById('paymentDetailsInput').value = JSON.stringify(paymentDetails);
  
  console.log('Prepared Payment Details:', paymentDetails);
  
  // Reload the parent page after a short delay to reflect payment status
  // Since form submits to _blank, this page stays open but needs refresh
  setTimeout(() => {
	  window.location.reload();
  }, 1500);

  return true; // Allow form submission
}

function closePayModal() {
  document.getElementById('payModal').style.display = 'none';
}

// Close on outside click
document.getElementById('payModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closePayModal();
  }
});

function toggleSelectAll() {
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.getElementsByClassName('student-checkbox');
  
  for(let checkbox of checkboxes) {
    checkbox.checked = selectAll.checked;
  }
  updateBulkAction();
}

function updateBulkAction() {
  const checkboxes = document.getElementsByClassName('student-checkbox');
  const bulkActions = document.getElementById('bulkActions');
  let checkedCount = 0;
  
  for(let checkbox of checkboxes) {
    if(checkbox.checked) checkedCount++;
  }
  
  if(checkedCount > 0) {
    bulkActions.style.display = 'block';
  } else {
    bulkActions.style.display = 'none';
  }
}

function confirmBulkDelete() {
  const checkboxes = document.getElementsByClassName('student-checkbox');
  let checkedCount = 0;
  for(let checkbox of checkboxes) {
    if(checkbox.checked) checkedCount++;
  }

  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete ${checkedCount} students. This action cannot be undone!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete them!'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('bulkActionForm').submit();
    }
  })
}

// Handle bulk action dropdown
function handleBulkAction(action) {
  if (!action) return;
  
  const checkboxes = document.getElementsByClassName('student-checkbox');
  let checkedCount = 0;
  
  for(let checkbox of checkboxes) {
    if(checkbox.checked) checkedCount++;
  }
  
  if (checkedCount === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'No Students Selected',
      text: 'Please select at least one student to perform bulk actions.',
      confirmButtonColor: '#e37814'
    });
    return;
  }
  
  switch(action) {
    case 'delete':
      confirmBulkDelete();
      break;
      
    case 'export-pdf':
      Swal.fire({
        icon: 'info',
        title: 'Export to PDF',
        text: `Exporting ${checkedCount} selected students to PDF...`,
        confirmButtonColor: '#e37814',
        timer: 2000
      });
      // TODO: Implement PDF export functionality
      break;
      
    case 'export-excel':
      Swal.fire({
        icon: 'info',
        title: 'Export to Excel',
        text: `Exporting ${checkedCount} selected students to Excel...`,
        confirmButtonColor: '#e37814',
        timer: 2000
      });
      // TODO: Implement Excel export functionality
      break;
  }
}

function confirmDelete(url) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.getElementById('deleteForm');
      form.action = url;
      form.submit();
    }
  })
}
</script>

<form id="deleteForm" method="POST" style="display:none">
  @csrf
  @method('DELETE')
</form>
@endsection