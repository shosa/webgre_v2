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
    }

    .drop-icon {
        font-size: 48px;
        color: #007BFF;
        margin-bottom: 20px;
    }

    #file-list {
        list-style-type: none;
        padding: 0;
    }

    #file-list li {
        margin-bottom: 10px;
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


                    <!-- Drag and Drop Area -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="drop-zone" id="drop-zone">
                                Trascina qui le schede tecniche in formato Excel <br>
                                <i class="fas fa-plus" style="color:#007BFF;font-size:20pt;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- File List -->
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <ul id="file-list"></ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <!-- Aggiunto il pulsante "Avanti" allineato a destra -->
                            <button type="button" class="btn btn-primary float-end"
                                onclick="navigateToStep3()">Avanti</button>
                        </div>
                    </div>
                    <!-- Excel Content Modal -->
                    <div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="excelModalLabel">Contenuto Excel</h5>
                                    <!-- Rimozione del pulsante di chiusura -->
                                </div>
                                <div class="modal-body">
                                    <div id="modello"
                                        style="background-color:#f0e8fc;border-radius:2px;padding:4px;margin-bottom:10px;">
                                    </div>
                                    <div id="opzioni"
                                        style="background-color:#e8f0fc;border-radius:2px;padding:4px;margin-bottom:10px;">
                                    </div>
                                    <h4>TAGLIO</h4>
                                    <table class="table" id="excelTableTaglio"></table>

                                    <h4 class="mt-4">ORLATURA</h4>
                                    <table class="table" id="excelTableOrlatura"></table>
                                </div>
                                <div class="modal-footer">
                                    <!-- Aggiunta del pulsante SALVA in alto a destra -->
                                    <button type="button" class="btn btn-primary btn-save"
                                        onclick="saveToExcel()">SALVA</button>
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

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
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
                    alert('Carica solo file Excel (.xlsx)');
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
                            fileList.innerHTML += `<li>${file.name} <button class="btn btn-sm btn-primary" onclick="showExcelContent('${file.name}')">Visualizza</button></li>`;
                        } else {
                            alert(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        }

        window.showExcelContent = function (fileName) {
            fetchExcelContent(fileName);
        }

        function fetchExcelContent(fileName) {
            fetch(`process-excel.php?fileName=${fileName}`)
                .then(response => response.json())
                .then(data => {
                    let modelloDiv = document.getElementById('modello');
                    let opzioniDiv = document.getElementById('opzioni');
                    let tableTaglio = document.getElementById('excelTableTaglio');
                    let tableOrlatura = document.getElementById('excelTableOrlatura');

                    modelloDiv.innerHTML = `<strong>Modello:</strong> ${data.modello.split('Lancio:')[0].trim()}`;
                    opzioniDiv.innerHTML = `<strong>Lancio:</strong> <input type="text" id="lancio"> <strong>Qtà:</strong> <input type="number" id="qty" value="1">`;

                    tableTaglio.innerHTML = `<thead><tr><th></th>${data.headers.map(header => `<th>${header}</th>`).join('')}<th>Totale</th></tr></thead><tbody>${data.rows.taglio.map(row => `<tr><td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)"><i class="fa fa-trash"></i></button></td>${row.slice(0, 5).map(cell => `<td>${cell}</td>`).join('')}<td>${(document.getElementById('qty').value * row[4]).toFixed(2)}</td></tr>`).join('')}</tbody>`;

                    tableOrlatura.innerHTML = `<thead><tr><th></th>${data.headers.map(header => `<th>${header}</th>`).join('')}<th>Totale</th></tr></thead><tbody>${data.rows.orlatura.map(row => `<tr><td><button class="btn btn-danger btn-sm" onclick="deleteRow(this)"><i class="fa fa-trash"></i></button></td>${row.slice(0, 5).map(cell => `<td>${cell}</td>`).join('')}<td>${(document.getElementById('qty').value * row[4]).toFixed(2)}</td></tr>`).join('')}</tbody>`;

                    $('#excelModal').modal('show');

                    // Aggiornamento in tempo reale del totale quando cambia il valore di Qtà
                    document.getElementById('qty').addEventListener('input', updateTotals);
                })
                .catch(error => {
                    console.error('Error:', error);
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
                    alert('Errore nel salvataggio del file Excel.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nel salvataggio del file Excel.');
            });
    }

</script>