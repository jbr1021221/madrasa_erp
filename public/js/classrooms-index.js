/**
 * Classrooms Index Page JavaScript
 * Handles DataTables initialization
 */

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
            "searchPlaceholder": "Search classes...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            }
        }
    });
});
