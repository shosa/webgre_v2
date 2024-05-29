<fieldset>

    <!-- Text input-->
    <div style="padding:20px;" name="inserimento">
        <div class="row">
            <div class="col-md-1">
                <div class="form-group">
                    <label for="ID">ID</label>
                    <input type="text" name="ID" value="" placeholder="XX" class="form-control" required="required"
                        id="ID">
                </div>
            </div>
        </div>
        <div class="row">
            <?php
            for ($i = 1; $i <= 10; $i++) {
                $columnName = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT);

                echo '<div class="col-md-1">
            <div class="form-group">
                <label for="' . $columnName . '">' . $columnName . '</label>
                <input style="width: 50px;" type="text" name="' . $columnName . '" value="" placeholder="XX" class="form-control" required="required" id="' . $columnName . '">
            </div>
          </div>';
            }
            ?>
        </div>
        <div class="row">
            <?php
            for ($i = 11; $i <= 20; $i++) {
                $columnName = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT);

                echo '<div class="col-md-1">
            <div class="form-group">
                <label for="' . $columnName . '">' . $columnName . '</label>
                <input style="width: 50px;" type="text" name="' . $columnName . '" value="" placeholder="XX" class="form-control" required="required" id="' . $columnName . '">
            </div>
          </div>';
            }
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