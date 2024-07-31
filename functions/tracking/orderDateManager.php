<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

$pdo = getDbInstance();

// Funzione per ottenere gli ordini senza data
function getOrdersWithoutDate($pdo)
{
    $query = "
        SELECT dt.Ordine, NULL AS date
        FROM track_links tl
        LEFT JOIN dati dt ON tl.cartel = dt.Cartel
        WHERE dt.Ordine NOT IN ( 
            SELECT ordine 
            FROM track_order_info 
            WHERE date IS NOT NULL
        )
        UNION 
        SELECT toi.ordine AS Ordine, toi.date 
        FROM track_order_info toi
        LEFT JOIN dati dt ON toi.ordine = dt.Ordine
        WHERE toi.date IS NULL OR toi.date = '' OR toi.date = '0000-00-00'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funzione per ottenere i dettagli di un ordine specifico
function getOrderDetails($pdo, $ordine)
{
    $query = "SELECT * FROM track_order_info WHERE ordine = :ordine";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ordine', $ordine);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Gestione del salvataggio dei riferimenti
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_references'])) {
    foreach ($_POST['orders'] as $ordineCode => $order) {
        $date = $order['date'];

        // Verifica se esiste già una riga per questo ordine
        $query = "SELECT id FROM track_order_info WHERE ordine = :ordine";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ordine', $ordineCode);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        if ($id) {
            // Aggiorna la riga esistente
            $query = "UPDATE track_order_info SET date = :date WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } else {
            // Inserisci una nuova riga
            $query = "INSERT INTO track_order_info (ordine, date) VALUES (:ordine, :date)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':ordine', $ordineCode);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
        }
    }
}

// Gestione dell'aggiornamento dei dettagli dell'ordine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_details'])) {
    $ordineCode = $_POST['ordine'];
    $date = $_POST['date'];

    // Verifica se esiste già una riga per questo ordine
    $query = "SELECT id FROM track_order_info WHERE ordine = :ordine";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ordine', $ordineCode);
    $stmt->execute();
    $id = $stmt->fetchColumn();

    if ($id) {
        // Aggiorna la riga esistente
        $query = "UPDATE track_order_info SET date = :date WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } else {
        // Inserisci una nuova riga
        $query = "INSERT INTO track_order_info (ordine, date) VALUES (:ordine, :date)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ordine', $ordineCode);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
    }
}

$ordersWithoutDate = getOrdersWithoutDate($pdo);
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
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Ordini</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Gestione Date Ordini</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-5 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Associazione Ordine Cliente</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <?php if (empty($ordersWithoutDate)): ?>
                                            <div class="alert alert-info text-center" role="alert">
                                                Tutti gli ordini per i quali è presente almeno 1
                                                associazione hanno una data associata. Per modificarli, usa il campo di
                                                ricerca.
                                            </div>
                                        <?php else: ?>
                                            <table class="table table-bordered table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20%;">Ordine</th>
                                                        <th style="width: 30%;">Data</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ordersWithoutDate as $order): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($order['Ordine']); ?></td>
                                                            <td>
                                                                <input type="hidden"
                                                                    name="orders[<?php echo htmlspecialchars($order['Ordine']); ?>][code]"
                                                                    value="<?php echo htmlspecialchars($order['Ordine']); ?>">
                                                                <?php
                                                                $dateValue = htmlspecialchars($order['date']);
                                                                $dateClass = empty($dateValue) ? 'bg-yellow' : '';
                                                                ?>
                                                                <input type="date"
                                                                    class="form-control form-control-sm <?php echo $dateClass; ?>"
                                                                    name="orders[<?php echo htmlspecialchars($order['Ordine']); ?>][date]"
                                                                    value="<?php echo $dateValue; ?>">
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
                                        data-target="#dateModal">
                                        Vedi tutto <i class="fas fa-expand-arrows"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="">
                                        <div class="form-group">
                                            <label for="search_ord">Ordine</label>
                                            <input type="text" class="form-control" id="search_ord" name="search_ord"
                                                placeholder="Inserisci ordine">
                                        </div>
                                        <button type="submit" class="btn btn-block btn-warning">Cerca</button>
                                    </form>
                                    <?php
                                    if (isset($_GET['search_ord'])) {
                                        $orderDetails = getOrderDetails($pdo, $_GET['search_ord']);
                                        if ($orderDetails) {
                                            echo '<form method="POST" action="" class="mt-2">';
                                            echo '<div class="form-group">';
                                            echo '<label for="ordine" class="mr-2">Ordine:</label>';
                                            echo '<input type="text" class="form-control form-control-sm" name="ordine" value="' . htmlspecialchars($orderDetails['ordine']) . '" readonly>';
                                            echo '</div>';
                                            echo '<div class="form-group">';
                                            echo '<label for="date" class="mr-2">Data:</label>';
                                            echo '<input type="date" class="form-control form-control-sm" id="date" name="date" value="' . htmlspecialchars($orderDetails['date']) . '">';
                                            echo '</div>';
                                            echo '<button type="submit" name="update_order_details" class="btn btn-block btn-info">Aggiorna</button>';
                                            echo '</form>';
                                        } else {
                                            echo '<p class="text-danger mt-3">Nessun dettaglio trovato per l\'ordine ' . htmlspecialchars($_GET["search_ord"]) . '</p>';
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

<!-- Modale per visualizzare tutte le date -->
<div class="modal fade" id="dateModal" tabindex="-1" role="dialog" aria-labelledby="dateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateModalLabel">Tutti i dettagli</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Ordine</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Funzione per ottenere tutte le date
                        function getAllDates($pdo)
                        {
                            $query = "SELECT * FROM track_order_info";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }

                        $allDates = getAllDates($pdo);
                        foreach ($allDates as $date): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($date['ordine']); ?></td>
                                <td><?php echo htmlspecialchars($date['date']); ?></td>
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