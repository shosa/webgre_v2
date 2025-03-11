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
        $_SESSION['mail'] = $row['mail'];
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
    .bg-login-image {
        background: url('img/login-bg.jpg');
        background-position: center;
        background-size: cover;
    }
    
    .login-wrapper {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 0;
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .logo-container img {
        max-width: 180px;
        height: auto;
    }
    
    .login-form-container {
        position: relative;
        z-index: 1;
    }
    
    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 14px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    
    .form-control:focus {
        background-color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
        border-color: #90cdf4;
    }
    
    .input-group-text {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }
    
    .btn-login {
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        background: linear-gradient(to right, #ed8936, #dd6b20);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
        background: linear-gradient(to right, #dd6b20, #c05621);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1f1f1;
        padding: 1.5rem;
        text-align: center;
        font-size: 1.5rem;
    }
    
    .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0.2rem;
    }
    
    .form-check-label {
        margin-left: 0.5rem;
        font-size: 14px;
    }
    
    .login-footer {
        text-align: center;
        font-size: 0.8rem;
        color: #718096;
        margin-top: 2rem;
    }
    
    @media screen and (max-width: 768px) {
        .login-image-column {
            display: none;
        }
        
        .logo-container img {
            max-width: 150px;
        }
    }
</style>

<body class="bg-gradient-light">
    <div class="login-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-6 col-md-9">
                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0">
                            <div class="row">
                               
                                <div class="col-lg-12">
                                    <div class="p-5 login-form-container">
                                        <div class="logo-container">
                                            <img src="img/logoMini.png" alt="Logo" class="img-fluid">
                                        </div>
                                        
                                        <div class="text-center mb-4">
                                            <h1 class="h4 text-gray-900">Benvenuto</h1>
                                            <p class="text-muted">Accedi per continuare</p>
                                        </div>
                                        
                                        <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                                        
                                        <form class="user" method="POST" action="authenticate.php">
                                            <div class="form-group mb-4">
                                                <label class="text-gray-700 mb-2">Username</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fas fa-user"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" name="username" class="form-control" required="required" placeholder="Inserisci username">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group mb-4">
                                                <label class="text-gray-700 mb-2">Password</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    </div>
                                                    <input type="password" name="passwd" class="form-control" required="required" placeholder="Inserisci password">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input name="remember" type="checkbox" class="form-check-input" value="1" id="rememberCheck">
                                                    <label class="form-check-label" for="rememberCheck">Ricordami su questo dispositivo</label>
                                                </div>
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
                                            
                                            <button type="submit" class="btn btn-login btn-block mt-4">
                                                <i class="fas fa-sign-in-alt mr-2"></i> Accedi
                                            </button>
                                        </form>
                                        
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include BASE_PATH . '/components/scripts.php'; ?>
</body>
</html>