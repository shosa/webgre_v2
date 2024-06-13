<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once BASE_PATH . "/helpers/helpers.php";
require_once BASE_PATH . "/utils/log_utils.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeModello = $_POST['nome_modello'] ?? '';
    $variante = $_POST['variante'] ?? '';
    $forma = $_POST['forma'] ?? '';
    $consegna = $_POST['consegna'] ?? '';
    $note = $_POST['note'] ?? '';
    $immagine = '';

    // Verifica se è stato caricato un file immagine
    if (!empty($_FILES['immagine']['name'])) {
        $uploadDir = BASE_PATH . "/functions/samples/img/";
        $fileName = basename($_FILES['immagine']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Verifica se l'immagine è valida
        $uploadOk = true;

        // Controlla se il file esiste già
        if (file_exists($targetFilePath)) {
            $_SESSION['error'] = "Il file immagine esiste già.";
            $uploadOk = false;
        }

        // Controlla il tipo di file
        if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['error'] = "Sono ammessi solo i formati JPG, JPEG, PNG, GIF.";
            $uploadOk = false;
        }

        // Controlla la dimensione del file
        if ($_FILES['immagine']['size'] > 500000) { // Limite di dimensione a 500 KB
            $_SESSION['error'] = "Il file immagine è troppo grande. Max 500 KB.";
            $uploadOk = false;
        }

        // Se l'upload è valido, sposta il file nella directory di destinazione
        if ($uploadOk) {
            if (move_uploaded_file($_FILES['immagine']['tmp_name'], $targetFilePath)) {
                $immagine = $fileName; // Salva il nome del file
            } else {
                $_SESSION['error'] = "Si è verificato un errore durante il caricamento dell'immagine.";
            }
        }
    } else {
        $_SESSION['error'] = "È necessario caricare un'immagine per il modello.";
    }

    // Inserisci il modello nel database anche senza l'immagine
    $pdo = getDbInstance();
    $stmt = $pdo->prepare("INSERT INTO samples_modelli (nome_modello, variante, forma, consegna, note, immagine) VALUES (:nome_modello, :variante, :forma, :consegna, :note, :immagine)");

    $stmt->bindParam(':nome_modello', $nomeModello);
    $stmt->bindParam(':variante', $variante);
    $stmt->bindParam(':forma', $forma);
    $stmt->bindParam(':consegna', $consegna);
    $stmt->bindParam(':note', $note);
    $stmt->bindParam(':immagine', $immagine); // Salva il nome del file nel database, anche se vuoto

    if ($stmt->execute()) {
        $idInserito = $pdo->lastInsertId();
        $_SESSION['success'] = "Modello inserito con successo.";
        logActivity($_SESSION['user_id'], 'CAMPIONARIO', 'CREA', 'Inserito nuovo modello', 'ID: ' . $idInserito);
        header('Location: ../../functions/samples/list');
        exit();
    } else {
        $_SESSION['error'] = "Errore durante l'inserimento del modello.";
    }
}
?>

<?php include BASE_PATH . "/components/header.php"; ?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Inserimento Nuovo Modello</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Inserimento Nuovo Modello</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Dettagli Modello</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST"
                                        enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="nome_modello">Nome Modello</label>
                                            <input type="text" name="nome_modello" class="form-control"
                                                value="<?php echo htmlspecialchars($_POST['nome_modello'] ?? ''); ?>"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="variante">Variante</label>
                                            <input type="text" name="variante" class="form-control"
                                                value="<?php echo htmlspecialchars($_POST['variante'] ?? ''); ?>"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="forma">Forma</label>
                                            <input type="text" name="forma" class="form-control"
                                                value="<?php echo htmlspecialchars($_POST['forma'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="consegna">Data di Consegna</label>
                                            <input type="date" name="consegna" class="form-control"
                                                value="<?php echo htmlspecialchars($_POST['consegna'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="note">Note</label>
                                            <textarea name="note" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="immagine">Immagine</label>
                                            <input type="file" name="immagine" class="form-control-file" id="immagine">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Inserisci Modello</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . "/components/scripts.php"; ?>
            <?php include_once BASE_PATH . "/components/footer.php"; ?>
        </div>
    </div>
</body>

</html>