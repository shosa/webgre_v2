<?php 
require_once '../../../config/config.php';
require_once BASE_PATH . '/utils/helpers.php';
$pdo = getDbInstance();
$queryInfo = "SELECT COUNT(*) as totalRows, MIN(Cartel) as minCartel, MAX(Cartel) as maxCartel FROM dati";
$stmtInfo = $pdo->query($queryInfo);
$info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
$totalRows = $info['totalRows'];
$minCartel = $info['minCartel'];
$maxCartel = $info['maxCartel'];
?>
<form id="uploadForm" action="forms/processing_UploadXLSX.php" method="post" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-light">
    <div class="form-group">
        <label for="file" class="font-weight-bold">Seleziona il file XLSX:</label>
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx" required>
            <label class="custom-file-label" for="file">Scegli file...</label>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-block btn-info mt-3">Importa</button>
        <div id="loader" class="spinner-border text-primary ml-3 mt-3" style="display: none;" role="status">
            <span class="sr-only">Caricamento in corso...</span>
        </div>
    </div>
</form>
<div class="alert alert-warning mt-4">
    <strong>Informazioni attuali su i dati:</strong><br>
    Numero di righe presenti: <?= htmlspecialchars($totalRows, ENT_QUOTES, 'UTF-8') ?><br>
    Cartellino minimo: <?= htmlspecialchars($minCartel, ENT_QUOTES, 'UTF-8') ?><br>
    Cartellino massimo: <?= htmlspecialchars($maxCartel, ENT_QUOTES, 'UTF-8') ?>
</div>