<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

// Gestione eliminazione
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $laboratorio_id = (int) $_GET['delete'];

    try {
        $pdo = getDbInstance();

        // Controllo se ci sono lanci associati
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM scm_lanci WHERE laboratorio_id = ?");
        $stmt->execute([$laboratorio_id]);
        $lanci_count = $stmt->fetchColumn();

        if ($lanci_count > 0) {
            $_SESSION['error'] = "Impossibile eliminare: il laboratorio ha $lanci_count lanci associati";
        } else {
            $stmt = $pdo->prepare("DELETE FROM scm_laboratori WHERE id = ?");
            $stmt->execute([$laboratorio_id]);
            $_SESSION['success'] = 'Laboratorio eliminato con successo';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Errore durante l\'eliminazione: ' . ($debug ? $e->getMessage() : 'Errore generico');
    }

    header('Location: lista_laboratori');
    exit;
}

// Caricamento laboratori
try {
    $pdo = getDbInstance();

    $stmt = $pdo->query("
        SELECT l.*, 
               COUNT(lanci.id) as numero_lanci,
               COUNT(CASE WHEN lanci.stato_generale = 'IN_LAVORAZIONE' THEN 1 END) as lanci_attivi,
               MAX(l.ultimo_accesso) as ultimo_accesso
        FROM scm_laboratori l
        LEFT JOIN scm_lanci lanci ON l.id = lanci.laboratorio_id
        GROUP BY l.id
        ORDER BY l.attivo DESC, l.nome_laboratorio
    ");
    $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
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
                            <div class="mr-3 bg-gradient-primary text-white p-3 rounded shadow-sm">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">Lista Laboratori</h1>
                                <p class="mb-0 text-gray-600">Gestisci tutti i laboratori terzisti</p>
                            </div>
                        </div>
                        <div>
                            <a href="crea_laboratorio.php" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Nuovo Laboratorio
                            </a>

                        </div>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index">SCM</a></li>
                        <li class="breadcrumb-item active">Elenco Laboratori</li>
                    </ol>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Tabella Laboratori -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list mr-2"></i>
                                Elenco Laboratori (<?= count($laboratori) ?>)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($laboratori)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nessun laboratorio presente</h5>
                                    <p class="text-muted">Crea il primo laboratorio per iniziare</p>
                                    <a href="crea_laboratorio" class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i>Crea Laboratorio
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Laboratorio</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Stato</th>
                                                <th>Lanci</th>
                                                <th>Ultimo Accesso</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($laboratori as $lab): ?>
                                                <tr class="<?= $lab['attivo'] ? '' : 'table-secondary' ?>">
                                                    <td>
                                                        <strong><?= htmlspecialchars($lab['nome_laboratorio']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?= $lab['id'] ?></small>
                                                    </td>
                                                    <td>
                                                        <code><?= htmlspecialchars($lab['username']) ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if ($lab['email']): ?>
                                                            <a href="mailto:<?= htmlspecialchars($lab['email']) ?>">
                                                                <?= htmlspecialchars($lab['email']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($lab['attivo']): ?>
                                                            <span class="badge badge-success">Attivo</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Disattivo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary"><?= $lab['numero_lanci'] ?></span>
                                                        <?php if ($lab['lanci_attivi'] > 0): ?>
                                                            <span class="badge badge-success ml-1"><?= $lab['lanci_attivi'] ?>
                                                                attivi</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($lab['ultimo_accesso']): ?>
                                                            <small><?= date('d/m/Y H:i', strtotime($lab['ultimo_accesso'])) ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Mai</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="crea_laboratorio?id=<?= $lab['id'] ?>"
                                                                class="btn btn-outline-primary btn-sm" title="Modifica">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="vista_laboratori?laboratorio=<?= $lab['id'] ?>"
                                                                class="btn btn-outline-info btn-sm" title="Visualizza lanci">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($lab['numero_lanci'] == 0): ?>
                                                                <a href="lista_laboratori?delete=<?= $lab['id'] ?>"
                                                                    class="btn btn-outline-danger btn-sm" title="Elimina"
                                                                    onclick="return confirm('Sei sicuro di voler eliminare questo laboratorio?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-outline-secondary btn-sm"
                                                                    title="Impossibile eliminare: ha lanci associati" disabled>
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
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
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>