document.getElementById('notificationForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Evita il ricaricamento della pagina

    // Ottieni i dati del form
    let formData = new FormData(this);

    // Invia i dati tramite fetch API
    fetch('forms/processing_SendNotification.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Notifica Inviata!',
                text: 'La notifica è stata inviata con successo.',
                confirmButtonText: 'Ok'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Errore',
                text: 'Si è verificato un errore: ' + data.message,
                confirmButtonText: 'Ok'
            });
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        Swal.fire({
            icon: 'error',
            title: 'Errore',
            text: 'Errore di rete o problema con il server.',
            confirmButtonText: 'Ok'
        });
    });
});
