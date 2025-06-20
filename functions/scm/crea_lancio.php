<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

$edit_mode = false;
$lancio = null;

// Controllo se siamo in modalità modifica
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_mode = true;
    $lancio_id = (int) $_GET['id'];

    try {
        $pdo = getDbInstance();
        $stmt = $pdo->prepare("
            SELECT l.*, lab.nome_laboratorio 
            FROM scm_lanci l
            LEFT JOIN scm_laboratori lab ON l.laboratorio_id = lab.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lancio_id]);
        $lancio = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lancio) {
            $_SESSION['error'] = 'Lancio non trovato';
            header('Location: lista_lanci');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
    }
}

// Caricamento laboratori per select
try {
    $pdo = getDbInstance();
    $stmt = $pdo->query("SELECT id, nome_laboratorio FROM scm_laboratori WHERE attivo = TRUE ORDER BY nome_laboratorio");
    $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $laboratori = [];
    $error = 'Errore caricamento laboratori: ' . ($debug ? $e->getMessage() : 'Errore generico');
}

// Gestione form
if ($_POST) {
    $numero_lancio = trim($_POST['numero_lancio'] ?? '');
    $laboratorio_id = (int) ($_POST['laboratorio_id'] ?? 0);
    $ciclo_fasi = trim($_POST['ciclo_fasi'] ?? '');
    $data_lancio = trim($_POST['data_lancio'] ?? '');
    $articoli = trim($_POST['articoli'] ?? '');
    $paia_per_articolo = trim($_POST['paia_per_articolo'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $stato_generale = trim($_POST['stato_generale'] ?? 'IN_PREPARAZIONE');

    $errors = [];

    // Validazioni
    if (empty($numero_lancio)) {
        $errors[] = 'Numero lancio è obbligatorio';
    }
    if ($laboratorio_id <= 0) {
        $errors[] = 'Seleziona un laboratorio';
    }
    if (empty($ciclo_fasi)) {
        $errors[] = 'Almeno una fase è obbligatoria';
    }
    if (empty($data_lancio)) {
        $errors[] = 'Data lancio è obbligatoria';
    }
    if (empty($articoli)) {
        $errors[] = 'Almeno un articolo è obbligatorio';
    }
    if (empty($paia_per_articolo)) {
        $errors[] = 'Quantità paia è obbligatoria';
    }

    // Validazione coerenza articoli/paia
    if (!empty($articoli) && !empty($paia_per_articolo)) {
        $arr_articoli = explode(';', $articoli);
        $arr_paia = explode(';', $paia_per_articolo);

        if (count($arr_articoli) != count($arr_paia)) {
            $errors[] = 'Il numero di articoli deve corrispondere al numero di quantità';
        }

        // Controllo che le quantità siano numeriche
        foreach ($arr_paia as $paia) {
            if (!is_numeric(trim($paia)) || intval(trim($paia)) <= 0) {
                $errors[] = 'Tutte le quantità devono essere numeri positivi';
                break;
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getDbInstance();
            $pdo->beginTransaction();

            if ($edit_mode) {
                // Modifica
                $stmt = $pdo->prepare("
                    UPDATE scm_lanci 
                    SET numero_lancio = ?, laboratorio_id = ?, ciclo_fasi = ?, data_lancio = ?, 
                        articoli = ?, paia_per_articolo = ?, note = ?, stato_generale = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $numero_lancio,
                    $laboratorio_id,
                    $ciclo_fasi,
                    $data_lancio,
                    $articoli,
                    $paia_per_articolo,
                    $note,
                    $stato_generale,
                    $lancio_id
                ]);

                // Aggiorna le fasi del lancio
                aggiornaFasiLancio($pdo, $lancio_id, $ciclo_fasi);

                // Aggiorna gli articoli del lancio
                aggiornaArticoliLancio($pdo, $lancio_id, $articoli, $paia_per_articolo);

                $pdo->commit();
                $_SESSION['success'] = 'Lancio aggiornato con successo';
            } else {
                // Creazione
                $stmt = $pdo->prepare("
                    INSERT INTO scm_lanci (numero_lancio, laboratorio_id, ciclo_fasi, data_lancio, 
                                          articoli, paia_per_articolo, note, stato_generale) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $numero_lancio,
                    $laboratorio_id,
                    $ciclo_fasi,
                    $data_lancio,
                    $articoli,
                    $paia_per_articolo,
                    $note,
                    $stato_generale
                ]);

                $nuovo_lancio_id = $pdo->lastInsertId();

                // Debug: log l'ID del nuovo lancio
                error_log("Nuovo lancio creato con ID: " . $nuovo_lancio_id);

                // Crea le fasi del lancio
                $risultato_fasi = creaFasiLancio($pdo, $nuovo_lancio_id, $ciclo_fasi);
                error_log("Risultato creazione fasi: " . ($risultato_fasi ? 'OK' : 'ERRORE'));

                // Crea gli articoli del lancio
                $risultato_articoli = creaArticoliLancio($pdo, $nuovo_lancio_id, $articoli, $paia_per_articolo);
                error_log("Risultato creazione articoli: " . ($risultato_articoli ? 'OK' : 'ERRORE'));

                $pdo->commit();
                $_SESSION['success'] = 'Lancio creato con successo';
            }

            header('Location: lista_lanci');
            exit;

        } catch (PDOException $e) {
            $pdo->rollback();
            error_log("Errore PDO: " . $e->getMessage());
            $errors[] = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico durante il salvataggio');
        } catch (Exception $e) {
            $pdo->rollback();
            error_log("Errore generico: " . $e->getMessage());
            $errors[] = 'Errore: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Funzioni helper per gestione fasi e articoli - CORRETTE
function creaFasiLancio($pdo, $lancio_id, $ciclo_fasi)
{
    try {
        $fasi = explode(';', $ciclo_fasi);
        $ordine = 1;

        foreach ($fasi as $fase) {
            $fase = trim($fase);
            if (!empty($fase)) {
                // CORREZIONE: Non inserire articolo_id qui, deve essere 0 come default
                $stmt = $pdo->prepare("
                    INSERT INTO scm_fasi_lancio (lancio_id, nome_fase, ordine_fase, articolo_id, stato_fase) 
                    VALUES (?, ?, ?, 0, 'NON_INIZIATA')
                ");
                $result = $stmt->execute([$lancio_id, $fase, $ordine]);
                if (!$result) {
                    error_log("Errore inserimento fase: " . $fase);
                    return false;
                }
                $ordine++;
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Errore in creaFasiLancio: " . $e->getMessage());
        return false;
    }
}

function creaArticoliLancio($pdo, $lancio_id, $articoli_string, $quantita_string)
{
    try {
        $articoli = explode(';', $articoli_string);
        $quantita = explode(';', $quantita_string);
        $ordine = 1;

        foreach ($articoli as $index => $articolo) {
            $articolo = trim($articolo);
            $qty = isset($quantita[$index]) ? intval(trim($quantita[$index])) : 0;

            if (!empty($articolo) && $qty > 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO scm_articoli_lancio (lancio_id, codice_articolo, quantita_totale, quantita_completata, ordine_articolo) 
                    VALUES (?, ?, ?, 0, ?)
                ");
                $result = $stmt->execute([$lancio_id, $articolo, $qty, $ordine]);
                if (!$result) {
                    error_log("Errore inserimento articolo: " . $articolo);
                    return false;
                }
                $ordine++;
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Errore in creaArticoliLancio: " . $e->getMessage());
        return false;
    }
}

function aggiornaFasiLancio($pdo, $lancio_id, $ciclo_fasi)
{
    try {
        // ATTENZIONE: Non eliminare fasi se ci sono già avanzamenti
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM scm_avanzamento WHERE lancio_id = ?");
        $stmt->execute([$lancio_id]);
        $has_avanzamenti = $stmt->fetchColumn() > 0;

        if ($has_avanzamenti) {
            throw new Exception("Impossibile modificare le fasi: ci sono già avanzamenti registrati per questo lancio");
        }

        // Elimina fasi esistenti solo se non ci sono avanzamenti
        $stmt = $pdo->prepare("DELETE FROM scm_fasi_lancio WHERE lancio_id = ?");
        $stmt->execute([$lancio_id]);

        // Ricrea le fasi
        return creaFasiLancio($pdo, $lancio_id, $ciclo_fasi);
    } catch (Exception $e) {
        error_log("Errore in aggiornaFasiLancio: " . $e->getMessage());
        throw $e;
    }
}

function aggiornaArticoliLancio($pdo, $lancio_id, $articoli_string, $quantita_string)
{
    try {
        // ATTENZIONE: Non eliminare articoli se ci sono già avanzamenti
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM scm_avanzamento WHERE lancio_id = ?");
        $stmt->execute([$lancio_id]);
        $has_avanzamenti = $stmt->fetchColumn() > 0;

        if ($has_avanzamenti) {
            throw new Exception("Impossibile modificare gli articoli: ci sono già avanzamenti registrati per questo lancio");
        }

        // Elimina articoli esistenti solo se non ci sono avanzamenti
        $stmt = $pdo->prepare("DELETE FROM scm_articoli_lancio WHERE lancio_id = ?");
        $stmt->execute([$lancio_id]);

        // Ricrea gli articoli
        return creaArticoliLancio($pdo, $lancio_id, $articoli_string, $quantita_string);
    } catch (Exception $e) {
        error_log("Errore in aggiornaArticoliLancio: " . $e->getMessage());
        throw $e;
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
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>

                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-gradient-success text-white p-3 rounded shadow-sm">
                                <i class="fas fa-rocket fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">
                                    <?= $edit_mode ? 'Modifica' : 'Nuovo' ?> Lancio
                                </h1>
                                <p class="mb-0 text-gray-600">
                                    <?= $edit_mode ? 'Aggiorna i dati del lancio' : 'Crea un nuovo lancio con ciclo di fasi' ?>
                                </p>
                            </div>
                        </div>

                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index">SCM</a></li>
                        <li class="breadcrumb-item"><a href="lista_lanci">Lista Lanci</a></li>
                        <li class="breadcrumb-item active">Nuovo</li>
                    </ol>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Debug Info (da rimuovere in produzione) -->
                    <?php if ($debug && $_POST): ?>
                        <div class="alert alert-info">
                            <strong>Debug Info:</strong><br>
                            Numero Lancio: <?= htmlspecialchars($numero_lancio ?? 'N/A') ?><br>
                            Laboratorio ID: <?= htmlspecialchars($laboratorio_id ?? 'N/A') ?><br>
                            Ciclo Fasi: <?= htmlspecialchars($ciclo_fasi ?? 'N/A') ?><br>
                            Articoli: <?= htmlspecialchars($articoli ?? 'N/A') ?><br>
                            Paia: <?= htmlspecialchars($paia_per_articolo ?? 'N/A') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-<?= $edit_mode ? 'edit' : 'plus' ?> mr-2"></i>
                                        Dati Lancio
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="numero_lancio">Numero Lancio *</label>
                                                    <input type="text" class="form-control" id="numero_lancio"
                                                        name="numero_lancio"
                                                        value="<?= htmlspecialchars($lancio['numero_lancio'] ?? $_POST['numero_lancio'] ?? '') ?>"
                                                        required>
                                                    <small class="text-muted">Es: L2025001</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="laboratorio_id">Laboratorio *</label>
                                                    <select class="form-control" id="laboratorio_id"
                                                        name="laboratorio_id" required>
                                                        <option value="">Seleziona laboratorio...</option>
                                                        <?php foreach ($laboratori as $lab): ?>
                                                            <option value="<?= $lab['id'] ?>"
                                                                <?= ($lab['id'] == ($lancio['laboratorio_id'] ?? $_POST['laboratorio_id'] ?? '')) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($lab['nome_laboratorio']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ciclo_fasi">Ciclo di Fasi *</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="ciclo_fasi"
                                                            name="ciclo_fasi"
                                                            value="<?= htmlspecialchars($lancio['ciclo_fasi'] ?? $_POST['ciclo_fasi'] ?? '') ?>"
                                                            readonly required>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-toggle="modal" data-target="#modalFasi">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Fasi del processo produttivo in ordine
                                                        cronologico</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="data_lancio">Data Lancio *</label>
                                                    <input type="date" class="form-control" id="data_lancio"
                                                        name="data_lancio"
                                                        value="<?= htmlspecialchars($lancio['data_lancio'] ?? $_POST['data_lancio'] ?? date('Y-m-d')) ?>"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="stato_generale">Stato</label>
                                                    <select class="form-control" id="stato_generale"
                                                        name="stato_generale">
                                                        <?php
                                                        $stati = ['IN_PREPARAZIONE', 'IN_LAVORAZIONE', 'COMPLETO'];
                                                        $stato_corrente = $lancio['stato_generale'] ?? $_POST['stato_generale'] ?? 'IN_PREPARAZIONE';
                                                        ?>
                                                        <?php foreach ($stati as $stato): ?>
                                                            <option value="<?= $stato ?>" <?= ($stato == $stato_corrente) ? 'selected' : '' ?>>
                                                                <?= str_replace('_', ' ', $stato) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="articoli">Articoli *</label>
                                            <input type="text" class="form-control" id="articoli" name="articoli"
                                                value="<?= htmlspecialchars($lancio['articoli'] ?? $_POST['articoli'] ?? '') ?>"
                                                required>
                                            <small class="text-muted">Separare con ; (es:
                                                SCARPA001;SCARPA002;SNEAKER003)</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="paia_per_articolo">Quantità Paia per Articolo *</label>
                                            <input type="text" class="form-control" id="paia_per_articolo"
                                                name="paia_per_articolo"
                                                value="<?= htmlspecialchars($lancio['paia_per_articolo'] ?? $_POST['paia_per_articolo'] ?? '') ?>"
                                                required>
                                            <small class="text-muted">Separare con ; (es: 100;150;200) - deve
                                                corrispondere agli articoli</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="note">Note</label>
                                            <textarea class="form-control" id="note" name="note"
                                                rows="3"><?= htmlspecialchars($lancio['note'] ?? $_POST['note'] ?? '') ?></textarea>
                                            <small class="text-muted">Note aggiuntive sul lancio</small>
                                        </div>

                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <a href="lista_lanci" class="btn btn-secondary">
                                                <i class="fas fa-times mr-2"></i>Annulla
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save mr-2"></i>
                                                <?= $edit_mode ? 'Aggiorna' : 'Crea' ?> Lancio
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar con aiuto -->
                        <div class="col-lg-4">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Guida Compilazione
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h6>Formato Articoli e Quantità:</h6>
                                    <div class="mb-3">
                                        <strong>Articoli:</strong><br>
                                        <code>SCARPA001;SCARPA002;BOOT003</code>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Quantità:</strong><br>
                                        <code>100;150;75</code>
                                    </div>

                                    <hr>
                                    <h6>Gestione Fasi:</h6>
                                    <p class="small mb-2">
                                        • Clicca il pulsante <i class="fas fa-edit"></i> per gestire le fasi<br>
                                        • Aggiungi le fasi in ordine cronologico<br>
                                        • Ogni fase sarà tracciabile separatamente<br>
                                        • I laboratori potranno aggiornare lo stato di ogni fase
                                    </p>




                                    <hr>
                                    <h6>Stati Lancio:</h6>
                                    <ul class="list-unstyled small">
                                        <li><span class="badge badge-secondary">IN PREPARAZIONE</span> - Lancio in fase
                                            di preparazione</li>

                                        <li><span class="badge badge-success">IN LAVORAZIONE</span> - In corso di
                                            lavorazione</li>
                                        <li><span class="badge badge-primary">COMPLETO</span> - Lavorazione completata
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Gestione Fasi -->
                    <div class="modal fade" id="modalFasi" tabindex="-1" role="dialog" aria-labelledby="modalFasiLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFasiLabel">
                                        <i class="fas fa-list-ol mr-2"></i>
                                        Gestione Ciclo di Fasi
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>Fasi del Ciclo Produttivo</h6>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="nuova_fase"
                                                        placeholder="Nome fase (es: Taglio)">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success"
                                                            onclick="aggiungiFase()">
                                                            <i class="fas fa-plus"></i> Aggiungi
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="lista_fasi" class="border rounded p-3"
                                                style="min-height: 200px; background-color: #f8f9fa;">
                                                <div class="text-center text-muted" id="no_fasi">
                                                    <i class="fas fa-info-circle mb-2"></i><br>
                                                    Nessuna fase aggiunta.<br>
                                                    Inizia aggiungendo la prima fase del processo.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <h6>Modelli Predefiniti</h6>
                                            <div class="list-group">
                                                <button type="button" class="list-group-item list-group-item-action"
                                                    onclick="applicaModello('TAGLIO;PREPARAZIONE;ORLATURA;SPEDITO')">
                                                    <strong>STANDARD</strong><br>
                                                    <small>Taglio → Preparazione → Orlatura → Spedito</small>
                                                </button>
                                                <button type="button" class="list-group-item list-group-item-action"
                                                    onclick="applicaModello('TAGLIO;SPEDIZIONE SEMILAVORATI;PREPARAZIONE;ORLATURA;SPEDITO')">
                                                    <strong>STANDARD + LAVORAZIONI</strong><br>
                                                    <small>Taglio → Spedizione Semilavorati → Preparazione → Orlatura →
                                                        Spedito</small>
                                                </button>
                                                <button type="button" class="list-group-item list-group-item-action"
                                                    onclick="applicaModello('CUCITURA TALLONE;SECONDA FASE;CUCITURA PATTINA;SPEDITO')">
                                                    <strong>MOCASSINI CUCITURA</strong><br>
                                                    <small>Cucitura Tallone → Seconda Fase → Cucitura Pattina →
                                                        Spedito</small>
                                                </button>
                                            </div>

                                            <div class="mt-3">
                                                <button type="button" class="btn btn-outline-warning btn-sm btn-block"
                                                    onclick="svuotaFasi()">
                                                    <i class="fas fa-trash mr-1"></i>Svuota Tutto
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-primary" onclick="salvaFasi()">
                                        <i class="fas fa-save mr-1"></i>Salva Fasi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <script>
        // Array per gestire le fasi
        let fasiArray = [];

        // Carica fasi esistenti se in modalità modifica
        <?php if ($edit_mode && !empty($lancio['ciclo_fasi'])): ?>
            fasiArray = <?= json_encode(explode(';', $lancio['ciclo_fasi'])) ?>;
            document.addEventListener('DOMContentLoaded', function () {
                aggiornaVistaFasi();
            });
        <?php endif; ?>

        function aggiungiFase() {
            const input = document.getElementById('nuova_fase');
            const fase = input.value.trim();

            if (fase === '') {
                alert('Inserisci il nome della fase');
                return;
            }

            if (fasiArray.includes(fase)) {
                alert('Questa fase è già presente');
                return;
            }

            fasiArray.push(fase);
            input.value = '';
            aggiornaVistaFasi();
        }

        function rimuoviFase(index) {
            fasiArray.splice(index, 1);
            aggiornaVistaFasi();
        }

        function spostaSu(index) {
            if (index > 0) {
                [fasiArray[index], fasiArray[index - 1]] = [fasiArray[index - 1], fasiArray[index]];
                aggiornaVistaFasi();
            }
        }

        function spostaGiu(index) {
            if (index < fasiArray.length - 1) {
                [fasiArray[index], fasiArray[index + 1]] = [fasiArray[index + 1], fasiArray[index]];
                aggiornaVistaFasi();
            }
        }

        function aggiornaVistaFasi() {
            const container = document.getElementById('lista_fasi');
            let noFasi = document.getElementById('no_fasi');

            if (fasiArray.length === 0) {
                // Ricrea l'elemento no_fasi se non esiste
                if (!noFasi) {
                    container.innerHTML = `
                    <div class="text-center text-muted" id="no_fasi">
                        <i class="fas fa-info-circle mb-2"></i><br>
                        Nessuna fase aggiunta.<br>
                        Inizia aggiungendo la prima fase del processo.
                    </div>
                `;
                } else {
                    noFasi.style.display = 'block';
                }
                return;
            }

            // Nascondi o rimuovi il messaggio no_fasi
            if (noFasi) {
                noFasi.style.display = 'none';
            }

            let html = '';
            fasiArray.forEach((fase, index) => {
                html += `
                <div class="d-flex align-items-center mb-2 p-2 bg-white rounded border fase-item">
                    <span class="badge badge-primary mr-2">${index + 1}</span>
                    <span class="flex-grow-1">${escapeHtml(fase)}</span>
                    <div class="btn-group btn-group-sm">
                        ${index > 0 ? `<button type="button" class="btn btn-outline-secondary" onclick="spostaSu(${index})" title="Sposta su"><i class="fas fa-arrow-up"></i></button>` : ''}
                        ${index < fasiArray.length - 1 ? `<button type="button" class="btn btn-outline-secondary" onclick="spostaGiu(${index})" title="Sposta giù"><i class="fas fa-arrow-down"></i></button>` : ''}
                        <button type="button" class="btn btn-outline-danger" onclick="rimuoviFase(${index})" title="Rimuovi"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        // Funzione helper per escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function applicaModello(modello) {
            fasiArray = modello.split(';').map(f => f.trim());
            aggiornaVistaFasi();
        }

        function svuotaFasi() {
            if (confirm('Sei sicuro di voler rimuovere tutte le fasi?')) {
                fasiArray = [];
                aggiornaVistaFasi();
            }
        }

        function salvaFasi() {
            if (fasiArray.length === 0) {
                alert('Aggiungi almeno una fase');
                return;
            }

            const cicloFasi = fasiArray.join(';');
            document.getElementById('ciclo_fasi').value = cicloFasi;

            // Chiudi modal con Bootstrap 4
            $('#modalFasi').modal('hide');

            // Aggiorna il campo visivamente
            document.getElementById('ciclo_fasi').style.borderColor = '#28a745';
            setTimeout(() => {
                document.getElementById('ciclo_fasi').style.borderColor = '';
            }, 2000);
        }

        // Apri modal fasi quando si clicca sul campo
        document.getElementById('ciclo_fasi').addEventListener('click', function () {
            $('#modalFasi').modal('show');
        });

        // Permetti aggiunta fase con Enter
        document.getElementById('nuova_fase').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                aggiungiFase();
            }
        });

        // Calcolo automatico totale paia
        document.getElementById('paia_per_articolo').addEventListener('input', function () {
            const paia = this.value.split(';');
            let totale = 0;
            paia.forEach(p => {
                const num = parseInt(p.trim());
                if (!isNaN(num)) totale += num;
            });

            if (totale > 0) {
                this.style.borderColor = '#28a745';
                this.nextElementSibling.innerHTML = `Separare con ; (es: 100;150;200) - <strong>Totale: ${totale} paia</strong>`;
            } else {
                this.style.borderColor = '';
                this.nextElementSibling.innerHTML = 'Separare con ; (es: 100;150;200) - deve corrispondere agli articoli';
            }
        });

        // Controllo corrispondenza articoli/quantità
        function checkCorrispondenza() {
            const articoli = document.getElementById('articoli').value.split(';').filter(a => a.trim());
            const paia = document.getElementById('paia_per_articolo').value.split(';').filter(p => p.trim());

            if (articoli.length > 0 && paia.length > 0 && articoli.length !== paia.length) {
                document.getElementById('articoli').style.borderColor = '#dc3545';
                document.getElementById('paia_per_articolo').style.borderColor = '#dc3545';
            } else {
                document.getElementById('articoli').style.borderColor = '';
                document.getElementById('paia_per_articolo').style.borderColor = '';
            }
        }

        document.getElementById('articoli').addEventListener('input', checkCorrispondenza);
        document.getElementById('paia_per_articolo').addEventListener('input', checkCorrispondenza);

        // Validazione form prima dell'invio
        document.querySelector('form').addEventListener('submit', function (e) {
            if (document.getElementById('ciclo_fasi').value.trim() === '') {
                e.preventDefault();
                alert('Devi definire almeno una fase del ciclo produttivo');
                $('#modalFasi').modal('show');
                return false;
            }

            // Validazione aggiuntiva articoli/paia
            const articoli = document.getElementById('articoli').value.split(';').filter(a => a.trim());
            const paia = document.getElementById('paia_per_articolo').value.split(';').filter(p => p.trim());

            if (articoli.length !== paia.length) {
                e.preventDefault();
                alert('Il numero di articoli deve corrispondere al numero di quantità');
                return false;
            }

            // Controllo che tutte le quantità siano numeri validi
            for (let i = 0; i < paia.length; i++) {
                if (!paia[i] || isNaN(parseInt(paia[i])) || parseInt(paia[i]) <= 0) {
                    e.preventDefault();
                    alert('Tutte le quantità devono essere numeri positivi');
                    return false;
                }
            }

            // Se tutto ok, mostra indicatore di caricamento
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
            submitBtn.disabled = true;

            // In caso di errore, ripristina il pulsante dopo 5 secondi
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
</body>