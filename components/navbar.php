<!-- SIDEBAR -->
<?php
$colore = (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary";
// Verifica se la sessione contiene lo stato della navbar, altrimenti imposta un valore predefinito
if (!isset($_SESSION['navbar_toggled'])) {
    $_SESSION['navbar_toggled'] = false; // Valore predefinito
}
// Gestione della modifica dello stato della navbar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_navbar'])) {
    $_SESSION['navbar_toggled'] = $_POST['toggle_navbar'] === 'true';
}
?>

<ul class="navbar-nav shadow bg-gradient-<?php echo $colore; ?> sidebar sidebar-dark accordion <?php echo $_SESSION['navbar_toggled'] ? '' : 'toggled'; ?> fixed-sidebar"
    id="accordionSidebar">
    <!-- SIDEBAR INTESTAZIONE -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL ?>/index">
        <div class="sidebar-brand-icon">
            <img src="<?php echo BASE_URL ?>/img/roundLogo.png" alt="" width="40" height="40">
        </div>
        <div class="sidebar-brand-text mx-3">WEBGRE</div>
    </a>
    <!-- DIVISORE -->
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a id="home" class="nav-link" href="<?php echo BASE_URL ?>/index">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>
    <!-- DIVISORE -->
    <hr class="sidebar-divider">
    <style>
        .border-width-6 {
            border-width: 6px !important;
        }

        .nav-item .active {
            color: white !important;
            border-left: solid 6pt white;
            border-radius: 0 10px 10px 0;
            width: 95% !important;
            background-color: rgba(0, 0, 0, 0.05);
            /* Scuro trasparente sopra il background esistente */
        }

        .nav-item .active {
            color: white !important;
            border-left: solid 6pt white;
            border-radius: 0 10px 10px 0;
            width: 95% !important;
            background-color: rgba(0, 0, 0, 0.05);
            /* Scuro trasparente sopra il background esistente */
        }
    </style>
    <!-- TITOLO SEZIONE -->
    <div class="sidebar-heading">
        Funzioni
    </div>
    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
        <!-- RIPARAZIONI -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRiparazioni"
                aria-expanded="true" aria-controls="collapseRiparazioni">
                <i class="fas fa-fw fa-hammer"></i>
                <span>Riparazioni</span>
            </a>
            <div id="collapseRiparazioni" class="collapse" style="z-index:9999" aria-labelledby="headingRiparazioni"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="riparazioni-add-step1" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/riparazioni/add_step1"><i class="fa fa-plus fa-fw"></i>
                        Nuova</a>
                    <a id="riparazioni-elenco" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni"><i class="fa fa-list fa-fw"></i>
                        Elenco</a>
                    <a id="riparazioni-cerca" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/riparazioni/search"><i class="fa fa-search fa-fw"></i>
                        Cerca</a>
                    <a id="riparazioni-close-barcode" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/riparazioni/close_barcode"><i class="far fa-scanner"></i>
                        Chiudi Più</a>
                    <a id="riparazioni-make-plist" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/riparazioni/make_plist"><i class="fa fa-stream fa-fw"></i>
                        Packing List</a>
                </div>
            </div>
        </li>

        <!-- EXPORT -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseExport" aria-expanded="true"
                aria-controls="collapseExport">
                <i class="fas fa-fw fa-globe-europe"></i>
                <span>Export</span>
            </a>
            <div id="collapseExport" class="collapse" style="z-index:9999" aria-labelledby="headingExport"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="export-add-step1" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/export/new_step1"><i class="fa fa-plus fa-fw"></i>
                        Nuova</a>
                    <a id="export-elenco" class="collapse-item" href="<?php echo BASE_URL ?>/functions/export/documenti"><i
                            class="fa fa-list fa-fw"></i>
                        Elenco</a>
                    <a id="export-terzisti" class="collapse-item" href="<?php echo BASE_URL ?>/functions/export/terzisti"><i
                            class="fa fa-user fa-fw"></i>
                        Terzisti</a>

                </div>
            </div>
        </li>

        <!-- SCM -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSCM" aria-expanded="true"
                aria-controls="collapseSCM">
                <i class="far fa-user-hard-hat"></i>
                <span>SCM</span>
            </a>
            <div id="collapseSCM" class="collapse" style="z-index:9999" aria-labelledby="headingSCM"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="scm-home" class="collapse-item" href="<?php echo BASE_URL ?>/functions/scm/index"><i
                            class="fa fa-home"></i>
                        Home</a>
                  
                </div>
            </div>
        </li>



    <?php endif; ?>
    <!-- CONTROLLO QUALITA -->
    <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCQ" aria-expanded="true"
                aria-controls="collapseCQ">
                <i class="far fa-box-check"></i>
                <span>Controllo Qualità</span>
            </a>
            <div id="collapseCQ" class="collapse" style="z-index:9999" aria-labelledby="headingCQ"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="quality-home" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/home"><i
                            class="fa fa-home"></i>
                        Home</a>
                    <a id="quality-new" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/new"><i
                            class="fa fa-plus"></i>
                        Nuova</a>
                    <a id="quality-read" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/read"><i
                            class="far fa-calendar-alt"></i>
                        Consulta</a>
                    <a id="quality-search" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/search"><i
                            class="fa fa-search"></i>
                        Cerca</a>
                    <a id="quality-barcode" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/barcode"><i
                            class="fal fa-barcode-alt"></i>
                        Barcodes</a>
                    <a id="quality-plist" class="collapse-item" href="<?php echo BASE_URL ?>/functions/quality/makePList"><i
                            class="far fa-chart-pie-alt"></i>
                        Packing List</a>
                </div>
            </div>
        </li>

    <?php endif; ?>


    <!-- PRODUZIONE & SPEDIZIONE -->
    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProd" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-calendar-alt"></i>
                <span>Produzione</span>
            </a>
            <div id="collapseProd" class="collapse" style="z-index:9999" aria-labelledby="headingProd"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="production-new" class="collapse-item" href="<?php echo BASE_URL ?>/functions/production/new"><i
                            class="fa fa-plus"></i>
                        Nuova Produzione</a>
                    <a id="production-ship" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/production/newShipment"><i class="fa fa-truck-fast"></i>
                        Nuova Spedizione</a>
                    <a id="production-calendario" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/production/calendario"><i class="far fa-calendar-alt"></i>
                        Calendario</a>
                </div>
            </div>
        </li>


    <?php endif; ?>
    <!-- CAMPIONARIO -->
    <?php if (isset($_SESSION['permessi_campionario']) && $_SESSION['permessi_campionario'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSamples"
                aria-expanded="true" aria-controls="collapseSamples">
                <i class="fa-solid fa-cloud"></i>
                <span>Campionario</span>
            </a>
            <div id="collapseSamples" class="collapse" style="z-index:9999" aria-labelledby="headingSamples"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="samples-new" class="collapse-item" href="<?php echo BASE_URL ?>/functions/samples/newSample"><i
                            class="fa fa-plus"></i>
                        Nuovo</a>
                    <a id="samples-list" class="collapse-item" href="<?php echo BASE_URL ?>/functions/samples/list"><i
                            class="far fa-list"></i>
                        In corso</a>
                </div>
            </div>
        </li>
    <?php endif; ?>
    <!-- TRACKING -->
    <?php if (isset($_SESSION['permessi_tracking']) && $_SESSION['permessi_tracking'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTracking"
                aria-expanded="true" aria-controls="collapseTracking">
                <i class="fal fa-radar fa-lg"></i>
                <span>Tracking</span>
            </a>
            <div id="collapseTracking" class="collapse" style="z-index:9999" aria-labelledby="headingTracking"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="tracking-home" class="collapse-item" href="<?php echo BASE_URL ?>/functions/tracking/home"><i
                            class="fa fa-home"></i>
                        Menu</a>
                    <a id="tracking-multi" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/multiSearch"><i
                            class="fa fa-magnifying-glass-plus"></i>
                        Associa per Ricerca</a>
                    <a id="tracking-order" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/orderSearch"><i class="fa fa-link"></i>
                        Associa per Cartellini</a>
                    <a id="tracking-pkl" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/makePackingList"><i class="fa fa-list"></i>
                        Crea Packing List</a>
                    <a id="tracking-tree" class="collapse-item" href="<?php echo BASE_URL ?>/functions/tracking/treeView"><i
                            class="fa fa-folder-tree"></i>
                        Albero Dettagli</a>
                    <a id="tracking-lots-details" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/lotDetailManager"><i class="fa fa-file-invoice"></i>
                        Dettagli Lotti</a>
                    <a id="tracking-sku" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/skuManager"><i class="fa fa-barcode"></i>
                        Dettagli SKU</a>
                    <a id="tracking-dates" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/orderDateManager"><i class="fa fa-calendar"></i>
                        Dettagli Date</a>
                    <a id="tracking-fiches" class="collapse-item"
                        href="<?php echo BASE_URL ?>/functions/tracking/makeFiches"><i class="fa fa-tag"></i>
                        Stampa Fiches</a>
                </div>
            </div>
        </li>
    <?php endif; ?>



    <!-- DIVISORE -->
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Strumenti
    </div>
    <!-- UTENTI -->
    <?php if (isset($_SESSION['permessi_utenti']) && $_SESSION['permessi_utenti'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" id="users" href="<?php echo BASE_URL ?>/functions/users/manageUsers"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-users"></i>
                <span>Utenti</span>
            </a>
        </li>
    <?php endif; ?>
    <!-- LOG ATTIVITA' -->
    <?php if (isset($_SESSION['permessi_log']) && $_SESSION['permessi_log'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" id="log" href="<?php echo BASE_URL ?>/functions/users/log_admin"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-monitor-heart-rate"></i>
                <span>Attività</span>
            </a>
        </li>
    <?php endif; ?>
    <!-- ETICHETTE -->
    <?php if (isset($_SESSION['permessi_etichette']) && $_SESSION['permessi_etichette'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" id="etichette" href="<?php echo BASE_URL ?>/functions/tools/eti_index"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-barcode-alt"></i>
                <span>Etichette</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if (isset($_SESSION['permessi_sql']) && $_SESSION['permessi_sql'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" id="database" href="<?php echo BASE_URL ?>/functions/database/manager"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-database"></i>
                <span>Database</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" id="filemanager" href="<?php echo BASE_URL ?>/functions/files/manager"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-folders"></i>
                <span>Files</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if (isset($_SESSION['permessi_settings']) && $_SESSION['permessi_settings'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" id="settings" href="<?php echo BASE_URL ?>/functions/settings/settings"
                aria-expanded="true" aria-controls="collapseProd">
                <i class="far fa-cog"></i>
                <span>Impostazioni</span>
            </a>
        </li>
    <?php endif; ?>
    <!-- TITOLO SEZIONE -->
    <!-- DIVISORE -->
    <hr class="sidebar-divider d-none d-md-block">
    <!-- TOGGLE SIDEBAR -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebarToggleButton = document.getElementById('sidebarToggle');
        if (sidebarToggleButton) {
            sidebarToggleButton.addEventListener('click', function () {
                var isToggled = document.getElementById('accordionSidebar').classList.contains('toggled');
                // Invia una richiesta AJAX al server per aggiornare lo stato della navbar
                var xhr = new XMLHttpRequest();
                xhr.open('POST', window.location.href, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('toggle_navbar=' + !isToggled);
            });
        }
        var currentUrl = window.location.pathname;
        // Rimuove eventuali prefissi e normalizza l'URL
        currentUrl = currentUrl.replace(/\/$/, ''); // Rimuove l'eventuale barra finale
        // Mappa degli URL alle classi degli elementi di navigazione
        var navLinks = {
            '/index': 'home',
            '/functions/riparazioni/add_step1': 'riparazioni-add-step1',
            '/functions/riparazioni/riparazioni': 'riparazioni-elenco',
            '/functions/riparazioni/search': 'riparazioni-cerca',
            '/functions/riparazioni/close_barcode': 'riparazioni-close-barcode',
            '/functions/riparazioni/make_plist': 'riparazioni-make-plist',
            '/functions/quality/new': 'quality-new',
            '/functions/quality/read': 'quality-read',
            '/functions/quality/search': 'quality-search',
            '/functions/quality/barcode': 'quality-barcode',
            '/functions/quality/home': 'quality-home',
            '/functions/quality/makePList': 'quality-plist',
            '/functions/production/new': 'production-new',
            '/functions/production/newShipment': 'production-ship',
            '/functions/production/calendario': 'production-calendario',
            '/functions/samples/newSample': 'samples-new',
            '/functions/samples/list': 'samples-list',
            '/functions/samples/done': 'samples-done',
            '/functions/tracking/home': 'tracking-home',
            '/functions/tracking/multiSearch': 'tracking-multi',
            '/functions/tracking/orderSearch': 'tracking-order',
            '/functions/tracking/makePackingList': 'tracking-pkl',
            '/functions/tracking/treeView': 'tracking-tree',
            '/functions/tracking/lotDetailManager': 'tracking-lots-details',
            '/functions/tracking/makeFiches': 'tracking-fiches',
            '/functions/tracking/skuManager': 'tracking-sku',
            '/functions/tracking/orderDateManager': 'tracking-dates',
            '/functions/tools/eti_index': 'etichette',
            '/functions/users/log_admin': 'log',
            '/functions/users/manageUsers': 'users',
            '/functions/settings/settings': 'settings',
            '/functions/database/manager': 'database',
            '/functions/files/manager': 'filemanager',
            '/functions/shipTracker/index': 'shipTracker',
            '/functions/attrezzatura/manage': 'attrezzatura-manage',
            '/functions/attrezzatura/return': 'attrezzatura-return',
            '/functions/export/documenti': 'export-elenco',
            '/functions/export/new_step1': 'export-add-step1',
            '/functions/export/terzisti': 'export-terzisti',

            // Aggiungi qui altri link come necessario
        };
        // Controlla se l'URL corrente corrisponde a uno degli URL nel menu
        for (var url in navLinks) {
            if (navLinks.hasOwnProperty(url) && currentUrl.includes(url)) {
                var navItem = document.getElementById(navLinks[url]);
                if (navItem) {

                    navItem.classList.add('text-<?php echo $colore; ?>');
                    var parentNavLink = navItem.closest('.nav-item').querySelector('.nav-link');
                    if (parentNavLink) {
                        parentNavLink.classList.remove('collapsed');
                        parentNavLink.classList.add('active');
                    }
                    var parentCollapse = navItem.closest('.collapse');
                    var navbarNav = document.getElementById('accordionSidebar');
                    if (parentCollapse && (!navbarNav || !navbarNav.classList.contains('toggled'))) {
                        parentCollapse.classList.add('show');


                    }
                }
            }
        }
        // Gestione speciale per la dashboard
        if (currentUrl.endsWith('/index') || currentUrl === '/index' || currentUrl === '/' || currentUrl.endsWith('/index.php')) {
            var homeNavItem = document.getElementById('home');
            if (homeNavItem) {
                homeNavItem.classList.add('active');
                homeNavItem.classList.remove('text-<?php echo $colore; ?>');
            }
        }
    });
</script>
<!-- FINE SIDEBAR -->