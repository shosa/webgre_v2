<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Include header
require_once BASE_PATH . '/components/header.php';
?>
<style>
    .btn-wrapper {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
    }

    .btn-block {
        display: inline-block;
        width: 100%;
        margin-right: 10px;
        /* Aggiunto margine per separare i pulsanti */
    }
</style>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php include BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Packing List</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Opzioni</h6>
                                </div>
                                <div class="card-body">
                                    <textarea id="cartellini-input" class="form-control" rows="10"
                                        placeholder="Inserisci cartellini, uno per riga"></textarea>

                                    <!-- Radio buttons per scegliere il tipo di report -->
                                    <div class="form-group mt-3 row">
                                        <div class="col-sm-6">
                                            <legend class="mb-2 text-dark font-weight-bold" style="font-size: 12pt;">
                                                Ricerca:</legend>
                                            <label class="mr-3"><input type="radio" name="reportType"
                                                    value="perCartellino" checked> Per Cartellino</label>
                                            <label class="mr-3"><input type="radio" name="reportType" value="perLotto">
                                                Per Lotto</label>
                                        </div>
                                        <div class="col-sm-6 text-right">
                                            <legend class="mb-2 text-dark font-weight-bold" style="font-size: 12pt;">
                                                Formato:</legend>
                                            <label class="mr-3"><input type="radio" name="formatType" value="pdf"
                                                    checked> <i
                                                    class="fa-solid fa-file-pdf fa-xl text-danger"></i></label>
                                            <label><input type="radio" name="formatType" value="xlsx"> <i
                                                    class="fa-solid fa-file-excel fa-xl text-success"></i></label>
                                        </div>
                                    </div>

                                    <!-- Bottone per generare il report -->
                                    <button id="generateReportBtn" class="btn btn-warning btn-block mt-2 shadow">Genera
                                        Report</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Packing List</h6>
                                </div>
                                <div class="card-body" id="report-container">
                                    <!-- Placeholder per il contenuto del report o del file XLSX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>

        <!-- JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Aggiorna il placeholder della textarea in base all'opzione selezionata
                var radioButtons = document.querySelectorAll('input[name="reportType"]');
                var textarea = document.getElementById('cartellini-input');

                radioButtons.forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        if (radio.value === 'perCartellino') {
                            textarea.placeholder = 'Inserisci cartellini, uno per riga';
                        } else if (radio.value === 'perLotto') {
                            textarea.placeholder = 'Inserisci lotti, uno per riga';
                        }
                    });
                });

                // Gestione del click sul pulsante Genera Report
                document.getElementById('generateReportBtn').addEventListener('click', function () {
                    var inputText = textarea.value.trim(); // Ottieni il testo dalla textarea e rimuovi spazi vuoti iniziali e finali
                    var lines = inputText.split('\n').filter(Boolean); // Dividi il testo in righe e rimuovi eventuali righe vuote

                    var reportType = document.querySelector('input[name="reportType"]:checked').value;
                    var formatType = document.querySelector('input[name="formatType"]:checked').value;

                    var dataToSend = {};

                    if (reportType === 'perCartellino') {
                        dataToSend.cartellini = lines; // Invia cartellini se è selezionato 'Per Cartellino'
                    } else if (reportType === 'perLotto') {
                        dataToSend.lotti = lines; // Invia lotti se è selezionato 'Per Lotto'
                    }

                    var xhr = new XMLHttpRequest();

                    // Scegli lo script PHP in base al reportType e al formatType selezionato
                    var url = '';
                    if (formatType === 'pdf') {
                        url = reportType === 'perCartellino' ? 'generateReportCartel.php' : 'generateReportLot.php';
                    } else if (formatType === 'xlsx') {
                        url = reportType === 'perCartellino' ? 'generateXlsxCartel.php' : 'generateXlsxLot.php';
                    }

                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            if (formatType === 'pdf') {
                                var blob = new Blob([xhr.response], { type: 'application/pdf' });
                                var url = URL.createObjectURL(blob);
                                var reportContainer = document.getElementById('report-container');
                                reportContainer.innerHTML = '<iframe src="' + url + '" width="100%" height="500px"></iframe>';
                            } else if (formatType === 'xlsx') {
                                var blobUrl = URL.createObjectURL(xhr.response);

                                // Creazione di un link per il download del file
                                var a = document.createElement('a');
                                a.style.display = 'none';
                                a.href = blobUrl;
                                a.download = 'report.xlsx'; // Nome del file da scaricare
                                document.body.appendChild(a);
                                a.click();
                                URL.revokeObjectURL(blobUrl);
                            }
                        }
                    };
                    xhr.responseType = 'blob';
                    xhr.send(JSON.stringify(dataToSend));
                });
            });
        </script>
</body>