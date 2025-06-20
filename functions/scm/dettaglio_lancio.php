<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Controllo ID lancio
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID lancio non valido';
    header('Location: lista_lanci');
    exit;
}

$lancio_id = (int) $_GET['id'];

// Gestione aggiunta avanzamento rapido


// Gestione aggiunta nota rapida
if ($_POST && isset($_POST['add_nota'])) {
    $titolo = trim($_POST['titolo'] ?? '');
    $contenuto = trim($_POST['contenuto'] ?? '');
    $tipo_nota = trim($_POST['tipo_nota'] ?? 'GENERALE');
    $priorita = trim($_POST['priorita'] ?? 'MEDIA');
    $autore = trim($_POST['autore'] ?? 'Admin');

    if (!empty($contenuto)) {
        try {
            $pdo = getDbInstance();
            $stmt = $pdo->prepare("
                INSERT INTO scm_note (lancio_id, titolo, contenuto, tipo_nota, priorita, autore) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$lancio_id, $titolo, $contenuto, $tipo_nota, $priorita, $autore]);
            $_SESSION['success'] = 'Nota aggiunta con successo';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Errore aggiunta nota: ' . ($debug ? $e->getMessage() : 'Errore generico');
        }
    } else {
        $_SESSION['error'] = 'Il contenuto della nota è obbligatorio';
    }

    header("Location: dettaglio_lancio?id=$lancio_id");
    exit;
}

// Caricamento dati lancio
try {
    $pdo = getDbInstance();

    // Dati lancio principale
    $stmt = $pdo->prepare("
        SELECT l.*, lab.nome_laboratorio, lab.email, lab.username as lab_username
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

    // Articoli del lancio
    $stmt = $pdo->prepare("
        SELECT * FROM scm_articoli_lancio 
        WHERE lancio_id = ? 
        ORDER BY ordine_articolo
    ");
    $stmt->execute([$lancio_id]);
    $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fasi del lancio con dettagli
    $stmt = $pdo->prepare("
        SELECT f.*, a.codice_articolo, a.quantita_totale,
               (SELECT MAX(av.data_aggiornamento) 
                FROM scm_avanzamento av 
                WHERE av.fase_id = f.id AND av.articolo_id = f.articolo_id) as ultimo_aggiornamento
        FROM scm_fasi_lancio f
        JOIN scm_articoli_lancio a ON f.articolo_id = a.id
        WHERE f.lancio_id = ? 
        ORDER BY a.ordine_articolo, f.ordine_fase
    ");
    $stmt->execute([$lancio_id]);
    $fasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Storico avanzamenti
    $stmt = $pdo->prepare("
        SELECT av.*, f.nome_fase, a.codice_articolo
        FROM scm_avanzamento av
        LEFT JOIN scm_fasi_lancio f ON av.fase_id = f.id
        LEFT JOIN scm_articoli_lancio a ON av.articolo_id = a.id
        WHERE av.lancio_id = ? 
        ORDER BY av.data_aggiornamento DESC, av.id DESC
    ");
    $stmt->execute([$lancio_id]);
    $avanzamenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Note
    $stmt = $pdo->prepare("
        SELECT * FROM scm_note 
        WHERE lancio_id = ? 
        ORDER BY data_creazione DESC
    ");
    $stmt->execute([$lancio_id]);
    $note = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
}

// Funzioni helper
function calcolaTotalePaia($articoli)
{
    return array_sum(array_column($articoli, 'quantita_totale'));
}

function calcolaPaiaCompletate($articoli)
{
    return array_sum(array_column($articoli, 'quantita_completata'));
}

$totale_paia = calcolaTotalePaia($articoli);
$paia_completate = calcolaPaiaCompletate($articoli);
$percentuale_generale = $totale_paia > 0 ? round(($paia_completate / $totale_paia) * 100, 1) : 0;
$ultimo_avanzamento = !empty($avanzamenti) ? $avanzamenti[0] : null;
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
                            <div class="mr-3 bg-gradient-info text-white p-3 rounded shadow-sm">
                                <i class="fas fa-eye fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Dettaglio Lancio</h1>
                                <p class="mb-0 text-gray-600"><?= htmlspecialchars($lancio['numero_lancio']) ?> -
                                    <?= htmlspecialchars($lancio['nome_laboratorio'] ?? 'Non assegnato') ?></p>
                            </div>
                        </div>
                        <div>
                            <a href="crea_lancio?id=<?= $lancio['id'] ?>" class="btn btn-outline-success">
                                <i class="fas fa-edit mr-2"></i>Modifica
                            </a>
                            <a href="lista_lanci" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-arrow-left mr-2"></i>Torna alla Lista
                            </a>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Colonna sinistra: Dati principali -->
                        <div class="col-lg-8">
                            <!-- Informazioni Lancio -->
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Informazioni Lancio
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Numero Lancio:</th>
                                                    <td><strong><?= htmlspecialchars($lancio['numero_lancio']) ?></strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Ciclo Fasi:</th>
                                                    <td>
                                                        <?php
                                                        $fasi_ciclo = explode(';', $lancio['ciclo_fasi']);
                                                        echo '<span class="badge badge-light">' . implode('</span> → <span class="badge badge-light">', array_map('trim', $fasi_ciclo)) . '</span>';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Data Lancio:</th>
                                                    <td><?= date('d/m/Y', strtotime($lancio['data_lancio'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Stato:</th>
                                                    <td>
                                                        <?php
                                                        switch ($lancio['stato_generale']) {
                                                            case 'IN_PREPARAZIONE':
                                                                $badge_class = 'badge-secondary';
                                                                break;
                                                            case 'LANCIATO':
                                                                $badge_class = 'badge-info';
                                                                break;
                                                            case 'IN_LAVORAZIONE':
                                                                $badge_class = 'badge-success';
                                                                break;
                                                            case 'COMPLETO':
                                                                $badge_class = 'badge-primary';
                                                                break;
                                                            case 'SOSPESO':
                                                                $badge_class = 'badge-warning';
                                                                break;
                                                            default:
                                                                $badge_class = 'badge-secondary';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $badge_class ?>">
                                                            <?= str_replace('_', ' ', $lancio['stato_generale']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Laboratorio:</th>
                                                    <td>
                                                        <?= htmlspecialchars($lancio['nome_laboratorio'] ?? 'Non assegnato') ?>
                                                        <?php if ($lancio['email']): ?>
                                                            <br><small
                                                                class="text-muted"><?= htmlspecialchars($lancio['email']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Totale Paia:</th>
                                                    <td><strong><?= number_format($totale_paia) ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Paia Completate:</th>
                                                    <td>
                                                        <strong
                                                            class="text-success"><?= number_format($paia_completate) ?></strong>
                                                        <small class="text-muted">/
                                                            <?= number_format($totale_paia) ?></small>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Avanzamento:</th>
                                                    <td>
                                                        <div class="progress mb-1" style="height: 20px;">
                                                            <div class="progress-bar bg-success"
                                                                style="width: <?= $percentuale_generale ?>%">
                                                                <?= $percentuale_generale ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <?php if ($lancio['note']): ?>
                                        <hr>
                                        <h6 class="text-muted">Note Generali:</h6>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($lancio['note'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Articoli -->
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-list mr-2"></i>
                                        Articoli (<?= count($articoli) ?>)
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Codice Articolo</th>
                                                    <th>Quantità Totale</th>
                                                    <th>Quantità Completata</th>
                                                    <th>% Completamento</th>
                                                    <th>Progresso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($articoli as $art): ?>
                                                    <?php
                                                    $perc_articolo = $art['quantita_totale'] > 0 ?
                                                        round(($art['quantita_completata'] / $art['quantita_totale']) * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($art['codice_articolo']) ?></strong>
                                                        </td>
                                                        <td><?= number_format($art['quantita_totale']) ?></td>
                                                        <td class="text-success">
                                                            <strong><?= number_format($art['quantita_completata']) ?></strong>
                                                        </td>
                                                        <td><?= $perc_articolo ?>%</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-success"
                                                                    style="width: <?= $perc_articolo ?>%">
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



                            <!-- Storico Avanzamenti -->
                            <div class="card shadow mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-info">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        Storico Avanzamenti (<?= count($avanzamenti) ?>)
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($avanzamenti)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Nessun avanzamento registrato</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Data</th>
                                                        <th>Fase</th>
                                                        <th>Articolo</th>
                                                        <th>Avanzamento</th>
                                                        <th>Paia</th>
                                                        <th>Operatore</th>
                                                        <th>Note</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($avanzamenti as $avanz): ?>
                                                        <tr>
                                                            <td><?= date('d/m/Y', strtotime($avanz['data_aggiornamento'])) ?>
                                                            </td>
                                                            <td class="small">
                                                                <?= htmlspecialchars($avanz['nome_fase'] ?? '-') ?></td>
                                                            <td class="small">
                                                                <?= htmlspecialchars($avanz['codice_articolo'] ?? '-') ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 15px;">
                                                                    <div class="progress-bar bg-success"
                                                                        style="width: <?= $avanz['percentuale_completamento'] ?>%">
                                                                    </div>
                                                                </div>
                                                                <small><?= $avanz['percentuale_completamento'] ?>%</small>
                                                            </td>
                                                            <td class="small">
                                                                <strong><?= number_format($avanz['paia_completate']) ?></strong>
                                                                <?php if ($avanz['paia_totali'] > 0): ?>
                                                                    / <?= number_format($avanz['paia_totali']) ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="small"><?= htmlspecialchars($avanz['operatore']) ?></td>
                                                            <td class="small">
                                                                <?= htmlspecialchars($avanz['note_avanzamento']) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Colonna destra: Azioni rapide e Note -->
                        <div class="col-lg-4">
                            <!-- Azioni Rapide -->
                            <div class="card shadow mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Azioni Rapide
                                    </h6>
                                </div>
                                <div class="card-body">


                                    <!-- Form Aggiunta Nota -->
                                    <form method="POST">
                                        <h6 class="text-info">Aggiungi Nota</h6>
                                        <div class="form-group">
                                            <label for="titolo" class="small">Titolo (opzionale)</label>
                                            <input type="text" class="form-control form-control-sm" id="titolo"
                                                name="titolo">
                                        </div>
                                        <div class="form-group">
                                            <label for="contenuto" class="small">Contenuto *</label>
                                            <textarea class="form-control form-control-sm" id="contenuto"
                                                name="contenuto" rows="3" required></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="tipo_nota" class="small">Tipo</label>
                                                    <select class="form-control form-control-sm" id="tipo_nota"
                                                        name="tipo_nota">
                                                        <option value="GENERALE">Generale</option>
                                                        <option value="PROBLEMA">Problema</option>
                                                        <option value="URGENTE">Urgente</option>
                                                        <option value="QUALITA">Qualità</option>
                                                        <option value="LOGISTICA">Logistica</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="priorita" class="small">Priorità</label>
                                                    <select class="form-control form-control-sm" id="priorita"
                                                        name="priorita">
                                                        <option value="BASSA">Bassa</option>
                                                        <option value="MEDIA" selected>Media</option>
                                                        <option value="ALTA">Alta</option>
                                                        <option value="CRITICA">Critica</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="autore" class="small">Autore</label>
                                            <input type="text" class="form-control form-control-sm" id="autore"
                                                name="autore" value="Admin">
                                        </div>
                                        <button type="submit" name="add_nota" class="btn btn-info btn-sm btn-block">
                                            <i class="fas fa-sticky-note mr-1"></i>Aggiungi Nota
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-info">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        Note (<?= count($note) ?>)
                                    </h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (empty($note)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-sticky-note fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Nessuna nota presente</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($note as $nota): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <div class="d-flex">
                                                            <?php
                                                            switch ($nota['tipo_nota']) {
                                                                case 'PROBLEMA':
                                                                    $tipo_badge = 'badge-danger';
                                                                    break;
                                                                case 'URGENTE':
                                                                    $tipo_badge = 'badge-warning';
                                                                    break;
                                                                case 'QUALITA':
                                                                    $tipo_badge = 'badge-info';
                                                                    break;
                                                                case 'LOGISTICA':
                                                                    $tipo_badge = 'badge-secondary';
                                                                    break;
                                                                default:
                                                                    $tipo_badge = 'badge-light';
                                                                    break;
                                                            }

                                                            switch ($nota['priorita']) {
                                                                case 'CRITICA':
                                                                    $priorita_badge = 'badge-danger';
                                                                    break;
                                                                case 'ALTA':
                                                                    $priorita_badge = 'badge-warning';
                                                                    break;
                                                                case 'MEDIA':
                                                                    $priorita_badge = 'badge-info';
                                                                    break;
                                                                default:
                                                                    $priorita_badge = 'badge-secondary';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span
                                                                class="badge <?= $tipo_badge ?> mr-1"><?= $nota['tipo_nota'] ?></span>
                                                            <span
                                                                class="badge <?= $priorita_badge ?>"><?= $nota['priorita'] ?></span>
                                                        </div>
                                                        <small><?= date('d/m H:i', strtotime($nota['data_creazione'])) ?></small>
                                                    </div>
                                                    <?php if ($nota['titolo']): ?>
                                                        <h6 class="mb-1 mt-2"><?= htmlspecialchars($nota['titolo']) ?></h6>
                                                    <?php endif; ?>
                                                    <p class="mb-1 small"><?= nl2br(htmlspecialchars($nota['contenuto'])) ?></p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($nota['autore']) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gestione selezione fase per avanzamento
            const faseSelect = document.getElementById('fase_id');
            const articoloInput = document.getElementById('articolo_id');
            const percentualeInput = document.getElementById('percentuale');
            const paiaInput = document.getElementById('paia_completate');

            if (faseSelect && articoloInput) {
                faseSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const articoloId = selectedOption.getAttribute('data-articolo');
                    articoloInput.value = articoloId || '';
                });
            }

            // Calcolo automatico percentuale in base alle paia completate
            if (paiaInput && percentualeInput) {
                paiaInput.addEventListener('input', function () {
                    const paiaCompletate = parseInt(this.value) || 0;
                    const totalePaia = <?= $totale_paia ?>;

                    if (totalePaia > 0) {
                        const percentuale = Math.round((paiaCompletate / totalePaia) * 100);
                        percentualeInput.value = Math.min(percentuale, 100);
                    }
                });

                // Calcolo automatico paia completate in base alla percentuale
                percentualeInput.addEventListener('input', function () {
                    const percentuale = parseFloat(this.value) || 0;
                    const totalePaia = <?= $totale_paia ?>;

                    if (totalePaia > 0) {
                        const paiaCompletate = Math.round((percentuale / 100) * totalePaia);
                        paiaInput.value = paiaCompletate;
                    }
                });
            }

            // Evidenziazione note in base al tipo e priorità
            const noteItems = document.querySelectorAll('.list-group-item');
            noteItems.forEach(item => {
                const badges = item.querySelectorAll('.badge');
                badges.forEach(badge => {
                    if (badge.textContent.includes('URGENTE') || badge.textContent.includes('CRITICA')) {
                        item.style.borderLeft = '4px solid #dc3545';
                    } else if (badge.textContent.includes('PROBLEMA') || badge.textContent.includes('ALTA')) {
                        item.style.borderLeft = '4px solid #ffc107';
                    }
                });
            });

            // Auto-refresh ogni 3 minuti per vedere nuovi aggiornamenti
            let autoRefresh = setInterval(function () {
                // Verifica solo se la pagina è visibile
                if (!document.hidden) {
                    location.reload();
                }
            }, 180000); // 3 minuti

            // Ferma auto-refresh quando l'utente interagisce con i form
            document.querySelectorAll('form input, form textarea, form select').forEach(input => {
                input.addEventListener('focus', function () {
                    clearInterval(autoRefresh);
                });
            });
        });
    </script>
</body>