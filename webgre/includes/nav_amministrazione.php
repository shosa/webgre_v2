<?php
$db = getDbInstance();
$querySettings = "SELECT value FROM settings WHERE item = 'modulo_produzione'";
$resultSettings = $db->rawQuery($querySettings);
$moduloProduzioneValue = !empty($resultSettings) ? intval($resultSettings[0]["value"]) : 0;
if ($moduloProduzioneValue == 1): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#lanciMenu">
            <i class="fad fa-archive fa-fw"></i> Gestione Lanci
            <i class="fas fa-chevron-right float-right"></i>
        </a>
        <ul class="nav flex-column metismenu collapse pl-4" id="lanciMenu">
            <li class="nav-item">
                <a class="nav-link" href="../../functions/lanci/add_lancio.php"><i class="fa fa-plus fa-fw"></i>
                    Nuovo Lancio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/lanci/lanci.php"><i class="fad fa-list fa-fw"></i>
                    Elenco Lanci</a>
            </li>
            <?php
            $query = "SELECT SUM(paia) AS sumQTA
                          FROM lanci
                          WHERE stato = 'IN ATTESA'";
            $result = $db->rawQuery($query);
            if (empty($result) || $result[0]["sumQTA"] === null) {
                echo '<li class="nav-item"><a class="nav-link" href="../../functions/lanci/wait_lanci.php"><i class="fad fa-clock fa-fw"></i> Lanci in preparazione</a></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link" href="../../functions/lanci/wait_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-clock fa-fw"></i> Lanci in preparazione</span>
                                        <span class="badge" style="background-color: #e8a71a;">' .
                    $result[0]["sumQTA"] .
                    '</span>
                                    </a></li>';
            }
            ?>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#monitorMenu">
            <i class="fad fa-eye"></i> Monitor Produzione
            <i class="fas fa-chevron-right float-right"></i>
        </a>
        <ul class="nav flex-column metismenu collapse pl-4" id="monitorMenu">
            <li class="nav-item">
                <a class="nav-link" href="../../functions/monitor/monitor_generale.php"><i class="fad fa-chart-pie"></i>
                    Situazione Generale</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/monitor/monitor_laboratori.php"><i class="fad fa-tv"></i>
                    Situazione Laboratori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/monitor/monitor_marchi.php"><i class="fad fa-tv"></i>
                    Situazione Marchio</a>
            </li>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../../functions/modelli/modelli.php"><i class="fad fa-book fa-fw"></i>
            Modelli</a>
    </li>
<?php endif; ?>

<li class="nav-item">
    <a class="nav-link" data-toggle="collapse" href="#riparazioniMenu">
        <i class="fas fa-folder fa-fw"></i> Riparazioni
        <i class="fas fa-chevron-right float-right"></i>
    </a>
    <ul class="nav flex-column metismenu collapse pl-4" id="riparazioniMenu">
        <li class="nav-item">
            <a class="nav-link" href="../../functions/riparazioni/add_step1.php"><i class="fa fa-plus fa-fw"></i>
                Nuova</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../functions/riparazioni/riparazioni.php"><i class="fad fa-list fa-fw"></i>
                Elenco</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../functions/riparazioni/close_barcode.php"><i
                    class="fad fa-scanner fa-fw"></i>
                Completa Più (Barcode)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../../functions/riparazioni/make_plist.php"><i class="fad fa-stream fa-fw"></i>
                Crea Packing List</a>
        </li>
    </ul>
</li>
<?php
$querySettings = "SELECT value FROM settings WHERE item = 'modulo_export'";
$resultSettings = $db->rawQuery($querySettings);
$moduloExportValue = !empty($resultSettings) ? intval($resultSettings[0]["value"]) : 0;
if ($moduloExportValue == 1): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#exportMenu">
            <i class="fad fa-globe-americas fa-fw"></i> Export
            <i class="fas fa-chevron-right float-right"></i>
        </a>
        <ul class="nav flex-column metismenu collapse pl-4" id="exportMenu">
            <li class="nav-item">
                <a class="nav-link" href="../../functions/export/new_step1.php"><i class="fa fa-plus fa-fw"></i>
                    Nuovo DDT</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/export/registro_export.php"><i class="fad fa-list fa-fw"></i>
                    Registro</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/export/terzisti.php"><i class="fad fa-id-card fa-fw"></i>
                    Terzisti</a>
            </li>
        </ul>
    </li>
<?php endif ?>
<!-- FINE PARTE VISIBILE SOLO ALL'AMMINISTRAZIONE -->