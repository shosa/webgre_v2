<hr>
<style>
    /* Rimuove le freccette su e giù dai campi di input di tipo number */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    /* Larghezza uniforme per i campi di input */
    .input-number {
        width: 100%;
        /* Larghezza del campo di input */
    }

    /* Imposta la larghezza delle colonne della tabella */
    .table th,
    .table td {
        min-width: 80px;
        /* Larghezza minima delle colonne della tabella */
    }
</style>
<fieldset>
    <div class="form-group">
        <label for="ID">ID</label>
        <input type="text" name="IDRIP"
            value="<?php echo htmlspecialchars($edit ? $riparazione['IDRIP'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="IDRIP" class="form-control" required="required" id="IDRIP" readonly>
    </div>


    <div class="form-group">
        <label for="Articolo">Articolo</label>
        <input type="text" name="ARTICOLO"
            value="<?php echo htmlspecialchars($edit ? $riparazione['ARTICOLO'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="ARTICOLO" class="form-control" required="required" id="ARTICOLO" readonly>
    </div>
    <div class="form-group">
        <label for="Codice">Codice Articolo</label>
        <input type="text" name="CODICE"
            value="<?php echo htmlspecialchars($edit ? $riparazione['CODICE'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="CODICE" class="form-control" required="required" id="CODICE" readonly>
    </div>

    <div class="form-group">

        <label>Numerata</label>
        <div class="table-responsive ">
            <table class="table table-bordered table-condensed border-left-info text-center"
                style="border: solid 1pt black;">
                <thead>
                    <tr>
                        <?php
                        // Recupera i dati dalla tabella id_numerate utilizzando PDO
                        $stmt = $pdo->prepare("SELECT * FROM id_numerate WHERE ID = :id");
                        $stmt->bindParam(':id', $riparazione['NU']);
                        $stmt->execute();
                        $idNumerateData = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Cicla attraverso i campi N01, N02, ecc. e crea le celle della tabella
                        for ($i = 1; $i <= 20; $i++) {
                            $fieldName = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisci il nome del campo N01, N02, ecc.
                            // Aggiungi l'attributo 'disabled' ai campi N01, N02, ecc.
                            echo '<td class="text-center"><span>' . htmlspecialchars($idNumerateData[$fieldName], ENT_QUOTES, 'UTF-8') . '</span></td>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <!--CARICAMENTO VALORI P01...P20-->
                        <?php
                        for ($i = 1; $i <= 20; $i++) {
                            $fieldName = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisci il nome del campo P01, P02, ecc.
                            $fieldValue = isset($riparazione[$fieldName]) ? htmlspecialchars($riparazione[$fieldName], ENT_QUOTES, 'UTF-8') : '';
                            echo '<td style="min-width: 100px;"><input type="number" name="' . $fieldName . '" value="' . $fieldValue . '" class="form-control input-number"></td>';
                        }
                        ?>


                    </tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <label for="cartellino">Cartellino</label>
            <input type="text" name="cartellino"
                value="<?php echo htmlspecialchars($edit ? $riparazione['CARTELLINO'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Last Name" class="form-control" required="required" id="cartellino" readonly>
        </div>

        <div class="form-group">
            <label>REPARTO</label>
            <?php
            // Esegui una query per ottenere i valori distinti dalla colonna "Nome" della tabella "reparti"
            $stmt = $pdo->query("SELECT Nome FROM reparti");
            $reparti = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verifica se è in modalità di modifica e ottieni il valore corrente di "REPARTO"
            $currentReparto = isset($riparazione['REPARTO']) ? $riparazione['REPARTO'] : '';

            echo '<select name="reparto" class="form-control selectpicker" required>';

            // Genera le opzioni basate sui risultati della query
            foreach ($reparti as $reparto) {
                $value = $reparto['Nome'];
                $selected = ($currentReparto === $value) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
            }

            echo '</select>';
            ?>
        </div>


        <div class="form-group">
            <label for="causale">Causale</label>
            <textarea name="causale" placeholder="causale" class="form-control"
                id="causale"><?php echo htmlspecialchars(($edit) ? $riparazione['CAUSALE'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="form-group">
            <label>LABORATORIO</label>
            <?php
            // Esegui una query per ottenere i valori distinti dalla colonna "Nome" della tabella "laboratori"
            $stmt = $pdo->query("SELECT Nome FROM laboratori");
            $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verifica se è in modalità di modifica e ottieni il valore corrente di "LABORATORIO"
            $currentLaboratorio = isset($riparazione['LABORATORIO']) ? $riparazione['LABORATORIO'] : '';

            echo '<select name="laboratorio" class="form-control selectpicker" required>';

            // Genera le opzioni basate sui risultati della query
            foreach ($laboratori as $laboratorio) {
                $value = $laboratorio['Nome'];
                $selected = ($currentLaboratorio === $value) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
            }

            echo '</select>';
            ?>
        </div>
        <div class="form-group">
            <label>Urgenza</label>
            <select name="urgenza" class="form-control selectpicker" required>
                <option value="BASSA" <?php echo ($riparazione['URGENZA'] == 'BASSA') ? 'selected' : ''; ?>>BASSA</option>
                <option value="MEDIA" <?php echo ($riparazione['URGENZA'] == 'MEDIA') ? 'selected' : ''; ?>>MEDIA</option>
                <option value="ALTA" <?php echo ($riparazione['URGENZA'] == 'ALTA') ? 'selected' : ''; ?>>ALTA</option>
            </select>
        </div>


        <div class="form-group">
            <label for="data">Data</label>
            <input type="data" name="data"
                value="<?php echo htmlspecialchars($edit ? $riparazione['DATA'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="E-Mail cartellino" class="form-control" id="data">
        </div>

        <div class="form-group">
            <label for="numerata">Numerata</label>
            <input name="numerata"
                value="<?php echo htmlspecialchars($edit ? $riparazione['NU'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="987654321" class="form-control" type="text" id="numerata" readonly>
        </div>

        <div class="form-group">
            <label>Utente</label>
            <input name="utente"
                value="<?php echo htmlspecialchars($edit ? $riparazione['UTENTE'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Birth date" class="form-control" type="utente" readonly>
        </div>

        <div class="form-group">
            <label>Cliente</label>
            <input name="cliente"
                value="<?php echo htmlspecialchars($edit ? $riparazione['CLIENTE'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Birth date" class="form-control" type="cliente" readonly>
        </div>

        <div class="form-group">
            <label>Commessa</label>
            <input name="commessa"
                value="<?php echo htmlspecialchars($edit ? $riparazione['COMMESSA'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Birth date" class="form-control" type="commessa" readonly>
        </div>

        <div class="form-group">
            <label>Linea</label>
            <input name="linea"
                value="<?php echo htmlspecialchars($edit ? $riparazione['LINEA'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Birth date" class="form-control" type="linea" readonly>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-warning">Salva <i class="fa-solid fa-floppy-disk"></i></span></button>
        </div>
</fieldset>