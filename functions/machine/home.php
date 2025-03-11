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
                        <h1 class="h3 mb-0 text-gray-800">Gestione Macchinari Aziendali</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Macchinari</li>
                    </ol>
                    <!-- Griglia di bottoni -->
                    <div class="row text-center my-5">
                        <div class="col-md-4 mb-4">
                            <a href="new"
                                class="btn btn-success btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-plus fa-3x mr-3 "></i>
                                <div class="text-left">
                                    <h5><b>Nuovo Macchinario</b></h5>
                                    <i>Aggiungi una nuova anagrafica</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="lista_macchinari"
                                class="btn btn-info btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-list-alt fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Lista Macchinari</b></h5>
                                    <i>Visualizza tutti i macchinari registrati</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-search fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Cerca</b></h5>
                                    <i>Ricerca per matricola, tipologia, produttore o modello</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#"
                                class="btn btn-warning btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-tools fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Manutenzioni</b></h5>
                                    <i>Gestisci le manutenzioni programmate</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#"
                                class="btn btn-primary btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-chart-bar fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Reportistica</b></h5>
                                    <i>Statistiche e report sui macchinari</i>
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