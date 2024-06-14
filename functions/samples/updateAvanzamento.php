<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once "../../helpers/helpers.php";
require_once "../../utils/log_utils.php";

// Verifica se l'ID del modello è passato tramite GET
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID del modello non fornito.";
    header('Location: ../../404?message=SampleUpdateAvanzamento');
    exit();
}

$modelId = (int) $_GET['id'];
$pdo = getDbInstance();

// Recupera i dati del modello
$stmt = $pdo->prepare("SELECT * FROM samples_modelli WHERE id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$model = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$model) {
    $_SESSION['error'] = "Modello non trovato.";
    header('Location: ../../404?message=SampleUpdateAvanzamento');
    exit();
}

// Recupera l'avanzamento corrente
$stmt = $pdo->prepare("SELECT * FROM samples_avanzamenti WHERE modello_id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$avanzamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$avanzamento) {
    // Crea un record di avanzamento se non esiste
    $stmt = $pdo->prepare("INSERT INTO samples_avanzamenti (modello_id) VALUES (:model_id)");
    $stmt->execute(['model_id' => $modelId]);
    $stmt = $pdo->prepare("SELECT * FROM samples_avanzamenti WHERE modello_id = :model_id");
    $stmt->execute(['model_id' => $modelId]);
    $avanzamento = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Azioni di avanzamento
$actions = [
    'TAGLIO' => ['label' => 'Taglio', 'column' => 'stato_taglio', 'date_column' => 'data_taglio'],
    'ORLATURA' => ['label' => 'Orlatura', 'column' => 'stato_orlatura', 'date_column' => 'data_orlatura'],
    'MONTAGGIO' => ['label' => 'Montaggio', 'column' => 'stato_montaggio', 'date_column' => 'data_montaggio'],
    'SPEDITO' => ['label' => 'Spedito', 'column' => 'stato_spedito', 'date_column' => 'data_spedito'],
];

// Verifica se il parametro action è passato
$actionToExecute = isset($_GET['action']) ? $_GET['action'] : null;

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avanzamento Modello</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <style>
        .square-image-container {
            width: 150px;
            height: 150px;
            overflow: hidden;
            margin: auto;
            margin-bottom: 10px;
        }

        .square-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .list-group-item.not-completed {
            background-color: #f8f9fa;
        }

        .list-group-item.completed {
            background-color: #d4edda;
        }

        .list-group-item.disabled {
            /* Rimuovi lo stile che influisce sulla visualizzazione */
            /* pointer-events: none;
            opacity: 0.65; */
            /* Puoi lasciare questo blocco vuoto o rimuoverlo */
        }
    </style>
</head>

<body>
    <div class="container mt-2">
        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <p>MODELLO:</p>
                        <h2 class="card-title"><?php echo htmlspecialchars($model['nome_modello']); ?></h2>
                    </div>
                    <div class="col-auto">
                        <h3 class="mb-1 alert alert-primary" style="padding:5px !important;">
                            <i>#<?php echo htmlspecialchars($modelId); ?></i></h3>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="text-center">
                    <div class="square-image-container">
                        <img src="img/<?php echo htmlspecialchars($model['immagine']); ?>" alt="Immagine Modello"
                            class="square-image">
                    </div>
                </div>
                <p>VARIANTE:</p>
                <h4><?php echo htmlspecialchars($model['variante']); ?></h4>
                <p>CONSEGNA:</p>
                <h5><?php echo htmlspecialchars(date('d/m/Y', strtotime($model['consegna']))); ?></h5>
            </div>
        </div>

        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['warning']);
                unset($_SESSION['warning']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="updateForm">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($modelId); ?>">
            <div class="list-group">
                <?php foreach ($actions as $key => $action): ?>
                    <button type="button"
                        class="list-group-item list-group-item-action <?php echo $avanzamento[$action['column']] ? 'completed' : 'not-completed'; ?>"
                        data-action="<?php echo htmlspecialchars($key); ?>" <?php echo $avanzamento[$action['column']] ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($action['label']); ?>
                        <?php if ($avanzamento[$action['column']]): ?>
                            <span class="badge bg-success float-end">Completato</span>
                            <small
                                class="text-muted float-end me-3"><?php echo htmlspecialchars($avanzamento[$action['date_column']]); ?></small>
                        <?php else: ?>
                            <span class="badge bg-secondary float-end">Incompleto</span>
                            <i class="far fa-hand-pointer float-end me-3 text-info"></i>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </form>
        <div id="message" class="mt-4"></div>
        <div class="card mt-4">
            <div class="card-header">
                <h5>Note</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <p><?php echo htmlspecialchars($model['note']); ?></p>
                </div>
            </div>

        </div>




    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Funzione per aggiornare lo stato
            function updateStatus(modelId, action) {
                fetch(`updateAvanzamentoHandler.php?id=${modelId}&action=${action}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                })
                    .then(response => response.json())
                    .then(data => {
                        let message = document.getElementById('message');
                        if (data.success) {
                            message.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                            // Ricarica la pagina senza il parametro action
                            setTimeout(() => window.location.href = `updateAvanzamento.php?id=${modelId}`, 1000);
                        } else {
                            message.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
            // Verifica se un'azione è passata tramite query string
            let actionToExecute = '<?php echo $actionToExecute; ?>';
            if (actionToExecute) {
                let modelId = <?php echo $modelId; ?>;
                if (confirm(`Sei sicuro di voler aggiornare lo stato a ${actionToExecute}?`)) {
                    updateStatus(modelId, actionToExecute);
                }
            }

            // Gestisci click sui bottoni
            document.querySelectorAll('.list-group-item.not-completed').forEach(function (button) {
                button.addEventListener('click', function () {
                    let action = this.dataset.action;
                    let modelId = <?php echo $modelId; ?>;
                    if (confirm(`Sei sicuro di voler aggiornare lo stato a ${action}?`)) {
                        updateStatus(modelId, action);
                    }
                });
            });
        });
    </script>
</body>

</html>