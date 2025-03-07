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
                        <h1 class="h3 mb-0 text-gray-800">Gestione Attrezzatura</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attrezzatura</li>
                    </ol>
                    <!-- Griglia di 6 bottoni -->
                    <div class="row text-center my-5">
                        <div class="col-md-6 mb-4">
                            <a href="configure"
                                class="btn btn-orange btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-apple-crate fa-3x mr-3 "></i>
                                <div class="text-left">
                                    <h5><b>Anagrafiche</b></h5>
                                    <i>Crea o Modifica le attrezzature esistenti.</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="moveTo"
                                class="btn btn-success btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-shipping-fast fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Sposta</b></h5>
                                    <i>Dichiara l'invio e assegnazione ad un laboratorio di attrezzatura.</i>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4 mb-4">
                            <a href="askReturn"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-file-invoice fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Chiedi</b></h5>
                                    <i>Compila e invia una richiesta di rientro attrezzatura da un Laboratorio.</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="moveFrom"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-house-return fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Rientra</b></h5>
                                    <i>Completa il rientro dell'attrezzatura da un Laboratorio.</i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="inventory"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">
                                <i class="fad fa-calendar fa-3x mr-3"></i>
                                <div class="text-left">
                                    <h5><b>Inventario</b></h5>
                                    <i>Controllo della posizione delle attrezzature.</i>
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