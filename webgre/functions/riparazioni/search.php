<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
// Recupera i criteri di ricerca dal form
$search_criteria = isset($_GET['search']) ? $_GET['search'] : '';
// Connessione al database
try {
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Prepara la query SQL con le condizioni di ricerca se i criteri sono stati forniti
    $sql = "SELECT * FROM riparazioni";
    $params = [];
    if ($search_criteria) {
        $sql .= " WHERE IDRIP LIKE :search_criteria 
                OR CARTELLINO LIKE :search_criteria 
                OR COMMESSA LIKE :search_criteria 
                OR ARTICOLO LIKE :search_criteria 
                OR CODICE LIKE :search_criteria 
                OR LINEA LIKE :search_criteria 
                OR REPARTO LIKE :search_criteria 
                OR DATA LIKE :search_criteria
                OR CLIENTE LIKE :search_criteria";
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
                    <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                </div>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cerca Cedole</li>
                </ol>
                <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ricerca Cedole</h6>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cerca per ID, Reparto, Cartellino, Linea, Data, Cliente .."
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
                                        <table class="table table-striped table-bordered table-condensed">
                                            <thead>
                                                <tr>
                                                    <th width="5%">ID</th>
                                                    <th width="15%">Codice</th>
                                                    <th width="35%">Articolo</th>
                                                    <th width="5%">Quantità</th>
                                                    <th width="10%">Cartellino</th>
                                                    <th width="5%">Data</th>
                                                    <th width="10%">Reparto</th>
                                                    <th width="5%">Linea</th>
                                                    <th width="10%">Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $record): ?>
                                                    <!-- MODALE CANCELLA  -->
                                                    <div class="modal fade" style="z-index: 90000"
                                                        id="confirm-delete-<?php echo $record['IDRIP']; ?>" role="dialog"
                                                        aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered ">
                                                            <form action="search_delete_riparazioni.php" method="POST">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="confirm-delete-modal-label">
                                                                            Conferma
                                                                        </h5>
                                                                        <button type="button" class="close" data-dismiss="modal"
                                                                            aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body"
                                                                        style="color: #f96363; background: #ffe9e9;">
                                                                        <input type="hidden" name="del_id" id="del_id"
                                                                            value="<?php echo $record['IDRIP']; ?>">
                                                                        <p>Sicuro di voler procedere ad eliminare questa riga?
                                                                        </p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" class="btn btn-danger">Si</button>
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-dismiss="modal">No</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <tr>
                                                        <td><?php echo $record['IDRIP']; ?></td>
                                                        <td><?php echo $record['CODICE']; ?></td>
                                                        <td><?php echo $record['ARTICOLO']; ?></td>
                                                        <td><?php echo $record['QTA']; ?></td>
                                                        <td><?php echo $record['CARTELLINO']; ?></td>
                                                        <td><?php echo $record['DATA']; ?></td>
                                                        <td><?php echo $record['REPARTO']; ?></td>
                                                        <td><?php echo $record['LINEA']; ?></td>
                                                        <td>
                                                            <button class="btn btn-info btn-detail"
                                                                data-idrip="<?php echo $record['IDRIP']; ?>" data-toggle="modal"
                                                                data-target="#detailModal">
                                                                <i class="fal fa-search-plus"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif ($search_criteria): ?>
                                    <div class="alert alert-warning">Nessuna riparazione trovata.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Modale per i dettagli -->
                    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog"
                        aria-labelledby="detailModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered " role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel">Dettagli</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <span class="badge bg-success text-white" style="margin-left: 10px;"
                                        id="detail-cartellino"></span>
                                    <span class="badge bg-primary text-white" style="margin-left: 10px;"
                                        id="detail-commessa"></span>
                                    <div id="detail-container"></div>
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
                                    const recordId = this.getAttribute('data-idrip');
                                    fetch('search_record_details.php?id=' + recordId)
                                        .then(response => response.text())
                                        .then(data => {
                                            document.getElementById('detail-container').innerHTML = data;
                                        })
                                        .catch(error => console.error('Errore:', error));
                                });
                            });
                        });
                    </script>
                </div>
            </div>
            <script src="<?php echo BASE_URL?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <!-- Core plugin JavaScript-->
            <script src="<?php echo BASE_URL?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <!-- Custom scripts for all pages-->
            <script src="<?php echo BASE_URL?>/js/sb-admin-2.min.js"></script>
            <!-- Page level plugins -->
            <script src="<?php echo BASE_URL?>/vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="<?php echo BASE_URL?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <!-- Page level custom scripts -->
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
    </body>