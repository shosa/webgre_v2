<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Ottieni l'istanza di mysqlidb
$db = getDbInstance();

// Ottieni l'orario corrente
$orario = date('H:i');

// Variabile per tracciare se l'inserimento è riuscito
$insert_success = false;

// Inserisci i dati nel database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_testid = $_POST['new_testid']; // Recupera il valore iniziale di testid
    $initial_testid = $new_testid; // Salva il valore iniziale per l'aggiornamento

    // Esegui un loop su ogni riga inviata dal modulo
    for ($i = 0; $i < count($_POST['calzata']); $i++) {
        // Controlla se il valore della calzata non è vuoto
        if (!empty($_POST['calzata'][$i])) {
            $data = array(
                'testid' => $new_testid, // Utilizza il valore corrente di testid
                'reparto' => $_POST['reparto'],
                'cartellino' => $_POST['cartellino'],
                'commessa' => $_POST['commessa'],
                'cod_articolo' => $_POST['codArticolo'],
                'articolo' => $_POST['descArticolo'],
                'calzata' => $_POST['calzata'][$i],
                'test' => $_POST['test'][$i],
                'note' => $_POST['note'][$i],
                'esito' => $_POST['esito'][$i],
                'data' => $_POST['data'], // Non deve essere un array
                'orario' => $_POST['orario'], // Non deve essere un array
                'operatore' => $_POST['operatore'], // Non deve essere un array
                'linea' => $_POST['siglaLinea'], // Non deve essere un array
                'pa' => $_POST['paia'] // Non deve essere un array
            );

            // Debug: verifica il contenuto dell'array $data
            error_log('Dati inserimento: ' . print_r($data, true));

            // Esegui l'istruzione SQL INSERT utilizzando l'istanza di mysqlidb
            $last_id = $db->insert('cq_records', $data);
            if ($last_id) {
                // Inserimento riuscito
                $insert_success = true;
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
        $update_data = array(
            'ID' => $new_testid - 1 // Usa l'ultimo valore di testid inserito
        );

        $db->where('ID', $initial_testid - 1); // Usa l'ID del valore iniziale - 1
        $updated = $db->update('cq_testid', $update_data);

        if (!$updated) {
            // Se c'è stato un errore nell'aggiornamento, mostra un messaggio di errore
            $insert_success = false;
        }
    }
}

require_once BASE_PATH . '/includes/header.php';
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

<div id="page-wrapper">
    <div class="confirmation">
        <?php if ($insert_success): ?>
            <h1>Operazione completata con successo!</h1>
            <p>Verrai reindirizzato alla pagina principale in pochi secondi.</p>
            <div class="spinner"></div>
        <?php else: ?>
            <h1>Errore durante l'inserimento del record!</h1>
            <p>Si è verificato un problema. Verrai reindirizzato alla pagina principale in pochi secondi.</p>
            <div class="spinner" style="border-left-color: red;"></div>
        <?php endif; ?>
    </div>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>
