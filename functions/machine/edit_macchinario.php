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
                              rif_fattura = ?, 
                              fornitore = ?, 
                              modello = ?, 
                              marca = ?,
                              anno_costruzione = ?,
                              locazione_documenti = ?,
                              note = ?
                              WHERE id = ?");

        $result = $stmt->execute([
            $_POST['matricola'],
            $tipologia,
            $_POST['data_acquisto'],
            $_POST['rif_fattura'],
            $_POST['fornitore'],
            $_POST['modello'],
            $_POST['marca'] ?? null,
            $_POST['anno_costruzione'] ?? null,
            $_POST['locazione_documenti'] ?? null,
            $_POST['note'] ?? null,
            $id
        ]);

        if ($result) {
            // Gestione degli allegati se presenti
            if (isset($_FILES['allegati']) && !empty($_FILES['allegati']['name'][0])) {
                $upload_dir = BASE_PATH . '/uploads/macchinari/allegati/';

                // Crea la directory se non esiste
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Processa ogni file allegato
                $file_count = count($_FILES['allegati']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['allegati']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['allegati']['tmp_name'][$i];
                        $nome_file = basename($_FILES['allegati']['name'][$i]);
                        $tipo_file = $_FILES['allegati']['type'][$i];
                        $dimensione = $_FILES['allegati']['size'][$i];
                        $categoria = $_POST['categorie_allegati'][$i] ?? 'altro';
                        $descrizione = $_POST['descrizioni_allegati'][$i] ?? null;

                        // Genera un nome file univoco per evitare sovrascritture
                        $percorso_file = uniqid() . '_' . $nome_file;
                        $destination = $upload_dir . $percorso_file;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            // Inserisci il record dell'allegato nel database
                            $attachStmt = $pdo->prepare("INSERT INTO mac_anag_allegati (mac_id, nome_file, percorso_file, tipo_file, categoria, descrizione, dimensione) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $attachStmt->execute([
                                $id,
                                $nome_file,
                                $percorso_file,
                                $tipo_file,
                                $categoria,
                                $descrizione,
                                $dimensione
                            ]);
                        }
                    }
                }
            }

            // Gestione eliminazione allegati
            if (isset($_POST['delete_allegati']) && !empty($_POST['delete_allegati'])) {
                $deleteIds = $_POST['delete_allegati'];
                foreach ($deleteIds as $deleteId) {
                    $attachId = filter_var($deleteId, FILTER_VALIDATE_INT);
                    if ($attachId) {
                        // Recupera il percorso del file prima di eliminare il record
                        $stmtFile = $pdo->prepare("SELECT percorso_file FROM mac_anag_allegati WHERE id = ? AND mac_id = ?");
                        $stmtFile->execute([$attachId, $id]);
                        $fileInfo = $stmtFile->fetch(PDO::FETCH_ASSOC);

                        if ($fileInfo) {
                            // Elimina il file dal filesystem
                            $filePath = BASE_PATH . '/uploads/macchinari/allegati/' . $fileInfo['percorso_file'];
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }

                            // Elimina il record dal database
                            $stmtDelete = $pdo->prepare("DELETE FROM mac_anag_allegati WHERE id = ? AND mac_id = ?");
                            $stmtDelete->execute([$attachId, $id]);
                        }
                    }
                }
            }

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

    // Recupera gli allegati esistenti
    $hasAllegati = false;
    try {
        $stmtAllegati = $pdo->prepare("SELECT * FROM mac_anag_allegati WHERE mac_id = ? ORDER BY data_caricamento DESC");
        $stmtAllegati->execute([$id]);
        $allegati = $stmtAllegati->fetchAll(PDO::FETCH_ASSOC);
        $hasAllegati = $stmtAllegati->rowCount() > 0;
    } catch (PDOException $e) {
        $allegati = [];
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
                            <form method="POST" action="" id="macchinarioForm" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="matricola"><strong>Matricola/Numero di Serie *</strong></label>
                                        <input type="text" name="matricola" id="matricola"
                                            class="form-control <?= !empty($errorMessage) && strpos($errorMessage, 'matricola') !== false ? 'is-invalid' : '' ?>"
                                            required value="<?= htmlspecialchars($formData['matricola'] ?? '') ?>" readonly>
                                        <?php if (!empty($errorMessage) && strpos($errorMessage, 'matricola') !== false): ?>
                                            <div class="invalid-feedback">
                                                La matricola inserita è già in uso. Inserire una matricola unica.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="tipologia"><strong>Tipologia Macchina *</strong></label>
                                        <select name="tipologia" id="tipologia" class="form-control selectpicker"
                                            data-live-search="true" required>
                                            <option value="">-- Seleziona tipo --</option>
                                            <?php foreach ($tipi_macchine as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo['tipo']) ?>"
                                                    <?= (isset($formData['tipologia']) && $formData['tipologia'] == $tipo['tipo']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipo['tipo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="nuovo" <?= (isset($formData['tipologia']) && $formData['tipologia'] == 'nuovo') ? 'selected' : '' ?>>➕ Aggiungi nuovo
                                                tipo...</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sezione per nuovo tipo (nascosta inizialmente) -->
                                <div id="nuovo_tipo_section" class="row mt-3"
                                    style="display:<?= (isset($formData['tipologia']) && $formData['tipologia'] == 'nuovo') ? 'flex' : 'none' ?>;">
                                    <div class="col-md-6 form-group">
                                        <label for="nuovo_tipo"><strong>Nuovo Tipo di Macchina *</strong></label>
                                        <input type="text" name="nuovo_tipo" id="nuovo_tipo" class="form-control"
                                            value="<?= htmlspecialchars($formData['nuovo_tipo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="descrizione_tipo">Descrizione (opzionale)</label>
                                        <input type="text" name="descrizione_tipo" id="descrizione_tipo"
                                            class="form-control"
                                            value="<?= htmlspecialchars($formData['descrizione_tipo'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6 form-group">
                                        <label for="data_acquisto"><strong>Data Acquisto *</strong></label>
                                        <input type="date" name="data_acquisto" id="data_acquisto" class="form-control"
                                            required value="<?= htmlspecialchars($formData['data_acquisto'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="rif_fattura"><strong>Rif. Fattura</strong></label>
                                        <input type="text" name="rif_fattura" id="rif_fattura" class="form-control"
                                            value="<?= htmlspecialchars($formData['rif_fattura'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="fornitore"><strong>Fornitore *</strong></label>
                                        <input type="text" name="fornitore" id="fornitore" class="form-control" required
                                            value="<?= htmlspecialchars($formData['fornitore'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="locazione_documenti"><strong>Locazione Documenti</strong></label>
                                        <input type="text" name="locazione_documenti" id="locazione_documenti"
                                            class="form-control" placeholder="Es. Armadio A, Scaffale 3"
                                            value="<?= htmlspecialchars($formData['locazione_documenti'] ?? '') ?>">
                                        <small class="text-muted">Indicare dove sono conservati fisicamente i
                                            documenti</small>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4 form-group">
                                        <label for="modello"><strong>Modello *</strong></label>
                                        <input type="text" name="modello" id="modello" class="form-control" required
                                            value="<?= htmlspecialchars($formData['modello'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="marca"><strong>Marca</strong></label>
                                        <input type="text" name="marca" id="marca" class="form-control"
                                            value="<?= htmlspecialchars($formData['marca'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="anno_costruzione"><strong>Anno di Costruzione</strong></label>
                                        <input type="number" name="anno_costruzione" id="anno_costruzione"
                                            class="form-control" min="1900" max="<?= date('Y') ?>"
                                            placeholder="<?= date('Y') ?>"
                                            value="<?= htmlspecialchars($formData['anno_costruzione'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="note">Note (opzionale)</label>
                                        <textarea name="note" id="note" class="form-control"
                                            rows="3"><?= htmlspecialchars($formData['note'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Sezione per gli allegati -->
                                <div class="row mt-4" id="allegati">
                                    <div class="col-12">
                                        <h5 class="font-weight-bold text-primary">Allegati</h5>
                                        <p class="text-muted">Gestisci manuali, certificazioni e altri documenti
                                            relativi al macchinario</p>
                                    </div>
                                </div>

                                <!-- Lista allegati esistenti -->
                                <?php if ($hasAllegati): ?>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <div class="card border-left-info">
                                                <div class="card-body py-2">
                                                    <h6 class="font-weight-bold text-info mb-3">Allegati esistenti</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Nome File</th>
                                                                    <th>Categoria</th>
                                                                    <th>Descrizione</th>
                                                                    <th>Dimensione</th>
                                                                    <th>Data</th>
                                                                    <th>Azioni</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($allegati as $allegato): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <i
                                                                                class="fas fa-file-<?= getFileIcon($allegato['tipo_file']) ?> mr-1"></i>
                                                                            <?= htmlspecialchars($allegato['nome_file']) ?>
                                                                        </td>
                                                                        <td><?= htmlspecialchars(ucfirst($allegato['categoria'])) ?>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($allegato['descrizione'] ?: '-') ?>
                                                                        </td>
                                                                        <td><?= formatFileSize($allegato['dimensione']) ?></td>
                                                                        <td><?= date('d/m/Y', strtotime($allegato['data_caricamento'])) ?>
                                                                        </td>
                                                                        <td>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="allegati/download?id=<?= $allegato['id'] ?>"
                                                                                    class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-download"></i>
                                                                                </a>
                                                                                <div
                                                                                    class="custom-control custom-checkbox ml-2 mt-1">
                                                                                    <input type="checkbox"
                                                                                        name="delete_allegati[]"
                                                                                        value="<?= $allegato['id'] ?>"
                                                                                        class="custom-control-input"
                                                                                        id="delete_<?= $allegato['id'] ?>">
                                                                                    <label
                                                                                        class="custom-control-label text-danger"
                                                                                        for="delete_<?= $allegato['id'] ?>">Elimina</label>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Upload nuovi allegati -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6 class="font-weight-bold">Aggiungi nuovi allegati</h6>
                                    </div>
                                </div>

                                <div id="allegati-container">
                                    <div class="allegato-item row mb-3">
                                        <div class="col-md-4">
                                            <label><strong>File</strong></label>
                                            <input type="file" name="allegati[]" class="form-control-file">
                                        </div>
                                        <div class="col-md-3">
                                            <label><strong>Categoria</strong></label>
                                            <select name="categorie_allegati[]" class="form-control">
                                                <option value="manuale">Manuale</option>
                                                <option value="certificazione">Certificazione</option>
                                                <option value="scheda_tecnica">Scheda Tecnica</option>
                                                <option value="sicurezza">Sicurezza</option>
                                                <option value="altro">Altro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label><strong>Descrizione</strong></label>
                                            <input type="text" name="descrizioni_allegati[]" class="form-control"
                                                placeholder="Descrizione opzionale">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <button type="button" id="add-allegato" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-plus-circle"></i> Aggiungi altro allegato
                                        </button>
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
                                    <button type="submit" name="save_and_back" value="1"
                                        class="btn btn-primary btn-lg ml-2">
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
                                    <strong>Attenzione:</strong> La modifica della matricola potrebbe influenzare i
                                    riferimenti in altri moduli.
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

                    // Gestione degli allegati - aggiunta di un nuovo allegato
                    $("#add-allegato").click(function () {
                        const newRow = `
                            <div class="allegato-item row mb-3">
                                <div class="col-md-4">
                                    <label><strong>File</strong></label>
                                    <input type="file" name="allegati[]" class="form-control-file">
                                </div>
                                <div class="col-md-3">
                                    <label><strong>Categoria</strong></label>
                                    <select name="categorie_allegati[]" class="form-control">
                                        <option value="manuale">Manuale</option>
                                        <option value="certificazione">Certificazione</option>
                                        <option value="scheda_tecnica">Scheda Tecnica</option>
                                        <option value="sicurezza">Sicurezza</option>
                                        <option value="altro">Altro</option>
                                    </select>
                                </div>
                               Ecco il codice a partire dal punto che hai indicato:
phpCopy                                <div class="col-md-4">
                                    <label><strong>Descrizione</strong></label>
                                    <input type="text" name="descrizioni_allegati[]" class="form-control" placeholder="Descrizione opzionale">
                                </div>
                                <div class="col-md-1 d-flex align-items-end mb-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-allegato">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $("#allegati-container").append(newRow);
                    });

                    // Rimozione di un allegato quando si clicca sul pulsante di eliminazione
                    $(document).on("click", ".remove-allegato", function () {
                        $(this).closest(".allegato-item").remove();
                    });

                    // Conferma prima di abbandonare i cambiamenti non salvati
                    var formChanged = false;

                    $('#macchinarioForm input, #macchinarioForm textarea, #macchinarioForm select').change(function () {
                        formChanged = true;
                    });

                    $('a').click(function (e) {
                        if (formChanged && !$(this).hasClass('no-confirm')) {
                            var confirmLeave = confirm("Hai modifiche non salvate. Sei sicuro di voler abbandonare la pagina?");
                            if (!confirmLeave) {
                                e.preventDefault();
                            }
                        }
                    });
                });
            </script>

            <?php
            // Helper per determinare l'icona del file
            function getFileIcon($fileType)
            {
                if (empty($fileType))
                    return 'alt';

                $type = strtolower(pathinfo($fileType, PATHINFO_EXTENSION));
                if (empty($type)) {
                    // Try to get the MIME type part
                    $parts = explode('/', $fileType);
                    $type = end($parts);
                }

                switch ($type) {
                    case 'pdf':
                        return 'pdf';
                    case 'doc':
                    case 'docx':
                    case 'msword':
                    case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
                        return 'word';
                    case 'xls':
                    case 'xlsx':
                    case 'vnd.ms-excel':
                    case 'vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        return 'excel';
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'image':
                        return 'image';
                    default:
                        return 'alt';
                }
            }

            // Helper per formattare la dimensione del file
            function formatFileSize($bytes)
            {
                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                } else {
                    return $bytes . ' bytes';
                }
            }
            ?>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>