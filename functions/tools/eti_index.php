<?php
session_start();
require_once '../../config/config.php';
include BASE_PATH . '/includes/header-nomenu.php';
require_once BASE_PATH . '/includes/auth_validate.php';
?>
<style>
    #suggestions-container {
        max-height: 150px;
        overflow-y: auto;
        position: relative;
        background-color: #f0f0f0;
        border-bottom-left-radius: 10px;
    }

    .suggestion-item {
        padding: 8px;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #6610f2;
        color: white;
    }
</style>

<head>
    <script src="https://qajavascriptsdktests.azurewebsites.net/JavaScript/dymo.connect.framework.js"
        type="text/javascript" charset="UTF-8"> </script>
    <script src="eti_script.js" type="text/javascript" charset="UTF-8"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>


    <!-- Add Bootstrap CSS link -->

</head>

<div id="page-wrapper">
    <div class="content container mt-4">
        <div class="printControls">
            <div id="printersDiv" class="form-group">
                <label for="printersSelect">Stampante:</label>
                <select id="printersSelect" class="form-control"></select>
            </div>
            <div class="form-group">
                <label for="codice_articolo">Codice:</label>
                <input type="text" id="codice_articolo" name="codice_articolo" class="form-control" autocomplete="off">
                <div id="suggestions-container"></div>
            </div>

            <!-- Pulsanti sulla stessa riga -->
            <div class="row">
                <div class="col-md-4">
                    <button id="printButton" class="btn btn-primary" style="width:100%">STAMPA</button>
                </div>
                <div class="col-md-4">
                    <button id="addArticleButton" class="btn btn-warning" style="width:100%">NUOVO</button>
                </div>
                <div class="col-md-4">
                    <button id="decodePageButton" class="btn btn-info"
                        style="background-color:#6610f2;border-color:#6610f2;width:100%">CREA LISTA</button>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div id="suggestions-container"></div>
            </div>
        </div>
        <div class="mt-2">
            <!-- Aggiunto un accordion di Bootstrap -->
            <div id="accordion">


                <div class="card" style="display:none;">
                    <div class="card-header" id="printerDetailHeading">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#printerDetailContainer"
                                aria-expanded="true" aria-controls="printerDetailContainer">
                                Dettagli Etichettatrice:
                            </button>
                        </h5>
                    </div>
                    <!-- Rimossa la classe "show" per nascondere i dettagli inizialmente -->
                    <div id="printerDetailContainer" class="collapse" aria-labelledby="printerDetailHeading"
                        data-parent="#accordion">
                        <div id="printerDetail" class="table-responsive"></div>
                    </div>
                </div>

            </div>

            <img id="labelPreview" alt="Anteprima Etichetta" class="mt-3"
                style="border: 1px solid #ccc; max-width: 100%;" />
        </div>
    </div>
    <div class="modal fade" id="addArticleModal" tabindex="-1" role="dialog" aria-labelledby="addArticleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addArticleModalLabel">Nuovo Articolo</h5>
                    <button type="button" class="close" id="closeModalButton" aria-label="Chiudi">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addArticleForm">
                        <div class="form-group">
                            <label for="cm">CATEGORIA:</label>
                            <input type="text" class="form-control" id="cm" name="cm" required>
                        </div>
                        <div class="form-group">
                            <label for="art">CODICE ARTICOLO:</label>
                            <input type="text" class="form-control" id="art" name="art" required>
                        </div>
                        <div class="form-group">
                            <label for="des">DESCRIZIONE:</label>
                            <textarea class="form-control" id="des" name="des" required></textarea>
                        </div>
                        <button id="submitArticle" class="btn btn-success" style="width:100%">Salva Articolo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('addArticleButton').addEventListener('click', function () {
            // Utilizza jQuery per mostrare il modale
            $('#addArticleModal').modal('show');
        });

        document.getElementById('submitArticle').addEventListener('click', function (e) {
            e.preventDefault(); // Prevenire il submit di default del form

            var cm = document.getElementById('cm').value;
            var art = document.getElementById('art').value;
            var des = document.getElementById('des').value;

            fetch('eti_new_article.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cm=${encodeURIComponent(cm)}&art=${encodeURIComponent(art)}&des=${encodeURIComponent(des)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Articolo aggiunto con successo!');
                        $('#addArticleModal').modal('hide'); // Nascondi il modale con jQuery
                        // Qui potresti anche voler aggiornare la tua UI per riflettere il nuovo articolo aggiunto
                    } else {
                        alert('Errore nell\'aggiunta dell\'articolo: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        });
        $('#closeModalButton').on('click', function () {
            $('#addArticleModal').modal('hide');
        });
        document.getElementById('decodePageButton').addEventListener('click', function () {
            window.location.href = 'eti_decode.php'; // Cambia con il percorso corretto se necessario
        });
    </script>