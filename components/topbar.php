<!-- Topbar Search -->
<?php

$pdoTopbar = getDbInstance();

$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdoTopbar->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();

// Query per ottenere le notifiche non lette per l'utente corrente
$queryNotifications = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY timestamp DESC LIMIT 3";
$stmtNotifications = $pdoTopbar->prepare($queryNotifications);
$stmtNotifications->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$stmtNotifications->execute();
$notifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

// Conta il numero di notifiche non lette
$unreadCount = count($notifications);

?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i
            class="fa fa-bars text-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"></i>
    </button>

    <form action="<?php echo BASE_URL ?>/spotlight.php" method="GET"
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

    <?php if (isset($debug) && $debug == true): ?>
        <div class="mx-auto">
            <span class="h6 text-danger text-center font-weight-bold">** AMBIENTE DI TEST **</span>
        </div>
    <?php endif; ?>

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
                <form action="<?php echo BASE_URL ?>/spotlight.php" method="GET"
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

        <!-- Nav Item - Notifications -->
        <li class="nav-item dropdown no-arrow mx-1">

            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw <?php echo $unreadCount > 0 ? 'fa-bounce' : ''; ?>"></i>
                <!-- Counter - Notifications -->
                <span
                    class="badge badge-danger badge-counter"><?php echo $unreadCount > 0 ? $unreadCount : ''; ?></span>
            </a>
            <!-- Dropdown - Notifications -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header bg-<?php echo $colore; ?> border-<?php echo $colore; ?>">
                    Centro Notifiche
                </h6>
                <?php if ($unreadCount > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        $iconClass = '';
                        $bgClass = '';

                        switch ($notification['type']) {
                            case 'warning':
                                $iconClass = 'fas fa-exclamation-triangle';
                                $bgClass = 'warning';
                                break;
                            case 'success':
                                $iconClass = 'fas fa-check-circle';
                                $bgClass = 'success';
                                break;
                            case 'info':
                                $iconClass = 'fas fa-info-circle';
                                $bgClass = 'info';
                                break;
                            case 'danger':
                                $iconClass = 'fas fa-exclamation-triangle';
                                $bgClass = 'danger';
                                break;
                            default:
                                $iconClass = 'fas fa-exclamation-circle';
                                $bgClass = 'primary';
                                break;
                        }
                        ?>
                        <a class=" dropdown-item d-flex align-items-center" href="<?php echo $notification['link']; ?>">
                            <div class="mr-3">
                                <div class="icon-circle bg-<?php echo $bgClass; ?>">
                                    <i class="<?php echo $iconClass; ?> text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500"><?php echo $notification['timestamp']; ?></div>
                                <?php echo $notification['message']; ?>
                            </div>
                            <button class="btn btn-sm text-danger ml-auto mark-as-read font-weight-bold"
                                data-id="<?php echo $notification['id']; ?>">X</button>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
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
                <?php endif; ?>
                <a class="dropdown-item text-center small text-gray-500"
                    href="<?php echo BASE_URL ?>/functions/users/notifications.php">Mostra tutte</a>
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
                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/users/log">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Registro Attività
                </a>
                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/users/themes">
                    <i
                        class="fas fa-circle fa-sm fa-fw mr-2 text-<?php echo (isset($_SESSION["tema"]) && !empty($_SESSION["tema"])) ? $_SESSION["tema"] : "primary"; ?>"></i>
                    Tema
                </a>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo BASE_URL ?>/logout" data-toggle="modal"
                    data-target="#logoutModal">
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
                <a class="btn btn-primary" href="<?php echo BASE_URL ?>/logout">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Funzione per marcare la notifica come letta
    document.querySelectorAll('.mark-as-read').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation(); // Evita la propagazione dell'evento al dropdown
            const notificationId = this.getAttribute('data-id');

            fetch('<?php echo BASE_URL ?>/utils/mark_notification_read.php', {  // Utilizza BASE_URL definito in config.php
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notificationId })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Errore nella richiesta.');
                    }
                    return response.text();  // Leggi la risposta come testo
                })
                .then(data => {
                    // Verifica se la risposta contiene un messaggio di successo
                    if (data.includes('success')) {
                        // Rimuovi la notifica dalla lista
                        this.closest('.dropdown-item').remove();
                        // Aggiorna il contatore delle notifiche
                        let badgeCounter = document.querySelector('.badge-counter');
                        let count = parseInt(badgeCounter.textContent) - 1;
                        badgeCounter.textContent = count > 0 ? count : '';
                    } else {
                        throw new Error(data);  // Lancia un errore con il testo della risposta
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    // Mostra l'errore direttamente sulla pagina HTML
                    document.getElementById('error-message').textContent = error.message;
                });
        });
    });
</script>