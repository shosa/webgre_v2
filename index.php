<?php
include ("config/config.php");
session_start();
require_once BASE_PATH . '/components/auth_validate.php';

// Ottieni il tipo di utente dall'array di sessione
$tipoUtente = $_SESSION['admin_type'];

// Ottieni un'istanza del database utilizzando PDO
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Prepara le query per ottenere le informazioni del dashboard
$queryNumRiparazioni = "SELECT SUM(QTA) FROM riparazioni";
$stmtNumRiparazioni = $pdo->query($queryNumRiparazioni);
$numRiparazioni = $stmtNumRiparazioni->fetchColumn();

$queryNumRiparazioniPersonali = $pdo->prepare("SELECT SUM(QTA) FROM riparazioni WHERE utente = :username");
$queryNumRiparazioniPersonali->execute([':username' => $_SESSION['username']]);
$numRiparazioniPersonali = $queryNumRiparazioniPersonali->fetchColumn();

$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdo->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();
$data_oggi = date('d/m/Y');

try {
    // Query per contare i record con la data odierna
    $sql = "SELECT COUNT(*) AS num_records FROM cq_records WHERE data = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data_oggi]);

    // Ottieni il risultato della query
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_cq_records = $row['num_records'];
} catch (PDOException $e) {
    // Gestione degli errori
    echo "Errore durante l'esecuzione della query: " . $e->getMessage();
    $num_cq_records = 0; // Imposta il numero di record a 0 in caso di errore
}
?>

<?php include ("components/header.php"); ?>

<body id="page-top">

    <div id="wrapper">
        <?php include ("components/navbar.php"); //INCLUSIONE NAVBAR ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include ("components/topbar.php"); ?>

                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>
                    <!-- INIZIO ROW CARDS -->
                    <div class="row">
                        <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
                            <!-- CARD RIPARAZIONI -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-primary">
                                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Riparazioni attive</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($numRiparazioni) ? '0' : $numRiparazioni; ?>
                                                </div>

                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-tools fa-3x text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- CARD RIPARAZIONI PERSONALI -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-success">
                                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/myRiparazioni"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Riparazioni attive Personali</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($numRiparazioniPersonali) ? '0' : $numRiparazioniPersonali; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-user-clock fa-3x text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
                            <!-- CARD CQ -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-info">
                                    <a href="<?php echo BASE_URL ?>/functions/quality/detail?date=<?php echo $data_oggi ?>"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Test
                                                    eseguiti oggi</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($num_cq_records) ? '0' : $num_cq_records; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-vials fa-3x text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- CHIUSURA ROW CARDS -->
                </div>
            </div>
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include (BASE_PATH . "/components/scripts.php"); ?>

    <style>
        .card-hover {
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;

        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);

        }

        .card-hover a {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
    </style>

</body>