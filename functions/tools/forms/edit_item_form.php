<div class="row align-items-center">
    <div class="col-lg-6">
        <h2 class="page-header page-action-links text-left">Modifica Riga #
            <?php echo $inventario_id ?>
        </h2>
    </div>
    <div class="col-lg-6">
        <div class="page-action-links text-right">
            <button type="submit" class="btn btn-warning" style="font-size:20pt;"><i class="fad fa-save"></i><span
                    class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
</div>

<hr>
<fieldset>
    <div class="form-group">
        <label for="ID">ID</label>
        <input type="text" name="ID"
            value="<?php echo htmlspecialchars($edit ? $inventario['ID'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="ID" class="form-control" required="required" id="ID" readonly>
    </div>

    <div class="form-group">
        <label for="ID">Categoria Merceologica</label>
        <input type="text" name="cm"
            value="<?php echo htmlspecialchars($edit ? $inventario['cm'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="cm" class="form-control" required="required" id="cm" readonly>
    </div>

    <div class="form-group">
        <label for="Articolo">Codice</label>
        <input type="text" name="art"
            value="<?php echo htmlspecialchars($edit ? $inventario['art'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="art" class="form-control" required="required" id="art" readonly>
    </div>
    <div class="form-group">
        <label for="Codice">Descrizione</label>
        <input type="text" name="des"
            value="<?php echo htmlspecialchars($edit ? $inventario['des'] : '', ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="des" class="form-control" required="required" id="des" readonly>
    </div>

    <div class="form-group">

        <div class="form-group">
            <label for="qta">Qta</label>
            <input type="text" name="qta"
                value="<?php echo htmlspecialchars($edit ? $inventario['qta'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="qta" class="form-control" required="required" id="qta">
        </div>

        <div class="form-group text-center">
            <label></label>
            <button type="submit" class="btn btn-warning">Salva <span class="glyphicon glyphicon-send"></span></button>
        </div>
</fieldset>