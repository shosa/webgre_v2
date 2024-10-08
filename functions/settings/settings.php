<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include(BASE_PATH . "/components/header.php");
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Impostazioni</h1>
                    </div>
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Impostazioni</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary" id="card-title">Menu</h6>
                                </div>
                                <div class="card-body">
                                    <ul id="tables-list" class="list-group">
                                        <li
                                            class="list-group-item active p-1 pl-2 no-cursor bg-light text-<?php echo $colore; ?> font-weight-bold border">
                                            Database</li>
                                        <span class="list-group-item" href="#" id="uploadXLSX"><i
                                                class="fal fa-database"></i> Aggiornamento Cartellini</span>
                                        <li
                                            class="list-group-item active p-1 pl-2 no-cursor bg-light text-<?php echo $colore; ?> font-weight-bold border">
                                            Strumenti</li>
                                        <span class="list-group-item" href="#" id="sendNotification"><i
                                                class="fal fa-bell"></i> Invio Notifiche</span>
                                        <span class="list-group-item" href="#" id="readLog"><i class="fal fa-code"></i>
                                            Log Sistema</span>
                                        <li
                                            class="list-group-item active p-1 pl-2 no-cursor bg-light text-<?php echo $colore; ?> font-weight-bold border">
                                            Email</li>
                                        <span class="list-group-item" href="#" id="productionSmtp"><i
                                                class="fal fa-envelope-open-text"></i> SMTP E-mail Produzione</span>
                                        <li
                                            class="list-group-item active p-1 pl-2 no-cursor bg-light text-<?php echo $colore; ?> font-weight-bold border">
                                            Tabelle</li>
                                        <span class="list-group-item" href="#" id="manageLines"><i
                                                class="fal fa-tasks"></i> Linee</span>
                                        <span class="list-group-item" href="#" id="manageLaboratory"><i
                                                class="fal fa-digging"></i> Laboratori</span>
                                        <span class="list-group-item" href="#" id="manageDepartment"><i
                                                class="fal fa-building"></i> Reparti</span>
                                        <span class="list-group-item" href="#" id="manageTabId"><i
                                                class="fal fa-forward"></i> Progressivo Cedole</span>
                                        <li
                                            class="list-group-item active p-1 pl-2 no-cursor bg-light text-<?php echo $colore; ?> font-weight-bold border">
                                            Generale</li>
                                        <span class="list-group-item" href="#" id="updateApp"><i
                                                class="fal fa-sync-alt"></i> Aggiornamento App</span>
                                        <!-- Altre voci di menu possono essere aggiunte qui -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary" id="card-titleName"></h6>
                                </div>
                                <div class="card-body">
                                    <div id="action-content" class="table-responsive">
                                        <!-- Il form verrà caricato qui -->
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
    <?php include(BASE_PATH . "/components/scripts.php"); ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function loadForm(formName, cardTitle) {
        fetch('forms/' + formName + '.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('action-content').innerHTML = html;
                document.getElementById('card-titleName').textContent = cardTitle; // Aggiorna il titolo della card
                // Carica lo script associato al form, se esiste
                var scriptPath = 'forms/script_' + formName + '.js';
                fetch(scriptPath)
                    .then(response => {
                        if (response.ok) {
                            var script = document.createElement('script');
                            script.src = scriptPath;
                            document.body.appendChild(script);
                        }
                    });
            });
    }
    document.getElementById('uploadXLSX').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_UploadXLSX', 'Aggiornamento Cartellini');
    });
    document.getElementById('sendNotification').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_SendNotification', 'Invio Notifiche');
    });
    document.getElementById('readLog').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ReadLog', 'Log Sistema');
    });
    document.getElementById('productionSmtp').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ProductionSmtp', 'SMTP E-mail Produzione');
    });
    document.getElementById('manageLines').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ManageLines', 'Linee');
    });
    document.getElementById('manageLaboratory').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ManageLaboratory', 'Laboratori');
    });
    document.getElementById('manageDepartment').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ManageDepartment', 'Reparti');
    });
    document.getElementById('manageTabId').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_ManageTabId', 'Progressivo Cedole');
    });
    document.getElementById('updateApp').addEventListener('click', function (event) {
        event.preventDefault();
        loadForm('form_UpdateApp', 'Aggiornamento App');
    });
    <?php if (isset($_SESSION['message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Successo',
            text: '<?php echo $_SESSION['message']; ?>',
        });
        <?php
        $_SESSION["info"] = $_SESSION['message'];
        unset($_SESSION['message']); ?>
    <?php endif; ?>
</script>