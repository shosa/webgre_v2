<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
$edit = false;
require_once BASE_PATH . '/components/header.php';
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
                    <!-- Intestazione pagina con breadcrumb integrato -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Nuova Riparazione</h1>
                        </div>
                    </div>
                    <!-- Breadcrumbs -->
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Nuova Riparazione</li>
                    </ol>


                    <!-- Card principale -->
                    <div class="card shadow-sm border-0 rounded-lg mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-plus-circle mr-2"></i>Inserimento Cedola
                            </h6>
                            <span class="badge badge-light text-muted">Step 1 di 2</span>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info border-0 shadow-sm">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>Inserisci il cartellino o il numero di commessa per procedere con la cedola di
                                    riparazione.</span>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 pr-lg-4">
                                    <!-- Form cartellino -->
                                    <div class="card bg-light border-0 rounded-lg mb-3 mb-lg-0">
                                        <div class="card-body">
                                            <h5 class="text-primary mb-3">Ricerca per Cartellino</h5>
                                            <div class="form-group">
                                                <label for="cartellino" class="text-muted font-weight-bold small">Numero
                                                    Cartellino</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0">
                                                            <i class="fas fa-tag text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" name="cartellino" value=""
                                                        placeholder="Inserisci il numero del cartellino"
                                                        class="form-control border-left-0" id="cartellino"
                                                        autocomplete="off">
                                                </div>
                                                <small class="form-text text-muted">
                                                    Es. 123456
                                                </small>
                                            </div>
                                            <button type="button" id="searchCartellino"
                                                class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i> Cerca Cartellino
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 pl-lg-4">
                                    <!-- Form commessa -->
                                    <div class="card bg-light border-0 rounded-lg">
                                        <div class="card-body">
                                            <h5 class="text-primary mb-3">Ricerca per Commessa</h5>
                                            <div class="form-group">
                                                <label for="commessa" class="text-muted font-weight-bold small">Numero
                                                    Commessa</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0">
                                                            <i class="fas fa-file-contract text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" name="commessa" value=""
                                                        placeholder="Inserisci il numero della commessa"
                                                        class="form-control border-left-0" id="commessa"
                                                        autocomplete="off">
                                                </div>
                                                <small class="form-text text-muted">
                                                    Es. 987654 o COM-987654
                                                </small>
                                            </div>
                                            <button type="button" id="searchCommessa" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i> Cerca Commessa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicatore di caricamento -->
                            <div id="loading" class="text-center mt-4 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Caricamento...</span>
                                </div>
                                <p class="mt-2 text-muted">Ricerca in corso...</p>
                            </div>
                        </div>
                    </div>

                  
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.min.css">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.all.min.js"></script>

            <script>
                // Carica le riparazioni recenti all'avvio della pagina
               

                // Cerca per cartellino
                document.getElementById('searchCartellino').addEventListener('click', function () {
                    var cartellinoValue = document.getElementById('cartellino').value.trim();

                    if (!cartellinoValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Attenzione',
                            text: "Inserisci un numero di cartellino per procedere."
                        });
                        return;
                    }

                    showLoading(true);
                    checkCartellino(cartellinoValue);
                });

                // Cerca per commessa
                document.getElementById('searchCommessa').addEventListener('click', function () {
                    var commessaValue = document.getElementById('commessa').value.trim();

                    if (!commessaValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Attenzione',
                            text: "Inserisci un numero di commessa per procedere."
                        });
                        return;
                    }

                    showLoading(true);
                    checkCommessa(commessaValue);
                });

                // Intercetta anche l'invio da tastiera per entrambi i campi
                document.getElementById('cartellino').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('searchCartellino').click();
                    }
                });

                document.getElementById('commessa').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('searchCommessa').click();
                    }
                });

                // Mostra/nasconde l'indicatore di caricamento
                function showLoading(show) {
                    var loadingElement = document.getElementById('loading');
                    if (show) {
                        loadingElement.classList.remove('d-none');
                    } else {
                        loadingElement.classList.add('d-none');
                    }
                }

                function checkCartellino(cartellino) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'check_cartellino.php?cartellino=' + encodeURIComponent(cartellino), true);
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            showLoading(false);

                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.exists) {
                                    // Il cartellino esiste, redirect
                                    window.location.href = 'add_step2.php?cartellino=' + encodeURIComponent(cartellino);
                                } else {
                                    // Il cartellino non esiste
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Cartellino non trovato',
                                        text: "Il cartellino non esiste nel database. Verifica il numero inserito o contatta l'amministratore.",
                                        confirmButtonText: 'Capito'
                                    });
                                }
                            } else {
                                // Errore di rete o server
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Errore',
                                    text: "Si è verificato un errore durante la ricerca. Riprova tra qualche istante.",
                                    confirmButtonText: 'OK'
                                });
                            }
                        }
                    };
                    xhr.send();
                }

                function checkCommessa(commessa) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'check_commessa.php?commessa=' + encodeURIComponent(commessa), true);
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            showLoading(false);

                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.exists) {
                                    // La commessa esiste, redirect
                                    window.location.href = 'add_step2.php?cartellino=' + encodeURIComponent(response.cartellino);
                                } else {
                                    // La commessa non esiste
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Commessa non trovata',
                                        text: "La commessa non esiste nel database. Verifica il numero inserito o contatta l'amministratore.",
                                        confirmButtonText: 'Capito'
                                    });
                                }
                            } else {
                                // Errore di rete o server
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Errore',
                                    text: "Si è verificato un errore durante la ricerca. Riprova tra qualche istante.",
                                    confirmButtonText: 'OK'
                                });
                            }
                        }
                    };
                    xhr.send();
                }
            </script>

            <style>
                /* Stili aggiuntivi */
                .card {
                    transition: all 0.3s ease;
                    border-radius: 0.5rem;
                }

                .input-group-text {
                    border-radius: 0.25rem 0 0 0.25rem;
                }

                .input-group .form-control {
                    border-radius: 0 0.25rem 0.25rem 0;
                }

                .breadcrumb {
                    margin-bottom: 0;
                }

                .breadcrumb-item+.breadcrumb-item::before {
                    content: "›";
                }

                /* Spinner personalizzato */
                .spinner-border {
                    width: 2.5rem;
                    height: 2.5rem;
                }
            </style>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>