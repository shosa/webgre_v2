<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php include (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Associazione per Cartellini</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-6 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Inserimento Cartellini</h6>
                                </div>
                                <div class="card-body">
                                    <div class="input-grid" id="commessa-fields">
                                        <?php for ($i = 0; $i < 30; $i++): ?>
                                            <div class="input-item">
                                                <input type="text" class="form-control commessa-input" placeholder=""
                                                    onchange="verifyCommessa(this)">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button class="btn btn-primary btn-circle mt-2" onclick="addField()"><i
                                            class="fas fa-plus"></i></button>
                                    <form id="invioForm" method="post" action="processLink.php">
                                        <!-- Input nascosto per i dati dei cartellini -->
                                        <input type="hidden" id="selectedCartelsInput" name="selectedCartels">
                                        <!-- Bottone per inviare i dati -->
                                        <button type="button" id="avantiBtn" class="btn btn-success btn-block mt-2"
                                            onclick="inviaDati()" disabled>
                                            <i class="fas fa-paper-plane"></i> AVANTI

                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Riepilogo Selezione</h6>
                                </div>
                                <div class="card-body">
                                    <button id="loadSummaryBtn" class="btn btn-warning btn-block mt-2"
                                        onclick="loadSummary()" disabled><i class="fas fa-refresh"></i> CARICA
                                        RIEPILOGO</button>
                                    <div id="summary-list" class="summary-list"></div>
                                    <div id="summary-total" class="summary-list"></div>

                                    <!-- Messaggio di errore -->
                                    <div id="error-message" class="alert alert-danger mt-3" style="display: none;">
                                        Impossibile generare un riepilogo, ci sono cartellini inesistenti nella griglia.
                                    </div>

                                    <!-- Messaggio di aggiornamento -->
                                    <div id="update-message" class="alert alert-info mt-3" style="display: none;">
                                        Rilevate modifiche , aggiorna il riepilogo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    <style>
        .input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            grid-gap: 10px;
        }

        .input-item {
            display: flex;
            align-items: center;
        }

        .summary-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .summary-item {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-item h5 {
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #333;
        }

        .summary-item p {
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        .summary-total {
            margin-top: 20px;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: right;
            color: #333;

        }
    </style>
    <script>
        function addField() {
            var container = document.getElementById('commessa-fields');
            var inputField = document.createElement('div');
            inputField.className = 'input-item';
            inputField.innerHTML = '<input type="text" class="form-control commessa-input" placeholder="" oninput="verifyCommessa(this)">';
            container.appendChild(inputField);
            showUpdateMessage(); // Mostra il messaggio di aggiornamento
            checkAvantiButton(); // Controlla lo stato del pulsante "AVANTI" dopo l'aggiunta di un campo
        }

        function verifyCommessa(input) {
            var commessa = input.value;
            if (commessa.length === 0) {
                input.style.borderColor = '';
                checkLoadSummaryButton(); // Controlla lo stato del pulsante "CARICA RIEPILOGO"
                checkAvantiButton(); // Controlla lo stato del pulsante "AVANTI"
                hideErrorMessage(); // Nasconde il messaggio di errore se presente
                showUpdateMessage(); // Mostra il messaggio di aggiornamento
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'checkCartels.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.exists) {
                            input.style.borderColor = 'green';
                        } else {
                            input.style.borderColor = 'red';
                        }
                    } else {
                        console.error('Errore nella verifica della commessa');
                    }
                    checkLoadSummaryButton(); // Controlla lo stato del pulsante "CARICA RIEPILOGO"
                    checkAvantiButton(); // Controlla lo stato del pulsante "AVANTI"
                    hideErrorMessage(); // Nasconde il messaggio di errore se presente
                    showUpdateMessage(); // Mostra il messaggio di aggiornamento
                }
            };
            xhr.send('commessa=' + encodeURIComponent(commessa));
        }

        function checkLoadSummaryButton() {
            var inputs = document.querySelectorAll('.commessa-input');
            var isValid = false; // Impostato su false di default per verificare se almeno un input è valido
            var hasInvalidInput = false; // Aggiunto per tenere traccia di input non validi

            inputs.forEach(function (input) {
                if (input.value.trim().length > 0 && input.style.borderColor === 'green') {
                    isValid = true; // Trovato almeno un input valido
                }
                if (input.value.trim().length > 0 && input.style.borderColor === 'red') {
                    hasInvalidInput = true; // Segna che ci sono input non validi
                }
            });

            var loadSummaryBtn = document.getElementById('loadSummaryBtn');
            loadSummaryBtn.disabled = !isValid;

            // Mostra il messaggio di errore solo se ci sono input non validi e nessun input valido
            var errorMessage = document.getElementById('error-message');
            var updateMessage = document.getElementById('update-message');

            if (hasInvalidInput && !isValid) {
                errorMessage.style.display = 'block';
                updateMessage.style.display = 'none';
            } else {
                errorMessage.style.display = 'none';
                updateMessage.style.display = 'block';
            }
        }

        function checkAvantiButton() {
            var inputs = document.querySelectorAll('.commessa-input');
            var isValid = false;

            inputs.forEach(function (input) {
                if (input.value.trim().length > 0 && input.style.borderColor === 'green') {
                    isValid = true;
                }
            });

            var avantiBtn = document.querySelector('#invioForm button[type="button"]');
            avantiBtn.disabled = !isValid;
        }

        function loadSummary() {
            var commessas = [];
            var inputs = document.querySelectorAll('.commessa-input');
            var invalidInput = false;

            inputs.forEach(function (input) {
                if (input.value.trim().length > 0) {
                    if (input.style.borderColor === 'red') {
                        invalidInput = true;
                    } else if (input.style.borderColor === 'green') {
                        commessas.push(input.value.trim());
                    }
                }
            });

            if (invalidInput) {
                alert('Uno o più campi non sono validi. Correggi i campi evidenziati in rosso.');
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'loadSummary.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        updateSummaryList(response.data, response.total);
                    } else {
                        console.error('Errore nel caricamento del riepilogo');
                    }
                }
            };
            xhr.send('commessas=' + encodeURIComponent(JSON.stringify(commessas)));
        }

        function updateSummaryList(data, total) {
            var summaryList = document.getElementById('summary-list');
            var summaryTotal = document.getElementById('summary-total');
            summaryList.innerHTML = '';
            summaryTotal.innerHTML = '';

            data.forEach(function (cartel) {
                var summaryItem = document.createElement('div');
                summaryItem.className = 'summary-item mt-2';
                summaryItem.innerHTML = '<h6><b>' + cartel['Articolo'] + '</b></h6>' +
                    '<p style="font-size:10pt;"><i>' + cartel['Descrizione Articolo'] + '</i></p>' +
                    '<p>PA: ' + cartel['Tot'] + '</p>';
                summaryList.appendChild(summaryItem);
            });

            // Aggiungi totale generale
            var totalElement = document.createElement('div');
            totalElement.className = 'summary-total';
            totalElement.textContent = 'Totale: ' + total;
            summaryTotal.appendChild(totalElement);

            hideUpdateMessage(); // Nasconde il messaggio di aggiornamento dopo il caricamento del riepilogo
        }

        function showUpdateMessage() {
            var updateMessage = document.getElementById('update-message');
            updateMessage.style.display = 'block';
        }

        function hideUpdateMessage() {
            var updateMessage = document.getElementById('update-message');
            updateMessage.style.display = 'none';
        }

        function showErrorMessage() {
            var errorMessage = document.getElementById('error-message');
            errorMessage.style.display = 'block';
        }

        function hideErrorMessage() {
            var errorMessage = document.getElementById('error-message');
            errorMessage.style.display = 'none';
        }

        function inviaDati() {
            var commessas = [];  // Array che raccoglie i valori dei cartellini

            // Raccogli i dati dei cartellini come necessario
            var inputs = document.querySelectorAll('.commessa-input');
            inputs.forEach(function (input) {
                var commessa = input.value.trim();
                if (commessa.length > 0 && input.style.borderColor === 'green') {
                    commessas.push(commessa);
                }
            });

            if (commessas.length === 0) {
                alert('Nessun cartellino valido da inviare.');
                return;
            }

            // Aggiorna l'input nascosto con i dati dei cartellini
            document.getElementById('selectedCartelsInput').value = JSON.stringify(commessas);

            // Invia il form per navigare a processLink.php
            document.getElementById('invioForm').submit();
        }
    </script>

</body>