<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/helpers/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';
include (BASE_PATH . "/components/header.php");

// Inizializza le variabili con i valori attuali dal database
$conn = getDbInstance();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getSetting($conn, $item)
{
    $sql = "SELECT value FROM settings WHERE item = :item";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':item', $item);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$production_senderEmail = getSetting($conn, 'production_senderEmail');
$production_senderPassword = getSetting($conn, 'production_senderPassword');
$production_senderSMTP = getSetting($conn, 'production_senderSMTP');
$production_senderPORT = getSetting($conn, 'production_senderPORT');
$production_recipients = getSetting($conn, 'production_recipients');

// Aggiorna le impostazioni nel database quando il form è inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $production_senderEmail = $_POST['production_senderEmail'];
    $production_senderPassword = $_POST['production_senderPassword'];
    $production_senderSMTP = $_POST['production_senderSMTP'];
    $production_senderPORT = $_POST['production_senderPORT'];
    $production_recipients = $_POST['production_recipients'];

    $settings = [
        'production_senderEmail' => $production_senderEmail,
        'production_senderPassword' => $production_senderPassword,
        'production_senderSMTP' => $production_senderSMTP,
        'production_senderPORT' => $production_senderPORT,
        'production_recipients' => $production_recipients
    ];

    foreach ($settings as $item => $value) {
        $sql = "UPDATE settings SET value = :value WHERE item = :item";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':item', $item);
        $stmt->execute();
    }
    $_SESSION["success"] = "Impostazioni aggiornate con successo!";
}

?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Impostazioni Produzione</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Invio E-Mail</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="production_senderEmail">Indirizzo E-Mail del Mittente</label>
                                    <input type="email" class="form-control" id="production_senderEmail"
                                        name="production_senderEmail"
                                        value="<?= htmlspecialchars($production_senderEmail) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="production_senderPassword">Password</label>
                                    <input type="password" class="form-control" id="production_senderPassword"
                                        name="production_senderPassword"
                                        value="<?= htmlspecialchars($production_senderPassword) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="production_senderSMTP">Server SMTP</label>
                                    <input type="text" class="form-control" id="production_senderSMTP"
                                        name="production_senderSMTP"
                                        value="<?= htmlspecialchars($production_senderSMTP) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="production_senderPORT">Server PORT</label>
                                    <input type="text" class="form-control" id="production_senderPORT"
                                        name="production_senderPORT"
                                        value="<?= htmlspecialchars($production_senderPORT) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="production_recipients">Destinatari (Separare indirizzi E-Mail da
                                        ";")</label>
                                    <textarea class="form-control" id="production_recipients"
                                        name="production_recipients"
                                        rows="3"><?= htmlspecialchars($production_recipients) ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block">SALVA</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include (BASE_PATH . "/components/scripts.php"); ?>
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
</body>