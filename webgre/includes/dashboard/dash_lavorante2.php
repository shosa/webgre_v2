<?php
$db = getDbInstance();
$username = $_SESSION['username'];
$query = "SELECT laboratori.ID
            FROM laboratori
            WHERE laboratori.Nome = (
                SELECT lab_user.lab
                FROM lab_user
                WHERE lab_user.user = '$username'
            )";
$result = $db->rawQuery($query);
$idLab = $result[0]['ID'];

$query = "SELECT SUM(paia) AS sumQTA
              FROM lanci
              WHERE lanci.id_lab = '$idLab' AND stato != 'IN ATTESA'";
$result = $db->rawQuery($query);
$sumQTA = (empty($result) || $result[0]['sumQTA'] === null) ? 0 : $result[0]['sumQTA'];
?>
<div class="col-lg-3 col-md-6">
    <div class="card bg-success text-white">
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <i class="fad fa-tasks fa-5x"></i>
                </div>
                <div class="col-9 text-right">
                    <b>
                        <div class="h1">
                            <?php echo $sumQTA; ?>
                        </div>
                        <div>Lanci in lavoro</div>
                </div>
            </div>
        </div>
    </div>
    <a href="../../functions/lab_lanci/lab_lanci.php" class="card-footer text-white">
        <span class="float-left">Gestici Avanzamento</span>
        <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
        <div class="clearfix"></div>
    </a>
</div>
</div>

<?php
$db = getDbInstance();
$username = $_SESSION['username'];
$query = "SELECT laboratori.ID
            FROM laboratori
            WHERE laboratori.Nome = (
                SELECT lab_user.lab
                FROM lab_user
                WHERE lab_user.user = '$username'
            )";
$result = $db->rawQuery($query);
$idLab = $result[0]['ID'];

$query = "SELECT SUM(paia) AS sumQTA
              FROM lanci
              WHERE lanci.id_lab = '$idLab' AND stato = 'IN ATTESA'";
$result = $db->rawQuery($query);
$sumQTA = (empty($result) || $result[0]['sumQTA'] === null) ? 0 : $result[0]['sumQTA'];
?>
<div class="col-lg-3 col-md-6">
    <div class="card bg-warning text-white">
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <i class="fad fa-clock fa-5x"></i>
                </div>
                <div class="col-9 text-right">
                    <b>
                        <div class="h1">
                            <?php echo $sumQTA; ?>
                        </div>
                        <div>Lanci in fase di preparazione</div>
                </div>
            </div>
        </div>
    </div>
    <a href="../../functions/lab_lanci/lab_wait_lanci.php" class="card-footer text-white">
        <span class="float-left">Controlla</span>
        <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
        <div class="clearfix"></div>
    </a>
</div>
</div>