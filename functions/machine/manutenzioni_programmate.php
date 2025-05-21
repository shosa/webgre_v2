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

// Gestione eliminazione
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    try {
        $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);

        $stmt = $pdo->prepare("DELETE FROM mac_manutenzioni_programmate WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $successMessage = "Manutenzione programmata eliminata con successo.";
        } else {
            $errorMessage = "Manutenzione programmata non trovata o già eliminata.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Errore durante l'eliminazione: " . $e->getMessage();
    }
}

// Gestione del form (aggiunta/modifica)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Salva i dati del form per il ripristino in caso di errore
    $formData = $_POST;

    try {
        // Validazione dati
        if (empty($_POST['mac_id']) || empty($_POST['tipo_id']) || empty($_POST['frequenza']) || empty($_POST['prossima_manutenzione'])) {
            throw new Exception("Tutti i campi contrassegnati con * sono obbligatori.");
        }

        // Se la frequenza è personalizzata, l'intervallo_giorni è obbligatorio
        if ($_POST['frequenza'] === 'personalizzata' && empty($_POST['intervallo_giorni'])) {
            throw new Exception("Per frequenza personalizzata, specificare l'intervallo in giorni.");
        }

        // Determina intervallo_giorni in base alla frequenza
        $intervallo_giorni = null;
        switch ($_POST['frequenza']) {
            case 'giornaliera':
                $intervallo_giorni = 1;
                break;
            case 'settimanale':
                $intervallo_giorni = 7;
                break;
            case 'mensile':
                $intervallo_giorni = 30;
                break;
            case 'trimestrale':
                $intervallo_giorni = 90;
                break;
            case 'semestrale':
                $intervallo_giorni = 180;
                break;
            case 'annuale':
                $intervallo_giorni = 365;
                break;
            case 'personalizzata':
                $intervallo_giorni = intval($_POST['intervallo_giorni']);
                break;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            // Modifica manutenzione programmata esistente
            $stmt = $pdo->prepare("UPDATE mac_manutenzioni_programmate SET 
                mac_id = ?,
                tipo_id = ?,
                frequenza = ?,
                intervallo_giorni = ?,
                prossima_manutenzione = ?,
                descrizione = ?,
                operatore_preferito = ?,
                priorita = ?,
                stato = ?,
                notifica_giorni_prima = ?
                WHERE id = ?");

            $result = $stmt->execute([
                $_POST['mac_id'],
                $_POST['tipo_id'],
                $_POST['frequenza'],
                $intervallo_giorni,
                $_POST['prossima_manutenzione'],
                $_POST['descrizione'],
                $_POST['operatore_preferito'] ?? null,
                $_POST['priorita'],
                $_POST['stato'],
                $_POST['notifica_giorni_prima'],
                $_POST['edit_id']
            ]);

            if ($result) {
                $successMessage = "Manutenzione programmata aggiornata con successo!";
                $formData = []; // Pulisci i dati del form dopo il successo
            } else {
                $errorMessage = "Errore durante l'aggiornamento della manutenzione programmata.";
            }
        } else {
            // Aggiunta nuova manutenzione programmata
            $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni_programmate 
                (mac_id, tipo_id, frequenza, intervallo_giorni, prossima_manutenzione, descrizione, operatore_preferito, priorita, stato, notifica_giorni_prima) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $result = $stmt->execute([
                $_POST['mac_id'],
                $_POST['tipo_id'],
                $_POST['frequenza'],
                $intervallo_giorni,
                $_POST['prossima_manutenzione'],
                $_POST['descrizione'],
                $_POST['operatore_preferito'] ?? null,
                $_POST['priorita'],
                $_POST['stato'],
                $_POST['notifica_giorni_prima']
            ]);

            if ($result) {
                $successMessage = "Nuova manutenzione programmata creata con successo!";
                $formData = []; // Pulisci i dati del form dopo il successo
            } else {
                $errorMessage = "Errore durante la creazione della manutenzione programmata.";
            }
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Filtraggio e ordinamento
$mac_id = isset($_GET['mac_id']) ? filter_var($_GET['mac_id'], FILTER_VALIDATE_INT) : null;
$stato = isset($_GET['stato']) ? $_GET['stato'] : '';
$priorita = isset($_GET['priorita']) ? $_GET['priorita'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'prossima_manutenzione';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Query di base
$query = "SELECT p.*, m.matricola, m.modello, m.fornitore, t.nome as tipo_nome, t.colore as tipo_colore 
          FROM mac_manutenzioni_programmate p 
          JOIN mac_anag m ON p.mac_id = m.id 
          JOIN mac_manutenzioni_tipi t ON p.tipo_id = t.id 
          WHERE 1=1";
$params = [];

// Aggiungi filtri
if ($mac_id) {
    $query .= " AND p.mac_id = ?";
    $params[] = $mac_id;
}

if (!empty($stato)) {
    $query .= " AND p.stato = ?";
    $params[] = $stato;
}

if (!empty($priorita)) {
    $query .= " AND p.priorita = ?";
    $params[] = $priorita;
}

// Validazione ordinamento
$validSortColumns = ['prossima_manutenzione', 'frequenza', 'priorita', 'stato'];
$validSortOrders = ['ASC', 'DESC'];

if (!in_array($sort, $validSortColumns)) {
    $sort = 'prossima_manutenzione';
}

if (!in_array($order, $validSortOrders)) {
    $order = 'ASC';
}

$query .= " ORDER BY $sort $order";

// Esecuzione query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$manutenzioni_programmate = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera i macchinari per il form
$stmtMacchine = $pdo->query("SELECT id, matricola, modello, fornitore FROM mac_anag ORDER BY matricola");
$macchine = $stmtMacchine->fetchAll(PDO::FETCH_ASSOC);

// Recupera i tipi di manutenzione (solo quelli programmabili)
$stmtTipi = $pdo->query("SELECT id, nome, colore, intervallo_giorni FROM mac_manutenzioni_tipi WHERE is_programmata = 1 ORDER BY nome");
$tipi_manutenzione = $stmtTipi->fetchAll(PDO::FETCH_ASSOC);

// Informazioni sul macchinario se filtrato
$macchinaInfo = null;
if ($mac_id) {
    $stmtMacchina = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmtMacchina->execute([$mac_id]);
    $macchinaInfo = $stmtMacchina->fetch(PDO::FETCH_ASSOC);
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-calendar-alt text-gray-500 mr-2"></i>
                            <?= $macchinaInfo ? "Manutenzioni Programmate: " . htmlspecialchars($macchinaInfo['matricola']) : "Manutenzioni Programmate" ?>
                        </h1>
                        <div>
                            <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal"
                                data-target="#addModal">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Nuova Programmazione
                            </button>
                            <?php if ($mac_id): ?>
                                <a href="dettaglio_macchinario?id=<?= $mac_id ?>"
                                    class="btn btn-info btn-sm shadow-sm ml-2">
                                    <i class="fas fa-clipboard-list fa-sm text-white-50"></i> Scheda Macchinario
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($stato) || !empty($priorita)): ?>
                                <a href="<?= $mac_id ? "manutenzioni_programmate?mac_id=$mac_id" : "manutenzioni_programmate" ?>"
                                    class="btn btn-secondary btn-sm shadow-sm ml-2">
                                    <i class="fas fa-undo fa-sm text-white-50"></i> Reset Filtri
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <?php if ($mac_id): ?>
                            <li class="breadcrumb-item"><a href="dettaglio_macchinario?id=<?= $mac_id ?>">Dettaglio
                                    Macchinario</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active">Manutenzioni Programmate</li>
                    </ol>

                    <?php if ($macchinaInfo): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Informazioni Macchinario</h6>
                                <span class="badge badge-secondary px-3 py-2">ID: <?= $mac_id ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Matricola
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($macchinaInfo['matricola']) ?>
                                        </div>
                                    </div>
                                    <div class="col-group">
                                        <label for="descrizione"><strong>Descrizione *</strong></label>
                                        <textarea name="descrizione" id="descrizione" class="form-control" rows="3"
                                            required><?= htmlspecialchars($formData['descrizione'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="operatore_preferito">Operatore Preferito</label>
                                        <input type="text" name="operatore_preferito" id="operatore_preferito"
                                            class="form-control"
                                            value="<?= htmlspecialchars($formData['operatore_preferito'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="stato"><strong>Stato *</strong></label>
                                        <select name="stato" id="stato" class="form-control" required>
                                            <option value="attiva" <?= (!isset($formData['stato']) || $formData['stato'] == 'attiva') ? 'selected' : '' ?>>Attiva</option>
                                            <option value="sospesa" <?= (isset($formData['stato']) && $formData['stato'] == 'sospesa') ? 'selected' : '' ?>>Sospesa</option>
                                            <option value="completata" <?= (isset($formData['stato']) && $formData['stato'] == 'completata') ? 'selected' : '' ?>>Completata</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="notifica_giorni_prima">Notifica (giorni prima)</label>
                                        <input type="number" name="notifica_giorni_prima" id="notifica_giorni_prima"
                                            class="form-control" min="0" max="60"
                                            value="<?= htmlspecialchars($formData['notifica_giorni_prima'] ?? '7') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-success">Salva</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Modifica Manutenzione Programmata -->
                <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Modifica Manutenzione Programmata</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="edit_id" id="edit_id">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="edit_mac_id"><strong>Macchinario *</strong></label>
                                            <select name="mac_id" id="edit_mac_id" class="form-control" required <?= $mac_id ? 'disabled' : '' ?>>
                                                <option value="">-- Seleziona Macchinario --</option>
                                                <?php foreach ($macchine as $macchina): ?>
                                                    <option value="<?= $macchina['id'] ?>">
                                                        <?= htmlspecialchars($macchina['matricola']) ?> -
                                                        <?= htmlspecialchars($macchina['modello']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if ($mac_id): ?>
                                                <input type="hidden" name="mac_id" value="<?= $mac_id ?>">
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="edit_tipo_id"><strong>Tipo di Manutenzione *</strong></label>
                                            <select name="tipo_id" id="edit_tipo_id" class="form-control" required>
                                                <option value="">-- Seleziona Tipo --</option>
                                                <?php foreach ($tipi_manutenzione as $tipo): ?>
                                                    <option value="<?= $tipo['id'] ?>"
                                                        data-intervallo="<?= $tipo['intervallo_giorni'] ?>">
                                                        <?= htmlspecialchars($tipo['nome']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="edit_frequenza"><strong>Frequenza *</strong></label>
                                            <select name="frequenza" id="edit_frequenza" class="form-control" required>
                                                <option value="giornaliera">Giornaliera</option>
                                                <option value="settimanale">Settimanale</option>
                                                <option value="mensile">Mensile</option>
                                                <option value="trimestrale">Trimestrale</option>
                                                <option value="semestrale">Semestrale</option>
                                                <option value="annuale">Annuale</option>
                                                <option value="personalizzata">Personalizzata</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 form-group" id="edit_intervalloGroup">
                                            <label for="edit_intervallo_giorni"><strong>Intervallo (giorni)
                                                    *</strong></label>
                                            <input type="number" name="intervallo_giorni" id="edit_intervallo_giorni"
                                                class="form-control" min="1" max="1825">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="edit_prossima_manutenzione"><strong>Prossima Manutenzione
                                                    *</strong></label>
                                            <input type="date" name="prossima_manutenzione" id="edit_prossima_manutenzione"
                                                class="form-control" required>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label for="edit_priorita"><strong>Priorità *</strong></label>
                                            <select name="priorita" id="edit_priorita" class="form-control" required>
                                                <option value="bassa">Bassa</option>
                                                <option value="media">Media</option>
                                                <option value="alta">Alta</option>
                                                <option value="critica">Critica</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 form-
                                    <div class=" col-md-3 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tipologia
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinaInfo['tipologia']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Fornitore
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinaInfo['fornitore']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Modello
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($macchinaInfo['modello']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    <?php endif; ?>

                    <!-- Card dei filtri -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Filtri</h6>
                            <button class="btn btn-link btn-sm" type="button" data-toggle="collapse"
                                data-target="#collapseFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body collapse show" id="collapseFilters">
                            <form method="GET" action="" class="form-inline">
                                <?php if ($mac_id): ?>
                                    <input type="hidden" name="mac_id" value="<?= $mac_id ?>">
                                <?php endif; ?>

                                <div class="form-group mb-2 mr-2">
                                    <label for="stato" class="sr-only">Stato</label>
                                    <select class="form-control" id="stato" name="stato">
                                        <option value="">-- Tutti gli Stati --</option>
                                        <option value="attiva" <?= ($stato == 'attiva') ? 'selected' : '' ?>>Attiva
                                        </option>
                                        <option value="sospesa" <?= ($stato == 'sospesa') ? 'selected' : '' ?>>Sospesa
                                        </option>
                                        <option value="completata" <?= ($stato == 'completata') ? 'selected' : '' ?>>
                                            Completata</option>
                                    </select>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="priorita" class="sr-only">Priorità</label>
                                    <select class="form-control" id="priorita" name="priorita">
                                        <option value="">-- Tutte le Priorità --</option>
                                        <option value="bassa" <?= ($priorita == 'bassa') ? 'selected' : '' ?>>Bassa
                                        </option>
                                        <option value="media" <?= ($priorita == 'media') ? 'selected' : '' ?>>Media
                                        </option>
                                        <option value="alta" <?= ($priorita == 'alta') ? 'selected' : '' ?>>Alta</option>
                                        <option value="critica" <?= ($priorita == 'critica') ? 'selected' : '' ?>>Critica
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="sort" class="sr-only">Ordinamento</label>
                                    <select class="form-control" id="sort" name="sort">
                                        <option value="prossima_manutenzione" <?= ($sort == 'prossima_manutenzione') ? 'selected' : '' ?>>Prossima data</option>
                                        <option value="frequenza" <?= ($sort == 'frequenza') ? 'selected' : '' ?>>Frequenza
                                        </option>
                                        <option value="priorita" <?= ($sort == 'priorita') ? 'selected' : '' ?>>Priorità
                                        </option>
                                        <option value="stato" <?= ($sort == 'stato') ? 'selected' : '' ?>>Stato</option>
                                    </select>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="order" class="sr-only">Direzione</label>
                                    <select class="form-control" id="order" name="order">
                                        <option value="ASC" <?= ($order == 'ASC') ? 'selected' : '' ?>>Crescente</option>
                                        <option value="DESC" <?= ($order == 'DESC') ? 'selected' : '' ?>>Decrescente
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mb-2">Applica Filtri</button>
                            </form>
                        </div>
                    </div>

                    <!-- Lista Manutenzioni Programmate -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Manutenzioni Programmate
                                <span class="badge badge-secondary"><?= count($manutenzioni_programmate) ?>
                                    programmate</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (count($manutenzioni_programmate) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="programmate-table" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <?php if (!$mac_id): ?>
                                                    <th>Macchinario</th>
                                                <?php endif; ?>
                                                <th>Tipo</th>
                                                <th>Prossima Manutenzione</th>
                                                <th>Frequenza</th>
                                                <th>Descrizione</th>
                                                <th>Priorità</th>
                                                <th>Stato</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($manutenzioni_programmate as $mp):
                                                // Calcola giorni mancanti
                                                $oggi = new DateTime();
                                                $prossimaData = new DateTime($mp['prossima_manutenzione']);
                                                $giorniMancanti = $oggi->diff($prossimaData)->days;

                                                // Determina la classe per la data
                                                $dataClass = 'text-success';
                                                if ($prossimaData < $oggi) {
                                                    $dataClass = 'text-danger font-weight-bold';
                                                } elseif ($giorniMancanti <= 7) {
                                                    $dataClass = 'text-warning font-weight-bold';
                                                }
                                                ?>
                                                <tr>
                                                    <?php if (!$mac_id): ?>
                                                        <td>
                                                            <a href="dettaglio_macchinario?id=<?= $mp['mac_id'] ?>">
                                                                <?= htmlspecialchars($mp['matricola']) ?>
                                                            </a>
                                                            <div class="small text-muted">
                                                                <?= htmlspecialchars($mp['modello']) ?>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <span class="badge badge-pill"
                                                            style="background-color: <?= htmlspecialchars($mp['tipo_colore']) ?>; color: white;">
                                                            <?= htmlspecialchars($mp['tipo_nome']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="<?= $dataClass ?>">
                                                        <?= date('d/m/Y', strtotime($mp['prossima_manutenzione'])) ?>
                                                        <?php if ($prossimaData >= $oggi): ?>
                                                            <div class="small">
                                                                tra <?= $giorniMancanti ?> giorni
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="small">
                                                                scaduta da <?= $giorniMancanti ?> giorni
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $frequenzaLabels = [
                                                            'giornaliera' => 'Giornaliera',
                                                            'settimanale' => 'Settimanale',
                                                            'mensile' => 'Mensile',
                                                            'trimestrale' => 'Trimestrale',
                                                            'semestrale' => 'Semestrale',
                                                            'annuale' => 'Annuale',
                                                            'personalizzata' => 'Personalizzata'
                                                        ];
                                                        echo $frequenzaLabels[$mp['frequenza']] ?? ucfirst($mp['frequenza']);

                                                        if ($mp['frequenza'] === 'personalizzata') {
                                                            echo " <div class='small'>ogni {$mp['intervallo_giorni']} giorni</div>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars(mb_substr($mp['descrizione'], 0, 50)) ?>
                                                        <?= (mb_strlen($mp['descrizione']) > 50) ? '...' : '' ?>
                                                        <?php if (!empty($mp['operatore_preferito'])): ?>
                                                            <div class="small text-muted">
                                                                Operatore: <?= htmlspecialchars($mp['operatore_preferito']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $prioritaLabels = [
                                                            'bassa' => '<span class="badge badge-info">Bassa</span>',
                                                            'media' => '<span class="badge badge-primary">Media</span>',
                                                            'alta' => '<span class="badge badge-warning">Alta</span>',
                                                            'critica' => '<span class="badge badge-danger">Critica</span>'
                                                        ];
                                                        echo $prioritaLabels[$mp['priorita']] ?? '<span class="badge badge-secondary">-</span>';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $statoLabels = [
                                                            'attiva' => '<span class="badge badge-success">Attiva</span>',
                                                            'sospesa' => '<span class="badge badge-warning">Sospesa</span>',
                                                            'completata' => '<span class="badge badge-secondary">Completata</span>'
                                                        ];
                                                        echo $statoLabels[$mp['stato']] ?? '<span class="badge badge-secondary">-</span>';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($prossimaData <= $oggi && $mp['stato'] == 'attiva'): ?>
                                                            <a href="manutenzione-form?id=<?= $mp['mac_id'] ?>&scheduled_id=<?= $mp['id'] ?>&token=<?= md5($mp['matricola'] . $mp['mac_id']) ?>"
                                                                target="_blank" class="btn btn-sm btn-success" title="Esegui">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <button type="button" class="btn btn-sm btn-primary edit-btn"
                                                            data-id="<?= $mp['id'] ?>" data-mac_id="<?= $mp['mac_id'] ?>"
                                                            data-tipo_id="<?= $mp['tipo_id'] ?>"
                                                            data-frequenza="<?= $mp['frequenza'] ?>"
                                                            data-intervallo_giorni="<?= $mp['intervallo_giorni'] ?>"
                                                            data-prossima_manutenzione="<?= $mp['prossima_manutenzione'] ?>"
                                                            data-descrizione="<?= htmlspecialchars($mp['descrizione']) ?>"
                                                            data-operatore_preferito="<?= htmlspecialchars($mp['operatore_preferito'] ?? '') ?>"
                                                            data-priorita="<?= $mp['priorita'] ?>"
                                                            data-stato="<?= $mp['stato'] ?>"
                                                            data-notifica_giorni_prima="<?= $mp['notifica_giorni_prima'] ?>"
                                                            title="Modifica">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                            data-id="<?= $mp['id'] ?>"
                                                            data-frequenza="<?= $frequenzaLabels[$mp['frequenza']] ?>"
                                                            data-data="<?= date('d/m/Y', strtotime($mp['prossima_manutenzione'])) ?>"
                                                            title="Elimina">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> Nessuna manutenzione programmata
                                    trovata<?= (!empty($stato) || !empty($priorita)) ? ' con i filtri applicati.' : '.' ?>
                                    <?php if (!empty($stato) || !empty($priorita)): ?>
                                        <a href="<?= $mac_id ? "manutenzioni_programmate?mac_id=$mac_id" : "manutenzioni_programmate" ?>"
                                            class="alert-link">Rimuovi i filtri</a> per visualizzare tutte le manutenzioni
                                        programmate.
                                    <?php else: ?>
                                        <button type="button" data-toggle="modal" data-target="#addModal"
                                            class="alert-link btn btn-link">Aggiungi una nuova programmazione</button> per
                                        iniziare.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Aggiungi Manutenzione Programmata -->
            <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Aggiungi Manutenzione Programmata</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="mac_id"><strong>Macchinario *</strong></label>
                                        <select name="mac_id" id="mac_id" class="form-control" required <?= $mac_id ? 'disabled' : '' ?>>
                                            <option value="">-- Seleziona Macchinario --</option>
                                            <?php foreach ($macchine as $macchina): ?>
                                                <option value="<?= $macchina['id'] ?>" <?= ($mac_id == $macchina['id'] || (isset($formData['mac_id']) && $formData['mac_id'] == $macchina['id'])) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($macchina['matricola']) ?> -
                                                    <?= htmlspecialchars($macchina['modello']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($mac_id): ?>
                                            <input type="hidden" name="mac_id" value="<?= $mac_id ?>">
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="tipo_id"><strong>Tipo di Manutenzione *</strong></label>
                                        <select name="tipo_id" id="tipo_id" class="form-control" required>
                                            <option value="">-- Seleziona Tipo --</option>
                                            <?php foreach ($tipi_manutenzione as $tipo): ?>
                                                <option value="<?= $tipo['id'] ?>"
                                                    data-intervallo="<?= $tipo['intervallo_giorni'] ?>"
                                                    <?= (isset($formData['tipo_id']) && $formData['tipo_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipo['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="frequenza"><strong>Frequenza *</strong></label>
                                        <select name="frequenza" id="frequenza" class="form-control" required>
                                            <option value="giornaliera" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'giornaliera') ? 'selected' : '' ?>>Giornaliera
                                            </option>
                                            <option value="settimanale" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'settimanale') ? 'selected' : '' ?>>Settimanale
                                            </option>
                                            <option value="mensile" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'mensile') ? 'selected' : '' ?>>Mensile</option>
                                            <option value="trimestrale" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'trimestrale') ? 'selected' : '' ?>>Trimestrale
                                            </option>
                                            <option value="semestrale" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'semestrale') ? 'selected' : '' ?>>Semestrale
                                            </option>
                                            <option value="annuale" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'annuale') ? 'selected' : '' ?>>Annuale</option>
                                            <option value="personalizzata" <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'personalizzata') ? 'selected' : '' ?>>
                                                Personalizzata</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group" id="intervalloGroup"
                                        style="display: <?= (isset($formData['frequenza']) && $formData['frequenza'] == 'personalizzata') ? 'block' : 'none' ?>;">
                                        <label for="intervallo_giorni"><strong>Intervallo (giorni) *</strong></label>
                                        <input type="number" name="intervallo_giorni" id="intervallo_giorni"
                                            class="form-control" min="1" max="1825"
                                            value="<?= htmlspecialchars($formData['intervallo_giorni'] ?? '90') ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="prossima_manutenzione"><strong>Prossima Manutenzione
                                                *</strong></label>
                                        <input type="date" name="prossima_manutenzione" id="prossima_manutenzione"
                                            class="form-control" required
                                            value="<?= htmlspecialchars($formData['prossima_manutenzione'] ?? date('Y-m-d')) ?>">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="priorita"><strong>Priorità *</strong></label>
                                        <select name="priorita" id="priorita" class="form-control" required>
                                            <option value="bassa" <?= (isset($formData['priorita']) && $formData['priorita'] == 'bassa') ? 'selected' : '' ?>>Bassa</option>
                                            <option value="media" <?= (!isset($formData['priorita']) || $formData['priorita'] == 'media') ? 'selected' : '' ?>>Media</option>
                                            <option value="alta" <?= (isset($formData['priorita']) && $formData['priorita'] == 'alta') ? 'selected' : '' ?>>Alta</option>
                                            <option value="critica" <?= (isset($formData['priorita']) && $formData['priorita'] == 'critica') ? 'selected' : '' ?>>Critica</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label for="edit_descrizione"><strong>Descrizione *</strong></label>
                                        <textarea name="descrizione" id="edit_descrizione" class="form-control" rows="3"
                                            required></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="edit_operatore_preferito">Operatore Preferito</label>
                                        <input type="text" name="operatore_preferito" id="edit_operatore_preferito"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="edit_stato"><strong>Stato *</strong></label>
                                        <select name="stato" id="edit_stato" class="form-control" required>
                                            <option value="attiva">Attiva</option>
                                            <option value="sospesa">Sospesa</option>
                                            <option value="completata">Completata</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="edit_notifica_giorni_prima">Notifica (giorni prima)</label>
                                        <input type="number" name="notifica_giorni_prima"
                                            id="edit_notifica_giorni_prima" class="form-control" min="0" max="60">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Elimina -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Conferma Eliminazione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Sei sicuro di voler eliminare la programmazione <span id="delete_frequenza"></span> del
                                <strong id="delete_data"></strong>?
                            </p>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                L'eliminazione rimuoverà definitivamente questa programmazione dalla pianificazione.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                            <a href="#" id="confirm-delete" class="btn btn-danger">Elimina</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <script>
                $(document).ready(function () {
                    // Mostra/nascondi campo intervallo giorni in base alla frequenza
                    $('#frequenza').change(function () {
                        if ($(this).val() === 'personalizzata') {
                            $('#intervalloGroup').fadeIn();
                        } else {
                            $('#intervalloGroup').fadeOut();
                        }
                    });

                    // Applica intervallo predefinito dal tipo di manutenzione
                    $('#tipo_id').change(function () {
                        var intervallo = $(this).find(':selected').data('intervallo');
                        if (intervallo) {
                            $('#intervallo_giorni').val(intervallo);
                        }
                    });

                    // Edit button click
                    $('.edit-btn').click(function () {
                        $('#edit_id').val($(this).data('id'));
                        $('#edit_mac_id').val($(this).data('mac_id'));
                        $('#edit_tipo_id').val($(this).data('tipo_id'));
                        $('#edit_frequenza').val($(this).data('frequenza'));
                        $('#edit_intervallo_giorni').val($(this).data('intervallo_giorni'));
                        $('#edit_prossima_manutenzione').val($(this).data('prossima_manutenzione'));
                        $('#edit_descrizione').val($(this).data('descrizione'));
                        $('#edit_operatore_preferito').val($(this).data('operatore_preferito'));
                        $('#edit_priorita').val($(this).data('priorita'));
                        $('#edit_stato').val($(this).data('stato'));
                        $('#edit_notifica_giorni_prima').val($(this).data('notifica_giorni_prima'));

                        // Nascondi/mostra l'intervallo giorni in base alla frequenza
                        if ($(this).data('frequenza') === 'personalizzata') {
                            $('#edit_intervalloGroup').show();
                        } else {
                            $('#edit_intervalloGroup').hide();
                        }

                        $('#editModal').modal('show');
                    });

                    // Edit form: mostra/nascondi intervallo giorni
                    $('#edit_frequenza').change(function () {
                        if ($(this).val() === 'personalizzata') {
                            $('#edit_intervalloGroup').fadeIn();
                        } else {
                            $('#edit_intervalloGroup').fadeOut();
                        }
                    });

                    // Edit form: applica intervallo predefinito dal tipo di manutenzione
                    $('#edit_tipo_id').change(function () {
                        var intervallo = $(this).find(':selected').data('intervallo');
                        if (intervallo) {
                            $('#edit_intervallo_giorni').val(intervallo);
                        }
                    });

                    // Delete button click
                    $('.delete-btn').click(function () {
                        $('#delete_frequenza').text($(this).data('frequenza'));
                        $('#delete_data').text($(this).data('data'));
                        $('#confirm-delete').attr('href', '?<?= $mac_id ? "mac_id=$mac_id&" : "" ?>delete=' + $(this).data('id'));
                        $('#deleteModal').modal('show');
                    });

                    // Initialize DataTable if available
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#programmate-table').DataTable({
                            "paging": false,
                            "ordering": false,
                            "info": false,
                            "searching": false,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json'
                            }
                        });
                    }
                });
            </script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>