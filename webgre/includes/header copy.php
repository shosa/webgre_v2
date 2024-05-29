<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Web G.R.E</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link href="/assets/js/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="/assets/css/sb-admin-2.css" rel="stylesheet">
    <link href="/assets/fonts/font-awesome/css/new.min.css" rel="stylesheet" type="text/css">
    <script src="/assets/js/jquery.min.js" type="text/javascript"></script>
</head>

<body>
    <div id="wrapper">
        <?php $db = getDbInstance(); ?>
        <!-- Navigation -->
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Navigazione</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="">
                        <?php echo isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Web - Gestionale Relazioni Emmegiemme'; ?>
                    </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">

                    <li><a href="../../logout.php"><i class="fa fa-sign-out fa-fw"></i>Logout</a>

                </ul>
                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav navbar-collapse">
                        <ul class="nav" id="side-menu">
                            <li>
                                <a href="../../index.php"><i class="fas fa-tachometer-alt fa-fw"></i> Dashboard</a>
                            </li>
                            <!-- INIZIO PARTE VISIBILE SOLO ALL'AMMINISTRAZIONE -->
                            <?php if ($_SESSION['admin_type'] !== 'lavorante'): ?>
                                <li <?php echo (CURRENT_PAGE == "lanci.php" || CURRENT_PAGE == "add_lancio.php") || CURRENT_PAGE == "working_items.php" ? 'class="active"' : ''; ?>>
                                    <a href="../../functions/lanci/lanci.php"><i class="fad fa-archive fa-fw"></i> Lanci<span
                                            class="fa arrow"></span></a>
                                    <ul class="nav nav-second-level">
                                        <li>
                                            <a href="../../functions/lanci/add_lancio.php"><i class="fa fa-plus fa-fw"></i>
                                                Nuovo</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/lanci/lanci.php"><i class="fad fa-list fa-fw"></i>
                                                Elenco</a>
                                        </li>
                                        <?php
                                        $query = "SELECT SUM(paia) AS sumQTA
                                        FROM lanci
                                        WHERE  stato = 'IN ATTESA'";
                                        $result = $db->rawQuery($query);
                                        if (empty($result) || $result[0]['sumQTA'] === null) {
                                            echo '<li><a href="../../functions/lanci/wait_lanci.php"><i class="fad fa-clock fa-fw"></i> Lanci in preparazione</a></li>';
                                        } else {
                                            echo '<li><a href="../../functions/lanci/wait_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-clock fa-fw"></i> Lanci preparazione</span>
                                        <span class="badge" style="background-color: #e8a71a;">' . $result[0]['sumQTA'] . '</span>
                                    </a></li>';
                                        } ?>
                                    </ul>
                                </li>
                                <li <?php echo (CURRENT_PAGE == "monitor_laboratori.php" || CURRENT_PAGE == "monitor_generale.php") || CURRENT_PAGE == "monitor_marchi.php" ? 'class="active"' : ''; ?>>
                                    <a href="../../functions/monitor/monitor_generale.php.php"><i class="fad fa-eye"></i>
                                        Monitor Produzione<span class="fa arrow"></span></a>

                                    <ul class="nav nav-second-level">
                                        <li>
                                            <a href="../../functions/monitor/monitor_generale.php"><i
                                                    class="fad fa-chart-pie"></i>
                                                Situazione
                                                Generale</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/monitor/monitor_laboratori.php"><i class="fad fa-tv"></i>
                                                Situazione
                                                Laboratori</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/monitor/monitor_marchi.php"><i class="fad fa-tv"></i>
                                                Situazione
                                                Marchio</a>
                                        </li>
                                    </ul>
                                </li>

                                <li <?php echo (CURRENT_PAGE == "riparazioni.php" || CURRENT_PAGE == "add_step1.php" || CURRENT_PAGE == "add_step2.php" || CURRENT_PAGE == "edit_riparazioni.php" || CURRENT_PAGE == "close_barcode.php") ? 'class="active"' : ''; ?>>
                                    <a href="../../functions/riparazioni/riparazioni.php"><i class="fad fa-screwdriver"></i>
                                        Riparazioni<span class="fa arrow"></span></a>
                                    <ul class="nav nav-second-level">
                                        <li>
                                            <a href="../../functions/riparazioni/add_step1.php"><i class="fa fa-plus fa-fw"></i>
                                                Nuova</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/riparazioni/riparazioni.php"><i
                                                    class="fad fa-list fa-fw"></i> Elenco</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/riparazioni/close_barcode.php"><i
                                                    class="fad fa-scanner fa-fw"></i> Completa Più (Barcode)</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/riparazioni/make_plist.php"><i
                                                    class="fad fa-stream fa-fw"></i></i> Crea Packing List</a>
                                        </li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="../../functions/modelli/modelli.php"><i class="fad fa-book fa-fw"></i> Modelli</a>
                                </li>

                            <?php endif; ?>
                            <!-- FINE PARTE VISIBILE SOLO ALL'AMMINISTRAZIONE -->
                            <?php
                            if ($_SESSION['admin_type'] === 'lavorante'): ?>
                                <?php
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
                                if (empty($result) || $result[0]['sumQTA'] === null) {
                                    echo '<li><a href="../../functions/lab_lanci/lab_lanci.php"><i class="fad fa-archive fa-fw"></i><b> Lanci</b></a></li>';
                                } else {
                                    echo '<li><a href="../../functions/lab_lanci/lab_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-archive fa-fw"></i><b> Lanci</b></span>
                                        <span class="badge" style="background-color: #50b35a;">' . $result[0]['sumQTA'] . '</span>
                                    </a></li>';
                                }
                                ?>

                                <li><a href="../../functions/lab_tools/working_items.php"><i class="fad fa-boot fa-fw"></i>
                                        Modelli in lavoro</a></li>
                                <?php
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
                                if (empty($result) || $result[0]['sumQTA'] === null) {
                                    echo '<li><a href="../../functions/lab_lanci/lab_wait_lanci.php"><i class="fad fa-clock fa-fw"></i> Lanci in arrivo</a></li>';
                                } else {
                                    echo '<li><a href="../../functions/lab_lanci/lab_wait_lanci.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-clock fa-fw"></i> Lanci in arrivo</span>
                                        <span class="badge" style="background-color: #e8a71a;">' . $result[0]['sumQTA'] . '</span>
                                    </a></li>';
                                }

                                $username = $_SESSION['username'];
                                $query = "SELECT SUM(QTA) AS sumQTA
                                FROM riparazioni
                                JOIN lab_user ON riparazioni.LABORATORIO = lab_user.lab
                                WHERE lab_user.user = '$username'";
                                $result = $db->rawQuery($query);
                                if (empty($result) || $result[0]['sumQTA'] === null) {
                                    echo '<li><a href="../../functions/lab_riparazioni/lab_riparazioni.php"><i class="fad fa-screwdriver"></i><b> Riparazioni</b></a></li>';
                                } else {
                                    echo '<li><a href="../../functions/lab_riparazioni/lab_riparazioni.php" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fad fa-screwdriver"></i><b> Riparazioni</b></span>
                                        <span class="badge" style="background-color: #eb463b;">' . $result[0]['sumQTA'] . '</span>
                                    </a></li>';
                                }
                                ?>

                            <?php endif;
                            if ($_SESSION['admin_type'] === 'super' || $_SESSION['admin_type'] === 'admin'): ?>
                                <li <?php echo (CURRENT_PAGE == "lab_user.php" || CURRENT_PAGE == "id_numerate.php") ? 'class="active"' : ''; ?>>
                                    <a href="../../functions/tables/linee.php"><i class="fa fa-table fa-fw"></i>Tabelle<span
                                            class="fa arrow"></span></a>
                                    <ul class="nav nav-second-level">
                                        <li>
                                            <a href="../../functions/tables/linee.php"><i class="fad fa-copyright"></i>
                                                Linee</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/tables/laboratori.php"><i class="fad fa-flask"></i>
                                                Laboratori</a>
                                        </li>
                                        <li>
                                            <a href="../../functions/tables/id_numerate.php"><i
                                                    class="fad fa-sort-size-down-alt"></i></i>
                                                Numerate</a>
                                        </li>

                                        <li>
                                            <a href="../../functions/tables/lab_user.php"><i class="fa fa-arrows-h fa-fw"></i>
                                                Laboratorio -
                                                Utente</a>
                                        </li>
                                    </ul>
                                </li>

                            <?php endif;
                            if ($_SESSION['admin_type'] === 'super'): ?>
                                <li><a href="../../functions/users/admin_users.php"><i class="fad fa-user fa-fw"></i> Utenti</a>
                                </li>
                                <li><a href="../../mysql/index.php"><i class="fad fa-database fa-fw"></i> Pannello MySQL</a>
                                </li>

                            <?php endif; ?>
                        </ul>
                    </div>
                    <!-- /.sidebar-collapse -->
                </div>
                <!-- /.navbar-static-side -->
            </nav>
        <?php endif; ?>
        <!-- The End of the Header -->
    </div>
</body>