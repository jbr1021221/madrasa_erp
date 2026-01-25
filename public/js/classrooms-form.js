/**
 * Classrooms Create/Edit Page JavaScript
 * Handles dynamic fee rows
 */

let feeIndex = window.initialFeeIndex || 0;

function addFeeRow() {
    const container = document.getElementById('feesContainer');
    const row = document.createElement('div');
    row.className = 'fees-row';
    row.innerHTML = `
        <input type="text" name="fees[${feeIndex}][name]" placeholder="Fee Name" required>
        <input type="number" name="fees[${feeIndex}][amount]" placeholder="Amount" required min="0" step="0.01">
        <select name="fees[${feeIndex}][type]" required>
            <option value="One Time">One Time</option>
            <option value="Monthly">Monthly</option>
            <option value="Quarterly">Quarterly</option>
            <option value="Half-Yearly">Half-Yearly</option>
            <option value="Yearly">Yearly</option>
        </select>
        <button type="button" class="btn ghost" onclick="this.parentElement.remove()">X</button>
    `;
    container.appendChild(row);
    feeIndex++;
}
