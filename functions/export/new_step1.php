<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

$edit = false;

try {
    $conn = getDbInstance();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Recupera l'ultimo id + 1 dalla tabella exp_documenti
    $stmt = $conn->prepare("SELECT id FROM exp_documenti ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $lastDocument = $stmt->fetch(PDO::FETCH_ASSOC);
    $newId = ($lastDocument ? $lastDocument['id'] + 1 : 1);
    
    // Recupera i dati dalla tabella exp_terzisti
    $stmt = $conn->prepare("SELECT id, ragione_sociale FROM exp_terzisti ORDER BY ragione_sociale ASC");
    $stmt->execute();
    $terzisti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_terzista = filter_input(INPUT_POST, 'terzista', FILTER_VALIDATE_INT);
        $data = date("Y-m-d");
        $stato = "Aperto";
        
        // Prepara l'inserimento
        $stmt = $conn->prepare("INSERT INTO exp_documenti (id, id_terzista, data, stato) VALUES (:id, :id_terzista, :data, :stato)");
        $stmt->bindParam(':id', $newId, PDO::PARAM_INT);
        $stmt->bindParam(':id_terzista', $id_terzista, PDO::PARAM_INT);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':stato', $stato);
        
        if ($stmt->execute()) {
            // Log dell'operazione
            if (isset($_SESSION['user_id'])) {
                logActivity($_SESSION['user_id'], 'DDT', 'CREAZIONE', 'Creato nuovo documento', $newId, '');
            }
            
            header("Location: new_step2.php?progressivo=$newId");
            exit();
        } else {
            $error = "Errore durante l'inserimento del nuovo documento";
        }
    }
} catch (PDOException $e) {
    $error = "Errore di database: " . $e->getMessage();
    error_log($error);
}

include(BASE_PATH . "/components/header.php");
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
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <span class="text-primary">STEP 1</span> > Inserimento Dettagli
                        </h1>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="documenti.php">Registro DDT</a></li>
                        <li class="breadcrumb-item active">Nuovo DDT</li>
                    </ol>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Nuovo Documento</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="terzista">Terzista</label>
                                                    <select class="form-control" id="terzista" name="terzista" required>
                                                        <option value="">Seleziona un terzista</option>
                                                        <?php foreach ($terzisti as $terzista): ?>
                                                            <option value="<?php echo $terzista['id']; ?>">
                                                                <?php echo htmlspecialchars($terzista['ragione_sociale']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="progressivo">Progressivo</label>
                                                    <input type="text" class="form-control" id="progressivo" name="progressivo"
                                                        value="<?php echo $newId; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-arrow-right"></i> Avanti
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>

<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
