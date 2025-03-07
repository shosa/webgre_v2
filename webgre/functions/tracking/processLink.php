<?php

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
// Verifica se il modulo è stato inviato e se esistono cartellini selezionati

// Mostra i cartellini selezionati

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
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <?php include (BASE_PATH . "/utils/alerts.php"); ?>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item"><a href="multiSearch">Selezione Multipla</a></li>
                        <li class="breadcrumb-item active">Associazione</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Riepilogo Selezione</h6>
                                </div>
                                <div class="card-body text-left">
                                    <div class="card-body text-left">
                                        <?php
                                        if (isset($_POST['selectedCartels'])) {
                                            // Decodifica la stringa JSON contenente i cartellini selezionati
                                            $selectedCartels = json_decode($_POST['selectedCartels'], true);

                                            // Verifica se l'array è stato decodificato correttamente
                                            if (is_array($selectedCartels) && !empty($selectedCartels)) {
                                                echo '<h5>Cartellini:</h5>';
                                                echo '<div class="table-responsive">'; // Per rendere la tabella responsive su dispositivi di piccole dimensioni
                                                echo '<table class="table table-bordered" style="border-collapse: separate; border-spacing: 5px;">'; // Utilizzo delle classi di Bootstrap per stili di tabella e aggiunta di border-spacing
                                        
                                                echo '<tbody>';

                                                $numColumns = 6; // Numero di colonne da visualizzare, puoi personalizzare questo valore
                                        
                                                // Calcolo del numero di righe necessarie in base al numero di cartellini e al numero di colonne
                                                $numRows = ceil(count($selectedCartels) / $numColumns);

                                                for ($i = 0; $i < $numRows; $i++) {
                                                    echo '<tr style="font-size: 10pt;background-color:#f5f5f5;">'; // Stile per il font-size delle celle
                                                    for ($j = 0; $j < $numColumns; $j++) {
                                                        $index = $i * $numColumns + $j;
                                                        if ($index < count($selectedCartels)) {
                                                            echo '<td class="p-1">' . htmlspecialchars($selectedCartels[$index]) . '</td>';
                                                        } else {
                                                            echo '<td></td>'; // Cella vuota se non ci sono abbastanza dati per riempire tutte le colonne
                                                        }
                                                    }
                                                    echo '</tr>';
                                                }

                                                echo '</tbody>';
                                                echo '</table>';
                                                echo '</div>';

                                            } else {
                                                $_SESSION["danger"] = "Nessun Cartellino Selezionato!";
                                            }
                                        } else {
                                            echo '<p>Dati non validi.</p>';
                                        }
                                        ?>


                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Esegui Associazione</h6>
                                </div>
                                <div class="card-body text-left">
                                    <form id="associazioneForm" method="post">
                                        <div class="form-group">
                                            <label for="type_id">Seleziona Tipo:</label>
                                            <select class="form-control" id="type_id" name="type_id"
                                                onchange="toggleLotInput()">
                                                <option value="">Seleziona Tipo</option>
                                                <?php
                                                // Connessione al database con PDO
                                                $pdo = getDbInstance();

                                                // Seleziona tutti i tipi disponibili
                                                $tipi = $pdo->query("SELECT id, name FROM track_types")->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($tipi as $tipo) {
                                                    echo '<option value="' . $tipo['id'] . '">' . htmlspecialchars($tipo['name']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="lotNumbers">Numeri di Lotto:</label>
                                            <textarea class="form-control" id="lotNumbers" name="lotNumbers" rows="3" placeholder="Attenzione inserire un valore per riga."
                                                disabled></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Salva</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleLotInput() {
        var typeSelect = document.getElementById('type_id');
        var lotInput = document.getElementById('lotNumbers');
        lotInput.disabled = !typeSelect.value; // Abilita/disabilita il campo in base alla selezione del tipo
    }
    document.getElementById('associazioneForm').addEventListener('submit', function (event) {
        event.preventDefault();

        var type_id = document.getElementById('type_id').value;
        var lotNumbers = document.getElementById('lotNumbers').value.trim();
        var cartelli = <?= json_encode($selectedCartels) ?>;

        // Trasformare i lotNumbers in un array dividendo per le righe
        lotNumbers = lotNumbers.split('\n').map(function (line) { return line.trim(); });

        // Invio tramite AJAX per l'inserimento dei dati
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'saveLinks.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        // Imposta il messaggio di successo nella sessione PHP
                        var message = 'Associazione salvata con successo.';
                        var xhrSession = new XMLHttpRequest();
                        xhrSession.open('POST', 'setSession.php', true);
                        xhrSession.setRequestHeader('Content-Type', 'application/json');
                        xhrSession.onreadystatechange = function () {
                            if (xhrSession.readyState === XMLHttpRequest.DONE && xhrSession.status === 200) {
                                // Mostra SweetAlert e reindirizza solo dopo aver impostato la sessione
                                Swal.fire({
                                    title: 'Successo!',
                                    text: message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'home.php';
                                    }
                                });
                            }
                        };
                        xhrSession.send(JSON.stringify({ message: message }));

                    } else {
                        Swal.fire({
                            title: 'Errore!',
                            text: 'Si è verificato un errore durante il salvataggio:\n' + response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }

                    // Visualizza le query eseguite (se necessario)
                    if (response.queries) {
                        console.log("Query eseguite:");
                        response.queries.forEach(function (query) {
                            console.log(query);
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Errore!',
                        text: 'Si è verificato un errore durante il salvataggio.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        };

        var data = {
            type_id: type_id,
            lotNumbers: lotNumbers,
            cartelli: cartelli
        };

        xhr.send(JSON.stringify(data));
    });
</script>