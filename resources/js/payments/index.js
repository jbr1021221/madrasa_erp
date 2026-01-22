// DataTables handled via jQuery global (loaded in layout)

document.addEventListener('DOMContentLoaded', function () {
    const pageContainer = document.getElementById('payments-page');
    if (!pageContainer) return;

    // Data handling
    let classroomData = {};
    let allSections = [];
    let currentSection = '';

    try {
        classroomData = JSON.parse(pageContainer.dataset.classrooms || '{}');
        allSections = JSON.parse(pageContainer.dataset.sections || '[]');
        currentSection = pageContainer.dataset.currentSection || '';
    } catch (e) {
        console.error("Failed to parse payment page data", e);
    }

    // Dropdown Elements
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section');
    const filterForm = document.getElementById('filter-form');

    // Auto-submit on change for filter inputs
    const filterInputs = filterForm ? filterForm.querySelectorAll('select, input[type="date"]') : [];
    filterInputs.forEach(input => {
        // Skip specialized logic inputs if needed, but here all seem to trigger submit
        // However, generic "on change submit" can be annoying for dates. 
        // Original code had it on everything.
        // We will keep it but maybe debounce dates?
        // For now, replicate original behavior: change -> submit.

        // Prevent double binding if we add specific listeners later
        if (input.id === 'class_id') return;

        input.addEventListener('change', () => filterForm.submit());
    });

    // Class Change Logic
    if (classSelect) {
        classSelect.addEventListener('change', function () {
            updateSections();
            filterForm.submit();
        });

        // Initial update in case of back button or reload logic (though blade usually renders correct state, 
        // the section dropdown needs populating)
        updateSections();
    }

    function updateSections() {
        if (!classSelect || !sectionSelect) return;

        const classId = classSelect.value;

        // Save current selection if we are just re-populating (though form submit reloads page)
        // actually, since form submits, we rely on server rendering usually. 
        // BUT the original code re-populated section on load. 
        // The server renders the page with *selected* values, but the Options might be dynamic?
        // Original code: "Populate dropdown".
        // It seems the server does NOT populate the Section dropdown options? 
        // The blade `section` select only had "All Sections" initially in original code (line 49).
        // So we MUST populate it via JS.

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
            // Show all unique sections
            if (allSections && allSections.length > 0) {
                allSections.forEach(s => sectionsToShow.add(s));
            }
            Object.values(classroomData).forEach(cls => {
                if (cls.sections && cls.sections.length > 0) {
                    cls.sections.forEach(s => sectionsToShow.add(s));
                }
            });
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

    // DataTable Initialization
    // Rely on global jQuery/$ for DataTables as per legacy approach (it's loaded in layout)
    if (window.jQuery && window.jQuery.fn.DataTable) {
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
                "search": "",
                "searchPlaceholder": "Search payments...",
                "paginate": {
                    "previous": "Prev",
                    "next": "Next"
                },
                "emptyTable": "No payment records found",
                "zeroRecords": "No matching payments found"
            }
        });
    }
});
