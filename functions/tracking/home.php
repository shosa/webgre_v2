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
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                                <p class="mb-0 text-gray-600">Tracking e gestione di lotti e cartellini</p>
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
                                            <a class="nav-link px-4 py-3 rounded text-center" href="multiSearch">
                                                <i class="fad fa-search-plus fa-lg d-block mb-2"></i>
                                                <span>Associa Ricerca</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="orderSearch">
                                                <i class="fad fa-link fa-lg d-block mb-2"></i>
                                                <span>Associa Cartellini</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="treeView">
                                                <i class="fad fa-folder-tree fa-lg d-block mb-2"></i>
                                                <span>Albero Dettagli</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="lotDetailManager">
                                                <i class="fad fa-file-invoice fa-lg d-block mb-2"></i>
                                                <span>Dettagli Lotti</span>
                                            </a>
                                            <a class="nav-link px-4 py-3 rounded text-center" href="makePackingList">
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
                        <!-- Colonna sinistra: Associazione e Mappatura -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Associazione e Mappatura</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-success text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-search-plus fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Associa per Ricerca</h5>
                                                    <p class="small text-center mb-3">Utilizza i campi di ricerca per selezionare i cartellini</p>
                                                    <a href="multiSearch" class="btn btn-light btn-sm btn-block">Associa <i class="fas fa-arrow-right ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-success text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-link fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Associa per Cartellini</h5>
                                                    <p class="small text-center mb-3">Inserisci manualmente i singoli cartellini</p>
                                                    <a href="orderSearch" class="btn btn-light btn-sm btn-block">Associa <i class="fas fa-arrow-right ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card bg-gradient-info text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-folder-tree fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Albero Dettagli</h5>
                                                    <p class="small text-center mb-3">Visualizza e modifica la mappatura lotti/cartellini</p>
                                                    <a href="treeView" class="btn btn-light btn-sm btn-block">Visualizza <i class="fas fa-eye ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seconda card: Gestione Dettagli -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-dark">Gestione Dettagli</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <div class="card bg-gradient-dark text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-file-invoice fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Dettagli Lotti</h5>
                                                    <p class="small text-center mb-3">Aggiungi riferimenti di consegna</p>
                                                    <a href="lotDetailManager" class="btn btn-light btn-sm btn-block">Gestisci <i class="fas fa-cog ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-4">
                                            <div class="card bg-gradient-dark text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-barcode fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Dettagli SKU</h5>
                                                    <p class="small text-center mb-3">Aggiungi riferimenti articolo cliente</p>
                                                    <a href="skuManager" class="btn btn-light btn-sm btn-block">Gestisci <i class="fas fa-cog ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-4">
                                            <div class="card bg-gradient-dark text-white shadow h-100">
                                                <div class="card-body">
                                                    <div class="text-center mb-2">
                                                        <i class="fad fa-calendar fa-4x opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-center">Dettagli Ordini/Date</h5>
                                                    <p class="small text-center mb-3">Aggiungi riferimenti date ordini</p>
                                                    <a href="orderDateManager" class="btn btn-light btn-sm btn-block">Gestisci <i class="fas fa-cog ml-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colonna destra: Output e Documentazione -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">Output e Documentazione</h6>
                                </div>
                                <div class="card-body">
                                    
                                
                                    <div class="mb-3">
                                        <a href="makePackingList" class="btn btn-light border-left-warning shadow-sm btn-block text-left d-flex align-items-center py-3 mb-3">
                                            <div class="btn btn-warning btn-circle mr-3">
                                                <i class="fas fa-list"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-warning text-uppercase">Documentazione</div>
                                                <div class="font-weight-bold">Packing List</div>
                                                <div class="small text-gray-600">Genera dettaglio lotti per cartellini</div>
                                            </div>
                                        </a>
                                        
                                        <a href="makeFiches" class="btn btn-light border-left-indigo shadow-sm btn-block text-left d-flex align-items-center py-3">
                                            <div class="btn btn-indigo btn-circle mr-3">
                                                <i class="fas fa-tags"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs text-indigo text-uppercase">Documentazione</div>
                                                <div class="font-weight-bold">Stampa Fiches</div>
                                                <div class="small text-gray-600">Genera fiches per retro cartellini</div>
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