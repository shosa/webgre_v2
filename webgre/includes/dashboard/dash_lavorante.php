<?php
$db = getDbInstance();
$username = $_SESSION['username'];
$query = "SELECT SUM(QTA) AS sumQTA
FROM riparazioni
JOIN lab_user ON riparazioni.LABORATORIO = lab_user.lab
WHERE lab_user.user = '$username'";
$result = $db->rawQuery($query);
$sumQTA = (empty($result) || $result[0]['sumQTA'] === null) ? 0 : $result[0]['sumQTA'];

echo '<div class="col-lg-12">
    <h1 class="page-header">Dashboard</h1>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card bg-danger text-white">
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <i class="fad fa-copy fa-5x"></i>
                </div>
                <div class="col-9 text-right">
                    <div class="h1"><b>' . $sumQTA . '</div>
                    <div>Riparazioni assegnate</div>
                </div>
            </div>
        </div>
        <a href="../../functions/lab_riparazioni/lab_riparazioni.php" class="card-footer text-white">
            <span class="float-left">Apri Elenco</span>
            <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
            <div class="clearfix"></div>
        </a>
    </div>
</div>';
?>