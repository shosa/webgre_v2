<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$edit = false;

require_once BASE_PATH . '/includes/header.php';

// Connessione al database
$db = getDbInstance();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'downloadReports') {

    if ($idripArray !== null && !empty($idripArray)) {
        // Crea un oggetto TCPDF
        require_once('../../assets/tcpdf/tcpdf.php');
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Itera attraverso gli IDRIP e crea una pagina per ciascuno
        foreach ($idripArray as $idrip) {
            // Recupera i dati della riparazione dal database
            $sql = "SELECT * FROM riparazioni WHERE IDRIP = $idrip";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            // Aggiungi una nuova pagina
            $pdf->AddPage();

            // Aggiungi il contenuto della pagina (simile a quanto hai fatto per il singolo PDF)
            // ...

            // Puoi anche aggiungere un'intestazione o un piè di pagina per distinguere le pagine
            $pdf->Cell(0, 10, 'Pagina ' . $pdf->getPage(), 0, 1, 'C');
        }

        // Output del PDF
        $pdf->Output('reports.pdf', 'D');
    }
}

// Verifica se è stata inviata una richiesta POST per la chiusura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'chiudi') {
    chiudiRiparazioni($_POST['idripArray']);
}

function chiudiRiparazioni($idripArray)
{
    global $db;

    if ($idripArray) {
        // Decodifica la stringa JSON in un array PHP
        $idArray = json_decode($idripArray, true);

        // Verifica se la decodifica è riuscita correttamente
        if ($idArray !== null) {
            // Inserisci gli IDRIP nella tabella rip_chiuse
            $db->where('IDRIP', $idArray, 'IN');
            $riparazioni = $db->get('riparazioni');

            if ($riparazioni) {
                $db->insertMulti('rip_chiuse', $riparazioni);
            }

            // Elimina le corrispondenti righe dalla tabella riparazioni
            $db->where('IDRIP', $idArray, 'IN');
            $db->delete('riparazioni');
        } else {
            // Errore nella decodifica JSON
            echo 'Errore nella decodifica JSON degli IDRIP.';
        }
    }
}
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Operazioni Barcode</h2>
        </div>
    </div>
    <hr>
    <input type="text" id="idripInput" placeholder="Inserisci l'IDRIP e premi INVIO" class="form-control">
    <hr style=" display: block;
  height: 1px;
  border: 0;
  border-top: 1px solid #0d6efd;
  margin: 1em 0;
  padding: 0;">
    <hr style=" display: block;
  height: 1px;
  border: 0;
  border-top: 1px solid #0d6efd;
  margin: 1em 0;
  padding: 0;">
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
    <div class="modal fade" id="ripNotFoundModal" tabindex="-1" role="dialog" aria-labelledby="ripNotFoundModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ripNotFoundModalLabel">Riparazione non trovata</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    La riparazione con l'IDRIP inserito non è stata trovata. Riprova con un altro IDRIP.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-danger" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ripChiuse" tabindex="-1" role="dialog" aria-labelledby="ripChiuseLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
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
                    <button type="button" class="btn btn-secondary btn-success" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-6">
            <button type="button" class="btn btn-success btn-lg" onclick="chiudiRiparazioni()">
                COMPLETA E CHIUDI <i class="fas fa-check-circle"></i>
            </button>
        </div>
    </div>
</div>
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

<?php include_once BASE_PATH . '/includes/footer.php'; ?>