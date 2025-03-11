<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Ottieni l'istanza del database
$pdo = getDbInstance();

// Variabile per il messaggio di successo
$successMessage = '';
$errorMessage = '';

// Verifica che l'ID sia stato passato
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID macchinario non specificato.";
    header("Location: lista_macchinari");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID macchinario non valido.";
    header("Location: lista_macchinari");
    exit;
}

// Gestione del form di aggiornamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Gestione del nuovo tipo di macchina
        $tipologia = $_POST['tipologia'];
        if ($_POST['tipologia'] === 'nuovo') {
            $nuovo_tipo = trim($_POST['nuovo_tipo']);
            if (!empty($nuovo_tipo)) {
                // Verifica se il tipo esiste già
                $stmt = $pdo->prepare("SELECT id FROM mac_types WHERE tipo = ?");
                $stmt->execute([$nuovo_tipo]);
                if (!$stmt->fetch()) {
                    // Inserisci il nuovo tipo
                    $stmt = $pdo->prepare("INSERT INTO mac_types (tipo, descrizione) VALUES (?, ?)");
                    $stmt->execute([$nuovo_tipo, $_POST['descrizione_tipo'] ?? null]);
                }
                $tipologia = $nuovo_tipo;
            } else {
                $errorMessage = "Se si seleziona 'Aggiungi nuovo tipo', è necessario specificare il nome del tipo.";
                // Continuiamo con il caricamento dei dati
                goto load_machine;
            }
        }

        // Verifica che la matricola non sia già utilizzata da un altro macchinario
        $stmt = $pdo->prepare("SELECT id FROM mac_anag WHERE matricola = ? AND id != ?");
        $stmt->execute([$_POST['matricola'], $id]);
        if ($stmt->fetch()) {
            $errorMessage = "La matricola '" . htmlspecialchars($_POST['matricola']) . "' è già in uso da un altro macchinario.";
            goto load_machine;
        }

        // Preparazione dell'aggiornamento del macchinario
        $stmt = $pdo->prepare("UPDATE mac_anag SET 
                              matricola = ?, 
                              tipologia = ?, 
                              data_acquisto = ?, 
                              produttore = ?, 
                              modello = ?, 
                              note = ?
                              WHERE id = ?");
        
        $result = $stmt->execute([
            $_POST['matricola'],
            $tipologia,
            $_POST['data_acquisto'],
            $_POST['produttore'],
            $_POST['modello'],
            $_POST['note'] ?? null,
            $id
        ]);

        if ($result) {
            $successMessage = "Macchinario aggiornato con successo!";
            
            // Se richiesto il redirect alla lista
            if (isset($_POST['save_and_back']) && $_POST['save_and_back'] == 1) {
                $_SESSION['success'] = $successMessage;
                header("Location: lista_macchinari");
                exit;
            }
        } else {
            $errorMessage = "Errore durante l'aggiornamento del macchinario.";
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Codice di errore MySQL per duplicazione
            $errorMessage = "La matricola inserita esiste già nel database.";
        } else {
            $errorMessage = "Errore database: " . $e->getMessage();
        }
    }
}

// Etichetta per il caricamento dei dati in caso di errore
load_machine:

// Recupera i dati del macchinario
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$macchinario) {
        $_SESSION['error'] = "Macchinario non trovato.";
        header("Location: lista_macchinari");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}

// Carica tutti i tipi di macchine esistenti
$stmt = $pdo->query("SELECT id, tipo FROM mac_types ORDER BY tipo");
$tipi_macchine = $stmt->fetchAll(PDO::FETCH_ASSOC);

// I valori per il form: se c'è un errore POST, usa i valori inviati, altrimenti usa i valori dal DB
$formData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $macchinario;

// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
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
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit text-gray-500 mr-2"></i>
                            Modifica Macchinario
                        </h1>
                        <div>
                            <a href="dettaglio_macchinario?id=<?= $id ?>" class="btn btn-info btn-sm shadow-sm">
                                <i class="fas fa-eye fa-sm text-white-50"></i> Visualizza Dettagli
                            </a>
                            <a href="lista_macchinari" class="btn btn-secondary btn-sm shadow-sm ml-2">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Torna alla Lista
                            </a>
                        </div>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item"><a href="lista_macchinari">Lista Macchinari</a></li>
                        <li class="breadcrumb-item active">Modifica</li>
                    </ol>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Modifica Dati Macchinario</h6>
                            <span class="badge badge-secondary">ID: <?= $id ?></span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="macchinarioForm">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="matricola"><strong>Matricola/Numero di Serie *</strong></label>
                                        <input type="text" name="matricola" id="matricola" class="form-control <?= !empty($errorMessage) && strpos($errorMessage, 'matricola') !== false ? 'is-invalid' : '' ?>" required 
                                            value="<?= htmlspecialchars($formData['matricola'] ?? '') ?>">
                                        <?php if (!empty($errorMessage) && strpos($errorMessage, 'matricola') !== false): ?>
                                        <div class="invalid-feedback">
                                            La matricola inserita è già in uso. Inserire una matricola unica.
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="tipologia"><strong>Tipologia Macchina *</strong></label>
                                        <select name="tipologia" id="tipologia" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">-- Seleziona tipo --</option>
                                            <?php foreach ($tipi_macchine as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo['tipo']) ?>" <?= (isset($formData['tipologia']) && $formData['tipologia'] == $tipo['tipo']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipo['tipo']) ?></option>
                                            <?php endforeach; ?>
                                            <option value="nuovo" <?= (isset($formData['tipologia']) && $formData['tipologia'] == 'nuovo') ? 'selected' : '' ?>>➕ Aggiungi nuovo tipo...</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sezione per nuovo tipo (nascosta inizialmente) -->
                                <div id="nuovo_tipo_section" class="row mt-3" style="display:<?= (isset($formData['tipologia']) && $formData['tipologia'] == 'nuovo') ? 'flex' : 'none' ?>;">
                                    <div class="col-md-6 form-group">
                                        <label for="nuovo_tipo"><strong>Nuovo Tipo di Macchina *</strong></label>
                                        <input type="text" name="nuovo_tipo" id="nuovo_tipo" class="form-control"
                                            value="<?= htmlspecialchars($formData['nuovo_tipo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="descrizione_tipo">Descrizione (opzionale)</label>
                                        <input type="text" name="descrizione_tipo" id="descrizione_tipo" class="form-control"
                                            value="<?= htmlspecialchars($formData['descrizione_tipo'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6 form-group">
                                        <label for="data_acquisto"><strong>Data Acquisto *</strong></label>
                                        <input type="date" name="data_acquisto" id="data_acquisto" class="form-control" required
                                            value="<?= htmlspecialchars($formData['data_acquisto'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="produttore"><strong>Produttore *</strong></label>
                                        <input type="text" name="produttore" id="produttore" class="form-control" required
                                            value="<?= htmlspecialchars($formData['produttore'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6 form-group">
                                        <label for="modello"><strong>Modello *</strong></label>
                                        <input type="text" name="modello" id="modello" class="form-control" required
                                            value="<?= htmlspecialchars($formData['modello'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="note">Note (opzionale)</label>
                                        <textarea name="note" id="note" class="form-control" rows="3"><?= htmlspecialchars($formData['note'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <label class="text-muted small">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            I campi contrassegnati con * sono obbligatori.
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mt-4 text-center">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save mr-2"></i>Salva Modifiche
                                    </button>
                                    <button type="submit" name="save_and_back" value="1" class="btn btn-primary btn-lg ml-2">
                                        <i class="fas fa-check-circle mr-2"></i>Salva e Torna alla Lista
                                    </button>
                                    <a href="dettaglio_macchinario?id=<?= $id ?>" class="btn btn-info btn-lg ml-2">
                                        <i class="fas fa-eye mr-2"></i>Visualizza Dettagli
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Card informativa -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle mr-1"></i> Informazioni sul macchinario
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Data Inserimento
                                    </div>
                                    <div class="text-gray-800 mb-3">
                                        <?= date('d/m/Y H:i', strtotime($macchinario['data_creazione'])) ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Ultimo Aggiornamento
                                    </div>
                                    <div class="text-gray-800 mb-3">
                                        <?= date('d/m/Y H:i', strtotime($macchinario['data_aggiornamento'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted">
                                <p>
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Attenzione:</strong> La modifica della matricola potrebbe influenzare i riferimenti in altri moduli.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            
            <script>
                $(document).ready(function () {
                    // Mostra/nascondi la sezione per nuovo tipo
                    $('#tipologia').change(function () {
                        if ($(this).val() === 'nuovo') {
                            $('#nuovo_tipo_section').fadeIn();
                            $('#nuovo_tipo').attr('required', true);
                        } else {
                            $('#nuovo_tipo_section').fadeOut();
                            $('#nuovo_tipo').attr('required', false);
                        }
                    });
                    
                    // Attiva sezione nuovo tipo se selezionato
                    if ($('#tipologia').val() === 'nuovo') {
                        $('#nuovo_tipo_section').show();
                        $('#nuovo_tipo').attr('required', true);
                    }
                    
                    // Conferma prima di abbandonare i cambiamenti non salvati
                    var formChanged = false;
                    
                    $('#macchinarioForm input, #macchinarioForm textarea, #macchinarioForm select').change(function() {
                        formChanged = true;
                    });
                    
                    $('a').click(function(e) {
                        if (formChanged && !$(this).hasClass('no-confirm')) {
                            var confirmLeave = confirm("Hai modifiche non salvate. Sei sicuro di voler abbandonare la pagina?");
                            if (!confirmLeave) {
                                e.preventDefault();
                            }
                        }
                    });
                });
            </script>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>