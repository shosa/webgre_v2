document.querySelectorAll('input[data-field="descrizione"]').forEach(input => {
    input.addEventListener('change', function () {
        const id = this.dataset.id;
        const value = this.value;
        const field = this.dataset.field;
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('field', field);
        formData.append('value', value);
        fetch('forms/processing_manageLines.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Successo' : 'Errore',
                    text: data.message,
                });
            })
            .catch(error => {
                Swal.fire
                    ({
                        icon: 'error',
                        title: 'Errore',
                        text: 'Si è verificato un errore durante l\'aggiornamento della linea.',
                    });
            });
    });
});
document.querySelectorAll('.btn-delete-line').forEach(button => {
    button.addEventListener('click', function () {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Questa azione non può essere annullata!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, elimina!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch('forms/processing_manageLines.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminata!', data.message, 'success');
                            reloadForm();
                        } else {
                            Swal.fire('Errore!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Errore!', 'Si è verificato un errore durante l\'eliminazione della linea.', 'error');
                    });
            }
        });
    });
});
document.getElementById('addLineBtn').addEventListener('click', function () {
    Swal.fire({
        title: 'Aggiungi Linea',
        html:
            '<input id="sigla" class="swal2-input" placeholder="Sigla">' +
            '<input id="descrizione" class="swal2-input" placeholder="Descrizione">',
        showCancelButton: true,
        confirmButtonText: 'Aggiungi',
        cancelButtonText: 'Annulla',
        preConfirm: () => {
            const sigla = Swal.getPopup().querySelector('#sigla').value;
            const descrizione = Swal.getPopup().querySelector('#descrizione').value;
            if (!sigla || !descrizione) {
                Swal.showValidationMessage('Per favore, inserisci sia la sigla che la descrizione');
            }
            return { sigla: sigla, descrizione: descrizione };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('sigla', result.value.sigla);
            formData.append('descrizione', result.value.descrizione);
            fetch('forms/processing_manageLines.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Aggiunta!', data.message, 'success');
                        reloadForm();
                    } else {
                        Swal.fire('Errore!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Errore!', 'Si è verificato un errore durante l\'aggiunta della linea.', 'error');
                });
        }
    });
});
function reloadForm() {
    fetch('forms/form_manageLines.php') // L'endpoint PHP che restituisce il modulo aggiornato
        .then(response => response.text())
        .then(html => {
            document.getElementById('formManageLines').innerHTML = html;
            // Ricarica gli eventi per i nuovi elementi del modulo
            initializeEventListeners();
        })
        .catch(error => {
            console.error('Errore durante il ricaricamento del modulo:', error);
        });
}
function initializeEventListeners() {
    document.querySelectorAll('input[data-field="descrizione"]').forEach(input => {
        input.addEventListener('change', function () {
            const id = this.dataset.id;
            const value = this.value;
            const field = this.dataset.field;
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', id);
            formData.append('field', field);
            formData.append('value', value);
            fetch('forms/processing_manageLines.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Successo' : 'Errore',
                        text: data.message,
                    });
                })
                .catch(error => {
                    Swal.fire
                        ({
                            icon: 'error',
                            title: 'Errore',
                            text: 'Si è verificato un errore durante l\'aggiornamento della linea.',
                        });
                });
        });
    });
    document.getElementById('addLineBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Aggiungi Linea',
            html:
                '<input id="sigla" class="swal2-input" placeholder="Sigla">' +
                '<input id="descrizione" class="swal2-input" placeholder="Descrizione">',
            showCancelButton: true,
            confirmButtonText: 'Aggiungi',
            cancelButtonText: 'Annulla',
            preConfirm: () => {
                const sigla = Swal.getPopup().querySelector('#sigla').value;
                const descrizione = Swal.getPopup().querySelector('#descrizione').value;
                if (!sigla || !descrizione) {
                    Swal.showValidationMessage('Per favore, inserisci sia la sigla che la descrizione');
                }
                return { sigla: sigla, descrizione: descrizione };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('sigla', result.value.sigla);
                formData.append('descrizione', result.value.descrizione);
                fetch('forms/processing_manageLines.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Aggiunta!', data.message, 'success');
                            reloadForm();
                        } else {
                            Swal.fire('Errore!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Errore!', 'Si è verificato un errore durante l\'aggiunta della linea.', 'error');
                    });
            }
        });
    });
    document.querySelectorAll('.btn-delete-line').forEach(button => {
        button.addEventListener('click', function () {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Sei sicuro?',
                    text: "Questa azione non può essere annullata!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sì, elimina!',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', id);
                        fetch('forms/processing_manageLines.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Eliminata!', data.message, 'success');
                                    reloadForm();
                                } else {
                                    Swal.fire('Errore!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Errore!', 'Si è verificato un errore durante l\'eliminazione della linea.', 'error');
                            });
                    }
                });
            });
        });
    });
}