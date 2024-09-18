<?php
session_start();
require_once 'config/config.php';
$token = bin2hex(openssl_random_pseudo_bytes(16));
// Se l'utente è già loggato, reindirizza alla dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
    exit;
}
// Se l'utente ha selezionato l'opzione "ricordami"
if (!empty($_COOKIE['series_id']) && !empty($_COOKIE['remember_token'])) {
    $series_id = filter_var($_COOKIE['series_id'], FILTER_SANITIZE_STRING);
    $remember_token = filter_var($_COOKIE['remember_token'], FILTER_SANITIZE_STRING);
    $pdo = getDbInstance();
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE series_id = :series_id");
    $stmt->bindParam(':series_id', $series_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($remember_token, $row['remember_token'])) {
        $expires = strtotime($row['expires']);
        if (strtotime(date('Y-m-d H:i:s')) > $expires) {
            clearAuthCookie();
            header('Location:login.php');
            exit;
        }
        $_SESSION['user_logged_in'] = TRUE;
        $_SESSION['admin_type'] = $row['admin_type'];
        $_SESSION['nome'] = $row['nome'];
        $_SESSION['username'] = $row['user_name'];
        header('Location:index.php');
        exit;
    } else {
        clearAuthCookie();
        header('Location:login.php');
        exit;
    }
}
include BASE_PATH . '/components/header.php';
?>
<style>
    .logo {
        margin: 5%;
        text-align: center;
    }
    @media screen and (max-width: 768px) {
        .logo img {
            width: 60%;
        }
    }
</style>
<body class="bg-gradient-light">
    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5 bg-gray-100 align-center">
            <div class="card-body p-0"></div>
            <div class="logo">
                <img src="img/logoMini.png" alt="Logo">
            </div>
            <form class="form loginform" method="POST" action="authenticate.php" style="padding:5%;">
                <div class="card">
                    <div class="card-header text-primary font-weight-bold">ACCEDI</div>
                    <div class="card-body">
                        <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                        <div class="form-group">
                            <label class="control-label">USERNAME</label>
                            <input type="text" name="username" class="form-control" required="required">
                        </div>
                        <div class="form-group">
                            <label class="control-label">PASSWORD</label>
                            <input type="password" name="passwd" class="form-control" required="required">
                        </div>
                        <div class="form-check">
                            <input name="remember" type="checkbox" class="form-check-input" value="1">
                            <label class="form-check-label">Ricordami su questo dispositivo.</label>
                        </div>
                        <?php if (isset($_SESSION['login_failure'])): ?>
                            <div class="alert alert-danger alert-dismissable fade show mt-3">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?php
                                echo $_SESSION['login_failure'];
                                unset($_SESSION['login_failure']);
                                ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-orange mt-3" style="width:100%;">Accedi</button>
                    </div>
                </div>
            </form>
            <?php include BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
</html>
