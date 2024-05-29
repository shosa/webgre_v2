<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

// Recupera i criteri di ricerca dal form
$search_criteria = isset($_GET['search']) ? $_GET['search'] : '';

// Connessione al database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara la query SQL con le condizioni di ricerca se i criteri sono stati forniti
    $sql = "SELECT * FROM cq_records";
    $params = [];
    if ($search_criteria) {
        $sql .= " WHERE testid LIKE :search_criteria 
                OR cartellino LIKE :search_criteria 
                OR commessa LIKE :search_criteria 
                OR articolo LIKE :search_criteria 
                OR cod_articolo LIKE :search_criteria 
                OR linea LIKE :search_criteria 
                OR reparto LIKE :search_criteria 
                OR data LIKE :search_criteria";
        $params[':search_criteria'] = '%' . $search_criteria . '%';
    }

    // Esegui la query preparata
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Recupera i risultati
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestione degli errori
    echo "Errore: " . $e->getMessage();
}

?>


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

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ricerca inserimenti</h6>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cerca per N° Test, Reparto, Cartellino, Commessa, Articolo, Linea o Data"
                                    value="<?php echo htmlspecialchars($search_criteria); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Cerca <i class="far fa-search"></i>
                            </button>
                        </form>
                        <hr>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if ($search_criteria && $data): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-condensed" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th>N°</th>
                                                    <th>Data</th>
                                                    <th>Orario</th>
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
                                                        <td><?php echo $record['testid']; ?></td>
                                                        <td><?php echo $record['data']; ?></td>
                                                        <td><?php echo $record['orario']; ?></td>
                                                        <td><?php echo $record['cartellino']; ?></td>
                                                        <td><?php echo $record['commessa']; ?></td>
                                                        <td><?php echo $record['reparto']; ?></td>
                                                        <td><?php echo $record['cod_articolo']; ?></td>
                                                        <td><?php echo $record['articolo']; ?></td>
                                                        <td><?php echo $record['calzata']; ?></td>
                                                        <td <?php echo ($record['esito'] == 'V') ? 'style="text-align:center; background-color: #b8ffba; color: green;"' : 'style="text-align:center;background-color: #ffb8c1; color: red;"'; ?>>
                                                            <?php echo ($record['esito'] == 'V') ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-info btn-detail"
                                                                data-testid="<?php echo $record['testid']; ?>"
                                                                data-toggle="modal" data-target="#detailModal">
                                                                <i class="fal fa-search-plus"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif ($search_criteria): ?>
                                    <div class="alert alert-warning">Nessuna registrazione trovata.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modale per i dettagli -->
                    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog"
                        aria-labelledby="detailModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel">Dettagli del Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <span class="badge bg-success text-white" style="margin-left: 10px;"
                                        id="detail-cartellino"></span>
                                    <span class="badge bg-primary text-white" style="margin-left: 10px;"
                                        id="detail-commessa"></span>
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


                </div>
            </div>
            <script src="<?php BASE_PATH ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="<?php BASE_PATH ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="<?php BASE_PATH ?>/js/sb-admin-2.min.js"></script>

            <!-- Page level plugins -->
            <script src="<?php BASE_PATH ?>/vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="<?php BASE_PATH ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="<?php BASE_PATH ?>/js/demo/datatables-demo.js"></script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>

        </div>
    </div>
    </body>