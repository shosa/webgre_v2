<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
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
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sistema CQ Emmegiemme</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Controllo Qualità </li>
                    </ol>
                    <!-- Griglia di 6 bottoni -->
                    <div class="row text-center my-5">
                        <div class="col-md-4 mb-4">
                            <a href="new"
                                class="btn btn-success btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-plus fa-3x mr-3 "></i>
                                <div class="text-left">
                                    <h5><b>Nuova Registrazione</b></h5>
                                    <i>Avvia un nuovo controllo</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="read"
                                class="btn btn-info btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-folder-tree fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Consulta</b></h5>
                                    <i>Visualizza le registrazioni per data.</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="search"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-search fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Cerca</b></h5>
                                    <i>Ricerca per numero Test, cartellino, commessa, articolo e data</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="barcode"
                                class="btn btn-indigo btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-barcode fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Gestione Barcode</b></h5>
                                    <i>Controlla i motivi rapidi</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="chartsOverview"
                                class="btn btn-warning btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-chart-pie fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Statistiche</b></h5>
                                    <i>Confronta i dati sotto forma di grafici</i>
                                </div>
                            </a>
                        </div>
                       
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>