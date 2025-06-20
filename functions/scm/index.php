<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Statistiche essenziali
try {
    $pdo = getDbInstance();
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as totale_lanci,
            COUNT(CASE WHEN stato_generale = 'IN_LAVORAZIONE' THEN 1 END) as in_lavorazione,
            COUNT(CASE WHEN stato_generale = 'COMPLETATO' THEN 1 END) as completati
        FROM scm_lanci
    ");
    $stats_lanci = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT COUNT(*) as totale_laboratori FROM scm_laboratori WHERE attivo = TRUE");
    $stats_laboratori = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-gradient-primary text-white p-3 rounded shadow-sm">
                                <i class="fas fa-industry fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Sistema SCM</h1>
                                <p class="mb-0 text-gray-600">Gestione produzione terzisti</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="small text-muted">Totale Lanci: <strong><?= $stats_lanci['totale_lanci'] ?></strong></div>
                            <div class="small text-muted">In Lavorazione: <strong><?= $stats_lanci['in_lavorazione'] ?></strong></div>
                            <div class="small text-muted">Laboratori Attivi: <strong><?= $stats_laboratori['totale_laboratori'] ?></strong></div>
                        </div>
                    </div>
                    
                    <!-- Menu Principale -->
                    <div class="row">
                        <!-- Gestione Laboratori -->
                        <div class="col-lg-6 mb-4">
                            <div class="border-left-primary shadow-sm p-4 bg-white">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-building fa-2x text-primary mr-3"></i>
                                    <div>
                                        <h5 class="mb-0">Gestione Laboratori</h5>
                                        <small class="text-muted">Crea e gestisci i laboratori terzisti</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="crea_laboratorio" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus mr-2"></i>Nuovo Laboratorio
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="lista_laboratori" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-list mr-2"></i>Lista Laboratori
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gestione Lanci -->
                        <div class="col-lg-6 mb-4">
                            <div class="border-left-success shadow-sm p-4 bg-white">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-rocket fa-2x text-success mr-3"></i>
                                    <div>
                                        <h5 class="mb-0">Gestione Lanci</h5>
                                        <small class="text-muted">Crea e gestisci i lanci di produzione</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="crea_lancio" class="btn btn-success btn-block">
                                            <i class="fas fa-plus mr-2"></i>Nuovo Lancio
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="lista_lanci" class="btn btn-outline-success btn-block">
                                            <i class="fas fa-list mr-2"></i>Lista Lanci
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Visualizzazioni -->
                    <div class="row">
                        <!-- Vista per Laboratorio -->
                        <div class="col-lg-6 mb-4">
                            <div class="border-left-info shadow-sm p-4 bg-white">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-eye fa-2x text-info mr-3"></i>
                                    <div>
                                        <h5 class="mb-0">Vista per Laboratorio</h5>
                                        <small class="text-muted">Visualizza lanci raggruppati per laboratorio</small>
                                    </div>
                                </div>
                                <a href="vista_laboratori" class="btn btn-info btn-block">
                                    <i class="fas fa-building mr-2"></i>Visualizza per Laboratorio
                                </a>
                            </div>
                        </div>
                        
                        <!-- Vista per Lancio -->
                        <div class="col-lg-6 mb-4">
                            <div class="border-left-warning shadow-sm p-4 bg-white">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-search fa-2x text-warning mr-3"></i>
                                    <div>
                                        <h5 class="mb-0">Vista per Lancio</h5>
                                        <small class="text-muted">Cerca e filtra lanci con opzioni avanzate</small>
                                    </div>
                                </div>
                                <a href="vista_lanci" class="btn btn-warning btn-block">
                                    <i class="fas fa-rocket mr-2"></i>Visualizza per Lancio
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>