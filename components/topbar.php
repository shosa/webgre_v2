<!-- Topbar Search -->
<?php

$pdoTopbar = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$pdoTopbar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdoTopbar->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn(); ?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i
            class="fa fa-bars text-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"></i>
    </button>

    <form action="<?php BASE_PATH ?>/spotlight.php" method="GET"
        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" name="terms" class="form-control bg-light border-0 small" placeholder="Cerca..."
                aria-label="Search" aria-describedby="basic-addon2" required>
            <div class="input-group-append">
                <button
                    class="btn btn-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"
                    type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>


    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i
                    class="fas fa-search fa-fw text-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form action="<?php BASE_PATH ?>/spotlight.php" method="GET"
                    class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" name="terms" class="form-control bg-light border-0 small"
                            placeholder="Cerca..." aria-label="Search" aria-describedby="basic-addon2" required>
                        <div class="input-group-append">
                            <button
                                class="btn btn-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"
                                type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">0</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Centro Notifiche
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">Oggi</div>
                        Nessuna nuova notifica è presente.
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Mostra tutte</a>
            </div>
        </li>



        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $nome; ?></span>
                <i class="fas fa-user-circle fa-2x " style="color: #74C0FC;"></i>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profilo
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Impostazioni
                </a>
                <a class="dropdown-item" href="../../functions/users/log">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Registro Attività
                </a>
                <a class="dropdown-item" href="../../functions/users/themes">
                    <i
                        class="fas fa-circle fa-sm fa-fw mr-2 text-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"></i>
                    Tema
                </a>
               
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../../logout" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Esci
                </a>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->
<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Vuoi Disconnetterti?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Seleziona "Logout" per interrompere la sessione.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annulla</button>
                <a class="btn btn-primary" href="../../logout">Logout</a>
            </div>
        </div>
    </div>
</div>


