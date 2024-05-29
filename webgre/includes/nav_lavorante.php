<?php if ($_SESSION["admin_type"] === "lavorante"): ?>
    <?php
    $username = $_SESSION["username"];
    $query = "SELECT laboratori.ID
                               FROM laboratori
                               WHERE laboratori.Nome = (
                                   SELECT lab_user.lab
                                   FROM lab_user
                                   WHERE lab_user.user = '$username'
                               )";
    $result = $db->rawQuery($query);
    $idLab = $result[0]["ID"];
    $query = "SELECT SUM(paia) AS sumQTA
                               FROM lanci
                               WHERE lanci.id_lab = '$idLab' AND stato != 'IN ATTESA'";
    $result = $db->rawQuery($query);
    if (
        empty($result) ||
        $result[0]["sumQTA"] === null
    ) {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_lanci/lab_lanci.php"><i class="fad fa-archive fa-fw"></i><b> Lanci</b></a></li>';
    } else {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_lanci/lab_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-archive fa-fw"></i><b> Lanci</b></span>
                                        <span class="badge badge-success">' .
            $result[0]["sumQTA"] .
            '</span>
                                    </a></li>';
    }
    ?>

    <li class="nav-item"><a class="nav-link" href="../../functions/lab_tools/working_items.php"><i
                class="fad fa-boot fa-fw"></i>
            Modelli in lavoro</a></li>

    <?php
    $username = $_SESSION["username"];
    $query = "SELECT laboratori.ID
                                FROM laboratori
                                WHERE laboratori.Nome = (
                                    SELECT lab_user.lab
                                    FROM lab_user
                                    WHERE lab_user.user = '$username'
                                )";
    $result = $db->rawQuery($query);
    $idLab = $result[0]["ID"];
    $query = "SELECT SUM(paia) AS sumQTA
                                FROM lanci
                                WHERE lanci.id_lab = '$idLab' AND stato = 'IN ATTESA'";
    $result = $db->rawQuery($query);
    if (
        empty($result) ||
        $result[0]["sumQTA"] === null
    ) {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_lanci/lab_wait_lanci.php"><i class="fad fa-clock fa-fw"></i> Lanci in arrivo</a></li>';
    } else {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_lanci/lab_wait_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-clock fa-fw"></i> Lanci in arrivo</span>
                                        <span class="badge badge-warning">' .
            $result[0]["sumQTA"] .
            '</span>
                                    </a></li>';
    }

    $username = $_SESSION["username"];
    $query = "SELECT SUM(QTA) AS sumQTA
                                FROM riparazioni
                                JOIN lab_user ON riparazioni.LABORATORIO = lab_user.lab
                                WHERE lab_user.user = '$username'";
    $result = $db->rawQuery($query);
    if (
        empty($result) ||
        $result[0]["sumQTA"] === null
    ) {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_riparazioni/lab_riparazioni.php"><i class="fad fa-screwdriver"></i><b> Riparazioni</b></a></li>';
    } else {
        echo '<li class="nav-item"><a class="nav-link" href="../../functions/lab_riparazioni/lab_riparazioni.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-screwdriver"></i><b> Riparazioni</b></span>
                                        <span class="badge badge-danger">' .
            $result[0]["sumQTA"] .
            '</span>
                                    </a></li>';
    }
    ?>

<?php endif; ?>