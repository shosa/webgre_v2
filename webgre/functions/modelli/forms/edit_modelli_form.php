<fieldset>
    <div class="form-group">
        <label for="ID">ID</label>
        <input type="text" name="ID"
            value="<?php echo htmlspecialchars($edit ? $modello['ID'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ID"
            class="form-control" required="required" id="ID" readonly>
    </div>
    <div class="form-group">
        <label for="Immagine">Scegli un'immagine</label>
        <input type="file" name="immagine" id="immagine" accept="image/*">
        </br>
        <img id="immagine-preview" src="" alt="Immagine anteprima"
            style="max-width: 200px; max-height: 200px; display: none; border: solid 1pt black;">
        </br>
        <button type="button" id="salva-immagine" class="btn btn-primary"
            data-codice="<?php echo htmlspecialchars($modello['codice'], ENT_QUOTES, 'UTF-8'); ?>">CARICA
            IMMAGINE</button>
        <input type="hidden" name="path_to_image" id="immagine-path"
            value="<?php echo htmlspecialchars($modello['path_to_image'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-group">
        <label for="Codice">Codice Modello</label>
        <input type="text" name="codice"
            value="<?php echo htmlspecialchars($edit ? $modello['codice'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="Codice Modello" class="form-control" required="required" id="codice" readonly>
    </div>
    <div class="form-group">
        <label for="Descrizione">Descrizione</label>
        <input type="text" name="descrizione"
            value="<?php echo htmlspecialchars($edit ? $modello['descrizione'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="Descrizione" class="form-control" required="required" id="descrizione">
    </div>
    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning">SALVA<span class="glyphicon glyphicon-send"></span></button>
    </div>
</fieldset>

<script>
    // Aggiungi un gestore di eventi per il caricamento dell'immagine
    document.getElementById('immagine').addEventListener('change', function () {
        const immaginePreview = document.getElementById('immagine-preview');
        const fileInput = document.getElementById('immagine');

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                immaginePreview.src = e.target.result;
                immaginePreview.style.display = 'block';
            };

            reader.readAsDataURL(file);
        } else {
            immaginePreview.src = '';
            immaginePreview.style.display = 'none';
        }
    });
    document.getElementById('salva-immagine').addEventListener('click', function () {
        const fileInput = document.getElementById('immagine');
        const immaginePathInput = document.getElementById('immagine-path');

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const formData = new FormData();

            // Recupera il valore di data-codice dal pulsante "Salva"
            const codiceModello = this.getAttribute('data-codice');

            // Modifica il nome del file basato su codiceModello
            const nuovoNomeFile = `${codiceModello}.${file.name.split('.').pop()}`;
            formData.append('immagine', file, nuovoNomeFile);

            // Invia l'immagine al server
            fetch('add_img.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Aggiorna il campo nascosto con il percorso dell'immagine appena salvata
                        immaginePathInput.value = `/src/img/${nuovoNomeFile}`;

                        alert('Immagine salvata!');
                        // Puoi aggiungere ulteriori azioni qui, come aggiornare l'anteprima o resettare l'input file
                    } else {
                        alert('Errore durante il caricamento dell\'immagine.');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                });
        } else {
            alert('Seleziona un\'immagine prima di cliccare su Salva.');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const immaginePreview = document.getElementById('immagine-preview');
        const immaginePathInput = document.getElementById('immagine-path');
        const pathToImage = immaginePathInput.value; // Recupera il valore da path_to_image

        if (pathToImage !== '') {
            immaginePreview.src = '../../' + pathToImage;
            immaginePreview.style.display = 'block';
        } else {
            immaginePreview.src = '../../src/img/default.jpg';
            immaginePreview.style.display = 'block';
        }
    });
</script>