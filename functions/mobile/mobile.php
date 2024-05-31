<?php
require_once '../../config/config.php';
// Ottieni l'ID della riparazione dalla richiesta GET
$idrip = filter_input(INPUT_GET, 'idrip', FILTER_SANITIZE_NUMBER_INT);

// Inizializza una variabile per il colore del testo
$colore_testo = "#333"; // Colore di default
include BASE_PATH . '/components/header.php';
// Verifica se l'ID della riparazione è valido
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid" style="margin-top:10%">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>

                    <div class=" card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h4 class="m-0 font-weight-bold text-primary">CEDOLA #<?php echo $idrip; ?></h4>
                        </div>


                        <div class="card-body">
                            <?php
                            if ($idrip) {
                                try {
                                    // Connessione al database utilizzando PDO
                                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                    // Query per ottenere i dettagli della riparazione
                                    $sql = "SELECT * FROM riparazioni WHERE IDRIP = :idrip";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindParam(':idrip', $idrip, PDO::PARAM_INT);
                                    $stmt->execute();
                                    
                                    // Verifica se la query ha restituito risultati
                                    if ($stmt->rowCount() == 1) {
                                        $record = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $colore_testo = "#fff"; // Imposta il colore del testo per l'intestazione
                            
                                        echo '<h2 style="text-align: center; color: ' . $colore_testo . '">Dettaglio</h2>';

                                        // Output dei dettagli della riparazione con input text e textarea
                                        echo '<div class="container">';
                                        echo '<div class="form-group">';
                                        echo '<label>ID:</label>';
                                        echo '<input type="text" class="form-control border-left-primary" value="' . $record['IDRIP'] . '" readonly>';
                                        echo '</div>';
                                        // Al posto dell'input text per "Descrizione", usiamo un textarea
                                        echo '<div class="form-group">';
                                        echo '<label>Descrizione:</label>';
                                        echo '<textarea class="form-control border-left-info" readonly>' . $record['ARTICOLO'] . '</textarea>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Note:</label>';
                                        echo '<textarea class="form-control border-left-success" readonly>' . $record['CAUSALE'] . '</textarea>';
                                        echo '</div>';
                                        // Restanti campi rimangono input text
                                        echo '<div class="form-group">';
                                        echo '<label>Linea:</label>';
                                        echo '<input type="text" class="form-control border-left-success" value="' . $record['LINEA'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Utente:</label>';
                                        echo '<input type="text" class="form-control border-left-danger" value="' . $record['UTENTE'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Codice:</label>';
                                        echo '<input type="text" class="form-control border-left-info" value="' . $record['CODICE'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Paia:</label>';
                                        echo '<input type="text" class="form-control border-left-warning" value="' . $record['QTA'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Urgenza:</label>';
                                        echo '<input type="text" class="form-control border-left-primary" value="' . $record['URGENZA'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Reparto:</label>';
                                        echo '<input type="text" class="form-control border-left-success" value="' . $record['REPARTO'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Laboratorio:</label>';
                                        echo '<input type="text" class="form-control border-left-success" value="' . $record['LABORATORIO'] . '" readonly>';
                                        echo '</div>';
                                        echo '<div class="form-group">';
                                        echo '<label>Data:</label>';
                                        echo '<input type="text" class="form-control border-left-danger" value="' . $record['DATA'] . '" readonly>';
                                        echo '</div>';
                                        // Pulsante per eliminare il record
                                        echo '<form method="post" action="delete.php" style="text-align: center;">';
                                        echo '<input type="hidden" name="idrip" value="' . $idrip . '">';
                                        echo '<button type="submit" class="btn btn-danger btn-lg btn-block"><i class="fal fa-trash-alt"></i> CHIUDI</button>';
                                        echo '</form>';
                                        echo '</div>';
                                    } else {
                                        echo ' <div class="alert alert-danger"><h5>Riparazione non trovata. Già chiusa ?</h5></div>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<div class="alert alert-danger"><h5>Errore di connessione: ' . $e->getMessage() . '</h5></div>';
                                }
                            } else {
                                echo ' <div class="alert alert-danger"><h5>Errore generico.</h5></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>