<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

$pdo = getDbInstance();

// Funzione per ottenere i lotti senza riferimenti con il tipo di lotto
function getArtsWithoutSku($pdo)
{
    $query = "
        SELECT dt.Articolo, NULL as sku 
        FROM track_links tl 
        LEFT JOIN dati dt ON tl.cartel = dt.Cartel
        WHERE dt.Articolo NOT IN ( 
            SELECT art 
            FROM track_sku 
            WHERE sku IS NOT NULL
        )
        UNION 
        SELECT tsk.art as Articolo, tsk.sku 
        FROM track_sku tsk 
        LEFT JOIN dati dt ON tsk.art = dt.Articolo 
        WHERE tsk.sku IS NULL OR tsk.sku = ''
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funzione per ottenere i dettagli di un articolo specifico
function getArtDetails($pdo, $art)
{
    $query = "SELECT * FROM track_sku WHERE art = :art";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':art', $art);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Gestione del salvataggio dei riferimenti
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_references'])) {
    foreach ($_POST['arts'] as $artCode => $art) {
        $sku = $art['sku'];

        // Verifica se esiste già una riga per questo articolo
        $query = "SELECT id FROM track_sku WHERE art = :art";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':art', $artCode);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        if ($id) {
            // Aggiorna la riga esistente
            $query = "UPDATE track_sku SET sku = :sku WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } else {
            // Inserisci una nuova riga
            $query = "INSERT INTO track_sku (art, sku) VALUES (:art, :sku)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':art', $artCode);
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
        }
    }
}

// Gestione dell'aggiornamento dei dettagli dell'articolo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_art_details'])) {
    $artCode = $_POST['lot'];
    $sku = $_POST['sku'];

    // Verifica se esiste già una riga per questo articolo
    $query = "SELECT id FROM track_sku WHERE art = :art";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':art', $artCode);
    $stmt->execute();
    $id = $stmt->fetchColumn();

    if ($id) {
        // Aggiorna la riga esistente
        $query = "UPDATE track_sku SET sku = :sku WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } else {
        // Inserisci una nuova riga
        $query = "INSERT INTO track_sku (art, sku) VALUES (:art, :sku)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':art', $artCode);
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
    }
}

$artsWithoutReferences = getArtsWithoutSku($pdo);
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
                        <li class="breadcrumb-item active">Gestione Sku</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-5 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Associazione Articolo/SKU
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">

                                        <?php if (empty($artsWithoutReferences)): ?>


                                            <div class="alert alert-info text-center" role="alert">
                                                Tutti gli articoli per i quali è presente almeno 1
                                                associazione hanno un SKU associato, per modificarlo usa il campo ricerca.
                                            </div>

                                        <?php else: ?>
                                            <table class="table table-bordered table-sm table-striped align-items-center">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20%;">Articolo</th>
                                                        <th style="width: 30%;">SKU</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                                <?php foreach ($artsWithoutReferences as $art): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo htmlspecialchars($art['Articolo']); ?>
                                                        </td>
                                                        <td>
                                                            <input type="hidden"
                                                                name="arts[<?php echo htmlspecialchars($art['Articolo']); ?>][code]"
                                                                value="<?php echo htmlspecialchars($art['Articolo']); ?>">

                                                            <?php
                                                            $skuValue = htmlspecialchars($art['sku']);
                                                            $skuClass = empty($skuValue) ? 'bg-yellow' : '';
                                                            ?>
                                                            <input type="text"
                                                                class="form-control form-control-sm <?php echo $skuClass; ?>"
                                                                name="arts[<?php echo htmlspecialchars($art['Articolo']); ?>][sku]"
                                                                placeholder="SKU"
                                                                value="<?php echo htmlspecialchars($art['sku']); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <button type="submit" name="save_references"
                                                class="btn btn-success btn-block">Salva</button>
                                        <?php endif; ?>


                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Ricerca</h6>
                                    <a class="btn text-info btn-sm ml-auto font-weight-bold" data-toggle="modal"
                                        data-target="#skuModal">
                                        Vedi tutto <i class="fas fa-expand-arrows"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="">
                                        <div class="form-group">
                                            <label for="search_art">Codice Articolo</label>
                                            <input type="text" class="form-control" id="search_art" name="search_art"
                                                placeholder="Inserisci codice articolo">
                                        </div>
                                        <button type="submit" class="btn btn-block btn-warning">Cerca</button>
                                    </form>
                                    <?php
                                    if (isset($_GET['search_art'])) {
                                        $artDetails = getArtDetails($pdo, $_GET['search_art']);
                                        if ($artDetails) {
                                            echo '<form method="POST" action="" class="mt-2">';
                                            echo '<div class="form-group">';
                                            echo '<label for="art" class="mr-2">ARTICOLO:</label>';
                                            echo '<div class="input-group">';
                                            echo '<input type="text" class="form-control form-control-sm" name="lot" value="' . htmlspecialchars($artDetails['art']) . '" readonly>';
                                            echo '</div>';
                                            echo '<label for="sku" class="mr-2 mt-2">SKU:</label>';
                                            echo '<div class="input-group">';
                                            echo '<input type="text" class="form-control form-control-sm" id="sku" name="sku" value="' . htmlspecialchars($artDetails['sku']) . '">';
                                            echo '</div>';
                                            echo '</div>';

                                            echo '<button type="submit" name="update_art_details" class="btn btn-block btn-info">Aggiorna</button>';
                                            echo '</form>';
                                        } else {
                                            echo '<i><p class="text-danger mt-3">Nessun dettaglio trovato per l\'articolo ' . htmlspecialchars($_GET["search_art"]) . '</p></i>';
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
        /* Utilizza il colore giallo di tua scelta */
    }
</style>

<!-- Modale per visualizzare tutte le SKU -->
<div class="modal fade" id="skuModal" tabindex="-1" role="dialog" aria-labelledby="skuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="skuModalLabel">Tutti gli SKU</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Articolo</th>
                            <th>SKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Funzione per ottenere tutte le SKU
                        function getAllSku($pdo)
                        {
                            $query = "SELECT * FROM track_sku";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }

                        $allSkus = getAllSku($pdo);
                        foreach ($allSkus as $sku): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sku['art']); ?></td>
                                <td><?php echo htmlspecialchars($sku['sku']); ?></td>
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