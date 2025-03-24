<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <!-- Header con icona e titoli -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-gradient-primary text-white p-3 rounded shadow-sm">
                                <i class="fas fa-clipboard-check fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Sistema CQ Emmegiemme</h1>
                                <p class="mb-0 text-gray-600">Controllo Qualità e Gestione Test</p>
                            </div>
                        </div>
                      
                    </div>
                    
                    <!-- Barra di navigazione personalizzata -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <div class="nav-scroller">
                                        <nav class="nav nav-underline justify-content-around p-2">
                                            <a class="nav-link active px-4 py-3 rounded text-center" href="new">
                                                <i class="fad fa-plus fa-lg d-block mb-2"></i>
                                                <span>Nuovo Test</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="read">
                                                <i class="fad fa-folder-tree fa-lg d-block mb-2"></i>
                                                <span>Consulta</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="search">
                                                <i class="fad fa-search fa-lg d-block mb-2"></i>
                                                <span>Cerca</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="barcode">
                                                <i class="fad fa-barcode fa-lg d-block mb-2"></i>
                                                <span>Gestione Barcode</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="makePList">
                                                <i class="fad fa-list fa-lg d-block mb-2"></i>
                                                <span>Packing List</span>
                                            </a>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Area di lavoro principale -->
                    <div class="row">
                        <!-- Colonna sinistra: Azioni veloci -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Azioni veloci</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-success text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-plus-circle fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Nuovo Test</h5>
                                                    <p class="small text-center mb-3">Avvia un nuovo controllo qualità</p>
                                                    <a href="new" class="btn btn-light btn-sm btn-block">Inizia <i class="fas fa-arrow-right ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-info text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-folder-open fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Consulta</h5>
                                                    <p class="small text-center mb-3">Visualizza registrazioni per data</p>
                                                    <a href="read" class="btn btn-light btn-sm btn-block">Visualizza <i class="fas fa-eye ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-dark text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-search fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Cerca</h5>
                                                    <p class="small text-center mb-3">Ricerca avanzata</p>
                                                    <a href="search" class="btn btn-light btn-sm btn-block">Cerca <i class="fas fa-search ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colonna destra: Strumenti -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Strumenti e output</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <a href="barcode" class="btn btn-light border-left-indigo shadow-sm btn-block text-left d-flex align-items-center py-3 mb-3">
                                            <div class="btn btn-indigo btn-circle mr-3">
                                                <i class="fas fa-barcode"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-indigo text-uppercase">Configurazione</div>
                                                <div class="font-weight-bold">Gestione Barcode</div>
                                                <div class="small text-gray-600">Codici e motivi rapidi</div>
                                            </div>
                                        </a>
                                        
                                        <a href="makePList" class="btn btn-light border-left-warning shadow-sm btn-block text-left d-flex align-items-center py-3">
                                            <div class="btn btn-warning btn-circle mr-3">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-warning text-uppercase">Output</div>
                                                <div class="font-weight-bold">Genera Packing List</div>
                                                <div class="small text-gray-600">PDF per il Cliente</div>
                                            </div>
                                        </a>

                                        <a href="#" class="btn btn-light border-left-success shadow-sm btn-block text-left d-flex align-items-center py-3">
                                            <div class="btn btn-success btn-circle mr-3">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-warning text-uppercase">Operatori</div>
                                                <div class="font-weight-bold">Gestisci gli utenti per il controllo qualità</div>
                                                <div class="small text-gray-600">Modifica utenti</div>
                                            </div>
                                        </a>
                                    </div>
                                    
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    
    <script>
    // Attiva gli hover effetti sui menu nav
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.classList.add('bg-light');
                }
            });
            link.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.classList.remove('bg-light');
                }
            });
        });
    });
    </script>
</body>