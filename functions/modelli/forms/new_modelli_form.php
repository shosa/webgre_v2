<fieldset>
    <div class="form-group">
        <label for="ID">ID</label>
        <input type="text" name="ID" value="<?php echo $newID; ?>" placeholder="ID" class="form-control"
            required="required" id="ID" readonly>
    </div>
    <div class="form-group">
        <label for="Linea">Linea</label>
        <select name="linea" class="form-control" required="required" id="linea">
            <option value="" disabled selected hidden>Scegli dal menu a tendina</option>
            <?php
            // Esegui una query per ottenere i dati dalla tabella linee
            $db = getDbInstance();
            $linee = $db->get('linee');

            // Cicla attraverso i dati e crea le opzioni del select
            foreach ($linee as $linea) {
                echo '<option value="' . $linea['sigla'] . '">' . $linea['descrizione'] . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="Codice">Codice Modello</label>
        <input type="text" name="codice" value="" placeholder="Codice Modello" class="form-control" required="required"
            id="codice">
    </div>
    <div class="form-group">
        <label for="Descrizione">Descrizione</label>
        <input type="text" name="descrizione" value="" placeholder="Descrizione" class="form-control"
            required="required" id="descrizione" required="required">
    </div>
    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning">SALVA<span class="glyphicon glyphicon-send"></span></button>
    </div>
</fieldset>

<script>

</script>