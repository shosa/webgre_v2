<style>
    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left: 4px solid #4e73df;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
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
<?php
require_once '../../../config/config.php';
$conn = getDbInstance();
function getToken($conn, $item)
{
    $sql = "SELECT value FROM settings WHERE item = :item";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':item', $item);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
$token = getToken($conn, 'github_token');
?>
<div>
    <p>Clicca il bottone per aggiornare l'app alla versione più recente.</p>
    <button id="updateAppBtn" class="btn btn-success ml-2">Aggiorna</button>
    <div id="spinner" class="spinner" style="display: none;"></div>
    <div class="mt-4 p-4 border rounded shadow-sm m-2 bg-light">
        <label for="progressBar">Stato:</label>
        <progress id="progressBar" value="0" max="100" style="width: 100%;"></progress>
        <span id="progressText" class="font-italic">In attesa dell'avvio da parte del Utente...</span>
        <pre id="updateLog" class="mt-2 rounded"
            style="display: none; background-color: black; color:lime; padding: 10px; border: 1px solid #e0e0e0; max-height: 300px; overflow-y: auto;"></pre>
    </div>
    <div class="accordion p-2" id="accordionToken">
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"
                        aria-expanded="true" aria-controls="collapseOne">
                        Token GitHub
                    </button>
                </h5>
            </div>

            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionToken">
                <div class="card-body">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($token['value']) ?>"
                        data-field="token" readonly>
                </div>
            </div>
        </div>
    </div>
</div>