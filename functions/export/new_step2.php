<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

$edit = false;

require_once BASE_PATH . '/components/header.php';
?>
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    .drop-zone {
        width: 100%;
        height: 150px;
        border: 2px dashed #007BFF;
        background-color: #E6F2FF;
        text-align: center;
        padding: 40px 0;
        cursor: pointer;
        position: relative;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .drop-zone:hover {
        background-color: #d1e7ff;
        border-color: #0056b3;
    }

    .drop-icon {
        font-size: 48px;
        color: #007BFF;
        margin-bottom: 10px;
    }

    #file-list {
        list-style-type: none;
        padding: 0;
    }

    #file-list li {
        padding: 12px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    
    #file-list li:hover {
        background-color: #e9ecef;
    }
    
    .processed-check {
        color: green;
        font-weight: bold;
        margin-left: 5px;
    }
    
    .btn-primary {
        background-color: #007BFF;
        border-color: #007BFF;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-danger {
        border-radius: 6px;
    }
    
    .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
        background-color: #f0f8ff;
    }
    
    .modal-footer {
        border-radius: 0 0 12px 12px;
    }
    
    /* Responsive tables */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .info-box {
        background-color: #f0e8fc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .breadcrumb {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 12px 20px;
    }
    
    /* Smartphone e tablet */
    @media (max-width: 991.98px) {
        .table th, .table td {
            white-space: nowrap;
            font-size: 14px;
        }
        
        .drop-zone {
            height: 120px;
            padding: 30px 0;
        }
        
        h4 {
            font-size: 18px;
            margin-top: 15px;
        }
        
        .modal-dialog {
            margin: 10px;
            max-width: 100%;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                <h1 class="h3 mb-0 text-gray-800">
                                    <span class="text-primary">STEP 2</span> > Caricamento
                                    Schede DDT n°
                                    <?php echo $_GET['progressivo']; ?>
                                </h1>
                            </div>

                            <ol class="breadcrumb mb-4">
                                <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="documenti.php">Registro DDT</a></li>
                                <li class="breadcrumb-item active">Nuovo DDT</li>
                            </ol>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Istruzioni -->
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle mr-2"></i> Carica le schede tecniche trascinandole nell'area sottostante o cliccando su di essa per selezionarle.
                            </div>

                            <!-- Drag and Drop Area -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="drop-zone" id="drop-zone">
                                        <i class="fas fa-file-excel mb-2" style="color:#007BFF;font-size:28pt;"></i>
                                        <p>Trascina qui le schede tecniche in formato Excel<br>o clicca per selezionarle</p>
                                    </div>
                                </div>
                            </div>

                            <!-- File List -->
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">File caricati</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul id="file-list" class="mb-0"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-lg-12 text-right">
                                    <!-- Pulsante "Avanti" allineato a destra -->
                                    <button type="button" class="btn btn-primary btn-lg float-end"
                                        onclick="navigateToStep3()">
                                        Avanti <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Excel Content Modal -->
                    <div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="excelModalLabel">
                                        <i class="fas fa-file-excel mr-2"></i> Contenuto Excel
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div id="modello" class="info-box">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="opzioni" class="info-box">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h4>
                                            <i class="fas fa-cut mr-2"></i> TAGLIO
                                        </h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" id="excelTableTaglio"></table>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <h4>
                                            <i class="fas fa-ruler mr-2"></i> ORLATURA
                                        </h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" id="excelTableOrlatura"></table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                                    <button type="button" class="btn btn-primary btn-save" onclick="saveToExcel()">
                                        <i class="fas fa-save mr-2"></i> SALVA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
</body>
<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>
<script>
    function navigateToStep3() {
        let progressivo = "<?php echo $_GET['progressivo']; ?>";
        window.location.href = `new_step3.php?progressivo=${progressivo}`;
    }

    function deleteRow(button) {
        let row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        updateTotals();
    }

    document.addEventListener('DOMContentLoaded', function () {
        let dropZone = document.getElementById('drop-zone');
        let fileList = document.getElementById('file-list');
        // Set per tracciare i file già processati
        let processedFiles = new Set();

        // Aggiungiamo un input file nascosto
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.multiple = true;
        fileInput.accept = '.xlsx';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        // Evento click sulla drop zone per aprire il selettore di file
        dropZone.addEventListener('click', function () {
            fileInput.click();
        });

        // Gestione quando i file vengono selezionati tramite il selettore
        fileInput.addEventListener('change', function () {
            handleFiles(this.files);
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Effetti visivi per il drag and drop
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropZone.style.borderColor = '#0056b3';
            dropZone.style.backgroundColor = '#cce5ff';
        }

        function unhighlight() {
            dropZone.style.borderColor = '#007BFF';
            dropZone.style.backgroundColor = '#E6F2FF';
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            ([...files]).forEach(file => {
                if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    Swal.fire({
                        title: 'Formato non valido',
                        text: 'Carica solo file Excel (.xlsx)',
                        icon: 'error'
                    });
                    return;
                }

                let formData = new FormData();
                formData.append('file', file);

                fetch('upload.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let fileId = `file-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
                            fileList.innerHTML += `
                            <li id="${fileId}">
                                <div>
                                    <i class="fas fa-file-excel text-success mr-2"></i>
                                    ${file.name}
                                    <span class="processed-check" style="display:none;"><i class="fas fa-check-circle"></i></span>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="showExcelContent('${file.name}', '${fileId}')">
                                    <i class="fas fa-eye mr-1"></i> Visualizza
                                </button>
                            </li>`;
                        } else {
                            Swal.fire({
                                title: 'Errore',
                                text: data.error,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Errore',
                            text: 'Si è verificato un errore durante il caricamento del file',
                            icon: 'error'
                        });
                    });
            });
        }

        window.showExcelContent = function (fileName, fileId) {
            fetchExcelContent(fileName, fileId);
        }

        function fetchExcelContent(fileName, fileId) {
            fetch(`process-excel.php?fileName=${fileName}`)
                .then(response => response.json())
                .then(data => {
                    let modelloDiv = document.getElementById('modello');
                    let opzioniDiv = document.getElementById('opzioni');
                    let tableTaglio = document.getElementById('excelTableTaglio');
                    let tableOrlatura = document.getElementById('excelTableOrlatura');

                    modelloDiv.innerHTML = `<strong><i class="fas fa-tag mr-2"></i>Modello:</strong> ${data.modello.split('Lancio:')[0].trim()}`;
                    opzioniDiv.innerHTML = `
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <label for="lancio"><strong><i class="fas fa-rocket mr-2"></i>Lancio:</strong></label>
                                <input type="text" id="lancio" class="form-control">
                            </div>
                            <div class="col-sm-6 mb-2">
                                <label for="qty"><strong><i class="fas fa-cubes mr-2"></i>Qtà:</strong></label>
                                <input type="number" id="qty" value="1" class="form-control">
                            </div>
                        </div>
                    `;

                    // Filtriamo le righe fino a trovare "06 - MONTAGGIO" o simili
                    let taglioRows = [];
                    let montaggioFoundTaglio = false;
                    for (let i = 0; i < data.rows.taglio.length; i++) {
                        let row = data.rows.taglio[i];

                        // Controlla se questa riga contiene "06 - MONTAGGIO"
                        let containsMontaggio = row.some(cell => {
                            if (typeof cell === 'string') {
                                return cell.includes("06 - MONTAGGIO") ||
                                    cell.includes("06 - 1 MONTAGGIO") ||
                                    cell.includes("06-MONTAGGIO") ||
                                    cell.includes("06-1 MONTAGGIO");
                            }
                            return false;
                        });

                        if (containsMontaggio) {
                            montaggioFoundTaglio = true;
                            break; // Interrompi il ciclo quando trovi la riga con "06 - MONTAGGIO"
                        }

                        if (!montaggioFoundTaglio) {
                            taglioRows.push(row);
                        }
                    }

                    // Facciamo lo stesso per le righe di orlatura
                    let orlaturaRows = [];
                    let montaggioFoundOrlatura = false;
                    for (let i = 0; i < data.rows.orlatura.length; i++) {
                        let row = data.rows.orlatura[i];

                        // Controlla se questa riga contiene "06 - MONTAGGIO"
                        let containsMontaggio = row.some(cell => {
                            if (typeof cell === 'string') {
                                return cell.includes("06 - MONTAGGIO") ||
                                    cell.includes("06 - 1 MONTAGGIO") ||
                                    cell.includes("06-MONTAGGIO") ||
                                    cell.includes("06-1 MONTAGGIO");
                            }
                            return false;
                        });

                        if (containsMontaggio) {
                            montaggioFoundOrlatura = true;
                            break; // Interrompi il ciclo quando trovi la riga con "06 - MONTAGGIO"
                        }

                        if (!montaggioFoundOrlatura) {
                            orlaturaRows.push(row);
                        }
                    }

                    tableTaglio.innerHTML = `<thead><tr><th style="width: 50px;"></th>${data.headers.map(header => `<th>${header}</th>`).join('')}<th>Totale</th></tr></thead><tbody>${taglioRows.map(row => `<tr><td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)"><i class="fa fa-trash"></i></button></td>${row.slice(0, 5).map(cell => `<td>${cell}</td>`).join('')}<td>${(document.getElementById('qty').value * row[4]).toFixed(2)}</td></tr>`).join('')}</tbody>`;

                    tableOrlatura.innerHTML = `<thead><tr><th style="width: 50px;"></th>${data.headers.map(header => `<th>${header}</th>`).join('')}<th>Totale</th></tr></thead><tbody>${orlaturaRows.map(row => `<tr><td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)"><i class="fa fa-trash"></i></button></td>${row.slice(0, 5).map(cell => `<td>${cell}</td>`).join('')}<td>${(document.getElementById('qty').value * row[4]).toFixed(2)}</td></tr>`).join('')}</tbody>`;

                    // Salviamo l'ID del file corrente per aggiornare lo stato dopo il salvataggio
                    $('#excelModal').data('currentFileId', fileId);

                    $('#excelModal').modal('show');

                    // Aggiornamento in tempo reale del totale quando cambia il valore di Qtà
                    document.getElementById('qty').addEventListener('input', updateTotals);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Errore',
                        text: 'Si è verificato un errore durante l\'elaborazione del file Excel',
                        icon: 'error'
                    });
                });
        }
    });
    
    function updateTotals() {
        let qty = parseFloat(document.getElementById('qty').value);
        let tableTaglioRows = [...document.getElementById('excelTableTaglio').querySelectorAll('tbody tr')];
        let tableOrlaturaRows = [...document.getElementById('excelTableOrlatura').querySelectorAll('tbody tr')];

        tableTaglioRows.forEach((row, index) => {
            let cells = row.querySelectorAll('td');
            let totale = parseFloat(cells[5].innerText) * qty;
            cells[cells.length - 1].innerText = isNaN(totale) ? '0.00' : totale.toFixed(2);
        });

        tableOrlaturaRows.forEach((row, index) => {
            let cells = row.querySelectorAll('td');
            let totale = parseFloat(cells[5].innerText) * qty;
            cells[cells.length - 1].innerText = isNaN(totale) ? '0.00' : totale.toFixed(2);
        });
    }
    
    function saveToExcel() {
        let modello = document.getElementById('modello').innerText.split(':')[1].trim();
        let lancio = document.getElementById('lancio').value;
        let qty = parseFloat(document.getElementById('qty').value);

        // Validazione
        if (!lancio) {
            Swal.fire({
                title: 'Attenzione',
                text: 'Inserisci un valore per il campo Lancio',
                icon: 'warning'
            });
            return;
        }

        let tableTaglio = document.getElementById('excelTableTaglio');
        let tableOrlatura = document.getElementById('excelTableOrlatura');

        let data = {
            'modello': modello,
            'lancio': lancio,
            'qty': qty,
            'tableTaglio': [],
            'tableOrlatura': []
        };

        // Aggiungi dati dalla tabella Taglio
        tableTaglio.querySelectorAll('tbody tr').forEach(row => {
            let rowData = [];
            let totale = 0;
            row.querySelectorAll('td').forEach((cell, index) => {
                if (index > 0) { // Ignora la prima colonna vuota
                    rowData.push(cell.innerText);
                    if (index === 4) { // Colonna Cons. nella tabella Taglio
                        totale = parseFloat(cell.innerText) * qty;
                    }
                }
            });
            rowData.push(totale.toFixed(2)); // Aggiungi totale alla fine della riga
            data.tableTaglio.push(rowData);
        });

        // Aggiungi dati dalla tabella Orlatura
        tableOrlatura.querySelectorAll('tbody tr').forEach(row => {
            let rowData = [];
            let totale = 0;
            row.querySelectorAll('td').forEach((cell, index) => {
                if (index > 0) { // Ignora la prima colonna vuota
                    rowData.push(cell.innerText);
                    if (index === 4) { // Colonna Cons. nella tabella Orlatura
                        totale = parseFloat(cell.innerText) * qty;
                    }
                }
            });
            rowData.push(totale.toFixed(2)); // Aggiungi totale alla fine della riga
            data.tableOrlatura.push(rowData);
        });

        fetch('save-excel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())  // Converti la risposta in JSON
            .then(data => {
                if (data.success) {
                    // Ottieni l'ID del file corrente dal modale
                    let currentFileId = $('#excelModal').data('currentFileId');
                    if (currentFileId) {
                        // Mostra il segno di spunta verde accanto al file
                        $(`#${currentFileId} .processed-check`).show();
                    }

                    Swal.fire({
                        title: 'Scheda Salvata!',
                        text: 'Carica la scheda successiva o vai al prossimo Step.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#excelModal').modal('hide'); // Chiudi il modale
                        }
                    });
                } else {
                    console.error('Error:', data.error);
                    Swal.fire({
                        title: 'Errore',
                        text: data.error || 'Errore nel salvataggio del file Excel.',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Errore',
                    text: 'Si è verificato un errore durante il salvataggio',
                    icon: 'error'
                });
            });
    }
</script>