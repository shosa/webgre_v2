<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

$pdo = getDbInstance();

// Funzione per ottenere i lotti senza riferimenti con il tipo di lotto
function getLotsWithoutReferences($pdo)
{
    $query = "
       SELECT tl.lot, tt.name AS type_name, NULL AS doc, NULL AS date FROM track_links tl 
       LEFT JOIN track_types tt ON tl.type_id = tt.id 
       WHERE tl.lot NOT IN ( SELECT lot FROM track_lots_info 
       WHERE doc IS NOT NULL AND date IS NOT NULL ) 
       UNION 
       SELECT tli.lot, tt.name AS type_name, tli.doc, tli.date FROM track_lots_info tli 
       LEFT JOIN track_links tl ON tli.lot = tl.lot 
       LEFT JOIN track_types tt ON tl.type_id = tt.id 
       WHERE tli.doc IS NULL OR tli.date IS NULL 
       OR tli.doc = '' OR tli.date = '0000-00-00';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $lots;
}

// Funzione per ottenere i dettagli di un lotto specifico
function getLotDetails($pdo, $lot)
{
    $query = "SELECT * FROM track_lots_info WHERE lot = :lot";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':lot', $lot);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Gestione del salvataggio dei riferimenti
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_references'])) {
    foreach ($_POST['lots'] as $lot) {
        $lot_number = $lot['number'];
        $doc = $lot['doc'];
        $date = $lot['date'];

        // Verifica se esiste già una riga per questo lotto
        $query = "SELECT id FROM track_lots_info WHERE lot = :lot";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':lot', $lot_number);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        if ($id) {
            // Aggiorna la riga esistente
            $query = "UPDATE track_lots_info SET doc = :doc, date = :date WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':doc', $doc);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } else {
            // Inserisci una nuova riga
            $query = "INSERT INTO track_lots_info (lot, doc, date) VALUES (:lot, :doc, :date)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':lot', $lot_number);
            $stmt->bindParam(':doc', $doc);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
        }
    }
}

// Gestione dell'aggiornamento dei dettagli del lotto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lot_details'])) {
    $lot = $_POST['lot'];
    $doc = $_POST['doc'];
    $date = $_POST['date'];
    $note = $_POST['note'];

    // Verifica se esiste già una riga per questo lotto
    $query = "SELECT id FROM track_lots_info WHERE lot = :lot";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':lot', $lot);
    $stmt->execute();
    $id = $stmt->fetchColumn();

    if ($id) {
        // Aggiorna la riga esistente
        $query = "UPDATE track_lots_info SET doc = :doc, date = :date, note = :note WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':doc', $doc);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':note', $note);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } else {
        // Inserisci una nuova riga
        $query = "INSERT INTO track_lots_info (lot, doc, date, note) VALUES (:lot, :doc, :date, :note)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':lot', $lot);
        $stmt->bindParam(':doc', $doc);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':note', $note);
        $stmt->execute();
    }
}

$lotsWithoutReferences = getLotsWithoutReferences($pdo);
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php include BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Gestione Lotti</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-5 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Lotti utilizzati senza riferimenti
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <?php if (empty($lotsWithoutReferences)): ?>
                                            <div class="alert alert-info text-center" role="alert">
                                                Tutti i Lotti per i quali è presente almeno 1
                                                associazione hanno dei riferimenti, per modificarli usa il campo ricerca.
                                            </div>

                                        <?php else: ?>
                                            <table class="table table-bordered table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20%;">Tipo</th>
                                                        <th style="width: 30%;">Lotto</th>
                                                        <th style="width: 20%;">DDT</th>
                                                        <th style="width: 25%;">Data</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($lotsWithoutReferences as $lot): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($lot['type_name']); ?></td>
                                                            <td>
                                                                <i><?php echo htmlspecialchars($lot['lot']); ?>
                                                                    <input type="hidden"
                                                                        name="lots[<?php echo $lot['lot']; ?>][number]"
                                                                        value="<?php echo htmlspecialchars($lot['lot']); ?>"></i>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $docValue = htmlspecialchars($lot['doc']);
                                                                $docClass = empty($docValue) ? 'bg-yellow' : '';
                                                                ?>
                                                                <input type="text"
                                                                    class="form-control form-control-sm <?php echo $docClass; ?>"
                                                                    name="lots[<?php echo $lot['lot']; ?>][doc]"
                                                                    placeholder="Doc" value="<?php echo $docValue; ?>">
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $dateValue = htmlspecialchars($lot['date']);
                                                                $dateClass = empty($dateValue) || $dateValue == '0000-00-00' ? 'bg-yellow' : '';
                                                                ?>
                                                                <input type="date"
                                                                    class="form-control form-control-sm <?php echo $dateClass; ?>"
                                                                    name="lots[<?php echo $lot['lot']; ?>][date]"
                                                                    placeholder="Date" value="<?php echo $dateValue; ?>">
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <button type="submit" name="save_references"
                                                class="btn btn-pink btn-block">Salva</button>
                                        <?php endif; ?>

                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Lotti utilizzati senza riferimenti
                                    </h6>
                                    <a class="btn text-info btn-sm ml-auto font-weight-bold" data-toggle="modal"
                                        data-target="#lotsModal">
                                        Vedi tutto <i class="fas fa-expand-arrows"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="">
                                        <div class="form-group">
                                            <label for="search_lot">Numero Lotto</label>
                                            <input type="text" class="form-control" id="search_lot" name="search_lot"
                                                placeholder="Inserisci numero lotto">
                                        </div>
                                        <button type="submit" class="btn btn-block btn-warning">Cerca</button>
                                    </form>
                                    <?php
                                    if (isset($_GET['search_lot'])) {
                                        $lotDetails = getLotDetails($pdo, $_GET['search_lot']);
                                        if ($lotDetails) {
                                            echo '<form method="POST" action="" class="mt-2">';
                                            echo '<div class="form-group">';
                                            echo '<label for="lot" class="mr-2">Lotto:</label>';
                                            echo '<input type="text" class="form-control form-control-sm" name="lot" value="' . htmlspecialchars($lotDetails['lot']) . '" readonly>';
                                            echo '</div>';
                                            echo '<div class="form-group">';
                                            echo '<label for="doc" class="mr-2">DDT:</label>';
                                            echo '<input type="text" class="form-control form-control-sm" id="doc" name="doc" value="' . htmlspecialchars($lotDetails['doc']) . '">';
                                            echo '</div>';
                                            echo '<div class="form-group">';
                                            echo '<label for="date" class="mr-2">Data:</label>';
                                            echo '<input type="date" class="form-control form-control-sm" id="date" name="date" value="' . htmlspecialchars($lotDetails['date']) . '">';
                                            echo '</div>';
                                            echo '<div class="form-group">';
                                            echo '<label for="note" class="mr-2">Note:</label>';
                                            echo '<textarea class="form-control" id="note" name="note">' . htmlspecialchars($lotDetails['note']) . '</textarea>';
                                            echo '</div>';
                                            echo '<button type="submit" name="update_lot_details" class="btn btn-block btn-success">Aggiorna</button>';
                                            echo '</form>';
                                        } else {
                                            echo '<i><p class="text-danger mt-3">Nessun dettaglio trovato per il lotto ' . htmlspecialchars($_GET["search_lot"]) . '</p></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>

<style>
    .bg-yellow {
        background-color: #ffeeba;
    }
</style>

<!-- Modale per visualizzare tutti i lotti -->
<div class="modal fade" id="lotsModal" tabindex="-1" role="dialog" aria-labelledby="lotsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lotsModalLabel">Tutti i Lotti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Lotto</th>
                            <th>DDT</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Funzione per ottenere tutti i lotti
                        function getAllLots($pdo)
                        {
                            $query = "
                                SELECT DISTINCT(tl.lot), tt.name AS type_name, tli.doc, tli.date
                                FROM track_lots_info tli
                                LEFT JOIN track_links tl ON tli.lot = tl.lot
                                LEFT JOIN track_types tt ON tl.type_id = tt.id
                            ";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }

                        $allLots = getAllLots($pdo);
                        foreach ($allLots as $lot): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lot['type_name']); ?></td>
                                <td><?php echo htmlspecialchars($lot['lot']); ?></td>
                                <td><?php echo htmlspecialchars($lot['doc']); ?></td>
                                <td><?php echo htmlspecialchars($lot['date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>