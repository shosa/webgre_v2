<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include (BASE_PATH . "/components/header.php");

// Funzione per ottenere l'istanza del database

// Funzione per controllare l'integrità del database
function checkDatabaseIntegrity($pdo)
{
    $expectedTables = [
        'activity_log' => [
            'id',
            'user_id',
            'category',
            'activity_type',
            'description',
            'note',
            'text_query',
            'created_at'
        ],
        'basi_modelli' => [
            'ID',
            'linea',
            'path_to_image',
            'codice',
            'descrizione',
            'qta_varianti'
        ],
        // Aggiungere tutte le altre tabelle con le rispettive colonne qui...
    ];

    $integrityErrors = [];

    foreach ($expectedTables as $table => $columns) {
        try {
            $result = $pdo->query("DESCRIBE $table");
            $existingColumns = $result->fetchAll(PDO::FETCH_COLUMN);

            foreach ($columns as $column) {
                if (!in_array($column, $existingColumns)) {
                    $integrityErrors[] = "Colonna mancante nella tabella $table: $column";
                }
            }
        } catch (PDOException $e) {
            $integrityErrors[] = "Tabella mancante: $table";
        }
    }

    return $integrityErrors;
}

$pdo = getDbInstance();
$integrityErrors = checkDatabaseIntegrity($pdo);

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
                        <li class="breadcrumb-item active">Integrità Database</li>
                    </ol>

                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Database</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($integrityErrors)): ?>
                                    <div class="alert alert-success" role="alert">
                                        Il database è integro e rispecchia la struttura prevista.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger" role="alert">
                                        Sono stati riscontrati i seguenti errori di integrità nel database:
                                        <ul>
                                            <?php foreach ($integrityErrors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
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