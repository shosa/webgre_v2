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
                                <i class="fas fa-industry fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Gestione Macchinari Aziendali</h1>
                                <p class="mb-0 text-gray-600">Monitoraggio e manutenzione apparecchiature</p>
                            </div>
                        </div>
                        <a href="new" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Nuovo Macchinario
                        </a>
                    </div>
                    
                    <!-- Barra di navigazione personalizzata -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <div class="nav-scroller">
                                        <nav class="nav nav-underline justify-content-around p-2">
                                            <a class="nav-link px-4 py-3 rounded text-center" href="new">
                                                <i class="fad fa-plus fa-lg d-block mb-2"></i>
                                                <span>Nuovo</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="lista_macchinari">
                                                <i class="fad fa-list-alt fa-lg d-block mb-2"></i>
                                                <span>Lista</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="#">
                                                <i class="fad fa-search fa-lg d-block mb-2"></i>
                                                <span>Cerca</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="#">
                                                <i class="fad fa-tools fa-lg d-block mb-2"></i>
                                                <span>Manutenzioni</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="#">
                                                <i class="fad fa-chart-bar fa-lg d-block mb-2"></i>
                                                <span>Report</span>
                                            </a>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Area di lavoro principale -->
                    <div class="row">
                        <!-- Colonna sinistra: Gestione principale -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Gestione Apparecchiature</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-success text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-plus-circle fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Nuovo Macchinario</h5>
                                                    <p class="small text-center mb-3">Aggiungi una nuova anagrafica</p>
                                                    <a href="new" class="btn btn-light btn-sm btn-block">Aggiungi <i class="fas fa-arrow-right ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-info text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-list-alt fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Lista Macchinari</h5>
                                                    <p class="small text-center mb-3">Visualizza tutti i macchinari registrati</p>
                                                    <a href="lista_macchinari" class="btn btn-light btn-sm btn-block">Visualizza <i class="fas fa-eye ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-dark text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-search fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Cerca Macchinari</h5>
                                                    <p class="small text-center mb-3">Ricerca per matricola, tipologia, modello</p>
                                                    <a href="#" class="btn btn-light btn-sm btn-block">Cerca <i class="fas fa-search ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seconda card: Manutenzioni e Report -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">Monitoraggio e Reportistica</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-gradient-warning text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-tools fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Manutenzioni</h5>
                                                    <p class="small text-center mb-3">Gestisci le manutenzioni programmate</p>
                                                    <a href="#" class="btn btn-light btn-sm btn-block">Gestisci <i class="fas fa-wrench ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-gradient-primary text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-chart-bar fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Reportistica</h5>
                                                    <p class="small text-center mb-3">Statistiche e report sui macchinari</p>
                                                    <a href="#" class="btn btn-light btn-sm btn-block">Visualizza <i class="fas fa-chart-line ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colonna destra: Dashboard e Statistiche -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Dashboard Macchinari</h6>
                                </div>
                                <div class="card-body">
                                  
                                    
                                    <div class="mt-3">
                                        <a href="#" class="btn btn-light border-left-warning shadow-sm btn-block text-left d-flex align-items-center py-3 mb-3">
                                            <div class="btn btn-warning btn-circle mr-3">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-warning text-uppercase">Attenzione</div>
                                                <div class="font-weight-bold">Manutenzioni in scadenza</div>
                                                <div class="small text-gray-600">## macchinari necessitano di controllo</div>
                                            </div>
                                        </a>
                                        
                                        <a href="lista_macchinari" class="btn btn-light border-left-info shadow-sm btn-block text-left d-flex align-items-center py-3">
                                            <div class="btn btn-info btn-circle mr-3">
                                                <i class="fas fa-clipboard-list"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-info text-uppercase">Inventario</div>
                                                <div class="font-weight-bold">Lista completa</div>
                                                <div class="small text-gray-600">Visualizza tutti i macchinari</div>
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