<form id="uploadForm" action="importDatiXlsx.php" method="post" enctype="multipart/form-data"
    class="p-4 border rounded shadow-sm bg-light">
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