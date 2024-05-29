<fieldset>
    <!-- Form Name -->
    <legend>Aggiunta Linea</legend>
    <!-- Text input-->
    <div style="padding:20px;">
        <div class="form-group">
            <label>Sigla</label>
            <input type="text" name="sigla" value="" placeholder="Sigla XX" class="form-control" required="required"
                maxlength="2" id="sigla">
        </div>
        <div class="form-group">
            <label>Marchio</label>
            <input type="text" name="descrizione" value="" placeholder="Nome completo Marchio" class="form-control"
                required="required" id="descrizione">
        </div>
    </div>
    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label"></label>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning">INSERISCI <span
                    class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
</fieldset>