<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once '../../utils/helpers.php';
require_once '../../utils/log_utils.php';
?>
<style>
</style>
<?php include (BASE_PATH . "/components/header.php"); ?>
<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Inventario</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Strumenti per Inventario</li>
                    </ol>
                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Seleziona un Deposito</h6>
                            </div>
                            <div class="card-body">
                                <form action="inv_inventory.php" method="post" id="selectDepositoForm" class="mt-3">
                                    <div class="form-group">
                                        <label for="select_deposito">Deposito:</label>
                                        <select id="select_deposito" name="select_deposito" class="form-control">
                                            <?php
                                            // Ottieni l'istanza di PDO
                                            $db = getDbInstance();
                                            try {
                                                // Prepara e esegui la query
                                                $sql = "SELECT dep, des FROM inv_depositi";
                                                $stmt = $db->query($sql);
                                                // Recupera i risultati
                                                $depositi = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                // Itera sui risultati e crea le opzioni
                                                foreach ($depositi as $deposito) {
                                                    $dep = htmlspecialchars($deposito['dep']);
                                                    $des = htmlspecialchars($deposito['des']);
                                                    echo "<option value=\"$dep\">$dep | $des</option>";
                                                }
                                            } catch (PDOException $e) {
                                                // Gestisci l'errore
                                                echo "Errore: " . $e->getMessage();
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">AVANTI</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>
            <?php include_once (BASE_PATH . '/components/scripts.php'); ?>
            <?php include_once (BASE_PATH . '/components/footer.php'); ?>
        </div>
    </div>
</body>