<?php if ($_SESSION["admin_type"] === "super" || $_SESSION["admin_type"] === "admin"): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#tablesMenu">
            <i class="fa fa-table fa-fw"></i> Tabelle
            <i class="fas fa-chevron-right float-right"></i>
        </a>
        <ul class="nav flex-column metismenu collapse pl-4" id="tablesMenu">
            <li class="nav-item">
                <a class="nav-link" href="../../functions/tables/linee.php"><i class="fad fa-copyright"></i> Linee</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/tables/laboratori.php"><i class="fad fa-flask"></i> Laboratori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/tables/id_numerate.php"><i class="fad fa-sort-size-down-alt"></i>
                    Numerate</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/tables/lab_user.php"><i class="fa fa-arrows-h fa-fw"></i>
                    Laboratorio - Utente</a>
            </li>
        </ul>
    </li>
<?php endif;

if ($_SESSION["admin_type"] === "super"): ?>
    <li class="nav-item">
        <a class="nav-link" href="../../functions/users/admin_users.php"><i class="fad fa-user fa-fw"></i> Utenti</a>
    </li>
    <!-- <li class="nav-item">
        <a class="nav-link" href="../../mysql/index.php"><i class="fad fa-database fa-fw"></i> Pannello MySQL</a>
    </li> -->
<?php endif; ?>