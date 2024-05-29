<?php if ($_SESSION["admin_type"] !== "lavorante"): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#cqMenu">
            <i class="fad fa-box-check"></i> Controllo Qualita
            <i class="fas fa-chevron-right float-right"></i>
        </a>
        <ul class="nav flex-column metismenu collapse pl-4" id="cqMenu">
            <li class="nav-item">
                <a class="nav-link" href="../../functions/quality/new"><i class="fa fa-plus fa-fw"></i>
                    Nuova</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../functions/quality/read"><i class="fad fa-folder-tree"></i>
                    Consulta
                    <span class="badge badge-danger">New</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../../functions/quality/search"><i class="fad fa-search fa-fw"></i>
                    Ricerca</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fad fa-chart-pie"></i>
                    Reportistica</a>
            </li>
            <!-- INIZIO PARTE VISIBILE SOLO ALL'AMMINISTRAZIONE -->



        </ul>
    </li>
<?php endif; ?>