<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

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
    #test_table td {
        vertical-align: middle;
    }

    .esito-btn.active {
        border-color: transparent !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 255, 0, 0.5), 0 0 0 0.2rem rgba(0, 0, 0, 0.125) !important;
        /* Verde per V */
    }

    .esito-btn.active[data-value="X"] {
        box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.5), 0 0 0 0.2rem rgba(0, 0, 0, 0.125) !important;
        /* Rosso per X */
    }

    .esito-btn.active i {
        color: inherit !important;
    }

    .esito-btn.active[data-value="V"] i {
        color: green !important;
        /* Colore verde per V */
    }

    .esito-btn.active[data-value="X"] i {
        color: red !important;
        /* Colore rosso per X */
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
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
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
                            <div class="d-flex flex-wrap align-items-center justify-content-center">
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
                                <div class="form-group row">
                                    <label for="reparto" class="col-sm-2 col-form-label">Filiera di Provenienza</label>
                                    <div class="col-sm-10">
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
                                                <th width="10%">CALZATA</th>
                                                <th width="40%">TEST</th>
                                                <th width="40%">ANNOTAZIONI</th>
                                                <th width="10%">ESITO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <tr data-row-id="<?php echo $i; ?>">

                                                    <td>
                                                        <select name="calzata[]" class="form-control">
                                                            <option value=""></option>
                                                            <?php foreach ($calzateOptions as $option): ?>
                                                                <option value="<?php echo $option; ?>"><?php echo $option; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary barcode-btn">
                                                            <i class="fas fa-barcode"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-secondary pen-btn">
                                                            <i class="fas fa-pen"></i>
                                                        </button>
                                                        <input type="hidden" name="test[]" class="test-input"
                                                            style="text-transform:uppercase" required>
                                                    </td>
                                                    <td>
                                                        <textarea name="note[]" class="form-control"
                                                            style="text-transform:uppercase"></textarea>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-light esito-btn"
                                                            data-value="V">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-light esito-btn"
                                                            data-value="X">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                        <input type="hidden" name="esito[]" class="esito-input" required>
                                                        <input type="text" value="<?php echo $cartellino; ?>"
                                                            name="cartellino" hidden>
                                                        <input type="text" value="<?php echo $commessa; ?>" name="commessa"
                                                            hidden>
                                                        <input type="text" value="<?php echo $descArticolo; ?>"
                                                            name="descArticolo" hidden>
                                                        <input type="text" value="<?php echo $codArticolo; ?>"
                                                            name="codArticolo" hidden>
                                                        <input type="text" value="<?php echo $new_testid; ?>"
                                                            name="new_testid" hidden>
                                                        <input type="text" value="<?php echo $data; ?>" name="data" hidden>
                                                        <input type="text" value="<?php echo $orario; ?>" name="orario"
                                                            hidden>
                                                        <input type="text" value="<?php echo $operatore; ?>"
                                                            name="operatore" hidden>
                                                        <input type="text" value="<?php echo $siglaLinea; ?>"
                                                            name="siglaLinea" hidden>
                                                        <input type="text" value="<?php echo $paia; ?>" name="paia" hidden>
                                                    </td>
                                                    <input type="hidden" name="row_ids[]" class="row-id-input">
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-group text-center floating-button">
                                    <button type="submit" class="btn btn-primary btn-lg">Salva <i
                                            class="fad fa-save"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="barcodeModal" tabindex="-1" role="dialog"
                            aria-labelledby="barcodeModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="barcodeModalLabel">Sparare il codice del test
                                            eseguito
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="text" id="barcodeInput" class="form-control"
                                            placeholder="Sparare il codice qui">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Chiudi</button>
                                        <button type="button" class="btn btn-primary" id="saveBarcode">Salva</button>
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
                                var currentInput;
                                var timeoutId;

                                document.querySelectorAll('.barcode-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        currentInput = this.nextElementSibling.nextElementSibling;
                                        $('#barcodeModal').modal('show');
                                    });
                                });

                                $('#barcodeModal').on('shown.bs.modal', function () {
                                    $('#barcodeInput').focus();
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
                                                        currentInput.previousElementSibling.previousElementSibling.textContent = data.test;
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
                                        }, 750); // Ritardo di 1 secondo (1000 millisecondi)
                                    } else {
                                        alert('Inserisci un codice a barre valido');
                                    }
                                });

                                document.querySelectorAll('.pen-btn').forEach(function (button) {
                                    button.addEventListener('click', function () {
                                        var input = prompt("Inserisci manualmente il test:");
                                        if (input) {
                                            input = input.toUpperCase(); // Trasforma il testo in maiuscolo
                                            var testInput = this.nextElementSibling;
                                            testInput.value = input;
                                            this.previousElementSibling.textContent = input;
                                        }
                                    });
                                });
                            });

                            function handleEsitoButtonClick() {
                                // Rimuove la classe attiva da tutti i pulsanti
                                var esitoButtons = this.parentNode.querySelectorAll('.esito-btn');
                                esitoButtons.forEach(function (btn) {
                                    btn.classList.remove('active');
                                });

                                // Aggiunge la classe attiva al pulsante cliccato
                                this.classList.add('active');

                                // Imposta il valore dell'esito nell'input nascosto
                                var esitoInput = this.parentNode.querySelector('.esito-input');
                                esitoInput.value = this.getAttribute('data-value');
                            }

                            document.addEventListener('DOMContentLoaded', function () {
                                // Abilita gli eventi sui pulsanti esito nella riga iniziale
                                var esitoButtons = document.querySelectorAll('#test_table .esito-btn');
                                esitoButtons.forEach(function (button) {
                                    button.addEventListener('click', handleEsitoButtonClick);
                                });

                                // Disabilita tutti gli elementi del form tranne i pulsanti "Aggiungi Riga" e "Salva"
                                document.querySelectorAll('#test_form input, #test_form button ,#test_form textarea').forEach(function (element) {
                                    element.disabled = true;
                                });

                                // Abilita i pulsanti "Aggiungi Riga" e "Salva"
                                document.querySelectorAll('#add_row, #test_form button[type="submit"]').forEach(function (button) {
                                    button.disabled = false;
                                });

                                document.querySelectorAll('#test_form select[name="calzata[]"]').forEach(function (select) {
                                    select.addEventListener('change', function () {
                                        var selectedOption = this.value;
                                        var currentRow = this.closest('tr');

                                        // Abilita gli elementi del form nella riga corrente solo se la calzata è stata selezionata
                                        if (selectedOption) {
                                            currentRow.querySelectorAll('input,button, textarea').forEach(function (element) {
                                                element.disabled = false;
                                            });
                                        } else {
                                            // Se non viene selezionata alcuna calzata, disabilita gli elementi del form nella riga corrente
                                            currentRow.querySelectorAll('input,button, textarea').forEach(function (element) {
                                                element.disabled = true;
                                            });
                                        }
                                    });
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