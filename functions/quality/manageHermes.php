<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include(BASE_PATH . "/components/header.php");
?>

<!-- Aggiungi questo nell'intestazione <head> o proprio prima del tuo script personalizzato -->
<script>
    // Fallback se jQuery non è caricato
    if (typeof jQuery == 'undefined') {
        var script = document.createElement('script');
        script.src = '<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js';
        document.head.appendChild(script);
    }
</script>
<style>
    /* Personalizzazione tab */
    .nav-tabs {
        border-bottom: 1px solid #e3e6f0 !important;
    }

    .nav-tabs .nav-link {
        border: none !important;
        color: #6c757d !important;
        padding: 0.75rem 1.25rem !important;
        font-weight: 500 !important;
        font-size: 0.85rem !important;
        border-radius: 0 !important;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df !important;
        background-color: transparent !important;
        border-bottom: 3px solid #4e73df !important;
        width: 100% !important;
    }

    .nav-tabs .nav-link:hover {
        color: #4e73df !important;
        border-bottom: 3px solid #e3e6f0 !important;
    }

    /* Stile per il calendario manuale */
    .future {
        color: #f2a5a0;
        cursor: not-allowed;
    }

    .future:hover {
        background-color: #f07067;
    }

    .giorno:hover {
        background-color: #DFF0FF;
    }

    .today {
        background-color: #B4E8C9;
        color: #439876;
        font-weight: bolder;
    }

    .has-records {
        position: relative;
    }

    .has-records::after {
        content: "";
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background-color: #4e73df;
        border-radius: 50%;
    }

    .has-records.many::after {
        background-color: #1cc88a;
    }
</style>

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
                        <h1 class="h3 mb-0 text-gray-800">CQ Dashboard Hermes</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Controllo Qualità</a></li>
                        <li class="breadcrumb-item active">Divisione Hermes</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Divisione Hermes</h6>
                        </div>
                        <div class="card-body">
                            <!-- Nav tabs per le diverse funzionalità -->
                            <ul class="nav nav-tabs" id="hermesTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview"
                                        role="tab">
                                        <i class="fas fa-chart-pie mr-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="records-tab" data-toggle="tab" href="#records" role="tab">
                                        <i class="fas fa-clipboard-list mr-1"></i>Cartellini
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="departments-tab" data-toggle="tab" href="#departments"
                                        role="tab">
                                        <i class="fas fa-building mr-1"></i>Reparti
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="defects-tab" data-toggle="tab" href="#defects" role="tab">
                                        <i class="fas fa-bug mr-1"></i>Tipi Difetti
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab">
                                        <i class="fas fa-file-alt mr-1"></i>Reports
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <!-- Dashboard/Overview Tab -->
                                <div class="tab-pane fade show active" id="overview" role="tabpanel"
                                    aria-labelledby="overview-tab">
                                    <div class="row mt-4">
                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-primary shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                Cartellini Totali</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                                                id="total-records">Caricamento...</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-success shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                                Cartellini Oggi</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                                                id="today-records">Caricamento...</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-warning shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                                Eccezioni Totali</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                                                id="total-exceptions">Caricamento...</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i
                                                                class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-danger shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                                Eccezioni Oggi</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"
                                                                id="today-exceptions">Caricamento...</div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i
                                                                class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-8 col-lg-7">
                                            <div class="card shadow mb-4">
                                                <div
                                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                    <h6 class="m-0 font-weight-bold text-primary">Trend Controlli Ultimi
                                                        7 Giorni</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-area">
                                                        <canvas id="weeklyCQChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-lg-5">
                                            <div class="card shadow mb-4">
                                                <div
                                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                    <h6 class="m-0 font-weight-bold text-primary">Distribuzione Tipi di
                                                        Difetti</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-pie pt-4 pb-2">
                                                        <canvas id="defectsPieChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cartellini Tab - MODIFICATO -->
                                <div class="tab-pane fade" id="records" role="tabpanel" aria-labelledby="records-tab">
                                    <div
                                        class="card-header py-3 d-flex justify-content-between align-items-center mt-4">
                                        <h6 class="m-0 font-weight-bold text-primary">Gestione Cartellini</h6>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#addRecordModal">
                                            <i class="fas fa-plus"></i> Nuovo Cartellino
                                        </button>
                                    </div>

                                    <!-- Nuovo layout con calendario e records -->
                                    <div id="calendar-records-container" class="mt-3 row">
                                        <!-- Calendario sulla sinistra - 4/12 -->
                                        <div class="col-md-4">
                                            <div id="calendar-wrapper" class="card shadow h-100">
                                                <div class="card-body">
                                                    <div class="calendar">
                                                        <div
                                                            class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                            <button id="prev-month" class="btn btn-danger shadow"><i
                                                                    class="fas fa-chevron-double-left"></i></button>
                                                            <span>
                                                                <h5 class="m-0 font-weight-bold text-primary"
                                                                    id="calendar-month-year"></h5>
                                                            </span>
                                                            <button id="next-month" class="btn btn-danger shadow"><i
                                                                    class="fas fa-chevron-double-right"></i></button>
                                                        </div>
                                                        <table
                                                            class="table table-bordered table-sm table-condensed mt-3">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Lun</th>
                                                                    <th scope="col">Mar</th>
                                                                    <th scope="col">Mer</th>
                                                                    <th scope="col">Gio</th>
                                                                    <th scope="col">Ven</th>
                                                                    <th scope="col">Sab</th>
                                                                    <th scope="col">Dom</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="calendar-body">
                                                                <!-- Generato dinamicamente con JavaScript -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Records sulla destra - 8/12 -->
                                        <div class="col-md-8">
                                            <div id="records-wrapper" class="card shadow h-100">
                                                <div
                                                    class="card-header py-3 d-flex justify-content-between align-items-center">
                                                    <h6 class="m-0 font-weight-bold text-primary"
                                                        id="records-date-header">
                                                        Cartellini <span id="selected-date">di oggi</span></h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered" id="recordsDataTable"
                                                            width="100%" cellspacing="0">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Numero Cartellino</th>
                                                                    <th>Reparto</th>
                                                                    <th>Operatore</th>
                                                                    <th>Tipo CQ</th>
                                                                    <th>Paia Totali</th>
                                                                    <th>Eccezioni</th>
                                                                    <th>Cod. Articolo</th>
                                                                    <th>Articolo</th>
                                                                    <th>Linea</th>
                                                                    <th>Azioni</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- I dati verranno caricati con AJAX -->
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- Sezione eccezioni per il record selezionato -->
                                                    <div id="record-exceptions-section" class="mt-4"
                                                        style="display: none;">
                                                        <div class="card">
                                                            <div
                                                                class="card-header py-3 d-flex justify-content-between align-items-center">
                                                                <h6 class="m-0 font-weight-bold text-warning">Eccezioni
                                                                    del
                                                                    Cartellino <span id="selected-record-number"></span>
                                                                </h6>
                                                                <button class="btn btn-warning btn-sm"
                                                                    id="add-exception-btn">
                                                                    <i class="fas fa-plus"></i> Aggiungi Eccezione
                                                                </button>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered"
                                                                        id="recordExceptionsTable" width="100%"
                                                                        cellspacing="0">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>ID</th>
                                                                                <th>Taglia</th>
                                                                                <th>Tipo Difetto</th>
                                                                                <th>Note Operatore</th>
                                                                                <th>Foto</th>
                                                                                <th>Data Creazione</th>
                                                                                <th>Azioni</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <!-- Dati caricati con AJAX -->
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Reparti Tab -->
                                <div class="tab-pane fade" id="departments" role="tabpanel"
                                    aria-labelledby="departments-tab">
                                    <div
                                        class="card-header py-3 d-flex justify-content-between align-items-center mt-4">
                                        <h6 class="m-0 font-weight-bold text-primary">Gestione Reparti</h6>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#addDepartmentModal">
                                            <i class="fas fa-plus"></i> Nuovo Reparto
                                        </button>
                                    </div>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered" id="departmentsDataTable" width="100%"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nome Reparto</th>
                                                    <th>Attivo</th>
                                                    <th>Ordine</th>
                                                    <th>Data Creazione</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- I dati verranno caricati con AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tipi Difetti Tab -->
                                <div class="tab-pane fade" id="defects" role="tabpanel" aria-labelledby="defects-tab">
                                    <div
                                        class="card-header py-3 d-flex justify-content-between align-items-center mt-4">
                                        <h6 class="m-0 font-weight-bold text-primary">Gestione Tipi Difetti</h6>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#addDefectModal">
                                            <i class="fas fa-plus"></i> Nuovo Tipo Difetto
                                        </button>
                                    </div>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered" id="defectsDataTable" width="100%"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Descrizione</th>
                                                    <th>Categoria</th>
                                                    <th>Attivo</th>
                                                    <th>Ordine</th>
                                                    <th>Data Creazione</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- I dati verranno caricati con AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Reports Tab -->
                                <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card shadow mb-4">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Report Giornaliero
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form id="dailyReportForm" method="post"
                                                        action="hermes/generate_report.php" target="_blank">
                                                        <div class="form-group">
                                                            <label for="reportDate">Data Report:</label>
                                                            <input type="date" class="form-control" id="reportDate"
                                                                name="reportDate" value="<?php echo date('Y-m-d'); ?>"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="reportType">Tipo Report:</label>
                                                            <select class="form-control" id="reportType"
                                                                name="reportType" required>
                                                                <option value="summary">Riepilogo Giornaliero
                                                                </option>
                                                                <option value="detailed">Dettaglio Completo</option>
                                                                <option value="exceptions">Solo Eccezioni</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="reportFormat">Formato:</label>
                                                            <select class="form-control" id="reportFormat"
                                                                name="reportFormat" required>
                                                                <option value="pdf">PDF</option>
                                                                <option value="excel">Excel</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="fas fa-file-download"></i> Genera Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card shadow mb-4">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Report Periodo
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <form id="periodReportForm" method="post"
                                                        action="hermes/generate_period_report.php" target="_blank">
                                                        <div class="form-group">
                                                            <label for="startDate">Data Inizio:</label>
                                                            <input type="date" class="form-control" id="startDate"
                                                                name="startDate"
                                                                value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="endDate">Data Fine:</label>
                                                            <input type="date" class="form-control" id="endDate"
                                                                name="endDate" value="<?php echo date('Y-m-d'); ?>"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="periodReportType">Tipo Report:</label>
                                                            <select class="form-control" id="periodReportType"
                                                                name="periodReportType" required>
                                                                <option value="summary">Riepilogo Periodo</option>
                                                                <option value="byDepartment">Analisi per Reparto
                                                                </option>
                                                                <option value="byDefect">Analisi per Tipo Difetto
                                                                </option>
                                                                <option value="byOperator">Analisi per Operatore
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="periodReportFormat">Formato:</label>
                                                            <select class="form-control" id="periodReportFormat"
                                                                name="periodReportFormat" required>
                                                                <option value="pdf">PDF</option>
                                                                <option value="excel">Excel</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="fas fa-file-download"></i> Genera Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modals -->
                            <!-- Modal Aggiungi Cartellino -->
                            <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog"
                                aria-labelledby="addRecordModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addRecordModalLabel">Aggiungi Nuovo
                                                Cartellino
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addRecordForm">
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label for="numero_cartellino">Numero Cartellino*</label>
                                                        <input type="text" class="form-control" id="numero_cartellino"
                                                            name="numero_cartellino" required>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="reparto">Reparto*</label>
                                                        <select class="form-control" id="reparto" name="reparto"
                                                            required>
                                                            <!-- Opzioni caricate dinamicamente con JavaScript -->
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label for="operatore">Operatore*</label>
                                                        <input type="text" class="form-control" id="operatore"
                                                            name="operatore" required>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="tipo_cq">Tipo CQ*</label>
                                                        <select class="form-control" id="tipo_cq" name="tipo_cq"
                                                            required>
                                                            <option value="INTERNO">INTERNO</option>
                                                            <option value="GRIFFE">GRIFFE</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label for="paia_totali">Paia Totali*</label>
                                                        <input type="number" class="form-control" id="paia_totali"
                                                            name="paia_totali" min="1" required>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="cod_articolo">Codice Articolo*</label>
                                                        <input type="text" class="form-control" id="cod_articolo"
                                                            name="cod_articolo" required>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-8">
                                                        <label for="articolo">Articolo*</label>
                                                        <input type="text" class="form-control" id="articolo"
                                                            name="articolo" required>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="linea">Linea*</label>
                                                        <input type="text" class="form-control" id="linea" name="linea"
                                                            maxlength="2" required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="note">Note</label>
                                                    <textarea class="form-control" id="note" name="note"
                                                        rows="3"></textarea>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveRecordBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Eccezione -->
                            <div class="modal fade" id="addExceptionModal" tabindex="-1" role="dialog"
                                aria-labelledby="addExceptionModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addExceptionModalLabel">Aggiungi Nuova
                                                Eccezione
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addExceptionForm" enctype="multipart/form-data">
                                                <input type="hidden" id="exception_cartellino_id" name="cartellino_id">
                                                <div class="form-group">
                                                    <label for="taglia">Taglia*</label>
                                                    <input type="text" class="form-control" id="taglia" name="taglia"
                                                        required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tipo_difetto">Tipo Difetto*</label>
                                                    <select class="form-control" id="tipo_difetto" name="tipo_difetto"
                                                        required>
                                                        <!-- Opzioni caricate dinamicamente con JavaScript -->
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="note_operatore">Note Operatore</label>
                                                    <textarea class="form-control" id="note_operatore"
                                                        name="note_operatore" rows="3"></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="fotoPath">Foto</label>
                                                    <input type="file" class="form-control-file" id="fotoPath"
                                                        name="fotoPath" accept="image/*">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveExceptionBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Visualizza Foto -->
                            <div class="modal fade" id="viewPhotoModal" tabindex="-1" role="dialog"
                                aria-labelledby="viewPhotoModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewPhotoModalLabel">Visualizza Foto
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img id="exceptionPhoto" src="" class="img-fluid" alt="Foto eccezione">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Chiudi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Reparto -->
                            <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog"
                                aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addDepartmentModalLabel">Aggiungi Nuovo
                                                Reparto
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addDepartmentForm">
                                                <div class="form-group">
                                                    <label for="nome_reparto">Nome Reparto*</label>
                                                    <input type="text" class="form-control" id="nome_reparto"
                                                        name="nome_reparto" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ordine">Ordine</label>
                                                    <input type="number" class="form-control" id="ordine" name="ordine"
                                                        value="0" min="0">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="attivo"
                                                        name="attivo" checked>
                                                    <label class="form-check-label" for="attivo">Attivo</label>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveDepartmentBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Aggiungi Tipo Difetto -->
                            <div class="modal fade" id="addDefectModal" tabindex="-1" role="dialog"
                                aria-labelledby="addDefectModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addDefectModalLabel">Aggiungi Nuovo Tipo
                                                Difetto
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addDefectForm">
                                                <div class="form-group">
                                                    <label for="descrizione">Descrizione*</label>
                                                    <input type="text" class="form-control" id="descrizione"
                                                        name="descrizione" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="categoria">Categoria</label>
                                                    <input type="text" class="form-control" id="categoria"
                                                        name="categoria" list="categorie">
                                                    <datalist id="categorie">
                                                        <option value="CUCITURE">
                                                        <option value="MATERIALE">
                                                        <option value="FINITURE">
                                                    </datalist>
                                                </div>
                                                <div class="form-group">
                                                    <label for="defect_ordine">Ordine</label>
                                                    <input type="number" class="form-control" id="defect_ordine"
                                                        name="ordine" value="0" min="0">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="defect_attivo"
                                                        name="attivo" checked>
                                                    <label class="form-check-label" for="defect_attivo">Attivo</label>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary"
                                                id="saveDefectBtn">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <?php include(BASE_PATH . "/components/footer.php"); ?>

        </div>

    </div>
    <!-- Script essenziali -->
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>

    <!-- DataTables Core -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>

    <!-- Dipendenze dei Buttons -->
    <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>

    <!-- FullCalendar - AGGIUNTO -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/it.js"></script>

    <!-- Chart.js - IMPORTANTE -->
    <script src="<?php echo BASE_URL ?>/vendor/chart.js/Chart.min.js"></script>
    <!-- Estensioni Buttons -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>

    <!-- Altre estensioni DataTables -->
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.colReorder.min.js"></script>

    <!-- Script personalizzati -->
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>
    <!-- JavaScript per gestire la logica dell'interfaccia -->
    <!-- JavaScript per gestire la logica dell'interfaccia -->
    <script>
        // Variabili globali
        let selectedDate = new Date();
        let currentMonth = selectedDate.getMonth();
        let currentYear = selectedDate.getFullYear();
        let selectedRecordId = null;
        let recordsCalendarEvents = [];
        const monthNames = ["GENNAIO", "FEBBRAIO", "MARZO", "APRILE", "MAGGIO", "GIUGNO", "LUGLIO", "AGOSTO", "SETTEMBRE", "OTTOBRE", "NOVEMBRE", "DICEMBRE"];

        function initCalendar() {
            // Inizializza il calendario manuale
            updateCalendar();

            // Aggiungi listener per i pulsanti di navigazione mese
            $('#prev-month').on('click', function () {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                updateCalendar();
            });

            $('#next-month').on('click', function () {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                updateCalendar();
            });

            // Carica gli eventi del calendario
            loadCalendarEvents();
        }

        function updateCalendar() {
            // Aggiorna l'intestazione del mese/anno
            $('#calendar-month-year').text(monthNames[currentMonth] + " " + currentYear);

            // Calcola il primo giorno del mese (0 = Domenica, 1 = Lunedì, ..., 6 = Sabato)
            let firstDay = new Date(currentYear, currentMonth, 1).getDay();
            // Adatta per iniziare dal lunedì (1) invece che dalla domenica (0)
            firstDay = firstDay === 0 ? 7 : firstDay;

            // Calcola i giorni nel mese corrente
            let daysInMonth = 32 - new Date(currentYear, currentMonth, 32).getDate();

            // Pulisci il corpo del calendario
            let calendarBody = document.getElementById("calendar-body");
            calendarBody.innerHTML = "";

            // Riempimento del calendario
            let date = 1;
            for (let i = 0; i < 6; i++) {
                // Crea una riga per ogni settimana
                let row = document.createElement("tr");

                // Riempimento delle celle per ogni settimana
                for (let j = 1; j <= 7; j++) {
                    if (i === 0 && j < firstDay) {
                        // Celle vuote prima del primo giorno del mese
                        let cell = document.createElement("td");
                        cell.style.backgroundColor = "#ededed";
                        row.appendChild(cell);
                    } else if (date > daysInMonth) {
                        // Celle vuote dopo l'ultimo giorno del mese
                        let cell = document.createElement("td");
                        cell.style.backgroundColor = "#ededed";
                        row.appendChild(cell);
                    } else {
                        // Celle con date
                        let cell = document.createElement("td");
                        cell.textContent = date;
                        cell.style.height = "4.5em";
                        cell.style.cursor = "pointer";
                        cell.classList.add("giorno");

                        // Verifica se è oggi
                        let today = new Date();
                        if (date === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
                            cell.classList.add("today");
                        }

                        // Verifica se è una data futura
                        let cellDate = new Date(currentYear, currentMonth, date);
                        let isToday = cellDate.toDateString() === today.toDateString();
                        let isFuture = !isToday && cellDate > today;

                        if (isFuture) {
                            cell.classList.add("future");
                        } else {
                            // Aggiungi click event solo per date non future
                            cell.addEventListener("click", function () {
                                // Crea una data locale con anno, mese e giorno
                                selectedDate = new Date(currentYear, currentMonth, parseInt(this.textContent));
                                updateSelectedDateDisplay();

                                // Usa formatDateForAPI per ottenere il formato corretto
                                const apiDate = formatDateForAPI(selectedDate);
                                loadRecordsByDate(apiDate);

                                // Rimuovi eventuali evidenziazioni precedenti
                                document.querySelectorAll('.giorno.selected').forEach(el => {
                                    el.classList.remove('selected');
                                });

                                // Evidenzia la cella selezionata
                                this.classList.add('selected');
                            });
                        }

                        // Aggiungi indicatore se ci sono record per questa data
                        let dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        let eventData = recordsCalendarEvents.find(e => e.date === dateStr);

                        if (eventData) {
                            cell.classList.add('has-records');
                            if (eventData.count > 5) {
                                cell.classList.add('many');
                            }
                        }

                        row.appendChild(cell);
                        date++;
                    }
                }

                calendarBody.appendChild(row);

                // Esci dal ciclo se abbiamo raggiunto la fine del mese
                if (date > daysInMonth) {
                    break;
                }
            }
        }

        function updateSelectedDateDisplay() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const formattedDate = selectedDate.toLocaleDateString('it-IT', options);
            $('#selected-date').text(formattedDate);
        }

        function loadCalendarEvents() {
            // Carica gli eventi del calendario dal server
            $.ajax({
                url: 'hermes/get_calendar_events.php',
                type: 'GET',
                data: {
                    month: currentMonth + 1, // JavaScript month è 0-based (0-11), PHP è 1-based (1-12)
                    year: currentYear
                },
                dataType: 'json',
                success: function (data) {
                    recordsCalendarEvents = data;
                    updateCalendar(); // Aggiorna il calendario con i nuovi dati
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento degli eventi del calendario:', error);
                }
            });
        }

        function loadRecordsByDate(dateStr) {
            const formattedDate = dateStr || formatDateForAPI(selectedDate);

            // Usa DataTables API per ricaricare i dati con filtro data
            $('#recordsDataTable').DataTable().clear().destroy();

            // Inizializza DataTable filtrato per data
            $('#recordsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_records_by_date.php',
                    data: {
                        date: formattedDate
                    },
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'numero_cartellino' },
                    { data: 'reparto' },
                    { data: 'operatore' },
                    { data: 'tipo_cq' },
                    { data: 'paia_totali' },
                    {
                        data: 'ha_eccezioni',
                        render: function (data, type, row) {
                            if (data == 1) {
                                return `<span class="badge badge-warning">Sì</span> <button class="btn btn-sm btn-outline-warning view-exceptions" data-id="${row.id}" data-numero="${row.numero_cartellino}"><i class="fas fa-eye"></i></button>`;
                            } else {
                                return '<span class="badge badge-success">No</span>';
                            }
                        }
                    },
                    { data: 'cod_articolo' },
                    { data: 'articolo' },
                    { data: 'linea' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info edit-record" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-record" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']]
            });

            // Nascondi la sezione eccezioni se era aperta
            $('#record-exceptions-section').hide();
            selectedRecordId = null;
        }

        // Funzione aggiuntiva per assicurarsi che la data sia formattata correttamente
        function formatDateForAPI(date) {
            // Aggiungi 1 giorno per compensare lo sfasamento
            const adjustedDate = new Date(date);
            adjustedDate.setDate(adjustedDate.getDate());

            // Formatta come YYYY-MM-DD assicurandosi di usare i valori locali
            const year = adjustedDate.getFullYear();
            // Nota: getMonth() restituisce 0-11, quindi aggiungiamo 1
            const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
            const day = String(adjustedDate.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }
        function loadExceptionsForRecord(recordId, recordNumber) {
            selectedRecordId = recordId;
            $('#selected-record-number').text(recordNumber);

            // Configura il pulsante per aggiungere eccezioni
            $('#add-exception-btn').off('click').on('click', function () {
                $('#exception_cartellino_id').val(recordId);
                $('#addExceptionModal').modal('show');
            });

            // Usa DataTables API per caricare le eccezioni
            if ($.fn.DataTable.isDataTable('#recordExceptionsTable')) {
                $('#recordExceptionsTable').DataTable().destroy();
            }

            $('#recordExceptionsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_exceptions_by_record.php',
                    data: {
                        record_id: recordId
                    },
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'taglia' },
                    { data: 'tipo_difetto' },
                    { data: 'note_operatore' },
                    {
                        data: 'fotoPath',
                        render: function (data) {
                            if (data) {
                                return `<button class="btn btn-sm btn-info view-photo" data-path="<?php echo BASE_URL; ?>/${data}"><i class="fas fa-image"></i> Visualizza</button>`;
                            } else {
                                return '<span class="badge badge-secondary">No foto</span>';
                            }
                        }
                    },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info edit-exception" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-exception" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']]
            });

            // Mostra la sezione delle eccezioni
            $('#record-exceptions-section').show();
        }

        function initDataTables() {
            // DataTable per i cartellini - carica inizialmente i record di oggi
            const today = new Date().toISOString().split('T')[0];

            $('#recordsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_records_by_date.php',
                    data: {
                        date: today
                    },
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'numero_cartellino' },
                    { data: 'reparto' },
                    { data: 'operatore' },
                    { data: 'tipo_cq' },
                    { data: 'paia_totali' },
                    {
                        data: 'ha_eccezioni',
                        render: function (data, type, row) {
                            if (data == 1) {
                                return `<span class="badge badge-warning">Sì</span> <button class="btn btn-sm btn-outline-warning view-exceptions" data-id="${row.id}" data-numero="${row.numero_cartellino}"><i class="fas fa-eye"></i></button>`;
                            } else {
                                return '<span class="badge badge-success">No</span>';
                            }
                        }
                    },
                    { data: 'cod_articolo' },
                    { data: 'articolo' },
                    { data: 'linea' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info edit-record" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-record" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']]
            });

            // Event listener per visualizzare le foto
            $('#records-wrapper').on('click', '.view-photo', function () {
                var path = $(this).data('path');
                $('#exceptionPhoto').attr('src', path);
                $('#viewPhotoModal').modal('show');
            });

            // Event listener per visualizzare le eccezioni di un record
            $('#recordsDataTable').on('click', '.view-exceptions', function () {
                const recordId = $(this).data('id');
                const recordNumber = $(this).data('numero');
                loadExceptionsForRecord(recordId, recordNumber);
            });

            // DataTable per i reparti
            $('#departmentsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_departments.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'nome_reparto' },
                    {
                        data: 'attivo',
                        render: function (data) {
                            return data == 1 ? '<span class="badge badge-success">Attivo</span>' : '<span class="badge badge-danger">Inattivo</span>';
                        }
                    },
                    { data: 'ordine' },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info edit-department" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-department" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[3, 'asc']]
            });

            // DataTable per i tipi di difetti
            $('#defectsDataTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'hermes/get_defects.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'descrizione' },
                    { data: 'categoria' },
                    {
                        data: 'attivo',
                        render: function (data) {
                            return data == 1 ? '<span class="badge badge-success">Attivo</span>' : '<span class="badge badge-danger">Inattivo</span>';
                        }
                    },
                    { data: 'ordine' },
                    { data: 'data_creazione' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info edit-defect" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-defect" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[4, 'asc']]
            });

            // Event listeners per i pulsanti di modifica
            $('#recordsDataTable').on('click', '.edit-record', function () {
                var id = $(this).data('id');
                editRecord(id);
            });

            // Event listener per modificare eccezioni
            $('#recordExceptionsTable').on('click', '.edit-exception', function () {
                var id = $(this).data('id');
                editException(id);
            });

            $('#departmentsDataTable').on('click', '.edit-department', function () {
                var id = $(this).data('id');
                editDepartment(id);
            });

            $('#defectsDataTable').on('click', '.edit-defect', function () {
                var id = $(this).data('id');
                editDefect(id);
            });

            // Event listeners per i pulsanti di eliminazione
            $('#recordsDataTable').on('click', '.delete-record', function () {
                var id = $(this).data('id');
                deleteRecord(id);
            });

            $('#recordExceptionsTable').on('click', '.delete-exception', function () {
                var id = $(this).data('id');
                deleteException(id);
            });

            $('#departmentsDataTable').on('click', '.delete-department', function () {
                var id = $(this).data('id');
                deleteDepartment(id);
            });

            $('#defectsDataTable').on('click', '.delete-defect', function () {
                var id = $(this).data('id');
                deleteDefect(id);
            });
        }

        function loadDashboardData() {
            // Carica i conteggi per la dashboard
            $.ajax({
                url: 'hermes/get_dashboard_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#total-records').text(data.total_records);
                    $('#today-records').text(data.today_records);
                    $('#total-exceptions').text(data.total_exceptions);
                    $('#today-exceptions').text(data.today_exceptions);
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento delle statistiche dashboard:', error);
                }
            });
        }

        function loadSelectOptions() {
            // Carica le opzioni per il select dei reparti
            $.ajax({
                url: 'hermes/get_departments.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '';
                    $.each(data, function (index, department) {
                        if (department.attivo == 1) {
                            options += `<option value="${department.nome_reparto}">${department.nome_reparto}</option>`;
                        }
                    });
                    $('#reparto').html(options);
                }
            });

            // Carica le opzioni per il select dei tipi di difetti
            $.ajax({
                url: 'hermes/get_defects.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var options = '';
                    $.each(data, function (index, defect) {
                        if (defect.attivo == 1) {
                            options += `<option value="${defect.descrizione}">${defect.descrizione}</option>`;
                        }
                    });
                    $('#tipo_difetto').html(options);
                }
            });
        }

        function saveRecord() {
            var formData = new FormData($('#addRecordForm')[0]);

            $.ajax({
                url: 'hermes/save_record.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addRecordModal').modal('hide');
                    $('#recordsDataTable').DataTable().ajax.reload();

                    // Aggiorna i conteggi nel calendario
                    loadCalendarEvents();

                    showAlert('success', 'Cartellino salvato con successo');
                    $('#addRecordForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del cartellino: ' + error);
                }
            });
        }

        function saveException() {
            var formData = new FormData($('#addExceptionForm')[0]);

            $.ajax({
                url: 'hermes/save_exception.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addExceptionModal').modal('hide');
                    // Aggiorna la tabella dei record
                    $('#recordsDataTable').DataTable().ajax.reload();

                    // Se stiamo visualizzando le eccezioni per un cartellino, aggiorna quella vista
                    if (selectedRecordId) {
                        $('#recordExceptionsTable').DataTable().ajax.reload();
                    }

                    // Aggiorna anche i conteggi nel calendario
                    loadCalendarEvents();

                    showAlert('success', 'Eccezione salvata con successo');
                    $('#addExceptionForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio dell\'eccezione: ' + error);
                }
            });
        }

        function saveDepartment() {
            var formData = new FormData($('#addDepartmentForm')[0]);
            formData.append('attivo', $('#attivo').is(':checked') ? 1 : 0);

            $.ajax({
                url: 'hermes/save_department.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addDepartmentModal').modal('hide');
                    $('#departmentsDataTable').DataTable().ajax.reload();
                    loadSelectOptions();
                    showAlert('success', 'Reparto salvato con successo');
                    $('#addDepartmentForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del reparto: ' + error);
                }
            });
        }

        function saveDefect() {
            var formData = new FormData($('#addDefectForm')[0]);
            formData.append('attivo', $('#defect_attivo').is(':checked') ? 1 : 0);

            $.ajax({
                url: 'hermes/save_defect.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#addDefectModal').modal('hide');
                    $('#defectsDataTable').DataTable().ajax.reload();
                    loadSelectOptions();
                    showAlert('success', 'Tipo difetto salvato con successo');
                    $('#addDefectForm')[0].reset();
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel salvataggio del tipo difetto: ' + error);
                }
            });
        }

        function editRecord(id) {
            $.ajax({
                url: 'hermes/get_record.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addRecordModalLabel').text('Modifica Cartellino');
                    $('#numero_cartellino').val(data.numero_cartellino);
                    $('#reparto').val(data.reparto);
                    $('#operatore').val(data.operatore);
                    $('#tipo_cq').val(data.tipo_cq);
                    $('#paia_totali').val(data.paia_totali);
                    $('#cod_articolo').val(data.cod_articolo);
                    $('#articolo').val(data.articolo);
                    $('#linea').val(data.linea);
                    $('#note').val(data.note);

                    // Aggiungi l'ID nascosto per l'aggiornamento
                    if ($('#record_id').length === 0) {
                        $('#addRecordForm').append('<input type="hidden" id="record_id" name="id" value="' + id + '">');
                    } else {
                        $('#record_id').val(id);
                    }

                    $('#addRecordModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del cartellino: ' + error);
                }
            });
        }

        function editException(id) {
            $.ajax({
                url: 'hermes/get_exception.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addExceptionModalLabel').text('Modifica Eccezione');
                    $('#exception_cartellino_id').val(data.cartellino_id);
                    $('#taglia').val(data.taglia);
                    $('#tipo_difetto').val(data.tipo_difetto);
                    $('#note_operatore').val(data.note_operatore);

                    // Aggiungi l'ID nascosto per l'aggiornamento
                    if ($('#exception_id').length === 0) {
                        $('#addExceptionForm').append('<input type="hidden" id="exception_id" name="id" value="' + id + '">');
                    } else {
                        $('#exception_id').val(id);
                    }

                    $('#addExceptionModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati dell\'eccezione: ' + error);
                }
            });
        }

        function editDepartment(id) {
            $.ajax({
                url: 'hermes/get_department.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addDepartmentModalLabel').text('Modifica Reparto');
                    $('#nome_reparto').val(data.nome_reparto);
                    $('#ordine').val(data.ordine);
                    $('#attivo').prop('checked', data.attivo == 1);

                    // Aggiungi l'ID nascosto per l'aggiornamento
                    if ($('#department_id').length === 0) {
                        $('#addDepartmentForm').append('<input type="hidden" id="department_id" name="id" value="' + id + '">');
                    } else {
                        $('#department_id').val(id);
                    }

                    $('#addDepartmentModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del reparto: ' + error);
                }
            });
        }

        function editDefect(id) {
            $.ajax({
                url: 'hermes/get_defect.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    $('#addDefectModalLabel').text('Modifica Tipo Difetto');
                    $('#descrizione').val(data.descrizione);
                    $('#categoria').val(data.categoria);
                    $('#defect_ordine').val(data.ordine);
                    $('#defect_attivo').prop('checked', data.attivo == 1);

                    // Aggiungi l'ID nascosto per l'aggiornamento
                    if ($('#defect_id').length === 0) {
                        $('#addDefectForm').append('<input type="hidden" id="defect_id" name="id" value="' + id + '">');
                    } else {
                        $('#defect_id').val(id);
                    }

                    $('#addDefectModal').modal('show');
                },
                error: function (xhr, status, error) {
                    showAlert('danger', 'Errore nel caricamento dei dati del tipo difetto: ' + error);
                }
            });
        }

        function deleteRecord(id) {
            if (confirm('Sei sicuro di voler eliminare questo cartellino? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_record.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#recordsDataTable').DataTable().ajax.reload();

                        // Nascondi la sezione eccezioni se era visualizzata
                        if (selectedRecordId == id) {
                            $('#record-exceptions-section').hide();
                            selectedRecordId = null;
                        }

                        // Aggiorna i conteggi nel calendario
                        loadCalendarEvents();

                        showAlert('success', 'Cartellino eliminato con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del cartellino: ' + error);
                    }
                });
            }
        }

        function deleteException(id) {
            if (confirm('Sei sicuro di voler eliminare questa eccezione? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_exception.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        // Aggiorna la tabella delle eccezioni se è visualizzata
                        if ($.fn.DataTable.isDataTable('#recordExceptionsTable')) {
                            $('#recordExceptionsTable').DataTable().ajax.reload();
                        }

                        // Aggiorna sempre la tabella dei record poiché lo stato delle eccezioni potrebbe essere cambiato
                        $('#recordsDataTable').DataTable().ajax.reload();

                        // Aggiorna i conteggi nel calendario
                        loadCalendarEvents();

                        showAlert('success', 'Eccezione eliminata con successo');
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione dell\'eccezione: ' + error);
                    }
                });
            }
        }

        function deleteDepartment(id) {
            if (confirm('Sei sicuro di voler eliminare questo reparto? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_department.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#departmentsDataTable').DataTable().ajax.reload();
                        showAlert('success', 'Reparto eliminato con successo');
                        loadSelectOptions();
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del reparto: ' + error);
                    }
                });
            }
        }

        function deleteDefect(id) {
            if (confirm('Sei sicuro di voler eliminare questo tipo difetto? Questa azione non può essere annullata.')) {
                $.ajax({
                    url: 'hermes/delete_defect.php',
                    type: 'POST',
                    data: { id: id },
                    success: function (response) {
                        $('#defectsDataTable').DataTable().ajax.reload();
                        showAlert('success', 'Tipo difetto eliminato con successo');
                        loadSelectOptions();
                    },
                    error: function (xhr, status, error) {
                        showAlert('danger', 'Errore nell\'eliminazione del tipo difetto: ' + error);
                    }
                });
            }
        }

        function initCharts() {
            // Grafico settimanale dei controlli
            $.ajax({
                url: 'hermes/get_weekly_data.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var ctx = document.getElementById('weeklyCQChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Controlli Totali',
                                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                    borderColor: 'rgba(78, 115, 223, 1)',
                                    pointRadius: 3,
                                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                                    pointHoverRadius: 3,
                                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: data.countRecords
                                },
                                {
                                    label: 'Eccezioni',
                                    backgroundColor: 'rgba(231, 74, 59, 0.05)',
                                    borderColor: 'rgba(231, 74, 59, 1)',
                                    pointRadius: 3,
                                    pointBackgroundColor: 'rgba(231, 74, 59, 1)',
                                    pointBorderColor: 'rgba(231, 74, 59, 1)',
                                    pointHoverRadius: 3,
                                    pointHoverBackgroundColor: 'rgba(231, 74, 59, 1)',
                                    pointHoverBorderColor: 'rgba(231, 74, 59, 1)',
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: data.countExceptions
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    left: 10,
                                    right: 25,
                                    top: 25,
                                    bottom: 0
                                }
                            },
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'day'
                                    },
                                    gridLines: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 7
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        maxTicksLimit: 5,
                                        padding: 10,
                                        beginAtZero: true
                                    },
                                    gridLines: {
                                        color: "rgb(234, 236, 244)",
                                        zeroLineColor: "rgb(234, 236, 244)",
                                        drawBorder: false,
                                        borderDash: [2],
                                        zeroLineBorderDash: [2]
                                    }
                                }],
                            },
                            legend: {
                                display: true
                            },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10
                            }
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento dei dati settimanali:', error);
                }
            });

            // Grafico a torta per i tipi di difetti
            $.ajax({
                url: 'hermes/get_defects_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    var ctx = document.getElementById('defectsPieChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.counts,
                                backgroundColor: [
                                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'
                                ],
                                hoverBackgroundColor: [
                                    '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#3a3b45', '#60616f'
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                            },
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            cutoutPercentage: 70,
                        },
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Errore nel caricamento delle statistiche difetti:', error);
                }
            });
        }

        function showAlert(type, message) {
            var alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            // Aggiungi l'alert sopra il contenuto e imposta un timer per rimuoverlo
            $('.container-fluid').prepend(alertHtml);

            // Rimuovi l'alert dopo 5 secondi
            setTimeout(function () {
                $('.alert').alert('close');
            }, 5000);
        }

        $(document).ready(function () {
            // Inizializza il calendario manuale
            initCalendar();

            // Inizializza i DataTables
            initDataTables();

            // Carica i dati per la dashboard
            loadDashboardData();

            // Carica le opzioni per i select nei form
            loadSelectOptions();

            // Event listeners per i pulsanti di salvataggio
            $('#saveRecordBtn').on('click', saveRecord);
            $('#saveExceptionBtn').on('click', saveException);
            $('#saveDepartmentBtn').on('click', saveDepartment);
            $('#saveDefectBtn').on('click', saveDefect);

            // Inizializza i grafici
            initCharts();

            // Imposta la visualizzazione della data corrente
            updateSelectedDateDisplay();

            // Quando cambia il tab, si assicura che il calendario sia aggiornato
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'records-tab') {
                    updateCalendar();
                }
            });
        });
    </script>
</body>