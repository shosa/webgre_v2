$(document).ready(function () {
    $('#addArticleButton').click(function () {
        $('#addArticleModal').modal('show');
    });

    $('#addArticleForm').submit(function (e) {
        e.preventDefault();
        // Utilizza AJAX per inviare i dati al tuo script PHP
        $.ajax({
            type: "POST",
            url: "eti_new_article.php", // Modifica con il percorso corretto
            data: $(this).serialize(),
            success: function (response) {
                alert(response);
                $('#addArticleModal').modal('hide');
                // Aggiorna la tua pagina o tabella qui, se necessario
            }
        });
    });
});