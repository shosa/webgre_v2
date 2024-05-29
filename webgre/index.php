<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';
$tipoUtente = $_SESSION['admin_type'];
// Get DB instance. function is defined in config.php
$db = getDbInstance();

// Get Dashboard information
$numRiparazioni = $db->getValue("riparazioni", "Sum(QTA)");
$db->Where("stato", "IN ATTESA");
$numDaCompletare = $db->getValue("lanci", "Sum(paia)");
$db->Where("user_name", $_SESSION["username"]);
$nome = $db->getValue("utenti", "nome");

include_once ('includes/header.php');
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Benvenuto/a, <i style="color:blue;"><?php echo $nome; ?> </i></h1>
        </div>
    </div>
    <hr>
    <div class="row">
        <?php if ($_SESSION['admin_type'] !== 'operatore'): ?>
            <?php if ($_SESSION['admin_type'] !== 'lavorante'):
                include ("includes/dashboard/dash_amministrazione.php");
            endif ?>
        <?php endif ?>
    </div>
    <!-- TAB RIPARAZIONI solo per LAVORANTE -->
    <div class="row">
        <?php
        if ($tipoUtente == "lavorante") {
            include ("includes/dashboard/dash_lavorante.php");
        }
        ?>
    </div>
    <div class="row">
        <?php
        if ($tipoUtente == "lavorante"):
            include ("includes/dashboard/dash_lavorante2.php");
        endif ?>
    </div>
    <!-- FINE TAB RIPARAZIONI solo per LAVORANTE -->
</div>

</div>
<!-- /#page-wrapper -->

<?php include_once ('includes/footer.php'); ?>