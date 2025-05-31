$(document).ready(function () {
    $("#tableBase").DataTable({
        dom: "<'#tableheader.row'<'col-12 col-md-6 pb-2'l><'col-12 col-md-4 ms-auto pb-2'f>><'#tableBaseDT.row'<'col-12'tr>><'#tableFooter.row'<'col-4'i><'col-8'p>>",
        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Data ke _START_ - _END_ dari _TOTAL_",
            infoFiltered: "(disaring dari total _MAX_ data)",
            emptyTable: "Tidak ada data",
            infoEmpty: "Menampilkan 0 data",
            zeroRecords: "Data tidak ditemukann",
        },
        pageLength: 5,
        autoWidth: false,
    });
});
