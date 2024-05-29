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

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">** Sistema CQ Emmegiemme **</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Sistema CQ</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold bg-success text-white"
                                style="border-radius: 5px;padding:5px;">Nuova Registrazione</h6>

                        </div>


                        <div class="card-body">




                            <fieldset>
                                <form id="myForm">
                                    <div class="form-group">
                                        <label for="cartellino">Cartellino</label>
                                        <input type="text" name="cartellino" value=""
                                            placeholder="Inserisci il cartellino interessato" class="form-control"
                                            id="cartellino">
                                    </div>
                                    <div class="form-group">
                                        <h2 class="page-header page-action-links text-left">Oppure</h2>
                                        <label for="commessa">Commessa</label>
                                        <input type="text" name="commessa" value=""
                                            placeholder="Inserisci la commessa interessata" class="form-control"
                                            id="commessa">
                                    </div>
                                    <div class="form-group floating-button">
                                        <button type="submit" class="btn btn-lg btn-primary">AVANTI</button>
                                    </div>
                                </form>
                            </fieldset>
                            <link rel="stylesheet"
                                href="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.min.css">
                            <script src="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.all.min.js"></script>
                            <script>
                                document.getElementById('myForm').addEventListener('submit', function (e) {
                                    e.preventDefault(); // Impedisce il ricaricamento della pagina

                                    var cartellinoValue = document.getElementById('cartellino').value;
                                    var commessaValue = document.getElementById('commessa').value;

                                    // Verifica che almeno uno dei campi sia compilato
                                    if (!cartellinoValue && !commessaValue) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Errore',
                                            text: "Per favore, inserisci un cartellino o una commessa."
                                        });
                                        return;
                                    }

                                    // Determina il tipo di ricerca da effettuare
                                    if (cartellinoValue) {
                                        // Se il campo cartellino è compilato, esegui la ricerca per cartellino
                                        checkCartellino(cartellinoValue);
                                    } else {
                                        // Altrimenti, esegui la ricerca per commessa
                                        checkCommessa(commessaValue);
                                    }
                                });

                                function checkCartellino(cartellino) {
                                    var xhr = new XMLHttpRequest();
                                    xhr.open('GET', 'check_cartellino?cartellino=' + cartellino, true);
                                    xhr.onreadystatechange = function () {
                                        if (xhr.readyState === 4 && xhr.status === 200) {
                                            var response = JSON.parse(xhr.responseText);

                                            if (response.exists) {
                                                // Il cartellino esiste, quindi puoi reindirizzare l'utente alla pagina successiva
                                                window.location.href = 'add?cartellino=' + cartellino;
                                            } else {
                                                // Il cartellino non esiste, mostra un alert SweetAlert2
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Errore',
                                                    text: "Il cartellino non esiste. Per favore, inserisci un cartellino valido. Se sei sicuro che dovrebbe essere presente, contatta l'amministratore per aggiornare il database."
                                                });
                                            }
                                        }
                                    };
                                    xhr.send();
                                }

                                function checkCommessa(commessa) {
                                    var xhr = new XMLHttpRequest();
                                    xhr.open('GET', 'check_commessa?commessa=' + commessa, true);
                                    xhr.onreadystatechange = function () {
                                        if (xhr.readyState === 4 && xhr.status === 200) {
                                            var response = JSON.parse(xhr.responseText);

                                            if (response.exists) {
                                                // La commessa esiste, usa il cartellino restituito per reindirizzare l'utente alla pagina successiva
                                                window.location.href = 'add?cartellino=' + response.cartellino;
                                            } else {
                                                // La commessa non esiste, mostra un alert SweetAlert2
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Errore',
                                                    text: "La commessa non esiste. Per favore, inserisci una commessa valida. Se sei sicuro che dovrebbe essere presente, contatta l'amministratore per aggiornare il database."
                                                });
                                            }
                                        }
                                    };
                                    xhr.send();
                                }
                            </script>

                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>