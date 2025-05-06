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
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        border: none;
    }
    
    .card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        padding: 15px 20px;
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #d1d3e2;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .form-group label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 8px;
    }
    
    .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .breadcrumb {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 12px 20px;
    }
    
    /* Progress Steps */
    .progress-step {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .progress-step::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: #e9ecef;
        z-index: 0;
    }
    
    .step {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #fff;
        border: 3px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }
    
    .step.active .step-number {
        background-color: #4e73df;
        border-color: #4e73df;
        color: #fff;
    }
    
    .step-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #666;
    }
    
    .step.active .step-label {
        color: #4e73df;
    }
    
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d3e2;
        border-radius: 8px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 15px;
        color: #6e707e;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    
    .terzista-card {
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
        background-color: #f8f9fc;
        margin-top: 20px;
        display: none;
    }
    
    .terzista-info {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .terzista-title {
        font-weight: 600;
        color: #4e73df;
        margin-bottom: 10px;
    }
    
    @media (max-width: 768px) {
        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .step-label {
            font-size: 0.8rem;
        }
    }
</style>

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
                    <div class="card">
                        <div class="card-body">
                            
                            
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
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i> Stai per creare un nuovo documento di trasporto. Seleziona il terzista dal menu a tendina e procedi allo step successivo.
                            </div>
                                
                            <div class="card shadow">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-file-invoice mr-2"></i> Nuovo Documento
                                    </h6>
                                    <span class="badge badge-primary">DDT N° <?php echo $newId; ?></span>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" id="createDdtForm">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="terzista">
                                                        <i class="fas fa-user-tie mr-1"></i> Seleziona Terzista
                                                    </label>
                                                    <select class="form-control select2" id="terzista" name="terzista" required>
                                                        <option value="">Seleziona un terzista</option>
                                                        <?php foreach ($terzisti as $terzista): ?>
                                                            <option value="<?php echo $terzista['id']; ?>">
                                                                <?php echo htmlspecialchars($terzista['ragione_sociale']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <!-- Anteprima del terzista selezionato -->
                                                <div class="terzista-card" id="terzistaPreview">
                                                    <div class="terzista-title">Dettagli Terzista</div>
                                                    <div class="terzista-info" id="terzistaNome"></div>
                                                    <div class="terzista-info" id="terzistaIndirizzo"></div>
                                                    <div class="terzista-info" id="terzistaCitta"></div>
                                                    <div class="terzista-info" id="terzistaNazione"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="progressivo">
                                                        <i class="fas fa-hashtag mr-1"></i> Progressivo
                                                    </label>
                                                    <input type="text" class="form-control bg-light" id="progressivo" name="progressivo"
                                                        value="<?php echo $newId; ?>" readonly>
                                                    <small class="form-text text-muted">Numero documento generato automaticamente</small>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="dataDoc">
                                                        <i class="fas fa-calendar-alt mr-1"></i> Data
                                                    </label>
                                                    <input type="text" class="form-control bg-light" id="dataDoc" name="dataDoc"
                                                        value="<?php echo date('d/m/Y'); ?>" readonly>
                                                    <small class="form-text text-muted">Data odierna</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="row mt-4">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                                    Avanti <i class="fas fa-arrow-right ml-2"></i>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {
    // Inizializza Select2
    $('.select2').select2({
        placeholder: "Seleziona un terzista",
        allowClear: true,
        width: '100%'
    });
    
    // Gestisci il cambio del terzista
    $('#terzista').on('change', function() {
        var terzistaId = $(this).val();
        if (terzistaId) {
            // Abilita il pulsante Avanti
            $('#submitBtn').prop('disabled', false);
            
            // Simula il caricamento dei dettagli del terzista (in un sistema reale, faresti una chiamata AJAX)
            $.ajax({
                url: 'get_terzista_details.php',
                method: 'POST',
                data: { id: terzistaId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Popola l'anteprima del terzista
                        $('#terzistaNome').text(response.data.ragione_sociale);
                        $('#terzistaIndirizzo').text(response.data.indirizzo_1);
                        $('#terzistaCitta').text(response.data.indirizzo_2);
                        $('#terzistaNazione').text(response.data.nazione);
                        
                        // Mostra la card di anteprima
                        $('#terzistaPreview').slideDown();
                    }
                },
                error: function() {
                    // Se c'è un errore o non hai l'API, mostra solo un testo base
                    $('#terzistaNome').text($('#terzista option:selected').text());
                    $('#terzistaIndirizzo').text('');
                    $('#terzistaCitta').text('');
                    $('#terzistaNazione').text('');
                    
                    // Mostra la card di anteprima
                    $('#terzistaPreview').slideDown();
                }
            });
        } else {
            // Disabilita il pulsante Avanti
            $('#submitBtn').prop('disabled', true);
            
            // Nascondi la card di anteprima
            $('#terzistaPreview').slideUp();
        }
    });
    
    // Gestisci il submit del form
    $('#createDdtForm').on('submit', function(e) {
        // Verifica se il form è valido
        if (!$(this)[0].checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            Swal.fire({
                icon: 'error',
                title: 'Compilazione incompleta',
                text: 'Seleziona un terzista prima di procedere',
                confirmButtonColor: '#4e73df'
            });
            
            return false;
        }
        
        // Se tutto è valido, mostra un loader
        Swal.fire({
            title: 'Creazione documento in corso',
            text: 'Attendere prego...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Il form verrà inviato normalmente
    });
});
</script>