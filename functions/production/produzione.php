<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
try {
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $month = $_GET['month'];
    $day = $_GET['day'];
    $sql = "SELECT * FROM prod_mesi WHERE MESE = :month AND GIORNO = :day";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':month', $month);
    $stmt->bindParam(':day', $day);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $MANOVIA1 = $row['MANOVIA1'];
        $MANOVIA1NOTE = $row['MANOVIA1NOTE'];
        $MANOVIA2 = $row['MANOVIA2'];
        $MANOVIA2NOTE = $row['MANOVIA2NOTE'];
        $MANOVIA3 = $row['MANOVIA3'];
        $MANOVIA3NOTE = $row['MANOVIA3NOTE'];
        $ORLATURA1 = $row['ORLATURA1'];
        $ORLATURA1NOTE = $row['ORLATURA1NOTE'];
        $ORLATURA2 = $row['ORLATURA2'];
        $ORLATURA2NOTE = $row['ORLATURA2NOTE'];
        $ORLATURA3 = $row['ORLATURA3'];
        $ORLATURA3NOTE = $row['ORLATURA3NOTE'];
        $ORLATURA4 = $row['ORLATURA4'];
        $ORLATURA4NOTE = $row['ORLATURA4NOTE'];
        $TAGLIO1 = $row['TAGLIO1'];
        $TAGLIO1NOTE = $row['TAGLIO1NOTE'];
        $TAGLIO2 = $row['TAGLIO2'];
        $TAGLIO2NOTE = $row['TAGLIO2NOTE'];
    } else {
        echo "Nessun risultato trovato per il mese $month e il giorno $day";
    }
} catch (PDOException $e) {
    echo "Connessione al database fallita: " . $e->getMessage();
}
try {
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Recupero dei dati per sped_mesi
    $sql_sped = "SELECT * FROM sped_mesi WHERE MESE = :month AND GIORNO = :day";
    $stmt_sped = $conn->prepare($sql_sped);
    $stmt_sped->bindParam(':month', $month);
    $stmt_sped->bindParam(':day', $day);
    $stmt_sped->execute();
    $rows_sped = $stmt_sped->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows_sped)) {
        $sped_data_js = '[]';
    } else {
        $sped_data_js = json_encode($rows_sped);
    }
} catch (PDOException $e) {
    echo "Connessione al database fallita: " . $e->getMessage();
}
include (BASE_PATH . "/components/header.php");
?>
<script>
    function generatePDF(month, day) {
        window.location.href = `generate_pdf.php?month=${month}&day=${day}`;
    }
</script>
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
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="calendario">Calendario</a></li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo $day; ?> <?php echo $month; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-container">
                                        <table class="table table-bordered table-striped table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>MANOVIA 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA1NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>MANOVIA 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA2NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>MANOVIA 3</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA3; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA3NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ORLATURA 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA1NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ORLATURA 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA2NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ORLATURA 3</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA3; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA3NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ORLATURA 4</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA4; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA4NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>TAGLIO 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $TAGLIO1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $TAGLIO1NOTE; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>TAGLIO 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $TAGLIO2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $TAGLIO2NOTE; ?>
                                                    </td>
                                                </tr>
                                                <!-- Ripeti per le altre attività -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- MODALE MAIL -->
                        <?php
                        require_once '../../config/config.php';
                        // Recupera gli indirizzi email dei destinatari dalla tabella 'settings'
                        $recipientSettings = $conn->query("SELECT value FROM settings WHERE item = 'production_recipients'")->fetchColumn();
                        ?>
                        <!-- Modale per comporre l'email -->
                        <div class="modal fade" id="emailModal" tabindex="-1" role="dialog"
                            aria-labelledby="emailModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="emailModalLabel">E-mail Produzione</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="emailForm">
                                            <div class="form-group">
                                                <label for="to">Destinatari</label>
                                                <input type="text" class="form-control" id="to"
                                                    value="<?= $recipientSettings ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="subject">Oggetto</label>
                                                <input type="text" class="form-control" id="subject"
                                                    value="PRODUZIONE DEL <?php echo $day; ?> <?php echo $month; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="body">Corpo del messaggio</label>
                                                <textarea class="form-control" id="body" rows="4"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <input class="form-control" id="month" value=<?php echo $month ?>
                                                    hidden></input>
                                            </div>
                                            <div class="form-group">
                                                <input class="form-control" id="day" value=<?php echo $day ?>
                                                    hidden></input>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-block"
                                                id="sendEmailButton">Invia</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog"
                            aria-labelledby="loadingModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-body text-center">
                                        <div id="loadingIcon">
                                            <!-- Aggiungi qui l'icona per mostrare l'esito dell'operazione -->
                                        </div>
                                        <p id="loadingMessage">Invio in corso</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FINE MODALE MAIL -->
                        <!-- INIZIO MODALE SPEDIZIONE -->
                        <div class="modal fade" id="spedModal" tabindex="-1" role="dialog"
                            aria-labelledby="spedModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="spedModalLabel">Dati Spedizioni</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-container">
                                            <table class="table table-bordered table-striped table-sm" id="spedTable">
                                                <thead>
                                                    <tr>
                                                        <th>MAN1</th>
                                                        <th>MAN1 RESO</th>
                                                        <th>MAN2</th>
                                                        <th>MAN3</th>
                                                        <th>ORL1</th>
                                                        <th>ORL2</th>
                                                        <th>ORL3</th>
                                                        <th>ORL4</th>
                                                        <th>TOM ESTERO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Qui verranno inseriti i dati di sped_mesi -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FINE MODALE SPEDIZIONE -->
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Strumenti</h6>
                                </div>
                                <div class="card-body text-center">
                                    <button id="pdfButton" class="btn btn-danger btn-lg btn-block shadow"
                                        onclick='generatePDF("<?php echo $month; ?>", "<?php echo $day; ?>")'>
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                    <button id="sendEmailModalButton" class="btn btn-primary btn-lg btn-block shadow">
                                        <i class="fas fa-share-square"></i> INVIA MAIL
                                    </button>
                                    <button id="spedModalButton" class="btn btn-warning btn-lg btn-block shadow">
                                        <i class="fas fa-truck-fast"></i> VEDI SPEDIZIONE
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?php echo BASE_URL?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <!-- Core plugin JavaScript-->
            <script src="<?php echo BASE_URL?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <!-- Custom scripts for all pages-->
            <script src="<?php echo BASE_URL?>/js/sb-admin-2.min.js"></script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
<script>
    $(document).ready(function () {
        // Apri il modale quando si clicca sul pulsante "INVIA MAIL"
        $('#sendEmailModalButton').click(function () {
            $('#emailModal').modal('show');
        });
        // Gestisci l'invio dell'email
        $('#sendEmailButton').click(function () {
            var to = $('#to').val();
            var cc = $('#cc').val();
            var subject = $('#subject').val();
            var body = $('#body').val();
            var month = $('#month').val();
            var day = $('#day').val();
            // Apri il modale di caricamento
            $('#loadingModal').modal('show');
            // Aggiorna l'icona nel modale di caricamento
            $('#loadingIcon').html('<i class="fas fa-spinner fa-spin fa-3x"></i>');
            $.ajax({
                url: 'send_email.php',
                type: 'POST',
                data: {
                    to: to,
                    cc: cc,
                    subject: subject,
                    body: body,
                    month: month,
                    day: day
                },
                success: function (response) {
                    // Aggiorna l'icona a una spunta verde
                    $('#loadingIcon').html('<i class="fas fa-check-circle text-success fa-3x"></i>');
                    $('#loadingMessage').text('Email inviata con successo!');
                    setTimeout(function () {
                        $('#loadingModal').modal('hide');
                        $('#emailModal').modal('hide');
                    }, 2000); // Nasconde il modale dopo 2 secondi
                },
                error: function (xhr, status, error) {
                    // Aggiorna l'icona a una X rossa
                    $('#loadingIcon').html('<i class="fas fa-times-circle text-danger fa-3x"></i>');
                    $('#loadingMessage').text('Errore nell\'invio dell\'email: ' + error);
                    setTimeout(function () {
                        $('#loadingModal').modal('hide');
                        $('#emailModal').modal('hide');
                    }, 2000); // Nasconde il modale dopo 2 secondi
                }
            });
        });
        $(document).ready(function () {
            // Funzione per verificare se tutti i valori sono vuoti o 0
            function areAllValuesEmpty() {
                var values = [
                    '<?php echo $MANOVIA1; ?>',
                    '<?php echo $MANOVIA2; ?>',
                    '<?php echo $MANOVIA3; ?>',
                    '<?php echo $ORLATURA1; ?>',
                    '<?php echo $ORLATURA2; ?>',
                    '<?php echo $ORLATURA3; ?>',
                    '<?php echo $TAGLIO1; ?>',
                    '<?php echo $TAGLIO2; ?>'
                ];
                // Itera sui valori e controlla se sono vuoti o 0
                for (var i = 0; i < values.length; i++) {
                    if (values[i] !== "" && values[i] !== "0") {
                        return false;
                    }
                }
                return true;
            }
            // Controlla se tutti i valori sono vuoti o 0 e disabilita i pulsanti se necessario
            if (areAllValuesEmpty()) {
                // Disabilita i pulsanti PDF e INVIA MAIL
                $('#pdfButton, #sendEmailModalButton').prop('disabled', true);
            }
        });
        $('#spedModalButton').click(function () {
            $('#spedModal').modal('show');
            populateSpedTable();
        });
        // Funzione per popolare la tabella di sped_mesi
        function populateSpedTable() {
            var spedData = <?php echo $sped_data_js; ?>;
            var tableBody = $('#spedTable tbody');
            tableBody.empty(); // Svuota la tabella prima di popolarla
            spedData.forEach(function (row) {
                var tr = $('<tr>');
                tr.append($('<td>').text(row.MANOVIA1));
                tr.append($('<td>').text(row.MANOVIA1RESO));
                tr.append($('<td>').text(row.MANOVIA2));
                tr.append($('<td>').text(row.MANOVIA3));
                tr.append($('<td>').text(row.ORLATURA1));
                tr.append($('<td>').text(row.ORLATURA2));
                tr.append($('<td>').text(row.ORLATURA3));
                tr.append($('<td>').text(row.ORLATURA4));
                tr.append($('<td>').text(row.TOMESTERO));
                tableBody.append(tr);
            });
        }
    });
</script>