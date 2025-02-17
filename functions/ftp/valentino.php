<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

use phpseclib3\Net\SFTP;

// Configurazione connessione
$host = 'ftp.valentino.com';
$port = 22;
$username = 'EMM01';
$password = 'V3!pXt@Lq9';

$db = GetDbInstance();
$fileList = [];
$oldCommesse = [];
$error = "";
$connected = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connect'])) {
    $commesse_lette = $db->query("SELECT commessa FROM ftp_commesse_lette")->fetchAll(PDO::FETCH_COLUMN);
    
    // Connessione SFTP
    $sftp = new SFTP($host, $port);
    if (!$sftp->login($username, $password)) {
        $error = "Autenticazione SFTP fallita.";
    } else {
        $allFiles = $sftp->nlist('.');
        if ($allFiles === false) {
            $error = "Impossibile recuperare l'elenco dei file.";
        } else {
            $connected = true;
            foreach ($allFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                    $nomeFile = pathinfo($file, PATHINFO_FILENAME);
                    if (!in_array($nomeFile, $commesse_lette)) {
                        $fileList[] = $nomeFile; // Nuove commesse
                    } else {
                        $oldCommesse[] = $nomeFile; // Già memorizzate
                    }
                }
            }
        }
    }
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Integrazione SFTP Valentino</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Valentino</li>
                    </ol>

                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Connessione</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <button type="submit" name="connect" class="btn btn-success btn-block">CONNETTI</button>
                                    </form>
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
                                    <?php elseif ($connected): ?>
                                        <div class="alert alert-success mt-3">Connessione SFTP riuscita.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-block mt-2" id="process-btn" disabled>
                                Processa Selezione
                            </button>
                        </div>

                        <div class="col-xl-9 col-lg-9">
                            <?php if ($connected && empty($error)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Nuove Commesse Trovate (<?= count($fileList) ?>)</h6>
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#nuoveCommesse">Mostra/Nascondi</button>
                                    </div>
                                    <div id="nuoveCommesse" class="collapse">
                                        <div class="card-body">
                                            <form method="POST" action="process_commesse.php">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Seleziona</th>
                                                            <th>Commesse</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($fileList as $file): ?>
                                                            <tr>
                                                                <td><input type="checkbox" name="commesse[]" value="<?= htmlspecialchars($file) ?>" class="commessa-checkbox"></td>
                                                                <td><?= htmlspecialchars($file) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Commesse già memorizzate (<?= count($oldCommesse) ?>)</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Commesse</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($oldCommesse as $file): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($file) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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
        document.addEventListener("DOMContentLoaded", function () {
            const checkboxes = document.querySelectorAll(".commessa-checkbox");
            const processBtn = document.getElementById("process-btn");

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function () {
                    processBtn.disabled = !document.querySelector(".commessa-checkbox:checked");
                });
            });
        });
    </script>
</body>
