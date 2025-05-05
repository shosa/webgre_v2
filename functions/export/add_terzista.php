<?php
/**
 * Aggiunta nuovo terzista
 * 
 * Questo script gestisce l'inserimento di un nuovo terzista nel database.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

// Elaborazione del form quando viene inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_to_store = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    
    try {
        $conn = getDbInstance();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verifica se il terzista esiste già
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM exp_terzisti WHERE ragione_sociale = :ragione_sociale");
        $stmt->bindParam(':ragione_sociale', $data_to_store['ragione_sociale']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] >= 1) {
            $_SESSION['failure'] = "Terzista già esistente!";
            header('location: add_terzista.php');
            exit();
        }
        
        // Preparazione della query di inserimento
        $sql = "INSERT INTO exp_terzisti (ragione_sociale, indirizzo_1, indirizzo_2, indirizzo_3, nazione, consegna) 
                VALUES (:ragione_sociale, :indirizzo_1, :indirizzo_2, :indirizzo_3, :nazione, :consegna)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ragione_sociale', $data_to_store['ragione_sociale']);
        $stmt->bindParam(':indirizzo_1', $data_to_store['indirizzo_1']);
        $stmt->bindParam(':indirizzo_2', $data_to_store['indirizzo_2']);
        $stmt->bindParam(':indirizzo_3', $data_to_store['indirizzo_3']);
        $stmt->bindParam(':nazione', $data_to_store['nazione']);
        $stmt->bindParam(':consegna', $data_to_store['consegna']);
        
        if ($stmt->execute()) {
            // Log dell'operazione
            logActivity($_SESSION['user_id'], 'TERZISTI', 'INSERIMENTO', 'Aggiunto nuovo terzista', $data_to_store['ragione_sociale'], '');
            
            $_SESSION['success'] = "Terzista aggiunto con successo!";
            header('location: terzisti.php');
            exit();
        } else {
            $_SESSION['failure'] = "Errore durante l'inserimento del terzista";
        }
    } catch (PDOException $e) {
        $_SESSION['failure'] = "Errore di database: " . $e->getMessage();
    }
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
                        <h1 class="h3 mb-0 text-gray-800">Nuovo Terzista</h1>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="terzisti.php">Terzisti</a></li>
                        <li class="breadcrumb-item active">Nuovo Terzista</li>
                    </ol>
                    
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Dati Terzista</h6>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" id="terzista_form">
                                        <!-- Ragione Sociale -->
                                        <div class="form-group row">
                                            <label for="ragione_sociale" class="col-sm-3 col-form-label">Ragione Sociale:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="ragione_sociale" id="ragione_sociale" 
                                                       class="form-control" required autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <!-- Indirizzi -->
                                        <div class="form-group row">
                                            <label for="indirizzo_1" class="col-sm-3 col-form-label">Indirizzo Riga 1:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="indirizzo_1" id="indirizzo_1" 
                                                       class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="indirizzo_2" class="col-sm-3 col-form-label">Indirizzo Riga 2:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="indirizzo_2" id="indirizzo_2" 
                                                       class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="indirizzo_3" class="col-sm-3 col-form-label">Indirizzo Riga 3:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="indirizzo_3" id="indirizzo_3" 
                                                       class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="nazione" class="col-sm-3 col-form-label">Nazione:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="nazione" id="nazione" 
                                                       class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="consegna" class="col-sm-3 col-form-label">Consegna:</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="consegna" id="consegna" 
                                                       class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Anteprima</h6>
                                </div>
                                <div class="card-body">
                                    <h4 id="preview_ragione_sociale" class="font-weight-bold"></h4>
                                    <div id="preview_indirizzi" class="mt-3"></div>
                                </div>
                            </div>
                            
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Azioni</h6>
                                </div>
                                <div class="card-body text-center">
                                    <button type="button" onclick="document.getElementById('terzista_form').submit();" 
                                            class="btn btn-success btn-lg btn-block shadow">
                                        <i class="fas fa-save"></i> Salva
                                    </button>
                                    <a href="terzisti.php" class="btn btn-danger btn-lg btn-block shadow mt-3">
                                        <i class="fas fa-times"></i> Annulla
                                    </a>
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

<script>
$(document).ready(function() {
    // Aggiornamento anteprima in tempo reale
    $('#ragione_sociale, #indirizzo_1, #indirizzo_2, #indirizzo_3, #nazione').on('input', function() {
        var ragione_sociale = $('#ragione_sociale').val();
        var indirizzo_1 = $('#indirizzo_1').val();
        var indirizzo_2 = $('#indirizzo_2').val();
        var indirizzo_3 = $('#indirizzo_3').val();
        var nazione = $('#nazione').val();

        $('#preview_ragione_sociale').text(ragione_sociale);
        $('#preview_indirizzi').html(
            (indirizzo_1 ? '<p>' + indirizzo_1 + '</p>' : '') + 
            (indirizzo_2 ? '<p>' + indirizzo_2 + '</p>' : '') + 
            (indirizzo_3 ? '<p>' + indirizzo_3 + '</p>' : '') + 
            (nazione ? '<p>' + nazione + '</p>' : '')
        );
    });
});
</script>