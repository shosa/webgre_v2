<?php
session_start();
require_once '../../../config/config.php';
$edit = false;
require_once BASE_PATH . '/components/header_kiosk.php';
?>
<!DOCTYPE html>
<html lang="it">
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
 
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
   
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h2 mb-0 text-gray-800 font-weight-bold">Sistema CQ Emmegiemme</h1>
                    </div>
                    
                    <!-- Breadcrumbs -->
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Nuova Registrazione</li>
                    </ol>
                    
                    <!-- Registration Card -->
                    <div class="card shadow-lg">
                        <div class="card-header py-3 bg-primary">
                            <h5 class="m-0 font-weight-bold text-white text-center">Nuova Registrazione</h5>
                        </div>
                        <div class="card-body p-5">
                            <form id="registrationForm" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="cartellino" class="form-label h5">Cartellino</label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="cartellino" 
                                               name="cartellino" 
                                               placeholder="Inserisci il cartellino" 
                                               autocomplete="off">
                                        <div class="invalid-feedback">
                                            Inserisci un cartellino valido
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="commessa" class="form-label h5">Commessa</label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="commessa" 
                                               name="commessa" 
                                               placeholder="Inserisci la commessa" 
                                               autocomplete="off">
                                        <div class="invalid-feedback">
                                            Inserisci una commessa valida
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-block btn-lg">
                                        <i class="fas fa-arrow-right mr-2"></i>PROCEDI
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Sweet Alert 2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    document.getElementById('registrationForm').addEventListener('submit', function (e) {
        e.preventDefault();
        
        var cartellinoValue = document.getElementById('cartellino').value.trim();
        var commessaValue = document.getElementById('commessa').value.trim();

        // Validate that at least one field is filled
        if (!cartellinoValue && !commessaValue) {
            Swal.fire({
                icon: 'error',
                title: 'Attenzione',
                text: "Inserisci un cartellino o una commessa.",
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Determine search type and proceed
        if (cartellinoValue) {
            checkCartellino(cartellinoValue);
        } else {
            checkCommessa(commessaValue);
        }
    });

    function checkCartellino(cartellino) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_cartellino?cartellino=' + encodeURIComponent(cartellino), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.exists) {
                    window.location.href = 'add?cartellino=' + encodeURIComponent(cartellino);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: "Il cartellino non esiste. Verificare o contattare l'amministratore.",
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Chiudi'
                    });
                }
            }
        };
        xhr.send();
    }

    function checkCommessa(commessa) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_commessa?commessa=' + encodeURIComponent(commessa), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.exists) {
                    window.location.href = 'm_add?cartellino=' + encodeURIComponent(response.cartellino);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: "La commessa non esiste. Verificare o contattare l'amministratore.",
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Chiudi'
                    });
                }
            }
        };
        xhr.send();
    }
    </script>
    
    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
</body>
</html>