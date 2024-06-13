<!-- SIDEBAR -->
<ul class="navbar-nav bg-gradient-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?> sidebar sidebar-dark accordion toggled"
    id="accordionSidebar">

    <!-- SIDEBAR INTESTAZIONE -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../../index">
        <div class="sidebar-brand-icon">
            <img src="<?php BASE_PATH ?>/img/roundLogo.png" alt="" width="40" height="40">
        </div>
        <div class="sidebar-brand-text mx-3">WEBGRE </div>
    </a>

    <!-- DIVISORE -->
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a id="home" class="nav-link" href="../../index">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- DIVISORE -->
    <hr class="sidebar-divider">

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
            <div id="collapseRiparazioni" class="collapse" aria-labelledby="headingRiparazioni"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="riparazioni-add-step1" class="collapse-item" href="../../functions/riparazioni/add_step1"><i
                            class="fa fa-plus fa-fw"></i> Nuova</a>
                    <a id="riparazioni-elenco" class="collapse-item" href="../../functions/riparazioni/riparazioni"><i
                            class="fa fa-list fa-fw"></i> Elenco</a>
                    <a id="riparazioni-cerca" class="collapse-item" href="../../functions/riparazioni/search"><i
                            class="fa fa-search fa-fw"></i> Cerca</a>
                    <a id="riparazioni-close-barcode" class="collapse-item"
                        href="../../functions/riparazioni/close_barcode"><i class="far fa-scanner"></i> Chiudi Più</a>
                    <a id="riparazioni-make-plist" class="collapse-item" href="../../functions/riparazioni/make_plist"><i
                            class="fa fa-stream fa-fw"></i> Packing List</a>
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
            <div id="collapseCQ" class="collapse" aria-labelledby="headingCQ" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="quality-new" class="collapse-item" href="../../functions/quality/new"><i class="fa fa-plus"></i>
                        Nuova</a>
                    <a id="quality-read" class="collapse-item" href="../../functions/quality/read"><i
                            class="far fa-calendar-alt"></i>
                        Consulta</a>
                    <a id="quality-search" class="collapse-item" href="../../functions/quality/search"><i
                            class="fa fa-search"></i>
                        Cerca</a>
                    <a id="quality-barcode" class="collapse-item" href="../../functions/quality/barcode"><i
                            class="fal fa-barcode-alt"></i>
                        Barcodes</a>
                    <a id="quality-charts" class="collapse-item" href="../../functions/quality/chartsOverview"><i
                            class="far fa-chart-pie-alt"></i>
                        Statistiche</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <!-- PRODUZIONE -->
    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProd" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-calendar-alt"></i>
                <span>Produzione</span>
            </a>
            <div id="collapseProd" class="collapse" aria-labelledby="headingProd" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="production-new" class="collapse-item" href="../../functions/production/new"><i
                            class="fa fa-plus"></i>
                        Nuova</a>
                    <a id="production-calendario" class="collapse-item" href="../../functions/production/calendario"><i
                            class="far fa-calendar-alt"></i>
                        Calendario</a>
                    <a id="production-settings" class="collapse-item" href="../../functions/production/settings"><i
                            class="far fa-gear"></i>
                        Impostazioni</a>
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
            <div id="collapseSamples" class="collapse" aria-labelledby="headingSamples" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Sezioni:</h6>
                    <a id="samples-new" class="collapse-item" href="../../functions/samples/newSample"><i
                            class="fa fa-plus"></i>
                        Nuovo</a>
                    <a id="samples-list" class="collapse-item" href="../../functions/samples/list"><i
                            class="far fa-list"></i>
                        In corso</a>
                    <a id="samples-done" class="collapse-item" href="#"><i
                            class="far fa-check"></i>
                        Completi</a>
                    <a id="samples-search" class="collapse-item" href="#"><i class="fa fa-search"></i>
                        Cerca</a>
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
            <a class="nav-link collapsed" href="../../functions/users/manageUsers" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-users"></i>
                <span>Utenti</span>
            </a>
        </li>
    <?php endif; ?>
    <!-- LOG ATTIVITA' -->
    <?php if (isset($_SESSION['permessi_log']) && $_SESSION['permessi_log'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="../../functions/users/log_admin" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-monitor-heart-rate"></i>
                <span>Log Attività</span>
            </a>
        </li>
    <?php endif; ?>
    <!-- ETICHETTE -->
    <?php if (isset($_SESSION['permessi_etichette']) && $_SESSION['permessi_etichette'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="../../functions/tools/eti_index" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-barcode-alt"></i>
                <span>Etichette</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if (isset($_SESSION['permessi_sql']) && $_SESSION['permessi_sql'] == 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="../../functions/database/manager" aria-expanded="true"
                aria-controls="collapseProd">
                <i class="far fa-database"></i>
                <span>Database</span>
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
            '/functions/quality/chartsOverview': 'quality-charts',
            '/functions/production/new': 'production-new',
            '/functions/production/calendario': 'production-calendario',
            '/functions/production/settings': 'production-settings',
            '/functions/samples/newSample': 'samples-new',
            '/functions/samples/list': 'samples-list',
            '/functions/samples/done': 'samples-done',

            // Aggiungi qui altri link come necessario
        };

        // Controlla se l'URL corrente corrisponde a uno degli URL nel menu
        for (var url in navLinks) {
            if (navLinks.hasOwnProperty(url) && currentUrl.includes(url)) {
                var navItem = document.getElementById(navLinks[url]);
                if (navItem) {
                    navItem.classList.add('active');
                    var parentNavLink = navItem.closest('.nav-item').querySelector('.nav-link');
                    if (parentNavLink) {
                        parentNavLink.classList.remove('collapsed');
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
        if (currentUrl.endsWith('/index') || currentUrl === '/index' || currentUrl === '/') {
            var homeNavItem = document.getElementById('home');
            if (homeNavItem) {
                homeNavItem.classList.add('active');
            }
        }
    });

</script>

<!-- FINE SIDEBAR -->