<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/header.php';
try {
    // Connessione al database usando PDO
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'downloadReports' && isset($_POST['idripArray'])) {
            $idripArray = json_decode($_POST['idripArray'], true);
            if (!empty($idripArray)) {
                // Crea un oggetto TCPDF
                require_once('../../vendor/tcpdf/tcpdf.php');
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                // Itera attraverso gli IDRIP e crea una pagina per ciascuno
                $stmt = $pdo->prepare("SELECT * FROM riparazioni WHERE IDRIP = ?");
                foreach ($idripArray as $idrip) {
                    // Recupera i dati della riparazione dal database
                    $stmt->execute([$idrip]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Aggiungi una nuova pagina
                    $pdf->AddPage();
                    // Aggiungi il contenuto della pagina
                    // ...
                    // Puoi anche aggiungere un'intestazione o un piè di pagina per distinguere le pagine
                    $pdf->Cell(0, 10, 'Pagina ' . $pdf->getPage(), 0, 1, 'C');
                }
                // Output del PDF
                $pdf->Output('reports.pdf', 'D');
            }
        } elseif ($action === 'chiudi' && isset($_POST['idripArray'])) {
            chiudiRiparazioni($pdo, $_POST['idripArray']);
        }
    }
} catch (PDOException $e) {
    echo 'Errore di connessione: ' . $e->getMessage();
}
function chiudiRiparazioni($pdo, $idripArray)
{
    try {
        $idArray = json_decode($idripArray, true);
        if ($idArray !== null && !empty($idArray)) {
            $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
            $querySelect = "SELECT * FROM riparazioni WHERE IDRIP IN ($placeholders)";
            $queryInsert = "INSERT INTO rip_chiuse SELECT * FROM riparazioni WHERE IDRIP IN ($placeholders)";
            $queryDelete = "DELETE FROM riparazioni WHERE IDRIP IN ($placeholders)";
            // Inserisci gli IDRIP nella tabella rip_chiuse
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute($idArray);
            // Elimina le corrispondenti righe dalla tabella riparazioni
            $stmtDelete = $pdo->prepare($queryDelete);
            $stmtDelete->execute($idArray);
            echo 'Riparazioni chiuse con successo.';
        } else {
            echo 'Errore nella decodifica JSON degli IDRIP.';
        }
    } catch (PDOException $e) {
        echo 'Errore durante la chiusura delle riparazioni: ' . $e->getMessage();
    }
}
?>


<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Chiudi Più</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Operazioni Barcode</h6>
                        </div>
                        <div class="card-body">
                            <input type="text" id="idripInput" placeholder="Inserisci l'IDRIP e premi INVIO"
                                class="form-control">
                            <hr>
                            <div class="table-responsive">
                                <table id="risultatiTabella" class="table table-striped table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th width="2%"></th>
                                            <th width="10%">IDRIP</th>
                                            <th width="20%">LABORATORIO</th>
                                            <th width="20%">ARTICOLO</th>
                                            <th width="40%">DESCRIZIONE</th>
                                            <th width="10%">QTA</th>
                                            <!-- Aggiungi altre colonne secondo le tue necessità -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- I risultati della ricerca verranno aggiunti qui dinamicamente -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal fade" id="ripNotFoundModal" tabindex="-1" role="dialog"
                                aria-labelledby="ripNotFoundModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="ripNotFoundModalLabel">Riparazione non trovata
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            La riparazione con l'IDRIP inserito non è stata trovata. Riprova con un
                                            altro IDRIP.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-danger"
                                                data-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="ripChiuse" tabindex="-1" role="dialog"
                                aria-labelledby="ripChiuseLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="ripChiuseLabel">Operazione Eseguita</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Le riparazioni sono state chiuse con successo.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-success"
                                                data-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-success btn-lg btn-block"
                                        onclick="chiudiRiparazioni()">
                                        COMPLETA E CHIUDI <i class="fas fa-check-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.colReorder.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
<script>
    function chiudiRiparazioni() {
        var idripArray = [];
        // Ottieni gli IDRIP dalla tabella dei risultati
        var tabella = document.getElementById("risultatiTabella").getElementsByTagName('tbody')[0];
        var righe = tabella.getElementsByTagName('tr');
        for (var i = 0; i < righe.length; i++) {
            var idrip = righe[i].getElementsByTagName('td')[1].innerHTML;
            idripArray.push(idrip);
        }
        if (idripArray.length > 0) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4) {
                    clearTimeout(timeout);
                    if (this.status == 200) {
                        console.log('Risposta del server:', this.responseText);
                        $('#ripChiuse').modal('show');
                        svuotaTabella();
                    } else {
                        console.error('Errore durante la chiusura delle riparazioni. Codice di stato:', this.status);
                        alert('Errore durante la chiusura delle riparazioni. Codice di stato: ' + this.status);
                    }
                }
            };
            xhttp.open("POST", "", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            // Invia l'elenco degli IDRIP della tabella dei risultati
            xhttp.send("action=chiudi&idripArray=" + JSON.stringify(idripArray));
            var timeout = setTimeout(function () {
                xhttp.abort();
                console.error('Timeout della richiesta. Riprova più tardi.');
                alert('Timeout della richiesta. Riprova più tardi.');
            }, 5000);
        } else {
            alert('Nessuna riparazione da chiudere.');
        }
    }
    function cercaRiparazione() {
        // Ottieni il valore inserito nell'input
        var idrip = document.getElementById("idripInput").value;
        // Effettua la richiesta AJAX per cercare la riparazione
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                // Funzione chiamata quando la richiesta ha successo
                aggiungiRisultato(JSON.parse(this.responseText));
                document.getElementById("idripInput").value = '';
            }
        };
        xhttp.open("GET", "cerca_riparazione.php?idrip=" + idrip, true);
        xhttp.send();
    }
    function aggiungiRisultato(riparazione) {
        // Ottieni la tabella dei risultati
        var tabella = document.getElementById("risultatiTabella").getElementsByTagName('tbody')[0];
        // Verifica se la riparazione è valida
        if (riparazione.IDRIP !== undefined) {
            // Riparazione trovata, aggiungi una riga con i dati della riparazione
            var riga = tabella.insertRow();
            var cellaAzione = riga.insertCell(0); // Colonna per l'icona del cestino
            var cellaIdrip = riga.insertCell(1);
            var cellaLaboratorio = riga.insertCell(2);
            var cellaArticolo = riga.insertCell(3);
            var cellaDescrizione = riga.insertCell(4);
            var cellaQTA = riga.insertCell(5);
            // Aggiungi l'icona del cestino di Font Awesome
            var btnRimuovi = document.createElement("i");
            btnRimuovi.setAttribute("class", "fas fa-trash-alt");
            btnRimuovi.style.color = "red";
            btnRimuovi.style.cursor = "pointer";
            btnRimuovi.onclick = function () {
                rimuoviRiga(this);
            };
            cellaAzione.appendChild(btnRimuovi);
            cellaIdrip.innerHTML = riparazione.IDRIP;
            cellaLaboratorio.innerHTML = riparazione.LABORATORIO;
            cellaArticolo.innerHTML = riparazione.CODICE;
            cellaDescrizione.innerHTML = riparazione.ARTICOLO;
            cellaQTA.innerHTML = riparazione.QTA;
        } else {
            // Riparazione non trovata, mostra il modale
            $('#ripNotFoundModal').modal('show');
        }
        // Svuota il campo di ricerca
        document.getElementById("idripInput").value = '';
    }
    idripInput.addEventListener("keydown", function (event) {
        // Verifica se il tasto premuto è "Enter" (codice 13)
        if (event.keyCode === 13) {
            // Chiama la funzione cercaRiparazione()
            cercaRiparazione();
        }
    });
    function rimuoviRiga(iconaCestino) {
        var riga = iconaCestino.parentNode.parentNode;
        riga.parentNode.removeChild(riga);
    }
    function svuotaTabella() {
        var tabella = document.getElementById("risultatiTabella").getElementsByTagName('tbody')[0];
        tabella.innerHTML = ''; // Questa riga svuota il contenuto della tabella
    }
</script>