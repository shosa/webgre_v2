<?php
session_start();
require_once '../../utils/log_utils.php';
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
// Connessione al database usando PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
// Ottieni il valore del cartellino dalla richiesta GET
$cartellino = filter_input(INPUT_GET, 'cartellino', FILTER_UNSAFE_RAW);
// Prepara la query per ottenere le informazioni del cartellino
$query = "SELECT * FROM dati WHERE Cartel = :cartellino";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
$stmt->execute();
$informazione = $stmt->fetch(PDO::FETCH_ASSOC);
// Serve POST method, After successful insert, redirect to riparazioni.php page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_UNSAFE_RAW);
    $data_to_store = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // Controlla i campi da P01 a P20 e imposta a 0 se vuoti
    for ($i = 1; $i <= 20; $i++) {
        $field = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisce il nome del campo (es. P01, P02, ..., P20)
        if (empty($data_to_store[$field])) {
            $data_to_store[$field] = 0;
        }
    }
    // Prepara la query di inserimento
    $columns = implode(", ", array_keys($data_to_store));
    $values = ":" . implode(", :", array_keys($data_to_store));
    $insert_query = "INSERT INTO riparazioni ($columns) VALUES ($values)";
    $insert_stmt = $pdo->prepare($insert_query);
    // Esegui la query di inserimento
    $esito = $insert_stmt->execute($data_to_store);
    // Debug query eseguita
    echo $insert_stmt->debugDumpParams();
    if ($esito) {
        // QUI DOBBIAMO CAPIRE COME LOGGARE L ATTIVITA SIA LA QUERY CHE L'ID RIPARAZIONE
        $update_query = "UPDATE tabid SET id = id + 1";
        $pdo->exec($update_query);
        $_SESSION['success'] = "Riparazione inserita!";
        $stmt = $pdo->query("SELECT MAX(ID) AS max_id FROM tabid");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxid = $result['max_id'];
        $real_query = replacePlaceholders($pdo, $insert_query, $data_to_store);
        logActivity($_SESSION['user_id'], 'RIPARAZIONI', 'CREA', 'Inserimento Cedola', '#' . $maxid , $real_query);
        header('Location: riparazioni.php');
        exit();
    }
}
require_once BASE_PATH . '/components/header.php';
// Prepara la query per ottenere il valore massimo di ID da tabid
$max_query = "SELECT MAX(ID) AS max_id FROM tabid";
$max_stmt = $pdo->query($max_query);
$max_tabid = $max_stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
// Calcola il nuovo valore per id
$new_id = $max_tabid + 1;
?>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/riparazioni/add_step1">Nuova
                                Riparazione</a></li>
                        <li class="breadcrumb-item active">Inserimento dettagli</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Nuova Cedola #<?php echo $new_id; ?></h6>
                        </div>
                        <div class="card-body">
                            <form class="form" action="" method="post" id="customer_form" enctype="multipart/form-data">
                                <?php include_once ('forms/new_step2_form.php'); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>