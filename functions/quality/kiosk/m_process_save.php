<?php
session_start();
require_once '../../../config/config.php';
// Ottieni l'orario corrente
$orario = date('H:i');
// Variabile per tracciare se l'inserimento è riuscito
$insert_success = false;
// Inserisci i dati nel database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_testid = $_POST['new_testid']; // Recupera il valore iniziale di testid
    $initial_testid = $new_testid; // Salva il valore iniziale per l'aggiornamento
    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Esegui un loop su ogni riga inviata dal modulo
        for ($i = 0; $i < count($_POST['calzata']); $i++) {
            // Controlla se il valore della calzata non è vuoto
            if (!empty($_POST['calzata'][$i])) {
                $data = array(
                    ':testid' => $new_testid, // Utilizza il valore corrente di testid
                    ':reparto' => $_POST['reparto'],
                    ':cartellino' => $_POST['cartellino'],
                    ':commessa' => $_POST['commessa'],
                    ':cod_articolo' => $_POST['codArticolo'],
                    ':articolo' => $_POST['descArticolo'],
                    ':calzata' => $_POST['calzata'][$i],
                    ':test' => $_POST['test'][$i],
                    ':note' => $_POST['note'][$i],
                    ':esito' => $_POST['esito'][$i],
                    ':data' => $_POST['data'], // Non deve essere un array
                    ':orario' => $_POST['orario'], // Non deve essere un array
                    ':operatore' => $_POST['operatore'], // Non deve essere un array
                    ':linea' => $_POST['siglaLinea'], // Non deve essere un array
                    ':pa' => $_POST['paia'] // Non deve essere un array
                );
                // Debug: verifica il contenuto dell'array $data
                error_log('Dati inserimento: ' . print_r($data, true));
                // Esegui l'istruzione SQL INSERT utilizzando PDO
                $sql = "INSERT INTO cq_records (testid, reparto, cartellino, commessa, cod_articolo, articolo, calzata, test, note, esito, data, orario, operatore, linea, pa) 
                        VALUES (:testid, :reparto, :cartellino, :commessa, :cod_articolo, :articolo, :calzata, :test, :note, :esito, :data, :orario, :operatore, :linea, :pa)";
                $stmt = $pdo->prepare($sql);
                $insert_success = $stmt->execute($data);
                $real_query = replacePlaceholders($pdo, $sql, $data);
                if ($insert_success) {
                    logActivity($_SESSION['user_id'], 'CQ', 'FINE', 'Test #' . $new_testid, 'Cartellino ' . $_POST['cartellino']);
                    $new_testid++; // Incrementa testid per il prossimo inserimento
                } else {
                    // Se c'è stato un errore nell'inserimento, mostra un messaggio di errore
                    $insert_success = false;
                    break;
                }
            }
        }
        // Se l'inserimento è riuscito, aggiorna il record nella tabella cq_testid
        if ($insert_success) {
            $update_sql = "UPDATE cq_testid SET ID = :id WHERE ID = :initial_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_success = $update_stmt->execute(array(':id' => $new_testid - 1, ':initial_id' => $initial_testid - 1));
            if (!$update_success) {
                // Se c'è stato un errore nell'aggiornamento, mostra un messaggio di errore
                $insert_success = false;
            }
        }
    } catch (PDOException $e) {
        // Gestione degli errori di connessione e di query
        error_log('Errore PDO: ' . $e->getMessage());
        $insert_success = false;
    }
}
require_once BASE_PATH . '/components/header.php';
?>
<style>
    .confirmation {
        text-align: center;
        margin-top: 50px;
    }
    .confirmation h1 {
        color: #28a745;
    }
    .confirmation p {
        font-size: 1.2em;
    }
    .spinner {
        margin: 20px auto;
        width: 50px;
        height: 50px;
        border: 5px solid rgba(0, 0, 0, .1);
        border-left-color: #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    .rosso {
        color: var(--red) !important;
    }
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
<script>
    setTimeout(function () {
        window.location.href = 'new';
    }, 2000);
</script>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
       
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"></h6>
                        </div>
                        <div class="card-body bg-white">
                            <div class="confirmation">
                                <?php if ($insert_success): ?>
                                    <h1>Operazione completata con successo!</h1>
                                    <p>Verrai reindirizzato alla pagina principale in pochi secondi.</p>
                                    <div class="spinner"></div>
                                <?php else: ?>
                                    <h1 class="rosso">Errore durante l'inserimento del record!</h1>
                                    <p>Si è verificato un problema. Verrai reindirizzato alla pagina principale in pochi
                                        secondi.</p>
                                    <div class="spinner" style="border-left-color: red;"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>