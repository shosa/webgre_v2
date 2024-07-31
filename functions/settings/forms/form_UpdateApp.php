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
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<div>
    <p>Clicca il bottone qui sotto per aggiornare l'app alla versione più recente.</p>
    <button id="updateAppBtn" class="btn btn-success p-2">Aggiorna</button>
    <div class="mt-4 p-4 border rounded shadow-sm bg-light">
        <label for="progressBar">Stato:</label>
        <progress id="progressBar" value="0" max="100" style="width: 100%;"></progress>
        <span id="progressText" class="font-italic">In attesa dell'avvio da parte del Utente...</span>
        <div id="spinner" class="spinner" style="display: none;"></div>
        <pre id="updateLog" class="mt-2 rounded" style="display: none; background-color: black; color:lime; padding: 10px; border: 1px solid #e0e0e0; max-height: 300px; overflow-y: auto;"></pre>
    </div>
</div>

