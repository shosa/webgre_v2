<fieldset>
    <!-- Form Name -->

    <!-- Text input-->
    <div style="padding:20px;">
        <div class="form-group">
            <label>Laboratorio</label>
            <?php
            // Esegui una query per ottenere i valori distinti dalla colonna "Nome" della tabella "laboratori"
            $laboratori = $db->rawQuery("SELECT DISTINCT Nome FROM laboratori ORDER BY Nome ASC");

            // Verifica se è in modalità di modifica e ottieni il valore corrente di "LABORATORIO"
            

            echo '<select name="lab" class="form-control selectpicker" required>';

            // Aggiungi un'opzione vuota come valore predefinito
            echo '<option value="" disabled selected>Seleziona un laboratorio</option>';

            // Genera le opzioni basate sui risultati della query
            foreach ($laboratori as $laboratorio) {
                $value = $laboratorio['Nome'];
                // Verifica se questa opzione è quella selezionata attualmente
            
                echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
            }

            echo '</select>';
            ?>
        </div>
        <div class="form-group">
            <label>Utente</label>
            <?php
            // Esegui una query per ottenere i valori distinti dalla colonna "user_name" della tabella "utenti"
            $utenti = $db->rawQuery("SELECT DISTINCT user_name FROM utenti  WHERE admin_type = 'lavorante' ORDER BY user_name ASC");

            // Verifica se è in modalità di modifica e ottieni il valore corrente di "utente"
            
            echo '<select name="user" class="form-control selectpicker" required>';

            // Aggiungi un'opzione vuota come valore predefinito
            echo '<option value="" disabled selected>Seleziona un utente</option>';

            // Genera le opzioni basate sui risultati della query
            foreach ($utenti as $utente) {
                $value = $utente['user_name']; // Utilizza 'user_name' anziché 'Nome'
                // Verifica se questa opzione è quella selezionata attualmente
            
                echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
            }

            echo '</select>';
            ?>
        </div>
    </div>


    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label"></label>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning">SALVA<span class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
</fieldset>