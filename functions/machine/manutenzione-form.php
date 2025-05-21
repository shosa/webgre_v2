<?php
session_start();
require_once '../../config/config.php';
require_once '../../utils/log_utils.php';

// Get database instance
$pdo = getDbInstance();

// Check if ID was passed
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['token']) || empty($_GET['token'])) {
    die("Accesso non autorizzato o link non valido.");
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$token = $_GET['token'];

if (!$id) {
    die("ID macchinario non valido.");
}

// Get machine details and validate token
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$macchinario) {
        die("Macchinario non trovato.");
    }

    // Validate token (simple hash based on machine serial + id)
    $expectedToken = md5($macchinario['matricola'] . $id);
    if ($token !== $expectedToken) {
        die("Token non valido. Accesso non autorizzato.");
    }

    // Log QR code scan
    $stmt = $pdo->prepare("INSERT INTO mac_qrcode_logs (mac_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([
        $id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);

    // Get maintenance types
    $stmt = $pdo->query("SELECT * FROM mac_manutenzioni_tipi ORDER BY nome");
    $tipi_manutenzione = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

// Process form submission
$successMessage = '';
$errorMessage = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save form data for restoring in case of error
    $formData = $_POST;

    try {
        // Validate required fields
        if (empty($_POST['tipo_id']) || empty($_POST['operatore']) || empty($_POST['descrizione'])) {
            throw new Exception("I campi contrassegnati con * sono obbligatori.");
        }

        // Prepare file upload if any
        $uploadedFiles = [];
        if (!empty($_FILES['allegati']['name'][0])) {
            $uploadDir = '../../uploads/manutenzioni/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['allegati']['name'] as $key => $name) {
                if ($_FILES['allegati']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['allegati']['tmp_name'][$key];
                    $fileName = time() . '_' . basename($name);
                    $filePath = $uploadDir . $fileName;

                    // Check file type (optional: add more secure validation)
                    $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception("Tipo di file non supportato: " . $fileType);
                    }

                    // Move uploaded file
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $uploadedFiles[] = [
                            'nome_file' => $name,
                            'percorso_file' => 'uploads/manutenzioni/' . $fileName,
                            'tipo_file' => $fileType,
                            'dimensione' => $_FILES['allegati']['size'][$key]
                        ];
                    } else {
                        throw new Exception("Errore durante il caricamento del file.");
                    }
                }
            }
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Check if it's a scheduled maintenance
        $is_programmata = 0;
        if (isset($_POST['scheduled_id']) && !empty($_POST['scheduled_id'])) {
            // Validate scheduled maintenance
            $stmtCheck = $pdo->prepare("SELECT * FROM mac_manutenzioni_programmate WHERE id = ? AND mac_id = ?");
            $stmtCheck->execute([$_POST['scheduled_id'], $id]);
            if ($stmtCheck->fetch()) {
                $is_programmata = 1;
            }
        }

        // Insert maintenance record
        $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni (
    mac_id, tipo_id, data_manutenzione, operatore, descrizione, 
    lavori_eseguiti, ricambi_utilizzati, tempo_impiegato, 
    stato, is_programmata
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");  // Nota: rimosso un parametro ? per il costo

        $stmt->execute([
            $id,
            $_POST['tipo_id'],
            $_POST['data_manutenzione'],
            $_POST['operatore'],
            $_POST['descrizione'],
            $_POST['lavori_eseguiti'] ?? null,
            $_POST['ricambi_utilizzati'] ?? null,
            $_POST['tempo_impiegato'] ?? null,
            'richiesta',
            $is_programmata
        ]); // Nota: rimosso il parametro $_POST['costo'] ?? null

        $manutenzioneId = $pdo->lastInsertId();

        // Save attachments if any
        foreach ($uploadedFiles as $file) {
            $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni_allegati (
                manutenzione_id, nome_file, percorso_file, tipo_file, dimensione
            ) VALUES (?, ?, ?, ?, ?)");

            $stmt->execute([
                $manutenzioneId,
                $file['nome_file'],
                $file['percorso_file'],
                $file['tipo_file'],
                $file['dimensione']
            ]);
        }

        // If it's a scheduled maintenance, update the schedule
        if ($is_programmata && isset($_POST['scheduled_id'])) {
            $stmt = $pdo->prepare("UPDATE mac_manutenzioni_programmate SET 
                ultima_manutenzione = ?, 
                prossima_manutenzione = DATE_ADD(?, INTERVAL intervallo_giorni DAY)
                WHERE id = ?");

            $stmt->execute([
                $_POST['data_manutenzione'],
                $_POST['data_manutenzione'],
                $_POST['scheduled_id']
            ]);
        }

        $pdo->commit();

        $successMessage = "Manutenzione registrata con successo! Sarà approvata dal responsabile.";
        $formData = []; // Clear form data after success

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = "Errore: " . $e->getMessage();
    }
}

// Simple layout for external access (not using the admin template)
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registrazione Manutenzione - <?= htmlspecialchars($macchinario['matricola']) ?></title>

    <!-- Custom fonts -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
        }

        .maintenance-logo {
            max-height: 80px;
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .bg-maintenance {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }

        .maintenance-header {
            text-align: center;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="maintenance-header bg-maintenance rounded-lg mb-4">
            <h2 class="h3 mb-0">
                <i class="fas fa-tools mr-2"></i>
                Registrazione Manutenzione
            </h2>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> <?= $successMessage ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $errorMessage ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Machine Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    Dettagli Macchinario
                </h6>
                <span class="badge badge-primary px-3 py-2">
                    #<?= $macchinario['id'] ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Matricola
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['matricola']) ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tipologia
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['tipologia']) ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Modello
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['modello']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clipboard-list mr-2"></i>Registra Intervento di Manutenzione
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data" id="maintenanceForm">
                    <?php if (isset($_GET['scheduled_id'])): ?>
                        <input type="hidden" name="scheduled_id"
                            value="<?= filter_var($_GET['scheduled_id'], FILTER_VALIDATE_INT) ?>">
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Stai registrando una manutenzione programmata.
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="tipo_id"><strong>Tipo di Intervento *</strong></label>
                            <select name="tipo_id" id="tipo_id" class="form-control" required>
                                <option value="">-- Seleziona tipo --</option>
                                <?php foreach ($tipi_manutenzione as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" <?= (isset($formData['tipo_id']) && $formData['tipo_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (empty($tipi_manutenzione)): ?>
                                    <option value="1">Manutenzione Ordinaria</option>
                                    <option value="2">Manutenzione Straordinaria</option>
                                    <option value="3">Riparazione</option>
                                    <option value="4">Controllo</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="data_manutenzione"><strong>Data Intervento *</strong></label>
                            <input type="date" name="data_manutenzione" id="data_manutenzione" class="form-control"
                                value="<?= htmlspecialchars($formData['data_manutenzione'] ?? date('Y-m-d')) ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="operatore"><strong>Nome Operatore/Tecnico *</strong></label>
                            <input type="text" name="operatore" id="operatore" class="form-control"
                                value="<?= htmlspecialchars($formData['operatore'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="tempo_impiegato">Tempo Impiegato (ore)</label>
                            <input type="number" name="tempo_impiegato" id="tempo_impiegato" class="form-control"
                                step="0.25" min="0" value="<?= htmlspecialchars($formData['tempo_impiegato'] ?? '') ?>"
                                placeholder="Es. 1.5">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="descrizione"><strong>Descrizione Intervento *</strong></label>
                            <textarea name="descrizione" id="descrizione" class="form-control" rows="3" required
                                placeholder="Descrivi il tipo di intervento e il motivo..."><?= htmlspecialchars($formData['descrizione'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="lavori_eseguiti">Lavori Eseguiti</label>
                            <textarea name="lavori_eseguiti" id="lavori_eseguiti" class="form-control" rows="3"
                                placeholder="Descrivi nel dettaglio i lavori eseguiti..."><?= htmlspecialchars($formData['lavori_eseguiti'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="ricambi_utilizzati">Ricambi Utilizzati</label>
                            <textarea name="ricambi_utilizzati" id="ricambi_utilizzati" class="form-control" rows="2"
                                placeholder="Elenca i ricambi utilizzati..."><?= htmlspecialchars($formData['ricambi_utilizzati'] ?? '') ?></textarea>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="allegati">Allegati (foto, documenti, ecc.)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="allegati" name="allegati[]" multiple>
                                <label class="custom-file-label" for="allegati">Scegli file...</label>
                            </div>
                            <small class="form-text text-muted">Puoi allegare fino a 5 file (max 10MB ciascuno). Formati
                                supportati: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX</small>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirmCheck" required>
                            <label class="custom-control-label" for="confirmCheck">
                                Confermo che le informazioni inserite sono corrette.
                            </label>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-save mr-2"></i>Registra Manutenzione
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mb-4">
            <p class="text-muted small">
                Questa manutenzione verrà registrata nel sistema e sarà visibile ai responsabili.
                <br>
                &copy; <?= date('Y') ?> Sistema di Gestione Macchinari - Tutti i diritti riservati
            </p>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Update file input label with selected files
            $('.custom-file-input').on('change', function () {
                var fileCount = $(this)[0].files.length;
                var label = fileCount > 0 ? fileCount + ' file selezionati' : 'Scegli file...';
                $(this).next('.custom-file-label').html(label);
            });

            // Set current date as default
            if ($('#data_manutenzione').val() === '') {
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;
                $('#data_manutenzione').val(today);
            }

            // Form validation
            $('#maintenanceForm').on('submit', function (e) {
                var isValid = true;

                // Check required fields
                $(this).find('[required]').each(function () {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Compila tutti i campi obbligatori contrassegnati con *');
                }

                // Check file size
                var maxFileSize = 10 * 1024 * 1024; // 10MB
                var files = $('#allegati')[0].files;

                if (files.length > 5) {
                    isValid = false;
                    alert('Puoi caricare al massimo 5 file.');
                    e.preventDefault();
                    return;
                }

                for (var i = 0; i < files.length; i++) {
                    if (files[i].size > maxFileSize) {
                        isValid = false;
                        alert('Il file ' + files[i].name + ' supera la dimensione massima di 10MB.');
                        e.preventDefault();
                        return;
                    }
                }
            });
        });
    </script>
</body>

</html>