<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
// Inizializza la connessione al database usando PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Recupera la data selezionata, se presente
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    // Recupera i dati relativi alla data selezionata dal database
    $stmt = $pdo->prepare("SELECT * FROM cq_records WHERE data = :date");
    $stmt->execute(['date' => $date]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/quality/read">Consultazione Date<a></a>
                        </li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Test effettuati in data
                                <span class="badge bg-primary text-white" style="margin-left: 10px;">
                                    <?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </h6>
                            <a href="generate_pdf.php?date=<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>"
                                class="btn btn-warning ml-auto"><i class="fal fa-print"></i>
                                REPORT</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered ">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Ora</th>
                                            <th>Cartellino</th>
                                            <th>Commessa</th>
                                            <th>Reparto</th>
                                            <th>Articolo</th>
                                            <th>Modello</th>
                                            <th>Calzata</th>
                                            <th>Esito</th>
                                            <th>Dettagli</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['testid'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['orario'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['cartellino'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['commessa'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['reparto'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['cod_articolo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['articolo'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['calzata'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td <?php echo ($record['esito'] == 'V') ? 'style="text-align:center; background-color: #b8ffba; color: green;"' : 'style="text-align:center;background-color: #ffb8c1; color: red;"'; ?>>
                                                    <?php echo ($record['esito'] == 'V') ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-detail"
                                                        data-testid="<?php echo htmlspecialchars($record['testid'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-toggle="modal" data-target="#detailModal">
                                                        <i class="fal fa-search-plus"></i>
                                                    </button>
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
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    <!-- Modale per i dettagli -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Dettagli del Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span class="badge bg-success text-white" style="margin-left: 10px;" id="detail-cartellino"></span>
                    <span class="badge bg-primary text-white" style="margin-left: 10px;" id="detail-commessa"></span>
                    <p><strong>Operatore:</strong> <span id="detail-operatore"></span></p>
                    <p><strong>Articolo:</strong> <span id="detail-articolo"></span></p>
                    <p><strong>Test:</strong> <span id="detail-test"></span></p>
                    <p><strong>Note:</strong> <span id="detail-note"></span></p>
                    <p><strong>Calzata:</strong> <span id="detail-calzata"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.btn-detail');
            buttons.forEach(button => {
                button.addEventListener('click', function () {
                    const testid = this.getAttribute('data-testid');
                    fetch('get_record_details.php?testid=' + testid)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('detail-operatore').textContent = data.operatore;
                            document.getElementById('detail-articolo').textContent = data.articolo;
                            document.getElementById('detail-cartellino').textContent = data.cartellino;
                            document.getElementById('detail-commessa').textContent = data.commessa;
                            document.getElementById('detail-test').textContent = data.test;
                            document.getElementById('detail-note').textContent = data.note;
                            document.getElementById('detail-calzata').textContent = data.calzata;
                        })
                        .catch(error => console.error('Errore:', error));
                });
            });
        });
    </script>
    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
</body>