<?php
// scm/index.php
session_start();
require_once '../config/config.php';

// Se già loggato, reindirizza alla dashboard
if (isset($_SESSION['laboratorio_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username e password sono obbligatori';
    } else {
        try {
            $pdo = getDbInstance();

            // Query per il login
            $stmt = $pdo->prepare("
                SELECT id, nome_laboratorio, email, username 
                FROM scm_laboratori 
                WHERE username = ? AND password_hash = SHA2(?, 256) AND attivo = TRUE
            ");
            $stmt->execute([$username, $password]);
            $laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($laboratorio) {
                // Login riuscito - salva in sessione
                $_SESSION['laboratorio_id'] = $laboratorio['id'];
                $_SESSION['laboratorio_nome'] = $laboratorio['nome_laboratorio'];
                $_SESSION['laboratorio_email'] = $laboratorio['email'];
                $_SESSION['laboratorio_username'] = $laboratorio['username'];

                // Aggiorna ultimo accesso
                $stmt = $pdo->prepare("UPDATE scm_laboratori SET ultimo_accesso = NOW() WHERE id = ?");
                $stmt->execute([$laboratorio['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Credenziali non valide o account disattivato';
            }
        } catch (PDOException $e) {
            $error = 'Errore di connessione al database';
            if ($debug) {
                $error .= ': ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCM - Login Laboratori</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-industry fa-3x text-primary mb-3"></i>
                            <h2 class="h3 mb-3 fw-normal">SCM Terzisti - Emmegiemme</h2>
                            <p class="text-muted">Accesso Laboratori</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username"
                                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password"
                                    name="password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Accedi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>