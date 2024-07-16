<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
require_once 'charts/charts_functions.php'; // Includi il file delle funzioni

// Calcola la data del lunedì della settimana corrente
$monday = strtotime('monday this week');
// Array per memorizzare le date della settimana corrente
$dates = array();
// Array per memorizzare i difetti per ciascuna data
$defects_percentage = array();
$bin_percentage = array();

// Array per i nomi dei giorni della settimana in italiano
$giorni_settimana = array('LUNEDÌ', 'MARTEDÌ', 'MERCOLEDÌ', 'GIOVEDÌ', 'VENERDÌ', 'SABATO', 'DOMENICA');

// CICLO DIFETTI SETTIMANA
for ($i = 0; $i < 5; $i++) {
    // Calcola la data del giorno corrente
    $current_date = strtotime("+$i days", $monday);
    $formatted_date = date('d/m/Y', $current_date);
    $day_of_month = date('d', $current_date);
    // Aggiungi la data formattata con il giorno della settimana all'array delle date
    $dates[] = $giorni_settimana[$i] . ' ' . $day_of_month;

    // Calcola le percentuali dei difetti
    $defects_percentage[] = calculate_percentage($formatted_date, 'difetti');
    $bin_percentage[] = calculate_percentage($formatted_date, 'scarti');
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Statistiche</li>
                    </ol>
                    <div class="row">

                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">

                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-warning">% Difettistica - Settimana Corrente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="weekDifettiChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">

                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-danger">% Scarti - Settimana Corrente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="weekScartiChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--
                    <div class="row">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">

                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Revenue Sources</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Direct
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Social
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Referral
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
-->
                </div>
            </div>

            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>


    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/chart-pie.js"></script>
    <?php include BASE_PATH . "/functions/quality/charts/weekDifettiChart.php" ?>
    <?php include BASE_PATH . "/functions/quality/charts/weekScartiChart.php" ?>
</body>

</body>