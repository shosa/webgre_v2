<?php
/**
 * Elimina terzista
 * 
 * Questo script gestisce l'eliminazione di un terzista dal database.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';



// ID del terzista da eliminare
$terzista_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$terzista_id) {
    $_SESSION['failure'] = "ID terzista non valido";
    header('location: terzisti.php');
    exit();
}

// Se si tratta di una richiesta di conferma (GET), mostra la pagina di conferma
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['confirm'])) {
    // Recupera i dati del terzista
    try {
        $conn = getDbInstance();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT * FROM exp_terzisti WHERE id = :id");
        $stmt->bindParam(':id', $terzista_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Verifica se il terzista esiste
        if ($stmt->rowCount() === 0) {
            $_SESSION['failure'] = "Terzista non trovato!";
            header('location: terzisti.php');
            exit();
        }
        
        $terzista = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['failure'] = "Errore di database: " . $e->getMessage();
        header('location: terzisti.php');
        exit();
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
                            <h1 class="h3 mb-0 text-gray-800">Elimina Terzista</h1>
                        </div>
                        
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="terzisti.php">Terzisti</a></li>
                            <li class="breadcrumb-item active">Elimina Terzista</li>
                        </ol>
                        
                        <div class="row justify-content-center">
                            <div class="col-xl-6 col-lg-8">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-danger">
                                        <h6 class="m-0 font-weight-bold text-white">Conferma Eliminazione</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-4">
                                            <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                                            <h4>Sei sicuro di voler eliminare questo terzista?</h4>
                                            <p class="lead">Questa operazione non può essere annullata.</p>
                                        </div>
                                        
                                        <div class="alert alert-light text-left mb-4">
                                            <h5 class="font-weight-bold"><?= htmlspecialchars($terzista['ragione_sociale']) ?></h5>
                                            <?php if(!empty($terzista['indirizzo_1'])): ?>
                                                <p class="mb-1"><?= htmlspecialchars($terzista['indirizzo_1']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($terzista['indirizzo_2'])): ?>
                                                <p class="mb-1"><?= htmlspecialchars($terzista['indirizzo_2']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($terzista['indirizzo_3'])): ?>
                                                <p class="mb-1"><?= htmlspecialchars($terzista['indirizzo_3']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($terzista['nazione'])): ?>
                                                <p class="mb-1"><?= htmlspecialchars($terzista['nazione']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <a href="delete_terzista.php?id=<?= $terzista_id ?>&confirm=yes" 
                                                   class="btn btn-danger btn-block btn-lg">
                                                    <i class="fas fa-trash"></i> Elimina
                                                </a>
                                            </div>
                                            <div class="col-md-6">
                                                <a href="terzisti.php" class="btn btn-secondary btn-block btn-lg">
                                                    <i class="fas fa-arrow-left"></i> Annulla
                                                </a>
                                            </div>
                                        </div>
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
    <?php
    exit();
}

// Se la richiesta include il parametro 'confirm=yes', procede con l'eliminazione
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    try {
        $conn = getDbInstance();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prima recupera il nome del terzista per il log
        $stmt = $conn->prepare("SELECT ragione_sociale FROM exp_terzisti WHERE id = :id");
        $stmt->bindParam(':id', $terzista_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['failure'] = "Terzista non trovato!";
            header('location: terzisti.php');
            exit();
        }
        
        $terzista = $stmt->fetch(PDO::FETCH_ASSOC);
        $ragione_sociale = $terzista['ragione_sociale'];
        
        // Elimina il terzista
        $stmt = $conn->prepare("DELETE FROM exp_terzisti WHERE id = :id");
        $stmt->bindParam(':id', $terzista_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Log dell'operazione
            logActivity($_SESSION['user_id'], 'TERZISTI', 'ELIMINAZIONE', 'Eliminato terzista', $ragione_sociale, '');
            
            $_SESSION['success'] = "Terzista eliminato con successo!";
            header('location: terzisti.php');
            exit();
        } else {
            $_SESSION['failure'] = "Errore durante l'eliminazione del terzista";
            header('location: terzisti.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['failure'] = "Errore di database: " . $e->getMessage();
        header('location: terzisti.php');
        exit();
    }
}

// Se la richiesta non è valida, reindirizza alla lista
header('location: terzisti.php');
exit();