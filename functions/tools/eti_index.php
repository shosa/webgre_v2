<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
?>
<style>
    #suggestions-container {
        max-height: 150px;
        overflow-y: auto;
        position: relative;
        background-color: #f0f0f0;
        border-bottom-left-radius: 10px;
    }

    .suggestion-item {
        padding: 8px;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #6610f2;
        color: white;
    }
</style>

<head>
    <script src="https://qajavascriptsdktests.azurewebsites.net/JavaScript/dymo.connect.framework.js"
        type="text/javascript" charset="UTF-8"> </script>
    <script src="eti/eti_script.js" type="text/javascript" charset="UTF-8"> </script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Etichette</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Etichette</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-5 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Stampa</h6>
                                </div>
                                <div class="card-body">
                                    <div class="printControls">
                                        <div id="printersDiv" class="form-group">
                                            <label for="printersSelect">Stampante:</label>
                                            <select id="printersSelect" class="form-control"></select>
                                        </div>
                                        <div class="form-group">
                                            <label for="codice_articolo">Codice:</label>
                                            <input type="text" id="codice_articolo" name="codice_articolo"
                                                class="form-control" autocomplete="off">
                                            <div id="suggestions-container"></div>
                                        </div>





                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Comandi</h6>
                                </div>
                                <div class="card-body">

                                    <button id="printButton" class="btn btn-indigo btn-block">STAMPA</button>
                                    <button id="decodePageButton" class="btn  btn-warning btn-block">CREA
                                        LISTA</button>

                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Anteprima</h6>
                                </div>
                                <div class="card-body text-center">
                                    <img id="labelPreview" onerror="this.onerror=null; this.src=''" alt="" class="mt-3"
                                        style="max-width: 100%;" />

                                    <div id="printerDetailContainer" aria-labelledby="printerDetailHeading"
                                        data-parent="#accordion">
                                        <div id="printerDetail" class="table-responsive"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include (BASE_PATH . "/components/scripts.php"); ?>
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
</body>
<script>
    document.getElementById('decodePageButton').addEventListener('click', function () {
        window.location.href = 'eti_decode.php'; // Cambia con il percorso corretto se necessario
    });
</script>