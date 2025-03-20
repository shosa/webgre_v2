<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Ottieni l'istanza del database
$pdo = getDbInstance();

// Variabili per i messaggi
$successMessage = '';
$errorMessage = '';
$formData = []; // Per mantenere i dati del form in caso di errore

// Gestione del form di inserimento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Salva i dati del form per il ripristino in caso di errore
    $formData = $_POST;

    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Gestione aggiornamento rapido
        try {
            // Verifica se la matricola è già esistente (escludendo l'ID corrente)
            $checkStmt = $pdo->prepare("SELECT id FROM mac_anag WHERE matricola = ? AND id != ?");
            $checkStmt->execute([$_POST['edit_matricola'], $_POST['edit_id']]);
            if ($checkStmt->fetch()) {
                throw new PDOException("Matricola duplicata", 1062);
            }

            $stmt = $pdo->prepare("UPDATE mac_anag SET 
                matricola = ?,
                tipologia = ?,
                data_acquisto = ?,
                rif_fattura = ?,
                fornitore = ?,
                modello = ?,
                note = ?
                WHERE id = ?");

            $result = $stmt->execute([
                $_POST['edit_matricola'],
                $_POST['edit_tipologia'],
                $_POST['edit_data_acquisto'],
                $_POST['edit_rif_fattura'],
                $_POST['edit_fornitore'],
                $_POST['edit_modello'],
                $_POST['edit_note'] ?? null,
                $_POST['edit_id']
            ]);

            if ($result) {
                $successMessage = "Macchinario aggiornato con successo!";
                // Aggiorna la lista degli ultimi 5 macchinari
                $stmt = $pdo->query("SELECT * FROM mac_anag ORDER BY data_creazione DESC LIMIT 5");
                $ultimi_macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorMessage = "Errore durante l'aggiornamento del macchinario.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 1062 || $e->errorInfo[1] == 1062) {
                $errorMessage = "Errore: La matricola '<strong>" . htmlspecialchars($_POST['edit_matricola']) . "</strong>' è già associata ad un altro macchinario. Inserire una matricola diversa.";
            } else {
                $errorMessage = "Errore database: " . $e->getMessage();
            }
        }
    } else {
        // Gestione inserimento nuovo macchinario
        try {
            // Verifica se la matricola esiste già prima di tentare l'inserimento
            $checkStmt = $pdo->prepare("SELECT id FROM mac_anag WHERE matricola = ?");
            $checkStmt->execute([$_POST['matricola']]);
            if ($checkStmt->fetch()) {
                throw new PDOException("Matricola duplicata", 1062);
            }

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
                    // Se il campo nuovo tipo è vuoto ma è selezionato "nuovo"
                    $errorMessage = "Errore: Se si seleziona 'Aggiungi nuovo tipo', è necessario specificare il nome del tipo.";
                    // Carica tipi e macchinari per continuare con la visualizzazione
                    goto load_data;
                }
            }

            // Preparazione dell'inserimento del macchinario
            $stmt = $pdo->prepare("INSERT INTO mac_anag (matricola, tipologia, data_acquisto, rif_fattura, fornitore, modello, note) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $result = $stmt->execute([
                $_POST['matricola'],
                $tipologia,
                $_POST['data_acquisto'],
                $_POST['rif_fattura'],
                $_POST['fornitore'],
                $_POST['modello'],
                $_POST['note'] ?? null
            ]);

            if ($result) {
                // Reset dei dati del form ma mantieni i dati per la visualizzazione del messaggio
                $successMessage = "Macchinario '<strong>" . htmlspecialchars($_POST['matricola']) . "</strong>' inserito con successo!";
                $formData = []; // Pulisci i dati del form dopo il successo

                // Aggiorna la lista degli ultimi 5 macchinari
                $stmt = $pdo->query("SELECT * FROM mac_anag ORDER BY data_creazione DESC LIMIT 5");
                $ultimi_macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $errorMessage = "Errore durante l'inserimento del macchinario.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 1062 || $e->errorInfo[1] == 1062) {
                $errorMessage = "Errore: La matricola '<strong>" . htmlspecialchars($_POST['matricola']) . "</strong>' esiste già nel database. Ogni macchinario deve avere una matricola unica.";
            } else {
                $errorMessage = "Errore database: " . $e->getMessage();
            }
        }
    }
}

// Etichetta per il caricamento dei dati in caso di errore
load_data:

// Carica tutti i tipi di macchine esistenti
$stmt = $pdo->query("SELECT id, tipo FROM mac_types ORDER BY tipo");
$tipi_macchine = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carica gli ultimi 5 macchinari inseriti (se non già caricati in caso di successo)
if (!isset($ultimi_macchinari)) {
    $stmt = $pdo->query("SELECT * FROM mac_anag ORDER BY data_creazione DESC LIMIT 5");
    $ultimi_macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
                        <h1 class="h3 mb-0 text-gray-800">Inserimento Nuovo Macchinario</h1>

                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item active">Nuovo Macchinario</li>
                    </ol>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Dati Macchinario</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Operazioni:</div>
                                    <a class="dropdown-item" href="lista_macchinari">Visualizza tutti</a>
                                    <a class="dropdown-item" href="#" id="resetForm">Pulisci form</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="macchinarioForm">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="matricola"><strong>Matricola/Numero di Serie *</strong></label>
                                        <input type="text" name="matricola" id="matricola"
                                            class="form-control <?= !empty($errorMessage) && strpos($errorMessage, 'matricola') !== false ? 'is-invalid' : '' ?>"
                                            required value="<?= htmlspecialchars($formData['matricola'] ?? '') ?>">
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
                                        <input type="text" name="rif_fattura" id="rif_Fattura" class="form-control"
                                             value="<?= htmlspecialchars($formData['rif_fattura'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="fornitore"><strong>fornitore *</strong></label>
                                        <input type="text" name="fornitore" id="fornitore" class="form-control"
                                            required value="<?= htmlspecialchars($formData['fornitore'] ?? '') ?>">
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
                                        <textarea name="note" id="note" class="form-control"
                                            rows="3"><?= htmlspecialchars($formData['note'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save mr-2"></i>Salva Macchinario
                                    </button>
                                    <button type="button" id="clearBtn" class="btn btn-warning btn-lg ml-2">
                                        <i class="fas fa-eraser mr-2"></i>Pulisci Form
                                    </button>
                                    <a href="lista_macchinari" class="btn btn-info btn-lg ml-2">
                                        <i class="fas fa-list mr-2"></i>Vai alla Lista
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabella ultimi inserimenti -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ultimi 5 Macchinari Inseriti</h6>
                        </div>
                        <div class="card-body">
                            <?php if (count($ultimi_macchinari) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="ultimi-macchinari" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Matricola</th>
                                                <th>Tipologia</th>
                                                <th>Fornitore</th>
                                                <th>Modello</th>
                                                <th>Data Acquisto</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimi_macchinari as $macchinario): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($macchinario['matricola']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['tipologia']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['fornitore']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['modello']) ?></td>
                                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($macchinario['data_acquisto']))) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary edit-btn"
                                                            data-id="<?= $macchinario['id'] ?>"
                                                            data-matricola="<?= htmlspecialchars($macchinario['matricola']) ?>"
                                                            data-tipologia="<?= htmlspecialchars($macchinario['tipologia']) ?>"
                                                            data-data_acquisto="<?= htmlspecialchars($macchinario['data_acquisto']) ?>"
                                                            data-rif_fattura="<?= htmlspecialchars($macchinario['rif_fattura']) ?>"
                                                            data-fornitore="<?= htmlspecialchars($macchinario['fornitore']) ?>"
                                                            data-modello="<?= htmlspecialchars($macchinario['modello']) ?>"
                                                            data-note="<?= htmlspecialchars($macchinario['note'] ?? '') ?>">
                                                            <i class="fas fa-edit"></i> Modifica
                                                        </button>
                                                        <a href="dettaglio_macchinario?id=<?= $macchinario['id'] ?>"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> Dettagli
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> Nessun macchinario inserito finora. Utilizza il
                                    form sopra per aggiungere il primo macchinario.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal per modifica rapida -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Modifica Rapida Macchinario</h5>
                            <button type="button" class="close" id="closeModalX" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="edit_matricola"><strong>Matricola/Numero di Serie *</strong></label>
                                        <input type="text" name="edit_matricola" id="edit_matricola"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="edit_tipologia"><strong>Tipologia Macchina *</strong></label>
                                        <select name="edit_tipologia" id="edit_tipologia" class="form-control" required>
                                            <?php foreach ($tipi_macchine as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo['tipo']) ?>">
                                                    <?= htmlspecialchars($tipo['tipo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="edit_data_acquisto"><strong>Data Acquisto *</strong></label>
                                        <input type="date" name="edit_data_acquisto" id="edit_data_acquisto"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="edit_rif_fattura"><strong>Rif. Fattura</strong></label>
                                        <input type="text" name="edit_rif_fattura" id="edit_rif_fattura"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="edit_fornitore"><strong>fornitore *</strong></label>
                                        <input type="text" name="edit_fornitore" id="edit_fornitore"
                                            class="form-control" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="edit_modello"><strong>Modello *</strong></label>
                                        <input type="text" name="edit_modello" id="edit_modello" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="edit_note">Note (opzionale)</label>
                                        <textarea name="edit_note" id="edit_note" class="form-control"
                                            rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closeModalBtn">Annulla</button>
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <script>
                $(document).ready(function () {
                    // Inizializza la data di oggi come default se il campo è vuoto
                    if ($('#data_acquisto').val() === '') {
                        var today = new Date();
                        var dd = String(today.getDate()).padStart(2, '0');
                        var mm = String(today.getMonth() + 1).padStart(2, '0');
                        var yyyy = today.getFullYear();
                        today = yyyy + '-' + mm + '-' + dd;
                        $('#data_acquisto').val(today);
                    }

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

                    // Pulisci form
                    $('#clearBtn, #resetForm').click(function () {
                        // Pulisci il modulo
                        $('#macchinarioForm')[0].reset();
                        $('#nuovo_tipo_section').hide();
                        $('#matricola').removeClass('is-invalid');

                        // Nascondi messaggi di errore e successo
                        $('.alert-danger, .alert-success').fadeOut();

                        // Reimposta la data odierna
                        var today = new Date();
                        var dd = String(today.getDate()).padStart(2, '0');
                        var mm = String(today.getMonth() + 1).padStart(2, '0');
                        var yyyy = today.getFullYear();
                        today = yyyy + '-' + mm + '-' + dd;
                        $('#data_acquisto').val(today);

                        return false;
                    });

                    // Sostituiamo la funzione modal con una soluzione alternativa
                    $('.edit-btn').click(function () {
                        // Imposta i valori nei campi del form
                        $('#edit_id').val($(this).data('id'));
                        $('#edit_matricola').val($(this).data('matricola'));
                        $('#edit_tipologia').val($(this).data('tipologia'));
                        $('#edit_data_acquisto').val($(this).data('data_acquisto'));
                        $('#edit_rif_fattura').val($(this).data('rif_fattura'));
                        $('#edit_fornitore').val($(this).data('fornitore'));
                        $('#edit_modello').val($(this).data('modello'));
                        $('#edit_note').val($(this).data('note'));

                        // Mostra il modal con JavaScript puro
                        document.getElementById('editModal').style.display = 'block';
                        document.getElementById('editModal').classList.add('show');
                        document.body.classList.add('modal-open');

                        // Crea un backdrop per il modal
                        var backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    });

                    // Gestione della chiusura del modal
                    $('#closeModalBtn, #closeModalX').click(function () {
                        // Nascondi il modal
                        document.getElementById('editModal').style.display = 'none';
                        document.getElementById('editModal').classList.remove('show');
                        document.body.classList.remove('modal-open');

                        // Rimuovi il backdrop
                        var backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.parentNode.removeChild(backdrop);
                        }
                    });

                    // Auto-focus sul primo campo quando la pagina si carica
                    if ($('#matricola').val() === '') {
                        $('#matricola').focus();
                    }

                    // Verifica matricola con AJAX (opzionale - richiede implementazione del server)
                    $('#matricola').blur(function () {
                        var matricola = $(this).val();
                        if (matricola !== '') {
                            // Qui puoi aggiungere una verifica AJAX per controllare se la matricola esiste già
                            // $.ajax({...});
                        }
                    });
                });
            </script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>