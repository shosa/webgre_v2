<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once "../../utils/helpers.php";
require_once "../../utils/log_utils.php";

// Verifica se l'ID del modello è passato tramite GET
if (!isset($_GET['model_id'])) {
    $_SESSION['error'] = "ID del modello non fornito.";
    header('Location: ../../index');
    exit();
}

$modelId = $_GET['model_id'];

$pdo = getDbInstance();
$stmt = $pdo->prepare("SELECT * FROM samples_modelli WHERE id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$model = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$model) {
    $_SESSION['error'] = "Modello non trovato.";
    header('Location: ../../index');
    exit();
}

// Recupera le voci esistenti della DiBa
$stmt = $pdo->prepare("SELECT * FROM samples_diba WHERE modello_id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$dibaEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$model = $stmt->fetch(PDO::FETCH_ASSOC);
// Controlla se ci sono modifiche non comunicate 
$stmt = $pdo->prepare("SELECT * FROM samples_modelli WHERE id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$model = $stmt->fetch(PDO::FETCH_ASSOC);
$notifyEdits = $model['notify_edits'];
?>
<?php include BASE_PATH . "/components/header.php"; ?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Modifica WorkSheet -
                            <?php echo htmlspecialchars($model['id']); ?>
                        </li>
                    </ol>
                    <?php if ($notifyEdits == 1): ?>
                        <div class="alert alert-warning d-flex justify-content-between align-items-center"
                            id="notificationBanner">
                            <div>
                                <strong>Attenzione:</strong> Questo WorkSheet presenta modifiche non comunicate.
                            </div>
                            <div>
                                <!-- Pulsante per copiare l'URL -->
                                <button class="btn btn-success" id="copyUrl"
                                    data-url="<?php echo htmlspecialchars($dominio . '/functions/samples/printBolla?model_id=' . $modelId); ?>">
                                    <i class="fal fa-clipboard"></i> LINK
                                </button>
                                <!-- Pulsante per confermare la notifica -->
                                <button class="btn btn-primary" id="confirmNotify">
                                    <i class="fal fa-bell"></i> CONFERMA NOTIFICA
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-xl-8 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">WorkSheet</h6>
                                </div>
                                <div class="card-body">
                                    <form action="updateDiba" method="POST" id="dibaForm">
                                        <input type="hidden" name="model_id"
                                            value="<?php echo htmlspecialchars($modelId); ?>">
                                        <table class="table table-bordered table-responsive" id="dibaTable">
                                            <thead>
                                                <tr>
                                                    <th>Posizione</th>
                                                    <th>Descrizione</th>
                                                    <th>Note</th> <!-- Nuova colonna per le note -->
                                                    <th>Unità di Misura</th>
                                                    <th>Consumo</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dibaEntries as $entry): ?>
                                                    <?php if (!empty($entry['descrizione'])): ?>
                                                        <tr>
                                                            <td><input type="text" name="posizione[]" class="form-control"
                                                                    value="<?php echo htmlspecialchars($entry['posizione']); ?>"
                                                                    readonly>
                                                            </td>
                                                            <td><input type="text" name="descrizione[]" class="form-control"
                                                                    value="<?php echo htmlspecialchars($entry['descrizione']); ?>"
                                                                    readonly>
                                                                <input type="hidden" name="entry_id[]"
                                                                    value="<?php echo htmlspecialchars($entry['id']); ?>">
                                                            </td>
                                                            <td><input type="text" name="note[]" class="form-control"
                                                                    value="<?php echo htmlspecialchars($entry['note']); ?>">
                                                            </td>
                                                            <td><input type="text" name="unita_misura[]" class="form-control"
                                                                    value="<?php echo htmlspecialchars($entry['unita_misura']); ?>">
                                                            </td>
                                                            <td><input type="number" step="0.01" name="consumo[]"
                                                                    class="form-control"
                                                                    value="<?php echo htmlspecialchars($entry['consumo']); ?>">
                                                            </td>
                                                            <td class="text-center"><button type="button"
                                                                    class="btn btn-danger btn-sm remove-row"><i
                                                                        class="fal fa-trash-alt"></i></button></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <?php for ($i = count($dibaEntries); $i < 5; $i++): ?>
                                                    <tr>
                                                        <td><input type="text" name="posizione[]" class="form-control"></td>
                                                        <td><input type="text" name="descrizione[]" class="form-control">
                                                            <input type="hidden" name="entry_id[]" value="">
                                                        </td>
                                                        <td><input type="text" name="note[]" class="form-control">
                                                        </td>
                                                        <td><input type="text" name="unita_misura[]" class="form-control">
                                                        </td>
                                                        <td><input type="number" step="0.001" name="consumo[]"
                                                                class="form-control"></td>
                                                        <td class="text-center"><button type="button"
                                                                class="btn btn-danger btn-sm remove-row"><i
                                                                    class="fal fa-trash-alt "></i></button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                endfor; ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <button type="button" id="addRow" class="btn btn-primary btn-circle"><i
                                                class="fal fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Operazioni</h6>
                                </div>
                                <div class="card-body">

                                    <button type="submit" form="dibaForm" class="btn btn-success btn-block"><i
                                            class="fal fa-save"></i> Salva
                                    </button>
                                    <a class="btn btn-warning ml-auto btn-block"
                                        href="printBolla.php?model_id=<?php echo $modelId ?>">
                                        <i class="fal fa-download"></i> Scarica
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Dettagli Modello
                                        #<?php echo htmlspecialchars($modelId); ?></h6>
                                </div>
                                <div class="card-body">
                                    <form action="updateModel.php" method="POST" id="modelForm"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="model_id"
                                            value="<?php echo htmlspecialchars($modelId); ?>">
                                        <div class="form-group">
                                            <label for="nome_modello">Nome Modello</label>
                                            <input type="text" name="nome_modello" class="form-control"
                                                value="<?php echo htmlspecialchars($model['nome_modello'] ?? ''); ?>"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="variante">Variante</label>
                                            <input type="text" name="variante" class="form-control" rows="3"
                                                value="<?php echo htmlspecialchars($model['variante'] ?? ''); ?>"
                                                required>
                                        </div>

                                        <!-- New Row for Image and Date -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="immagine">Immagine</label>
                                                    <?php if (!empty($model['immagine'])): ?>
                                                        <div class="mb-3" id="imageContainer">
                                                            <img src="../../functions/samples/img/<?php echo htmlspecialchars($model['immagine']); ?>"
                                                                alt="Immagine Modello" class="img-thumbnail"
                                                                style="max-width: 200px; max-height: 200px;">
                                                            <button type="button" class="btn btn-danger btn-circle"
                                                                id="removeImage"><i class="fal fa-trash-alt"></i></button>
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" name="immagine" class="form-control-file"
                                                        id="immagine">
                                                    <input type="hidden" name="remove_immagine" value="0"
                                                        id="remove_immagine">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="forma">Forma</label>
                                                    <input type="text" name="forma" class="form-control"
                                                        value="<?php echo htmlspecialchars($model['forma'] ?? ''); ?>"
                                                        required>
                                                    <label for="consegna">Data di Consegna</label>
                                                    <input type="date" name="consegna" class="form-control"
                                                        value="<?php echo htmlspecialchars($model['consegna'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="note">Note</label>
                                                    <textarea name="note" class="form-control"
                                                        rows="3"><?php echo htmlspecialchars($model['note'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info"> <strong>Attenzione:</strong> i dati non salvati
                                            del WorkSheet
                                            andranno persi.</div>
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fal fa-save"></i> Conferma</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . "/components/scripts.php"; ?>
            <?php include_once BASE_PATH . "/components/footer.php"; ?>
        </div>
    </div>

    <script>
        // Aggiungi una nuova riga alla tabella
        document.getElementById('addRow').addEventListener('click', function () {
            var table = document.getElementById('dibaTable').getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();
            newRow.innerHTML = `
<tr>
<td><input type="text" name="posizione[]" class="form-control" ></td>
<td><input type="text" name="descrizione[]" class="form-control" >
    <input type="hidden" name="entry_id[]" value="">
</td>
 <td><input type="text" name="note[]" class="form-control" ></td>
<td><input type="text" name="unita_misura[]" class="form-control" ></td>
<td><input type="number" step="0.001" name="consumo[]" class="form-control" ></td>
<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fal fa-trash-alt"></i></button></td>
</tr>`;
        });

        // Rimuovi una riga dalla tabella
        document.getElementById('dibaTable').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-row')) {
                var row = event.target.closest('tr');
                row.parentNode.removeChild(row);
            }
        });

        // Rimuovi l'immagine del modello
        document.getElementById('removeImage')?.addEventListener('click', function () {
            var removeImmagineInput = document.getElementById('remove_immagine');
            var immagineInput = document.getElementById('immagine');
            removeImmagineInput.value = '1';
            immagine.parentNode.removeChild(immagineInput);
            var imageContainer = document.getElementById('imageContainer');
            imageContainer.parentNode.removeChild(imageContainer);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const confirmNotifyButton = document.getElementById('confirmNotify');
            const copyUrlButton = document.getElementById('copyUrl');

            if (confirmNotifyButton) {
                confirmNotifyButton.addEventListener('click', function () {
                    updateNotifyEdits(0);
                });
            }

            if (copyUrlButton) {
                copyUrlButton.addEventListener('click', function () {
                    const urlToCopy = copyUrlButton.getAttribute('data-url');
                    navigator.clipboard.writeText(urlToCopy).then(() => {
                        alert('Link WorkSheet copiato negli appunti!');
                    }).catch(err => {
                        console.error('Errore nella copia dell\'URL:', err);
                    });
                });
            }

            function updateNotifyEdits(newValue) {
                const modelId = "<?php echo $modelId; ?>";
                const url = "../../functions/samples/updateNotify.php"; // Assicurati che il percorso sia corretto
                const params = new URLSearchParams();
                params.append('model_id', modelId);
                params.append('notify_edits', newValue);

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('notificationBanner').style.display = 'none';
                            location.reload();
                        } else {
                            alert('Errore nell\'aggiornamento.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    </script>
</body>