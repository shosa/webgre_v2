<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include (BASE_PATH . "/components/header.php");
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Impostazioni</h1>
                    </div>
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Impostazioni</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Menu</h6>
                                </div>
                                <div class="card-body">
                                    <ul id="tables-list" class="list-group">
                                        <span class="list-group-item" href="#" id="uploadXLSX">Aggiornamento Database
                                            Cartellini</span>
                                        <!-- Altre voci di menu possono essere aggiunte qui -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Azioni</h6>
                                </div>
                                <div class="card-body">
                                    <div id="action-content" class="table-responsive">
                                        <!-- Il form di upload verrà caricato qui -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <?php include (BASE_PATH . "/components/scripts.php"); ?>
</body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.getElementById('uploadXLSX').addEventListener('click', function (event) {
        event.preventDefault();
        document.getElementById('action-content').innerHTML = `
            <form id="uploadForm" action="form_ImportDatiXlsx.php" method="post" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-light">
    <div class="form-group">
        <label for="file" class="font-weight-bold">Seleziona il file XLSX:</label>
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx" required>
            <label class="custom-file-label" for="file">Scegli file...</label>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-block btn-primary mt-3">Importa</button>
        <div id="loader" class="spinner-border text-primary ml-3 mt-3" style="display: none;" role="status">
            <span class="sr-only">Caricamento in corso...</span>
        </div>
    </div>
</form>

        `;

        document.getElementById('uploadForm').addEventListener('submit', function () {
            document.getElementById('loader').style.display = 'block';
        });
    });

    <?php if (isset($_SESSION['message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Successo',
            text: '<?php echo $_SESSION['message']; ?>',
        });
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
</script>