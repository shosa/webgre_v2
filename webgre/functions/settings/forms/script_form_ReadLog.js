$(document).ready(function() {
    // Gestione del click sul pulsante Salva
    $('#saveBtn').click(function() {
        $.ajax({
            url: 'forms/processing_ReadLog.php',
            type: 'POST',
            data: {
                action: 'save',
                logContent: $('#logContent').val()
            },
            success: function(response) {
                const result = JSON.parse(response);
                $('#responseMessage').text(result.message).removeClass().addClass('alert alert-success');
            },
            error: function() {
                $('#responseMessage').text('Errore durante il salvataggio.').removeClass().addClass('alert alert-danger');
            }
        });
    });

    // Gestione del click sul pulsante Svuota
    $('#clearBtn').click(function() {
        $.ajax({
            url: 'forms/processing_ReadLog.php',
            type: 'POST',
            data: {
                action: 'clear'
            },
            success: function(response) {
                const result = JSON.parse(response);
                $('#logContent').val(''); // Svuota la textarea
                $('#responseMessage').text(result.message).removeClass().addClass('alert alert-success');
            },
            error: function() {
                $('#responseMessage').text('Errore durante lo svuotamento.').removeClass().addClass('alert alert-danger');
            }
        });
    });
});