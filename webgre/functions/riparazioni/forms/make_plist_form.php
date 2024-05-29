<fieldset>
    <legend>Inserisci i numeri cedola da includere</legend>
    <div class="form-group">
        <table class="table table">
            <tbody>
                <?php for ($row = 1; $row <= 10; $row++): ?>
                    <tr style="width:60%">
                        <?php for ($col = 1; $col <= 10; $col++): ?>
                            <td><input style="padding:4px;" type="number" name="idrip<?php echo ($row - 1) * 10 + $col; ?>"
                                    class="form-control"></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <div class="form-group">
        <div class="col-md-12">
            <button type="submit" style="width:100%;font-weight:bold;" class="btn btn-info">GENERA <i
                    class="fad fa-download"></i></button>
        </div>
    </div>
</fieldset>