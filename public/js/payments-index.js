/**
 * Payments Index Page JavaScript
 * Handles fee dropdown, section updates, and DataTables initialization
 */

// Fee Dropdown Toggle
function toggleFeeDropdown() {
    const dropdown = document.getElementById('feeDropdownContent');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Clear Fee Selection
function clearFeeSelection() {
    const inputs = document.querySelectorAll('#feeDropdownContent input[type="checkbox"]');
    inputs.forEach(input => input.checked = false);
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('feeDropdownContent');
    const button = document.getElementById('feeDropdownBtn');

    if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Section Update Logic
function updateSections() {
    const classSelect = document.getElementById('class_id');
    const classId = classSelect.value;
    const sectionSelect = document.getElementById('section');

    // Get data from window object (set by Blade)
    const classroomData = window.classroomData || {};
    const allSections = window.allSections || [];
    const currentSection = window.currentSection || '';

    // Reset section dropdown
    sectionSelect.innerHTML = '<option value="">All Sections</option>';

    let sectionsToShow = new Set();

    if (classId && classroomData[classId]) {
        // Show sections for specific class
        const data = classroomData[classId];
        if (data.sections && data.sections.length > 0) {
            data.sections.forEach(s => sectionsToShow.add(s));
        } else {
            sectionsToShow.add('A'); // Fallback
        }
    } else {
        // Show all unique sections from database
        if (allSections && allSections.length > 0) {
            allSections.forEach(s => sectionsToShow.add(s));
        }

        // Also add from classroom definitions
        Object.values(classroomData).forEach(cls => {
            if (cls.sections && cls.sections.length > 0) {
                cls.sections.forEach(s => sectionsToShow.add(s));
            }
        });

        // If no sections found, default to A, B
        if (sectionsToShow.size === 0) {
            sectionsToShow.add('A');
            sectionsToShow.add('B');
        }
    }

    // Populate dropdown
    Array.from(sectionsToShow).sort().forEach(section => {
        const option = document.createElement('option');
        option.value = section;
        option.textContent = `Section ${section}`;
        if (section === currentSection) {
            option.selected = true;
        }
        sectionSelect.appendChild(option);
    });
}

// Initialize DataTables
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
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "columnDefs": [
            { "orderable": false, "targets": "no-sort" }
        ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search payments...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            },
            "emptyTable": "No payment records found",
            "zeroRecords": "No matching payments found"
        }
    });

    // Initialize sections on page load
    updateSections();
});
