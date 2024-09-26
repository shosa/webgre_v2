<?php
require_once(dirname(__DIR__) . '/config/url.php');
?>

<style>
    /* Stile del modale per gli errori critici */
    .critical-modal {
        position: fixed;
        z-index: 9999;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
        width: 400px;
    }

    /* Stile del bottone "Torna a Home" */
    .critical-modal-content .btn-primary {
        margin-top: 20px;
    }

    /* Overlay che blura tutto il resto della pagina */
    .blur-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9998;
    }
</style>
</head>

<body>
    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['danger'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['danger'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['danger']);
    }
    if (isset($_SESSION['warning'])) {
        echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['warning'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['warning']);
    }
    if (isset($_SESSION['info'])) {
        echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info'], ENT_QUOTES, 'UTF-8') . '</div>';
        unset($_SESSION['info']);
    }
    if (isset($_SESSION['critical'])) {
        echo '
    <div id="criticalModal" class="critical-modal  border border-danger">
        <div class="critical-modal-content">
            <h2 class="text-danger font-weight-bold">Errore</h2>
            <p>' . htmlspecialchars($_SESSION['critical'], ENT_QUOTES, 'UTF-8') . '</p>
            <a href="' . BASE_URL . '/index" class="btn btn-danger">Torna a Home</a>
        </div>
    </div>
    <div class="blur-overlay"></div>
    <script>
        window.onload = function () {
            if (document.getElementById("criticalModal")) {
                document.body.style.overflow = "hidden";
            }
        };
    </script>
    ';
        // Unset della variabile sessione
        unset($_SESSION['critical']);
    }
    ?>