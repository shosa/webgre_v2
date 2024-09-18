document.getElementById('settingsEmailForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('forms/processing_SettingsEmail.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Successo',
                    text: data.message,
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Errore',
                    text: data.message,
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Errore',
                text: 'Si è verificato un errore durante l\'invio del form.',
            });
            console.error('Errore:', error);
        });
});
