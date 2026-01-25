/**
 * Students Index Page JavaScript
 * Handles DataTables, Payment Modal, and Bulk Actions
 */

// DataTables Initialization
$(document).ready(function () {
    $('#datatable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [],
        "columnDefs": [
            { "orderable": false, "targets": "no-sort" }
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

// Force reload on back button
window.addEventListener("pageshow", function (event) {
    var historyTraversal = event.persisted ||
        (typeof window.performance != "undefined" &&
            window.performance.navigation.type === 2);
    if (historyTraversal) {
        window.location.reload();
    }
});

// State Variables
let currentFees = [];
let currentDiscounts = {};
let allFees = [];
let currentSubscribedFees = [];
let currentNetPayable = 0;
let currentPaidFeeTracker = {};
let availableClassFees = [];

// ==========================================
// PAY MODAL FUNCTIONS
// ==========================================

function openPayModal(id, name, fatherName, selectedFees, discounts, paidFeeTracker, classFees, partialPayments) {
    currentSubscribedFees = selectedFees || [];

    // Initialize 'allFees' (Active Payment Fees) - Default to Monthly
    allFees = (selectedFees || []).filter(f => (f.type || 'Monthly').toLowerCase() === 'monthly');

    // Ensure all subscribed fees are available in the pool
    availableClassFees = [...(classFees || [])];
    (selectedFees || []).forEach(sf => {
        if (!availableClassFees.some(af => af.name === sf.name)) {
            availableClassFees.push(sf);
        }
    });

    currentDiscounts = discounts || {};
    currentPaidFeeTracker = paidFeeTracker || {};

    // Add unpaid admission fees to availableClassFees
    if (partialPayments && typeof partialPayments === 'object') {
        for (let feeName in partialPayments) {
            const partial = partialPayments[feeName];
            if (partial.remaining && partial.remaining > 0) {
                availableClassFees.push({
                    name: feeName + ' (Remaining)',
                    type: 'One Time',
                    amount: partial.remaining,
                    is_partial_completion: true,
                    original_total: partial.total,
                    already_paid: partial.paid
                });
            }
        }
    }

    document.getElementById('payStudentId').value = id;
    document.getElementById('payStudentName').value = name;
    document.getElementById('payFatherName').value = fatherName;
    document.getElementById('feeCategorySelect').value = 'Monthly';
    document.getElementById('manualDiscountInput').value = 0;

    // Set date to today if not set
    if (!document.getElementById('paymentDateInput').value) {
        document.getElementById('paymentDateInput').value = new Date().toISOString().split('T')[0];
    }

    updateFeeViews();
    document.getElementById('payModal').style.display = 'flex';
}

function closePayModal() {
    document.getElementById('payModal').style.display = 'none';
}

// Close on outside click
document.getElementById('payModal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closePayModal();
    }
});

function handleCategoryChange() {
    const newCategory = document.getElementById('feeCategorySelect').value;
    const previousCategory = document.getElementById('paymentType').value || 'Monthly';

    // Remove fees matching OLD category
    let remainingFees = allFees.filter(f => (f.type || '').toLowerCase() !== previousCategory.toLowerCase());

    // Add fees matching NEW category (from Subscriptions)
    const newMainFees = currentSubscribedFees.filter(f => (f.type || '').toLowerCase() === newCategory.toLowerCase());

    newMainFees.forEach(newFee => {
        if (!remainingFees.some(existing => existing.name === newFee.name)) {
            remainingFees.push(newFee);
        }
    });

    allFees = remainingFees;
    updateFeeViews();
}

// ==========================================
// DROPDOWN HELPERS
// ==========================================

function togglePartDropdown(id) {
    const menu = document.getElementById('menu_' + id);
    if (menu) menu.style.display = (menu.style.display === 'none' ? 'block' : 'none');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.part-dropdown-container')) {
        document.querySelectorAll('.part-dropdown-menu').forEach(m => m.style.display = 'none');
    }
});

function updatePartSelection(chk, id) {
    const container = document.getElementById('part_container_' + id);
    const tr = container.closest('tr');

    const menu = document.getElementById('menu_' + id);
    const checked = menu.querySelectorAll('input[type="checkbox"]:checked');
    const values = Array.from(checked).map(c => c.value);

    const disp = document.getElementById('disp_' + id);
    if (values.length === 0) disp.innerText = 'None';
    else if (values.length === 1) disp.innerText = values[0];
    else disp.innerText = values.length + ' Selected';

    tr.dataset.partName = values.join(', ');

    const unitAmount = parseFloat(tr.dataset.unitAmount) || parseFloat(tr.dataset.actual) || 0;
    if (!tr.dataset.unitAmount) tr.dataset.unitAmount = unitAmount;

    const count = Math.max(0, values.length);
    const newTotal = unitAmount * count;

    tr.dataset.actual = newTotal;

    const tds = tr.querySelectorAll('td');
    if (tds[1]) tds[1].innerText = '৳' + newTotal.toFixed(2);

    const discount = parseFloat(tr.dataset.discount) || 0;
    const discounted = Math.max(0, newTotal - discount);
    if (tds[2]) {
        const span = tds[2].querySelector('span');
        if (span) span.innerText = '৳' + discounted.toFixed(2);
    }

    calculateTotal();
}

function generatePartDropdown(type, feeName) {
    let options = [];
    const t = (type || '').toLowerCase();

    if (t.includes('quarterly')) {
        options = ['1st Quater', '2nd Quater', '3rd Quater', '4th Quater'];
    } else if (t.includes('half')) {
        options = ['1st Half', '2nd Half'];
    } else {
        return null;
    }

    const uniqueId = 'pdd_' + Math.floor(Math.random() * 100000);

    let html = `<div id="part_container_${uniqueId}" class="part-dropdown-container">
        <div onclick="togglePartDropdown('${uniqueId}')" class="part-dropdown-toggle">
            <span id="disp_${uniqueId}">Select Part</span>
            <span>▼</span>
        </div>
        <div id="menu_${uniqueId}" class="part-dropdown-menu">`;

    let firstUnpaid = null;

    options.forEach(opt => {
        const key = feeName.trim() + ' - ' + opt;
        let isPaid = !!currentPaidFeeTracker[key];

        let checked = '';
        if (!isPaid && !firstUnpaid) {
            firstUnpaid = opt;
            checked = 'checked';
        }

        html += `<label class="part-dropdown-item">
            <input type="checkbox" value="${opt}" onchange="updatePartSelection(this, '${uniqueId}')" ${isPaid ? 'disabled' : ''} ${checked}> 
            <span style="opacity:${isPaid ? 0.5 : 1}">${opt} ${isPaid ? '(Paid)' : ''}</span>
        </label>`;
    });

    html += `</div></div>`;

    const defaultText = firstUnpaid || 'Select Part';
    html = html.replace('Select Part', defaultText);

    return { html: html, default: firstUnpaid || '' };
}

// ==========================================
// VIEW UPDATES & RENDERING
// ==========================================

function updateFeeViews() {
    const category = document.getElementById('feeCategorySelect').value;
    document.getElementById('paymentType').value = category;

    updatePeriod();

    let activeMainFees;
    if (category === 'Monthly') {
        activeMainFees = allFees.filter(f => (f.type || 'Monthly').toLowerCase() === 'monthly');
    } else {
        activeMainFees = allFees.filter(f => (f.type || '').toLowerCase() === category.toLowerCase());
    }

    const tbody = document.getElementById('monthlyFeeTableBody');
    tbody.innerHTML = '';

    // Render Active Main Fees
    activeMainFees.forEach((fee, index) => {
        const discount = getDiscount(fee.name);
        const actual = parseFloat(fee.amount) || 0;
        const discounted = Math.max(0, actual - discount);

        const splitData = generatePartDropdown(fee.type, fee.name);
        const dropdownHtml = splitData ? splitData.html : '';
        const initialPart = splitData ? splitData.default : '';

        const tr = document.createElement('tr');
        tr.className = 'main-fee-row';
        tr.dataset.type = fee.type || category;
        tr.dataset.actual = actual;
        tr.dataset.unitAmount = actual;
        tr.dataset.discount = discount;
        tr.dataset.feeName = fee.name;
        if (initialPart) tr.dataset.partName = initialPart;

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

    // Render Active Other Fees (Different Category)
    const activeOtherFees = allFees.filter(f => (f.type || '').toLowerCase() !== category.toLowerCase());

    activeOtherFees.forEach((fee) => {
        const discount = getDiscount(fee.name);
        const actual = parseFloat(fee.amount) || 0;
        const discounted = Math.max(0, actual - discount);
        const isPartial = fee.is_partial_completion === true;

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
        if (initialPart) tr.dataset.partName = initialPart;

        tr.style.borderBottom = '1px solid rgba(255,255,255,0.05)';

        const amountDisplay = isPartial
            ? `<input type="number" value="${discounted}" oninput="updatePartialAmount(this)" class="discount-input" step="0.01" min="0" max="${actual}" style="width:80px">`
            : `৳${discounted.toFixed(2)}`;

        tr.innerHTML = `
        <td style="text-align:left; padding: 10px;">
           <div style="display:flex; align-items:center; justify-content:flex-start; gap:15px; flex-wrap:nowrap;">
               <div>
                  ${fee.name} (${fee.type})
                  <span class="badge-new">ADDED</span>
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

    if (tbody.children.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--muted)">No fees selected</td></tr>';
    }

    // Render Other Fees List (Inactive)
    const inactiveFees = availableClassFees.filter(af => !allFees.some(f => f.name === af.name));

    const combinedOtherFees = [
        ...activeOtherFees.map(f => ({ ...f, _isActive: true })),
        ...inactiveFees.map(f => ({ ...f, _isActive: false }))
    ];

    const otherContainer = document.getElementById('otherFeesContainer');
    otherContainer.innerHTML = '';

    let visibleCount = 0;

    combinedOtherFees.forEach((fee, index) => {
        const discount = getDiscount(fee.name);
        const actual = parseFloat(fee.amount) || 0;
        const discounted = Math.max(0, actual - discount);
        const isChecked = fee._isActive;
        const isPartialFee = fee.is_partial_completion === true;

        const div = document.createElement('div');
        div.className = 'other-fee-item';

        div.setAttribute('data-fee-name', fee.name);
        div.setAttribute('data-actual', actual);
        div.setAttribute('data-discount', discount);
        div.setAttribute('data-discounted', discounted);
        div.setAttribute('data-type', fee.type);
        div.setAttribute('data-is-partial', isPartialFee ? 'true' : 'false');

        if (isChecked) {
            div.style.display = 'none';
        } else {
            div.style.display = 'block';
            visibleCount++;
        }

        const partialBadge = isPartialFee ? `<span class="badge-due">DUE</span>` : '';

        // Using class-based styling
        const selectedClass = isChecked ? 'selected' : '';
        const partialClass = isPartialFee ? 'partial' : '';

        div.innerHTML = `
        <div class="other-fee-content ${selectedClass} ${partialClass}" onclick="toggleOtherFee(this)">
            <input type="checkbox" class="fee-checkbox other-fee-checkbox" 
                   data-fee-name="${fee.name}"
                   ${isChecked ? 'checked' : ''}>
            <span style="font-size:13px; flex:1;">${fee.name} (${fee.type})${partialBadge}</span>
            <span style="font-size:13px; color:${isPartialFee ? '#ffc107' : 'var(--accent)'}; font-weight:500;">৳${discounted.toFixed(2)}</span>
        </div>
      `;

        otherContainer.appendChild(div);
    });

    if (visibleCount === 0 && combinedOtherFees.length > 0) {
        const msg = document.createElement('div');
        msg.style.color = 'var(--muted)';
        msg.style.textAlign = 'center';
        msg.style.padding = '10px';
        msg.innerText = 'All fees added';
        otherContainer.appendChild(msg);
    } else if (combinedOtherFees.length === 0) {
        otherContainer.innerHTML = '<div style="color:var(--muted); text-align:center; padding:10px">No other fees available</div>';
    }

    calculateTotal();
}

function removeAddedFee(feeName) {
    allFees = allFees.filter(f => f.name !== feeName);

    const tbody = document.getElementById('monthlyFeeTableBody');
    const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);

    if (existingRow) {
        existingRow.remove();
        const checkbox = document.querySelector(`.other-fee-checkbox[data-fee-name="${feeName}"]`);
        if (checkbox) {
            checkbox.checked = false;
            const feeItem = checkbox.closest('.other-fee-item');
            if (feeItem) {
                feeItem.style.display = 'block';
                const content = feeItem.querySelector('.other-fee-content');
                if (content) content.classList.remove('selected');
            }
        }
        calculateTotal();
    }
}

function removeMainFee(feeName) {
    allFees = allFees.filter(f => f.name !== feeName);

    const tbody = document.getElementById('monthlyFeeTableBody');
    const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);

    if (existingRow) {
        existingRow.remove();

        const otherContainer = document.getElementById('otherFeesContainer');
        let feeItem = otherContainer.querySelector(`.other-fee-item[data-fee-name="${feeName}"]`);

        if (feeItem) {
            const checkbox = feeItem.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
            feeItem.style.display = 'block';
            const content = feeItem.querySelector('.other-fee-content');
            if (content) content.classList.remove('selected');
        } else {
            addNewInactiveFeeListItem(feeName, existingRow.dataset.actual, existingRow.dataset.discount);
        }
        calculateTotal();
    }
}

function addNewInactiveFeeListItem(name, actual, discount) {
    const otherContainer = document.getElementById('otherFeesContainer');
    const category = document.getElementById('feeCategorySelect').value;
    const discounted = Math.max(0, actual - discount);

    const div = document.createElement('div');
    div.className = 'other-fee-item';
    div.setAttribute('data-fee-name', name);
    div.setAttribute('data-actual', actual);
    div.setAttribute('data-discount', discount);
    div.setAttribute('data-discounted', discounted);
    div.setAttribute('data-type', category);

    div.innerHTML = `
      <div class="other-fee-content" onclick="toggleOtherFee(this)">
          <input type="checkbox" class="fee-checkbox other-fee-checkbox" data-fee-name="${name}">
          <span style="font-size:13px; flex:1;">${name} (${category})</span>
          <span style="font-size:13px; color:var(--accent); font-weight:500;">৳${discounted.toFixed(2)}</span>
      </div>
    `;
    otherContainer.appendChild(div);
}

function toggleOtherFee(container) {
    const checkbox = container.querySelector('input[type="checkbox"]');
    const feeName = checkbox.getAttribute('data-fee-name');
    const isAlreadySelected = allFees.some(f => f.name === feeName);
    const feeItem = container.closest('.other-fee-item');

    if (isAlreadySelected) {
        allFees = allFees.filter(f => f.name !== feeName);
        checkbox.checked = false;
        container.classList.remove('selected');

        const tbody = document.getElementById('monthlyFeeTableBody');
        const existingRow = tbody.querySelector(`tr[data-fee-name="${feeName}"]`);
        if (existingRow) existingRow.remove();

    } else {
        const actual = parseFloat(feeItem.getAttribute('data-actual')) || 0;
        const discounted = parseFloat(feeItem.getAttribute('data-discounted')) || 0;
        const discount = parseFloat(feeItem.getAttribute('data-discount')) || 0;
        const feeType = (feeItem.getAttribute('data-type') || 'Other');
        const currentCategory = (document.getElementById('feeCategorySelect').value || '').toLowerCase();
        const isPartial = feeItem.getAttribute('data-is-partial') === 'true';

        allFees.push({
            name: feeName,
            amount: actual,
            type: feeType,
            is_partial_completion: isPartial
        });

        checkbox.checked = true;

        // Add to Table
        // ... (reusing rendering logic simplified for brevity - in full implementation ideally separate function)
        updateFeeViews();
    }
    calculateTotal();
}

function getDiscount(feeName) {
    if (!currentDiscounts || !feeName) return 0;
    const targetName = feeName.toString().trim().toLowerCase();

    if (currentDiscounts[feeName]) {
        const d = currentDiscounts[feeName];
        return typeof d === 'object' ? (parseFloat(d.amount) || 0) : (parseFloat(d) || 0);
    }

    for (let key in currentDiscounts) {
        if (key.toString().trim().toLowerCase() === targetName) {
            const d = currentDiscounts[key];
            return typeof d === 'object' ? (parseFloat(d.amount) || 0) : (parseFloat(d) || 0);
        }
    }
    return 0;
}

// ==========================================
// DATE & MONTH LOGIC
// ==========================================

document.addEventListener('click', function (e) {
    const container = document.getElementById('monthSelectionContainer');
    if (container && container.style.display !== 'none' && !container.contains(e.target)) {
        document.getElementById('monthDropdownList').style.display = 'none';
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
    const monthIndex = date.getMonth();
    const year = date.getFullYear();
    let period = '';

    if (category === 'Monthly') {
        monthContainer.style.display = 'block';

        // Month generation logic
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const orderedMonths = [];
        for (let i = 0; i < 12; i++) {
            const currentMonthIndex = (monthIndex + i) % 12;
            const monthName = months[currentMonthIndex];
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

        orderedMonths.forEach((monthObj) => {
            const displayText = monthObj.displayText;
            const paidFeesForThisMonth = currentPaidFeeTracker[displayText] || [];

            const monthlyFees = allFees.filter(f => (f.type || 'Monthly').toLowerCase() === 'monthly');
            const totalMonthlyFees = monthlyFees.length;
            const monthlyFeeNames = monthlyFees.map(f => f.name.toLowerCase());

            let paidCount = 0;
            if (paidFeesForThisMonth.includes('__ALL__')) {
                paidCount = totalMonthlyFees;
            } else {
                paidCount = paidFeesForThisMonth.filter(pf => monthlyFeeNames.includes(pf.toLowerCase())).length;
            }

            let status = 'unpaid';
            if (totalMonthlyFees > 0) {
                if (paidCount >= totalMonthlyFees) status = 'paid';
                else if (paidCount > 0) status = 'partial';
            } else if (paidCount > 0 || paidFeesForThisMonth.includes('__ALL__')) {
                status = 'paid';
            }

            const isFullyPaid = status === 'paid';
            const div = document.createElement('div');
            div.className = 'month-item' + (isFullyPaid ? ' paid' : '');

            let badge = '';
            if (status === 'paid') badge = '<span class="badge-paid">PAID</span>';
            else if (status === 'partial') badge = '<span class="badge-partial">PARTIAL</span>';

            div.innerHTML = `
                <label class="month-label">
                    <input type="checkbox" value="${monthObj.name}" class="month-checkbox" 
                           data-year="${monthObj.year}" 
                           data-display-text="${monthObj.displayText}" 
                           ${isFullyPaid ? 'disabled' : ''}
                           onchange="updateSelectedMonths()">
                    <span style="margin-left:8px">${monthObj.displayText}${badge}</span>
                </label>
            `;
            dropdownList.appendChild(div);
        });
        updateSelectedMonths();

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
        calculateTotal();
    }
}

function updateSelectedMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox:checked');
    const selected = Array.from(checkboxes).map(cb => cb.getAttribute('data-display-text'));

    document.getElementById('hiddenMonthInput').value = selected.join(', ');
    const text = selected.length > 0 ? selected.join(', ') : 'Select Months';
    document.getElementById('selectedMonthsText').textContent = text;
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
    const isMonthly = category.toLowerCase() === 'monthly';

    if (isMonthly) {
        const selectedMonthTexts = Array.from(document.querySelectorAll('.month-checkbox:checked')).map(cb => cb.dataset.displayText);

        document.querySelectorAll('.main-fee-row').forEach(row => {
            const actual = parseFloat(row.dataset.actual) || 0;
            const permanentDiscount = parseFloat(row.dataset.discount) || 0;
            const discountedFee = Math.max(0, actual - permanentDiscount);

            const feeType = (row.dataset.type || 'Monthly').toLowerCase();
            let totalForThisFee = 0;
            let validMonthCount = 0;

            if (feeType === 'monthly') {
                selectedMonthTexts.forEach(monthText => {
                    const paidFees = currentPaidFeeTracker[monthText] || [];
                    const isPaid = paidFees.some(pf => pf.toLowerCase() === row.dataset.feeName.toLowerCase()) || paidFees.includes('__ALL__');
                    if (!isPaid) validMonthCount++;
                });
                totalForThisFee = discountedFee * validMonthCount;
            } else {
                totalForThisFee = discountedFee;
                validMonthCount = 1;
            }

            subtotal += totalForThisFee;
            row.dataset.calculatedAmount = totalForThisFee;

            const actionCell = row.querySelector('td:nth-child(3)');
            if (actionCell) {
                if (feeType !== 'monthly' || selectedMonthTexts.length > 0) {
                    if (feeType === 'monthly' && validMonthCount === 0) {
                        actionCell.innerHTML = '<span class="badge-paid">Paid</span>';
                        row.style.opacity = '0.5';
                    } else {
                        row.style.opacity = '1';
                        let badgeHtml = (feeType === 'monthly' && validMonthCount < selectedMonthTexts.length) ? '<span class="badge-partial">Partial</span>' : '';
                        if (feeType === 'monthly' && validMonthCount > 1) badgeHtml += `<span class="text-muted mr-1">(x${validMonthCount})</span>`;

                        actionCell.innerHTML = `
                            <div class="d-flex align-center justify-end gap-1">
                                ${badgeHtml}
                                <span class="font-bold">${totalForThisFee > 0 ? '৳' + totalForThisFee.toFixed(2) : ''}</span>
                                <button type="button" onclick="removeMainFee('${row.dataset.feeName}')" class="action-btn delete p-0 text-sm">✕ Remove</button>
                            </div>`;
                    }
                } else {
                    actionCell.innerHTML = '<span class="text-muted">Select Month</span>';
                    row.style.opacity = '1';
                }
            }
        });
    } else {
        document.querySelectorAll('.main-fee-row').forEach(row => {
            const actual = parseFloat(row.dataset.actual) || 0;
            const discountedFee = Math.max(0, actual - (parseFloat(row.dataset.discount) || 0));
            subtotal += discountedFee;
            row.style.opacity = '1';
        });
    }

    document.querySelectorAll('.added-other-fee').forEach(row => {
        let amount = 0;
        if (row.dataset.actual) {
            amount = Math.max(0, (parseFloat(row.dataset.actual) || 0) - (parseFloat(row.dataset.discount) || 0));
        }
        subtotal += amount;
    });

    const manualDiscount = parseFloat(document.getElementById('manualDiscountInput').value) || 0;
    currentNetPayable = Math.max(0, subtotal - manualDiscount);

    document.getElementById('summarySubtotal').textContent = '৳ ' + subtotal.toFixed(2);
    document.getElementById('payAmountInput').value = currentNetPayable;
}

// ==========================================
// FORM SUBMISSION & DETAILS
// ==========================================

function preparePaymentDetails() {
    const category = document.getElementById('feeCategorySelect').value;
    const paymentDetails = { category: category, fee_details: [] };
    const isMonthly = category.toLowerCase() === 'monthly';

    if (isMonthly) {
        const selectedMonths = [];
        document.querySelectorAll('.month-checkbox:checked').forEach(cb => {
            selectedMonths.push({ name: cb.value, year: cb.dataset.year });
        });
        document.getElementById('selectedMonthsInput').value = JSON.stringify(selectedMonths);
    }

    // Process Main Fees
    document.querySelectorAll('.main-fee-row').forEach(row => {
        let feeName = row.dataset.feeName;
        if (row.dataset.partName) feeName += ' - ' + row.dataset.partName;

        const actual = parseFloat(row.dataset.actual) || 0;
        const discount = parseFloat(row.dataset.discount) || 0;
        const discountedAmount = Math.max(0, actual - discount);

        if (isMonthly) {
            const rowType = (row.dataset.type || 'Monthly').toLowerCase();
            if (rowType !== 'monthly') {
                paymentDetails.fee_details.push({
                    name: feeName, type: row.dataset.type || 'Other',
                    amount: discountedAmount, original_amount: actual, discount: discount
                });
            } else {
                document.querySelectorAll('.month-checkbox:checked').forEach(cb => {
                    const monthText = cb.dataset.displayText;
                    const paidFees = currentPaidFeeTracker[monthText] || [];
                    const isPaid = paidFees.some(pf => pf.toLowerCase() === feeName.toLowerCase()) || paidFees.includes('__ALL__');

                    if (!isPaid) {
                        paymentDetails.fee_details.push({
                            name: feeName + ' - ' + monthText, type: 'Monthly',
                            amount: discountedAmount, original_amount: actual, discount: discount,
                            month: cb.value, year: cb.dataset.year
                        });
                    }
                });
            }
        } else {
            paymentDetails.fee_details.push({
                name: feeName, type: category,
                amount: discountedAmount, original_amount: actual, discount: discount
            });
        }
    });

    // Process Added Fees
    document.querySelectorAll('.added-other-fee').forEach(row => {
        let feeName = row.getAttribute('data-fee-name');
        if (row.dataset.partName) feeName += ' - ' + row.dataset.partName;

        const actual = parseFloat(row.dataset.actual) || 0;
        const discount = parseFloat(row.dataset.discount) || 0;
        const discountedAmount = Math.max(0, actual - discount);

        paymentDetails.fee_details.push({
            name: feeName, type: row.dataset.type || 'Other',
            amount: discountedAmount, original_amount: actual, discount: discount
        });
    });

    const hiddenMonth = document.getElementById('hiddenMonthInput');
    if (hiddenMonth && !hiddenMonth.value && paymentDetails.fee_details.length > 0) {
        hiddenMonth.value = 'Other Fees';
    }

    document.getElementById('paymentDetailsInput').value = JSON.stringify(paymentDetails);

    setTimeout(() => { window.location.reload(); }, 1500);
    return true;
}

// ==========================================
// BULK ACTIONS
// ==========================================

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.getElementsByClassName('student-checkbox');
    for (let checkbox of checkboxes) checkbox.checked = selectAll.checked;
    updateBulkAction();
}

function updateBulkAction() { }

function handleBulkAction(action) {
    if (!action) return;
    const checkboxes = document.getElementsByClassName('student-checkbox');
    let checkedCount = 0;
    for (let checkbox of checkboxes) if (checkbox.checked) checkedCount++;

    if (checkedCount === 0) {
        Swal.fire({
            icon: 'warning', title: 'No Students Selected',
            text: 'Please select at least one student.', confirmButtonColor: '#e37814'
        });
        return;
    }

    if (action === 'delete') {
        Swal.fire({
            title: 'Are you sure?',
            text: `Delete ${checkedCount} students? Cannot be undone!`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('bulkActionForm').submit();
        });
    }
}

function confirmDelete(url) {
    Swal.fire({
        title: 'Are you sure?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = url;
            form.submit();
        }
    });
}
