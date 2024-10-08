<?php ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once '../../utils/helpers.php';
require_once '../../utils/log_utils.php';
$deposito_selezionato = isset($_POST['select_deposito']) ? $_POST['select_deposito'] : '';
$_SESSION['deposito_selezionato'] = $deposito_selezionato;
$db = getDbInstance();
try {
    $sql = "SELECT dep, des FROM inv_depositi WHERE dep = :dep";
    $stmt = $db->prepare($sql);
    $stmt->execute(['dep' => $deposito_selezionato]);
    $deposito = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($deposito) {
        $dep = htmlspecialchars($deposito['dep']);
        $des = htmlspecialchars($deposito['des']);
    } else {
        echo "Nessun deposito trovato per il valore selezionato.";
    }
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
} ?>
<style>
    input {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px
    }
    #suggestions-container {
        max-height: 150px;
        overflow-y: auto;
        position: relative;
        background-color: #f0f0f0;
        border-bottom-left-radius: 10px
    }
    .suggestion-item {
        padding: 8px;
        cursor: pointer
    }
    .suggestion-item:hover {
        background-color: #6610f2;
        color: #fff
    }
    .result-info {
        font-size: 18px;
        margin-bottom: 15px
    }
    .toggle-container {
        display: flex;
        align-items: center;
        margin-bottom: 15px
    }
    .toggle-label {
        margin-right: 10px
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 34px
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: #fff;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 50%
    }
    .toggle-input:checked+.toggle-slider {
        background-color: #2196f3
    }
    .toggle-input:checked+.toggle-slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px)
    }
    .numerata-container {
        margin-top: 20px
    }
    .numerata-row {
        display: flex;
        margin-bottom: 0
    }
    .numerata-label {
        margin-right: 10px;
        flex: 0 0 50px;
        text-align: right;
        line-height: 28px
    }
    .numerata-input {
        flex: 1;
        margin-right: 5px;
        width: 40px
    }
    #numerata-container input {
        text-align: center;
        padding: 2px
    }
</style><?php include (BASE_PATH . "/components/header.php"); ?>
<body id="page-top">
    <div id="wrapper"><?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content"><?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid"><?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Inventario</h1>
                    </div>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="inv_select_dep">Selezione Deposito</a></li>
                        <li class="breadcrumb-item active"><?php echo $deposito['des']; ?></li>
                    </ol>
                    <div class="row">
                        <div class="col-lg-8 col-xl-8">
                            <div class="mb-4 card shadow">
                                <div
                                    class="align-items-center justify-content-between card-header d-flex flex-row py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Inventario</h6>
                                </div>
                                <div class="card-body"><strong>Deposito Selezionato:</strong> <span
                                        style="background-color:#6610f2;padding:5px;border-radius:8px;color:#fff;font-weight:700"><?php echo $deposito['dep'] . ' | ' . $deposito['des']; ?></span><a
                                        href="inv_select_dep.php" style="text-decoration:none"><i
                                            class="fa-exchange-alt fad"
                                            style="margin-left:2%;background-color:#0d6efd;padding:8px;border-radius:8px;color:#fff;font-weight:700"></i></a>
                                    <div class="row ml-3">
                                        <div class="col-md-6">
                                            <form action="#" class="mt-3" id="addArticoloForm" method="post"><input
                                                    name="deposito" type="hidden"
                                                    value="<?php echo $deposito_selezionato; ?>">
                                                <div class="form-group"><label for="codice_articolo">Codice:</label>
                                                    <input class="form-control" id="codice_articolo" autocomplete="off"
                                                        name="codice_articolo">
                                                    <div id="suggestions-container"></div>
                                                </div>
                                                <div class="mt-3" id="result-container"></div>
                                                <div class="numerata-container" id="numerata-container"
                                                    style="display:none">
                                                    <div class="numerata-row"><label class="numerata-label">Tag:</label>
                                                        <div class="numerata-row" id="taglie-fields"></div>
                                                    </div>
                                                    <div class="numerata-row"><label class="numerata-label">Qtà:</label>
                                                        <div class="numerata-row" id="quantita-fields"></div>
                                                    </div>
                                                </div>
                                                <div class="hidden toggle-container" style="margin-top:2%"><label
                                                        class="toggle-label">Numerata:</label> <label
                                                        class="toggle-switch"><input class="toggle-input"
                                                            id="toggleNumerata" type="checkbox"> <span
                                                            class="toggle-slider"></span></label></div>
                                                <div class="mt-3 button-container"><button class="btn btn-success"
                                                        id="dettagliItemBtn" type="submit"><i
                                                            class="fal fa-search-plus"></i> Carica Dettagli</button>
                                                    <button class="btn btn-success" id="insertItemBtn" type="button"
                                                        style="display:none"><i class="fal fa-cart-plus"></i> Inserisci
                                                        Articolo</button> <button class="btn btn-danger"
                                                        id="annullaItemBtn" type="button" style="display:none"><i
                                                            class="fal fa-backspace"></i> Svuota</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div id="suggestions-container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-xl-4">
                            <div class="mb-4 card shadow">
                                <div
                                    class="align-items-center justify-content-between card-header d-flex flex-row py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Funzioni</h6>
                                </div>
                                <div class="card-body">
                                    <div class="col-md-12"><button class="btn btn-block btn-warning" id="editList"
                                            type="button"><i class="fal fa-wrench"></i> MODIFICA LISTA</button> <button
                                            class="btn btn-block btn-info" id="generatePdfBtn" type="button"><i
                                                class="fal fa-file-pdf"></i> STAMPA ELENCO DI INVENTARIO</button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once (BASE_PATH . '/components/scripts.php'); ?><?php include_once (BASE_PATH . '/components/footer.php'); ?>
        </div>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("addArticoloForm");
        const input = document.getElementById("codice_articolo");
        const suggestionsContainer = document.getElementById("suggestions-container");
        const resultContainer = document.getElementById("result-container");
        const numerataContainer = document.getElementById("numerata-container");
        const taglieFieldsContainer = document.getElementById("taglie-fields");
        const quantitaFieldsContainer = document.getElementById("quantita-fields");
        const toggleNumerata = document.getElementById("toggleNumerata");
        const btnInserisci = document.getElementById("insertItemBtn");
        const btnDettagli = document.getElementById("dettagliItemBtn");
        const btnAnnulla = document.getElementById("annullaItemBtn");
        const generatePdfBtn = document.getElementById("generatePdfBtn");
        let selectedDetails = null;
        let isNumerata; // Dichiarazione spostata qui
        input.addEventListener("input", function () {
            suggestionsContainer.innerHTML = "";
            resultContainer.innerHTML = "";
            const inputValue = input.value.trim();
            if (inputValue !== "") {
                fetch(`inv_get_suggestions.php?q=${inputValue}`)
                    .then(response => response.json())
                    .then(data => showSuggestions(data));
            }
        });
        btnAnnulla.addEventListener("click", function () {
            resetForm();
        });
        function resetForm() {
            // Ripristina il campo del modulo e i container
            input.value = "";
            suggestionsContainer.innerHTML = "";
            resultContainer.innerHTML = "";
            numerataContainer.style.display = "none";
            taglieFieldsContainer.innerHTML = "";
            quantitaFieldsContainer.innerHTML = "";
            // Nascondi o mostra i pulsanti e container come all'inizio
            btnAnnulla.style.display = "none";
            btnInserisci.style.display = "none";
            btnDettagli.style.display = "inline";
            input.focus();
        }
        document.getElementById('editList').addEventListener('click', function () {
            // Naviga a inv_all_items.php
            window.location.href = 'inv_all_items.php';
        });
        function showSuggestions(suggestions) {
            suggestionsContainer.innerHTML = "";
            suggestions.forEach(suggestion => {
                const suggestionItem = document.createElement("div");
                suggestionItem.className = "suggestion-item";
                suggestionItem.innerHTML = `<strong>${suggestion.art}</strong> | ${suggestion.des}`;
                suggestionItem.addEventListener("click", function () {
                    input.value = suggestion.art;
                    suggestionsContainer.innerHTML = "";
                });
                suggestionsContainer.appendChild(suggestionItem);
            });
        }
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            const selectedArticolo = input.value;
            isNumerata = toggleNumerata.checked; // Assegna il valore qui
            fetch(`inv_get_details.php?art=${selectedArticolo}`)
                .then(response => response.json())
                .then(details => {
                    selectedDetails = details;
                    resultContainer.innerHTML = `
            <label for="selected_des">Descrizione:</label>
            <input type="text" style="width:100%" id="selected_des" name="selected_des" value="${details.des}" readonly>
        `;
                    if (selectedDetails) {
                        if (isNumerata) {
                            numerataContainer.style.display = toggleNumerata.checked ? "block" : "none";
                            // Aggiungi i campi numerati per le taglie e quantità
                            for (let i = 1; i <= 20; i++) {
                                const tagliaField = document.createElement("div");
                                tagliaField.className = "form-group numerata-input";
                                tagliaField.innerHTML = `
                        <input type="text" id="numerataTaglia${i}" name="numerataTaglia${i}" class="form-control" placeholder="">
                    `;
                                taglieFieldsContainer.appendChild(tagliaField);
                                const quantitaField = document.createElement("div");
                                quantitaField.className = "form-group numerata-input";
                                quantitaField.innerHTML = `
                        <input type="text" id="numerataQuantita${i}" name="numerataQuantita${i}" class="form-control" placeholder="">
                    `;
                                quantitaFieldsContainer.appendChild(quantitaField);
                            }
                        } else {
                            taglieFieldsContainer.innerHTML = "";
                            quantitaFieldsContainer.innerHTML = "";
                            resultContainer.innerHTML += `
                    <label for="quantita">Quantità:</label>
                    <input type="text" id="quantita" name="quantita" placeholder="Inserisci la quantità" required>
                `;
                            document.getElementById('quantita').focus();
                        }
                        // Aggiungi il pulsante "Inserisci Articolo"
                        btnAnnulla.style.display = "inline";
                        btnInserisci.style.display = "inline";
                        btnDettagli.style.display = "none";
                    }
                })
                .catch(error => {
                    console.error("Errore durante la richiesta dei dettagli dell'articolo:", error);
                });
        });
        const insertItemBtn = document.getElementById("insertItemBtn");
        insertItemBtn.removeEventListener("click", handleInsertItem); // Rimuovi il vecchio listener
        insertItemBtn.addEventListener("click", handleInsertItem);
        function handleInsertItem() {
            insertItem(isNumerata);
        }
        function insertItem(isNumerata) {
            const formData = new FormData(form);
            // Se l'opzione numerata è abilitata, ottieni tutti i valori dei campi numerati
            if (isNumerata) {
                const numerataTaglie = [];
                const numerataQuantita = [];
                for (let i = 1; i <= 20; i++) {
                    numerataTaglie.push(formData.get(`numerataTaglia${i}`));
                    numerataQuantita.push(formData.get(`numerataQuantita${i}`));
                }
                // Concatena i valori separati da ";"
                formData.set('num', numerataTaglie.join(';'));
                formData.set('qta', numerataQuantita.join(';'));
                formData.set('valueNumerata', '1');
            } else {
                formData.set('num', 'X');
                formData.set('qta', formData.get(`quantita`));
                formData.set('valueNumerata', '0');
            }
            fetch("inv_upload_item.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json()) // Modificato da 'response.text()' a 'response.json()'
                .then(data => {
                    console.log("Risposta dal server:", data);
                    // Verifica lo status della risposta
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        resetForm();
                    } else if (data.status === 'warning') {
                        showAlert('warning', data.message);
                        resetForm();
                        // Puoi aggiungere qui ulteriori azioni in base al tuo scenario di avviso
                    } else {
                        showAlert('danger', 'Si è verificato un errore durante l\'inserimento dell\'articolo.');
                        resetForm();
                    }
                })
                .catch(error => {
                    console.error("Errore durante la chiamata Ajax:", error);
                    console.error("Risposta completa del server:", error.responseText); // Aggiunto log per la risposta completa
                    showAlert('danger', 'Si è verificato un errore durante l\'inserimento dell\'articolo.');
                    resetForm();
                });
        }
        function showAlert(type, message) {
            // Crea l'elemento alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
            alertDiv.innerHTML = `
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                ${message}
            `;
            // Inserisci l'alert prima del form
            form.parentNode.insertBefore(alertDiv, form);
            // Imposta un timer per rimuovere l'alert dopo 2 secondi
            setTimeout(function () {
                alertDiv.remove();
            }, 10000);
        }
        generatePdfBtn.addEventListener("click", function () {
            // Ottieni il deposito selezionato
            const depositoSelezionato = "<?php echo $deposito_selezionato; ?>";
            // Costruisci l'URL per lo script PHP con il parametro del deposito
            const url = `inv_print_list.php?deposito=${encodeURIComponent(depositoSelezionato)}`;
            // Apri una nuova finestra o scheda del browser con l'URL
            window.open(url, '_blank');
        });
    });
</script>