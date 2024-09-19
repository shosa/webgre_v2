<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
header('Access-Control-Allow-Origin: *'); ?>
<style>
    #suggestions-container {
        max-height: 500px;
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
        background-color:
            var(--<?php echo $colore; ?>);
        color: #fff
    }
</style>

<head>
    <script charset="UTF-8" src="eti/dymo.js" type="text/javascript"></script>
    <script charset="UTF-8" src="eti/eti_script.js" type="text/javascript"></script>
</head>

<body id="page-top">
    <div id="wrapper"><?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content"><?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid"><?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Etichette</h1>
                    </div>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Etichette</li>
                    </ol>
                    <div class="row">
                        <div class="col-lg-5 col-xl-5">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Stampa</h6>
                                </div>
                                <div class="card-body">
                                    <div class="printControls">
                                        <div class="form-group" id="printersDiv"><label
                                                for="printersSelect">Stampante:</label> <select class="form-control"
                                                id="printersSelect"></select></div>
                                        <div class="form-group"><label for="codice_articolo">Codice:</label> <input
                                                autocomplete="off" class="form-control" id="codice_articolo"
                                                name="codice_articolo">
                                            <div id="suggestions-container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-xl-3">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Comandi</h6>
                                </div>
                                <div class="card-body"><button class="btn btn-block btn-indigo"
                                        id="printButton">STAMPA</button> <button class="btn btn-block btn-warning"
                                        id="decodePageButton">CREA LISTA</button></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-xl-4">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Anteprima</h6>
                                </div>
                                <div class="card-body text-center"><img alt="" class="mt-3" id="labelPreview"
                                        onerror='this.onerror=null,this.src=""' style="max-width:100%">
                                    <div id="printerDetailContainer" aria-labelledby="printerDetailHeading"
                                        data-parent="#accordion">
                                        <div class="table-responsive" id="printerDetail"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include(BASE_PATH . "/components/scripts.php"); ?><?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
</body>
<script>
    document.getElementById('decodePageButton').addEventListener('click', function () {
        window.location.href = 'eti_decode'; // Cambia con il percorso corretto se necessario
    });
</script>