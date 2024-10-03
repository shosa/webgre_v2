$(document).ready(function () {
  // Call the dataTables jQuery plugin
  var table = $('#dataTable').DataTable({
    "columnDefs": [
      { "type": "num-html", "targets": 0 }
    ],
    "info": true,
    "colReorder": true,
    "order": [[0, "desc"]],
    dom: '<"top"Bf>rt<"bottom"lip><"clear">',
    buttons: [
      { extend: 'excel', text: '<i class="fas fa-file-excel"></i> EXCEL', className: 'btn-light text-dark border-dark' },
      { extend: 'pdf', orientation: 'landscape', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-light text-dark border-dark', columns: ':visible:not(.notexport)' },
      { extend: 'print', text: '<i class="fas fa-print"></i> STAMPA', className: 'btn-light text-dark border-dark' },
    ],
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json"
    },

    // Use initComplete to add the select elements after the table is fully initialized
    initComplete: function () {
      this.api().columns().every(function (index) {
        var column = this;
        var headerName = $(column.header()).text();
    
        // Skip columns
        if (headerName === "Azioni" || headerName === "Note" || headerName === "#" || headerName === "ID" || headerName === "Timestamp") {
          var header = $('<span class="form-control border-0 font-weight-bold p-0 d-flex align-items-center" style="font-size:10pt;">' + headerName + '</span>')
            .appendTo($(column.header()).empty());
          return;
        }
    
        // Create select element
        var select = $('<select class="form-control border-0 font-weight-bold p-0 d-flex align-items-center" style="font-size:10pt;"><option value="">' + headerName + '</option></select>')
          .appendTo($(column.header()).empty())
          .on('change', function () {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            column.search(val ? '^' + val + '$' : '', true, false).draw();
          });
    
        // Add unique values to select
        column.data().unique().sort().each(function (d, j) {
          select.append('<option value="' + d + '">' + d + '</option>');
        });
      });
    }
  });
});
