<?php
require_once '../../../config/config.php';

$logFile = BASE_PATH . '/components/error_log.txt'; // Specifica il percorso del tuo file di log

// Inizializza il contenuto della textarea
$logContent = '';

// Verifica se il file esiste e leggi il contenuto
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
}
?>
<div id="responseMessage" class="mt-2"></div>
<div id="logForm" class="p-4 border rounded shadow-sm bg-light">
    <form id="logForm">
        <div class="form-group">
            <label for="logContent">Contenuto del log:</label>

            <textarea id="logContent" name="logContent" class="form-control"
                rows="20"><?php echo htmlspecialchars($logContent); ?></textarea>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <button type="button" id="saveBtn" class="btn btn-block btn-success"><i class="fal fa-floppy-disk"></i>
                    Salva</button>
            </div>
            <div class="col-xl-6 col-lg-6">
                <button type="button" id="clearBtn" class="btn btn-block btn-danger"><i class="fal fa-broom"></i>
                    Svuota</button>
            </div>
        </div>

    </form>
</div>