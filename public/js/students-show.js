/**
 * Student Show Page Scripts
 */

$(document).ready(function () {
    $('#paymentTable').DataTable({
        "stateSave": true,
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[7, "desc"]], // Sort by Created At (newest first)
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search payments...",
            "paginate": {
                "previous": "Prev",
                "next": "Next"
            }
        }
    });
});
