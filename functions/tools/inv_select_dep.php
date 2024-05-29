<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
include BASE_PATH . '/includes/header-nomenu.php';

?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Strumenti per Inventario</h1>
        </div>
        <hr>
    </div>

    <div class="row ml-3">
        <div class="col-md-6">
            <h2 class="mt-3">Seleziona un Deposito</h2>
            <form action="inv_inventory.php" method="post" id="selectDepositoForm" class="mt-3">
                <div class="form-group">
                    <label for="select_deposito">Deposito:</label>
                    <select id="select_deposito" name="select_deposito" class="form-control">
                        <?php
                        // Utilizza la variabile $db per eseguire la query
                        $db = getDbInstance();
                        $depositi = $db->get('inv_depositi', null, ['dep', 'des']);

                        foreach ($depositi as $deposito) {
                            $dep = $deposito['dep'];
                            $des = $deposito['des'];
                            echo "<option value=\"$dep\">$dep | $des</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">AVANTI</button>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/flash_messages.php'; ?>
</div>

<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>