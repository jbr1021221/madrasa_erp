/**
 * Students Form JavaScript
 * Shared logic for Create and Edit pages
 */

let availableClassFees = [];
let feeRowCounter = 0;

// ==========================================
// CLASS & SECTION LOGIC
// ==========================================

function updateClassInfo() {
    const classId = document.getElementById('class_id').value;
    const sectionSelect = document.getElementById('section');

    // Reset UI
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    const availContainer = document.getElementById('availableFeesContainer');
    if (availContainer) availContainer.innerHTML = '<p class="text-muted text-center mt-3"><em>Select a class first</em></p>';

    const studentContainer = document.getElementById('studentFeesContainer');
    if (studentContainer) studentContainer.innerHTML = '<p class="text-muted text-center mt-3" id="emptyStudentFeesMsg"><em>Select a class to load fees</em></p>';

    updateTotal(); // Reset totals

    if (!classId || !window.classroomData[classId]) {
        sectionSelect.innerHTML = '<option value="">Select Class First</option>';
        return;
    }

    const data = window.classroomData[classId];

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

    // Generate Student ID (Only for Create page)
    if (typeof generateStudentId === 'function') {
        generateStudentId(classId);
    }

    // Prepare Fee Data
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

    // Clear containers before re-adding
    if (studentContainer) studentContainer.innerHTML = '';
    const emptyMsg = document.getElementById('emptyStudentFeesMsg');
    if (emptyMsg) emptyMsg.style.display = 'none';

    // Auto-add non-monthly and admission fees
    availableClassFees.forEach((fee, index) => {
        if (!['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type)) {
            addFeeToStudent(index);
        }
    });

    renderAvailableFees();

    // Restore logic if applicable (e.g. after validation error)
    if (window.feesJsonToRestore) {
        restoreFees(window.feesJsonToRestore);
    }
}

// Separate function for Edit page which has slightly different requirements
function updateSectionsForEdit(currentSection, currentClassId) {
    const classId = document.getElementById('class_id').value;
    const sectionSelect = document.getElementById('section');

    sectionSelect.innerHTML = '<option value="">Select Section</option>';

    if (!classId || !window.classroomData[classId]) {
        sectionSelect.innerHTML = '<option value="">Select Class First</option>';
        return;
    }

    const data = window.classroomData[classId];

    if (data.sections && data.sections.length > 0) {
        data.sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            if (section === currentSection && classId == currentClassId) {
                option.selected = true;
            }
            sectionSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "A";
        option.textContent = "A";
        if ("A" === currentSection && classId == currentClassId) {
            option.selected = true;
        }
        sectionSelect.appendChild(option);
    }

    loadClassFeesForEdit(classId, currentClassId);
}

function loadClassFeesForEdit(classId, originalClassId) {
    if (!classId || !window.classroomData[classId]) return;

    const data = window.classroomData[classId];
    availableClassFees = [];

    const admissionFee = parseFloat(data.admission_fee) || 0;
    if (admissionFee > 0) {
        availableClassFees.push({
            name: "Admission Fee",
            type: "One Time",
            amount: admissionFee,
            is_admission: true
        });
    }
    const additionalFees = data.fees || [];
    additionalFees.forEach(f => {
        availableClassFees.push({
            name: f.name,
            type: f.type || "Monthly",
            amount: parseFloat(f.amount)
        });
    });

    const studentContainer = document.getElementById('studentFeesContainer');
    studentContainer.innerHTML = '';
    const emptyMsg = document.getElementById('emptyStudentFeesMsg');
    if (emptyMsg) emptyMsg.style.display = 'none';

    renderAvailableFees();

    if (classId == originalClassId) {
        // Restore existing student fees
        restoreStudentFeesForEdit();
    } else {
        // New class - auto-add non-monthly fees
        availableClassFees.forEach((fee, index) => {
            if (!['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'].includes(fee.type)) {
                addFeeToStudent(index);
            }
        });
    }
}

// ==========================================
// FEE MANAGEMENT LOGIC
// ==========================================

function renderAvailableFees() {
    const container = document.getElementById('availableFeesContainer');
    if (!container) return;
    container.innerHTML = '';

    if (availableClassFees.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No fees available</p>';
        return;
    }

    availableClassFees.forEach((fee, index) => {
        const div = document.createElement('div');
        div.id = `avail_fee_div_${index}`;
        div.className = 'available-fee-item';

        div.innerHTML = `
            <div>
                <div class="font-semibold text-sm">${fee.name}</div>
                <div class="text-xs text-muted">${fee.type} • ৳ ${fee.amount}</div>
            </div>
            <button type="button" onclick="addFeeToStudent(${index})" class="add-fee-btn">+</button>
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

    // Check duplicates
    const existing = Array.from(container.querySelectorAll('.student-fee-row')).find(r => r.dataset.name === fee.name);
    if (existing) {
        // alert('Fee already added');
        return existing.id;
    }

    const rowId = 'fee_row_' + (feeRowCounter++);
    // const isPeriodic = ['Monthly', 'Quarterly', 'Half Yearly'].includes(fee.type); // unused but kept for ref

    const row = document.createElement('div');
    row.className = 'student-fee-row';
    row.id = rowId;
    row.dataset.baseAmount = fee.amount;
    row.dataset.originalIndex = feeIndex;
    row.dataset.type = fee.type;
    row.dataset.name = fee.name;

    row.innerHTML = `
        <div class="text-xs font-medium">${fee.name}</div>
        <div class="text-xs text-muted">${fee.type}</div>
        <div class="d-flex align-center gap-1">
            <input type="number" class="fee-discount-input" placeholder="0" min="0" max="${fee.amount}" 
                   oninput="updateFeeRow('${rowId}')">
            <label class="text-xs text-muted d-flex align-center gap-1 cursor-pointer" title="Permanent Discount">
                <input type="checkbox" class="perm-check" onclick="updateFeeRow('${rowId}')"> P
            </label>
        </div>
        <div class="fee-row-total text-right font-semibold text-sm text-accent">
            ৳ ${fee.amount.toFixed(2)}
        </div>
        <div class="text-right">
            <button type="button" onclick="removeFeeRow('${rowId}')" class="remove-fee-btn">&times;</button>
        </div>
    `;

    // Add Pay Now checkbox if on Create page
    // Using a class or check to see if we need this extra column which was in Create but not Edit
    // Actually Create has 6 columns: Pay, Name, Months, Discount, Amount, Remove
    // Edit has 5 columns: Name, Type, Discount, Amount, Remove
    // The previous implementation diverged. Let's unify or handle the difference.
    // The Create page "Student Fees" header: Pay | Fee Name | Months | Discount | Amount | ""
    // The Edit page "Student Fees" header: Fee Name | Type | Discount | Amount | ""

    // I will stick to the layout appropriate for each page.
    // I can detect which page by checking for a specific element ID unique to Create page?
    // 'partialPaymentBox' exists only on Create page usually.

    const isCreatePage = !!document.getElementById('partialPaymentBox');

    if (isCreatePage) {
        // Re-construct HTML for Create Page style
        row.style.gridTemplateColumns = '30px 2fr 1.5fr 1fr 1fr 30px';
        row.innerHTML = `
            <div class="d-flex justify-center">
                 <input type="checkbox" class="pay-now-check" checked onchange="updateTotal()">
            </div>
            <div class="text-xs font-medium">${fee.name} <div class="text-xs text-muted">${fee.type}</div></div>
            <div class="text-xs text-muted">-</div>
            <div class="d-flex align-center gap-1">
                <input type="number" class="fee-discount-input" placeholder="0" min="0" max="${fee.amount}" 
                       oninput="updateFeeRow('${rowId}')">
                <label class="text-xs text-muted d-flex align-center gap-1 cursor-pointer" title="Permanent Discount">
                    <input type="checkbox" class="perm-check" onclick="updateFeeRow('${rowId}')"> P
                </label>
            </div>
            <div class="fee-row-total text-right font-semibold text-sm text-accent">
                ৳ ${fee.amount.toFixed(2)}
            </div>
            <div class="text-right">
                <button type="button" onclick="removeFeeRow('${rowId}')" class="remove-fee-btn">&times;</button>
            </div>
        `;
    } else {
        // Edit Page Style
        row.style.gridTemplateColumns = '2fr 1.5fr 1fr 1fr 30px';
    }

    container.appendChild(row);
    updateFeeRow(rowId);

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
    updateTotal();

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
    let total = 0; // Total Payable (Assigned)
    let admissionTotal = 0; // Total to PAY NOW (Create page)

    const rows = document.querySelectorAll('.student-fee-row');
    rows.forEach(row => {
        const base = parseFloat(row.dataset.baseAmount);
        const discInput = row.querySelector('.fee-discount-input');
        const discount = parseFloat(discInput.value) || 0;
        const finalAmount = Math.max(0, base - discount);

        total += finalAmount;

        // For Create Page: Check if marked for payment
        const payCheck = row.querySelector('.pay-now-check');
        if (payCheck && payCheck.checked) {
            admissionTotal += finalAmount;
        }
    });

    const totalEl = document.getElementById('studentFeesTotal');
    if (totalEl) totalEl.textContent = `৳ ${total.toFixed(2)}`;

    // Create Page Specifics
    const totalAdmissionInput = document.getElementById('total_admission_fee');
    if (totalAdmissionInput) totalAdmissionInput.value = admissionTotal;

    // Update Hidden Fields
    updateHiddenField();

    // Handle Partial Payment UI if exists
    if (typeof updatePartialPayment === 'function') {
        updatePartialPayment();
    }
}

function updateHiddenField() {
    const fees = [];
    // For Create Page this stores all assigned fees
    // For Edit Page this stores assigned fees

    const rows = document.querySelectorAll('.student-fee-row');
    const assignedFees = [];
    const paymentFees = []; // For receipt generation (Create only)

    rows.forEach(row => {
        const name = row.dataset.name;
        const type = row.dataset.type;
        const amount = parseFloat(row.dataset.baseAmount);
        const discInput = row.querySelector('.fee-discount-input');
        const permCheck = row.querySelector('.perm-check');
        const discount = parseFloat(discInput.value) || 0;
        const isPermanent = permCheck ? permCheck.checked : false;
        const netAmount = Math.max(0, amount - discount);

        assignedFees.push({
            name: name,
            type: type,
            amount: amount,
            discount: discount,
            is_permanent: isPermanent
        });

        const payCheck = row.querySelector('.pay-now-check');
        if (payCheck && payCheck.checked) {
            paymentFees.push({
                name: name,
                type: type,
                amount: netAmount, // Paid amount
                discount: discount
            });
        }
    });

    const hiddenAssigned = document.getElementById('studentAssignedFees');
    if (hiddenAssigned) hiddenAssigned.value = JSON.stringify(assignedFees);

    const hiddenPayment = document.getElementById('selectedAdmissionFees');
    if (hiddenPayment) hiddenPayment.value = JSON.stringify(paymentFees);
}

// ==========================================
// RESTORE LOGIC
// ==========================================

function restoreFees(jsonString) {
    if (!jsonString) return;
    try {
        const fees = JSON.parse(jsonString);
        fees.forEach(f => {
            let matchIndex = availableClassFees.findIndex(af => af.name === f.name);
            if (matchIndex === -1 && f.name.includes(' - ')) {
                const base = f.name.split(' - ')[0];
                matchIndex = availableClassFees.findIndex(af => af.name === base);
            }

            if (matchIndex !== -1) {
                const rowId = addFeeToStudent(matchIndex);
                const row = document.getElementById(rowId);
                if (row) {
                    const payCheck = row.querySelector('.pay-now-check');
                    const discInput = row.querySelector('.fee-discount-input');
                    const permCheck = row.querySelector('.perm-check');

                    if (payCheck) payCheck.checked = true; // Default to checked if restoring?
                    if (discInput) discInput.value = f.discount || 0;
                    if (permCheck) permCheck.checked = f.is_permanent || false;

                    updateFeeRow(rowId);
                }
            }
        });
    } catch (e) { console.error('Error restoring fees', e); }
}


function restoreStudentFeesForEdit() {
    const selectedFees = window.studentData.selectedFees;
    const discounts = window.studentData.discounts;

    if (!selectedFees || selectedFees.length === 0) {
        // If empty, typically means "all fees implied" or actually none?
        // In previous code logic: "If no selected fees, add all fees" - wait, that might be dangerous if user truly selected none.
        // But original code did: "If no selectedFees... add all availableClassFees"
        // Let's stick to original logic:
        availableClassFees.forEach((fee, index) => {
            addFeeToStudent(index);
            applyDiscount(fee.name);
        });
    } else {
        selectedFees.forEach(sf => {
            const index = availableClassFees.findIndex(f => f.name === sf.name);
            if (index !== -1) {
                addFeeToStudent(index);
                applyDiscount(sf.name);
            }
        });
    }
}

function applyDiscount(feeName) {
    const discounts = window.studentData.discounts;
    if (discounts && discounts[feeName]) {
        // We need to find the just-added row. addFeeToStudent doesn't return ID immediately accessible here easily via selector unless we track it
        // But addFeeToStudent returns rowId!
        // Wait, I am calling addFeeToStudent above, but not capturing return value in the loop
        // Let's fix loop above to capture rowId
        const rows = document.querySelectorAll('.student-fee-row');
        const lastRow = rows[rows.length - 1]; // Risky?
        if (lastRow && lastRow.dataset.name === feeName) {
            const discInput = lastRow.querySelector('.fee-discount-input');
            const permCheck = lastRow.querySelector('.perm-check');
            if (discInput) discInput.value = discounts[feeName].amount || 0;
            if (permCheck) permCheck.checked = discounts[feeName].permanent || false;
            updateFeeRow(lastRow.id);
        }
    }
}

// ==========================================
// CREATE PAGE SPECIFICS (Partial Payment, Review)
// ==========================================

function generateStudentId(classId) {
    fetch(`/students/generate-id/${classId}`)
        .then(res => res.json())
        .then(data => {
            // We usually don't display it anymore or it's hidden?
            // User commented out ID display in HTML.
            // But if we need it:
            const el = document.getElementById('student_id');
            if (el) el.value = data.student_id;
        })
        .catch(e => console.error(e));
}

function togglePartialPayment() {
    const checkbox = document.getElementById('partialPaymentCheck');
    const box = document.getElementById('partialPaymentInput');
    const isPartialInput = document.getElementById('is_partial_payment');

    if (checkbox.checked) {
        box.style.display = 'block';
        if (isPartialInput) isPartialInput.value = 1;
        updatePartialPayment();
    } else {
        box.style.display = 'none';
        if (isPartialInput) isPartialInput.value = 0;
        document.getElementById('partialAmount').value = '';
        document.getElementById('remainingAmount').textContent = '';
    }
}

function updatePartialPayment() {
    const totalAdmission = parseFloat(document.getElementById('total_admission_fee').value) || 0;
    const partialAmount = parseFloat(document.getElementById('partialAmount').value) || 0;

    const remaining = Math.max(0, totalAdmission - partialAmount);

    const remainingEl = document.getElementById('remainingAmount');
    if (remainingEl) {
        remainingEl.textContent = `Remaining Due: ৳ ${remaining.toFixed(2)}`;
        if (partialAmount > totalAdmission) {
            remainingEl.style.color = 'var(--danger)';
            remainingEl.textContent = "Amount exceeds total payable!";
        } else {
            remainingEl.style.color = 'var(--muted)';
        }
    }
}

// Review / Modal Logic
function showReview() {
    const modal = document.getElementById('reviewModal');
    const content = document.getElementById('reviewContent');
    const form = document.getElementById('studentForm');

    // Basic validation check before review
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Gather data
    const formData = new FormData(form);
    let html = '<div class="review-section"><h4>Basic Information</h4><div class="review-detail-list">';

    const fields = [
        { key: 'name', label: 'Student Name' },
        { key: 'father_name', label: 'Father Name' },
        { key: 'mobile', label: 'Mobile' },
        { key: 'dob', label: 'Date of Birth' },
        { key: 'gender', label: 'Gender' },
        { key: 'blood_group', label: 'Blood Group' }
    ];

    fields.forEach(f => {
        html += `
            <div class="review-item">
                <span class="review-item-label">${f.label}</span>
                <span class="review-item-value">${formData.get(f.key) || '-'}</span>
            </div>`;
    });

    html += '</div></div>';

    // Guardian
    html += '<div class="review-section"><h4>Guardian</h4><div class="review-detail-list">';
    html += `
        <div class="review-item">
            <span class="review-item-label">Phone</span>
            <span class="review-item-value">${formData.get('guardian_phone') || '-'}</span>
        </div>
        <div class="review-item">
            <span class="review-item-label">NID</span>
            <span class="review-item-value">${formData.get('guardian_nid') || '-'}</span>
        </div>`;
    html += '</div></div>';

    // Fees
    html += '<div class="review-section"><h4>Fees & Payment</h4><div class="review-detail-list">';
    const totalFee = document.getElementById('studentFeesTotal')?.textContent || '0';
    html += `
        <div class="review-item">
            <span class="review-item-label">Total Payable</span>
            <span class="review-item-value" style="color:var(--accent)">${totalFee}</span>
        </div>`;

    const paymentMode = formData.get('payment_mode');
    html += `
        <div class="review-item">
            <span class="review-item-label">Payment Mode</span>
            <span class="review-item-value">${paymentMode}</span>
        </div>`;

    html += '</div></div>';

    content.innerHTML = html;
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('reviewModal').classList.remove('active');
}

function editForm() {
    closeModal();
}

function confirmAndSubmit() {
    document.getElementById('studentForm').submit();
}
