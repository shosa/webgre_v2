<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
$db = getDbInstance();
// Recupera la variabile cartellino dall'URL
$cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_UNSAFE_RAW);
$nomeLinea = filter_input(INPUT_POST, 'nomeLinea', FILTER_UNSAFE_RAW);
$new_testid = filter_input(INPUT_POST, 'new_testid', FILTER_UNSAFE_RAW);
$orario = filter_input(INPUT_POST, 'orario', FILTER_UNSAFE_RAW);
$data = filter_input(INPUT_POST, 'data', FILTER_UNSAFE_RAW);
$operatore = filter_input(INPUT_POST, 'operatore', FILTER_UNSAFE_RAW);
$descArticolo = filter_input(INPUT_POST, 'descArticolo', FILTER_UNSAFE_RAW);
$codArticolo = filter_input(INPUT_POST, 'codArticolo', FILTER_UNSAFE_RAW);
$new_testid = filter_input(INPUT_POST, 'new_testid', FILTER_UNSAFE_RAW);
$data = filter_input(INPUT_POST, 'data', FILTER_UNSAFE_RAW);
$orario = filter_input(INPUT_POST, 'orario', FILTER_UNSAFE_RAW);
$operatore = filter_input(INPUT_POST, 'operatore', FILTER_UNSAFE_RAW);
$siglaLinea = filter_input(INPUT_POST, 'siglaLinea', FILTER_UNSAFE_RAW);
$paia = filter_input(INPUT_POST, 'paia', FILTER_UNSAFE_RAW);
// Ottiene l'informazione dalla tabella 'dati'
$stmt = $db->prepare("SELECT * FROM dati WHERE Cartel = :cartellino");
$stmt->execute(['cartellino' => $cartellino]);
$informazione = $stmt->fetch(PDO::FETCH_ASSOC);
logActivity($_SESSION['user_id'], 'CQ', 'INIZIO', 'Test', 'Cartellino ' . $cartellino, '');
$cartellino = $informazione["Cartel"];
$commessa = $informazione["Commessa Cli"];
// Query per ottenere le opzioni per il menu a tendina 'CALZATA' dalla tabella 'id_numerate'
$calzateOptions = [];
if (!empty($informazione["Nu"])) {
    $stmt = $db->prepare("SELECT * FROM id_numerate WHERE id = :id");
    $stmt->execute(['id' => $informazione["Nu"]]);
    $idNumerate = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($idNumerate) {
        for ($j = 1; $j <= 20; $j++) {
            $field = 'N' . str_pad($j, 2, '0', STR_PAD_LEFT);
            if (!empty($idNumerate[$field])) {
                $calzateOptions[] = htmlspecialchars($idNumerate[$field], ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

// Query per ottenere le opzioni per i test dalla tabella 'cq_barcodes'
$testOptions = [];
$stmt = $db->query("SELECT test FROM cq_barcodes ORDER BY test ASC");
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($tests as $test) {
    $testOptions[] = htmlspecialchars($test['test'], ENT_QUOTES, 'UTF-8');
}

// Query per ottenere le opzioni per il menu a tendina 'Reparti' dalla tabella 'reparti'
$repartiOptions = [];
$stmt = $db->query("SELECT * FROM reparti");
$reparti = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($reparti as $reparto) {
    $repartiOptions[] = htmlspecialchars($reparto['Nome'], ENT_QUOTES, 'UTF-8');
}
// Includi l'header
require_once BASE_PATH . '/components/header.php';
?>
<style>
    /* Stili generali per touch */
    body {
        touch-action: manipulation;
    }
    
    /* Dimensioni più grandi per tutti i pulsanti e input */
    .btn, .form-control, select, textarea {
   
        line-height: 1.5;
        padding: 0.75rem 1rem;
    }
    
    .btn {
        min-height: 50px;
        border-radius: 8px;
    }
    
    /* Stile per la tabella principale */
    #test_table td {
        vertical-align: middle;
        padding: 15px;
    }
    
    /* Stili per i pulsanti di esito */
    .esito-btn {
        width: 60px;
        height: 60px;
        margin: 5px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .esito-btn i {
        font-size: 1.5rem;
    }
    
    .esito-btn.active {
        border-color: transparent !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 255, 0, 0.5), 0 0 0 0.2rem rgba(0, 0, 0, 0.125) !important;
    }
    
    .esito-btn.active[data-value="X"] {
        box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.5), 0 0 0 0.2rem rgba(0, 0, 0, 0.125) !important;
    }
    
    .esito-btn.active i {
        color: inherit !important;
    }
    
    .esito-btn.active[data-value="V"] i {
        color: green !important;
    }
    
    .esito-btn.active[data-value="X"] i {
        color: red !important;
    }
    
    /* Stili per i pulsanti nei modali */
    #calzataButtons .btn, #testButtons .btn {
        min-width: 70px;
        min-height: 50px;
        margin: 8px;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    /* Stile per i campi di input con i pulsanti */
    .input-group-field {
        display: flex;
        width: 100%;
    }
    
    .input-group-field .form-control {
        flex-grow: 1;
        border-radius: 8px 0 0 8px;
        height: 50px;
    }
    
    .input-group-field .btn {
        border-radius: 0 8px 8px 0;
        height: 50px;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Stile per il pulsante salva */
    .btn-lg {
        padding: 15px 30px;
        font-size: 1.25rem;
        border-radius: 10px;
        min-height: 60px;
    }
    
    /* Stile per i modali */
    .modal-content {
        border-radius: 12px;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    /* Stile per la visualizzazione dei badge */
    .badge {
        font-size: 1rem;
        padding: 8px 15px;
        margin: 5px;
        border-radius: 8px;
    }
    
    /* Pulsanti per il test più evidenti */
    .action-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 5px;
    }
    
    .action-btn i {
        font-size: 1.5rem;
    }
    
    /* Display visibile per il test selezionato */
    .test-display {
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        min-height: 50px;
        margin-bottom: 10px;
        font-weight: bold;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
    }
</style>

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../functions/quality/home">Home</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/quality/new">Sistema CQ</a></li>
                        <li class="breadcrumb-item"><a
                                href="../../functions/quality/add?cartellino=<?php echo $cartellino ?>">Controllo Dati
                            </a></li>
                        <li class="breadcrumb-item active">Test</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Nuovo Test #<?php echo $new_testid; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-center mb-4">
                                <h3 class="badge bg-warning text-white text-center" style="margin-right: 10px;">
                                    <?php echo $nomeLinea; ?>
                                </h3>
                                <h3 class="badge bg-light text-center" style="margin-right: 10px;">
                                    Cartellino: <?php echo $cartellino; ?>
                                </h3>
                                <h3 class="badge bg-light text-center">
                                    Commessa: <?php echo $commessa; ?>
                                </h3>
                                <h3 class="badge bg-primary text-white" style="margin-left: 10px;">
                                    <?php echo $data ?>
                                </h3>
                                <h3 class="badge bg-success text-white" style="margin-left: 10px;">
                                    <?php echo $orario ?>
                                </h3>
                                <h3 class="badge bg-danger text-white" style="margin-left: 10px;">
                                    <?php echo $operatore ?>
                                </h3>
                            </div>
                            <form action="process_save.php" method="post" id="test_form">
                                <div class="form-group row mb-4">
                                    <label for="reparto" class="col-sm-3 col-form-label">Filiera di Provenienza</label>
                                    <div class="col-sm-9">
                                        <select id="reparto" name="reparto" class="form-control" required>
                                            <option value="">Scegli...</option>
                                            <?php foreach ($repartiOptions as $reparto): ?>
                                                <option value="<?php echo $reparto; ?>"><?php echo $reparto; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="test_table">
                                        <thead>
                                            <tr>
                                                <th width="15%">CALZATA</th>
                                                <th width="35%">TEST</th>
                                                <th width="35%">ANNOTAZIONI</th>
                                                <th width="15%">ESITO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <tr data-row-id="<?php echo $i; ?>">
                                                    <td>
                                                        <div class="input-group-field">
                                                            <input type="text" name="calzata[]"
                                                                class="form-control calzata-input" readonly required>
                                                            <button type="button" class="btn btn-primary calzata-btn">
                                                                <i class="fas fa-shoe-prints"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="test-display"></div>
                                                        <div class="d-flex justify-content-center">
                                                            <button type="button" class="btn btn-primary action-btn test-btn" title="Seleziona test">
                                                                <i class="fas fa-list"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-info action-btn barcode-btn" title="Barcode">
                                                                <i class="fas fa-barcode"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-secondary action-btn pen-btn" title="Inserimento manuale">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                        </div>
                                                        <input type="hidden" name="test[]" class="test-input" style="text-transform:uppercase" required>
                                                    </td>
                                                    <td>
                                                        <textarea name="note[]" class="form-control" style="text-transform:uppercase"></textarea>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            <button type="button" class="btn btn-light esito-btn" data-value="V">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-light esito-btn" data-value="X">
                                                                <i class="fas fa-times-circle"></i>
                                                            </button>
                                                        </div>
                                                        <input type="hidden" name="esito[]" class="esito-input" required>
                                                        <input type="text" value="<?php echo $cartellino; ?>" name="cartellino" hidden>
                                                        <input type="text" value="<?php echo $commessa; ?>" name="commessa" hidden>
                                                        <input type="text" value="<?php echo $descArticolo; ?>" name="descArticolo" hidden>
                                                        <input type="text" value="<?php echo $codArticolo; ?>" name="codArticolo" hidden>
                                                        <input type="text" value="<?php echo $new_testid; ?>" name="new_testid" hidden>
                                                        <input type="text" value="<?php echo $data; ?>" name="data" hidden>
                                                        <input type="text" value="<?php echo $orario; ?>" name="orario" hidden>
                                                        <input type="text" value="<?php echo $operatore; ?>" name="operatore" hidden>
                                                        <input type="text" value="<?php echo $siglaLinea; ?>" name="siglaLinea" hidden>
                                                        <input type="text" value="<?php echo $paia; ?>" name="paia" hidden>
                                                    </td>
                                                    <input type="hidden" name="row_ids[]" class="row-id-input">
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-group text-center floating-button mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">Salva <i class="fas fa-save"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Modal Barcode -->
                        <div class="modal fade" id="barcodeModal" tabindex="-1" role="dialog"
                            aria-labelledby="barcodeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="barcodeModalLabel">Sparare il codice del test eseguito</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="text" id="barcodeInput" class="form-control form-control-lg"
                                            placeholder="Sparare il codice qui" autofocus>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-lg"
                                            data-dismiss="modal">Chiudi</button>
                                        <button type="button" class="btn btn-primary btn-lg" id="saveBarcode">Salva</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Calzata -->
                        <div class="modal fade" id="calzataModal" tabindex="-1" role="dialog"
                            aria-labelledby="calzataModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="calzataModalLabel">Seleziona Calzata</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex flex-wrap justify-content-center" id="calzataButtons">
                                            <!-- I pulsanti verranno generati dinamicamente con JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Test -->
                        <div class="modal fade" id="testModal" tabindex="-1" role="dialog"
                            aria-labelledby="testModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="testModalLabel">Seleziona Test</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="text" id="testSearch" class="form-control form-control-lg mb-3" 
                                               placeholder="Cerca test..." autocomplete="off">
                                        <div class="d-flex flex-wrap justify-content-center" id="testButtons">
                                            <!-- I pulsanti verranno generati dinamicamente con JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php include_once BASE_PATH . '/components/scripts.php'; ?>
                        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
                        <!-- Includi Bootstrap JS -->
                        <script
                            src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                // Variabili per tenere traccia degli elementi correnti
                                var currentInput, currentTestDisplay, currentRow;
                                var timeoutId;

                                // Ottieni le opzioni delle calzate e dei test dal PHP
                                var calzateOptions = <?php echo json_encode($calzateOptions); ?>;
                                var testOptions = <?php echo json_encode($testOptions); ?>;
                                
                                // Gestione pulsanti calzata
                                document.querySelectorAll('.calzata-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        // Memorizza l'input corrente
                                        currentInput = this.closest('.input-group-field').querySelector('.calzata-input');
                                        currentRow = currentInput.closest('tr');
                                        
                                        // Genera i pulsanti delle calzate
                                        var buttonsContainer = document.getElementById('calzataButtons');
                                        buttonsContainer.innerHTML = '';
                                        
                                        calzateOptions.forEach(function (option) {
                                            var button = document.createElement('button');
                                            button.type = 'button';
                                            button.className = 'btn btn-outline-primary';
                                            button.textContent = option;
                                            button.addEventListener('click', function () {
                                                currentInput.value = option;
                                                
                                                // Abilita gli elementi nella riga corrente
                                                currentRow.querySelectorAll('input, button, textarea').forEach(function (element) {
                                                    element.disabled = false;
                                                });
                                                
                                                // Chiudi il modal
                                                $('#calzataModal').modal('hide');
                                            });
                                            
                                            buttonsContainer.appendChild(button);
                                        });
                                        
                                        // Mostra il modal
                                        $('#calzataModal').modal('show');
                                    });
                                });
                                
                                // Gestione pulsanti test
                                document.querySelectorAll('.test-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        // Memorizza l'input corrente e il display
                                        currentInput = this.closest('td').querySelector('.test-input');
                                        currentTestDisplay = this.closest('td').querySelector('.test-display');
                                        
                                        // Genera i pulsanti dei test
                                        renderTestButtons(testOptions);
                                        
                                        // Mostra il modal
                                        $('#testModal').modal('show');
                                    });
                                });
                                
                                // Funzione per renderizzare i pulsanti dei test con filtro
                                function renderTestButtons(options, filter = '') {
                                    var buttonsContainer = document.getElementById('testButtons');
                                    buttonsContainer.innerHTML = '';
                                    
                                    // Filtra le opzioni se necessario
                                    var filteredOptions = filter 
                                        ? options.filter(option => option.toLowerCase().includes(filter.toLowerCase())) 
                                        : options;
                                    
                                    // Mostra messaggio se non ci sono risultati
                                    if (filteredOptions.length === 0) {
                                        var noResults = document.createElement('div');
                                        noResults.className = 'alert alert-info w-100 text-center';
                                        noResults.textContent = 'Nessun test trovato';
                                        buttonsContainer.appendChild(noResults);
                                        return;
                                    }
                                    
                                    // Crea i pulsanti per ogni opzione
                                    filteredOptions.forEach(function (option) {
                                        var button = document.createElement('button');
                                        button.type = 'button';
                                        button.className = 'btn btn-outline-primary';
                                        button.textContent = option;
                                        button.addEventListener('click', function () {
                                            currentInput.value = option;
                                            currentTestDisplay.textContent = option;
                                            
                                            // Chiudi il modal
                                            $('#testModal').modal('hide');
                                        });
                                        
                                        buttonsContainer.appendChild(button);
                                    });
                                }
                                
                                // Campo di ricerca per i test
                                document.getElementById('testSearch').addEventListener('input', function() {
                                    renderTestButtons(testOptions, this.value.trim());
                                });
                                
                                // Reset della ricerca quando il modal viene mostrato
                                $('#testModal').on('shown.bs.modal', function () {
                                    document.getElementById('testSearch').value = '';
                                    document.getElementById('testSearch').focus();
                                    renderTestButtons(testOptions);
                                });
                                
                                // Gestione pulsanti barcode
                                document.querySelectorAll('.barcode-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        currentInput = this.closest('td').querySelector('.test-input');
                                        currentTestDisplay = this.closest('td').querySelector('.test-display');
                                        $('#barcodeModal').modal('show');
                                    });
                                });
                                
                                $('#barcodeModal').on('shown.bs.modal', function () {
                                    $('#barcodeInput').focus();
                                    $('#barcodeInput').val('');
                                });
                                
                                document.getElementById('barcodeInput').addEventListener('input', function () {
                                    clearTimeout(timeoutId); // Cancella il timeout precedente se presente
                                    var barcodeValue = this.value.trim();
                                    if (barcodeValue && currentInput) {
                                        timeoutId = setTimeout(function () {
                                            var url = 'process_barcode.php?barcode=' + encodeURIComponent(barcodeValue);
                                            fetch(url)
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        currentInput.value = data.test;
                                                        currentTestDisplay.textContent = data.test;
                                                        $('#barcodeModal').modal('hide');
                                                        // Svuota il contenuto del campo di testo dopo il salvataggio
                                                        document.getElementById('barcodeInput').value = '';
                                                    } else {
                                                        alert(data.message);
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Errore:', error);
                                                    alert('Si è verificato un errore durante il recupero del test.');
                                                });
                                        }, 750); // Ritardo di 750 millisecondi
                                    }
                                });
                                
                                // Pulsante salva barcode
                                document.getElementById('saveBarcode').addEventListener('click', function() {
                                    var barcodeValue = document.getElementById('barcodeInput').value.trim();
                                    if (barcodeValue) {
                                        var event = new Event('input');
                                        document.getElementById('barcodeInput').dispatchEvent(event);
                                    } else {
                                        alert('Inserisci un codice a barre valido');
                                    }
                                });
                                
                                // Gestione pulsanti penna
                                document.querySelectorAll('.pen-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        var input = prompt("Inserisci manualmente il test:");
                                        if (input) {
                                            input = input.toUpperCase(); // Trasforma il testo in maiuscolo
                                            var testInput = this.closest('td').querySelector('.test-input');
                                            var testDisplay = this.closest('td').querySelector('.test-display');
                                            testInput.value = input;
                                            testDisplay.textContent = input;
                                        }
                                    });
                                });

                                // Gestione pulsanti esito
                                function handleEsitoButtonClick() {
                                    // Rimuove la classe attiva da tutti i pulsanti nella stessa cella
                                    var esitoButtons = this.closest('td').querySelectorAll('.esito-btn');
                                    esitoButtons.forEach(function (btn) {
                                        btn.classList.remove('active');
                                    });
                                    // Aggiunge la classe attiva al pulsante cliccato
                                    this.classList.add('active');
                                    // Imposta il valore dell'esito nell'input nascosto
                                    var esitoInput = this.closest('td').querySelector('.esito-input');
                                    esitoInput.value = this.getAttribute('data-value');
                                }

                                // Aggiunge gli event listener ai pulsanti esito
                                document.querySelectorAll('#test_table .esito-btn').forEach(function (button) {
                                    button.addEventListener('click', handleEsitoButtonClick);
                                });

                                // Disabilita tutti gli elementi del form tranne i pulsanti "Salva" e "Calzata"
                                document.querySelectorAll('#test_form input:not([type="hidden"]), #test_form button:not(.calzata-btn):not([type="submit"]), #test_form textarea, #test_form select').forEach(function (element) {
                                    element.disabled = true;
                                });

                                // Abilita i pulsanti "Salva" e i pulsanti della calzata
                                document.querySelectorAll('#test_form button[type="submit"], .calzata-btn, #reparto').forEach(function (button) {
                                    button.disabled = false;
                                });

                                // Rende più touch-friendly la selezione dei reparti
                                document.getElementById('reparto').addEventListener('click', function() {
                                    // I device touch spesso richiedono un click per aprire un select
                                    this.focus();
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>