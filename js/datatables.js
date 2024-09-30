// Call the dataTables jQuery plugin
$(document).ready(function () {
  $.fn.dataTable.ext.type.order['num-html-pre'] = function (data) {
    var num = data.replace(/<.*?>/g, '');
    return parseFloat(num);
  };
  $('#dataTable').DataTable({
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
  });
});