<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/components/header.php';

$edit_mode = false;
$laboratorio = null;

// Controllo se siamo in modalità modifica
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_mode = true;
    $laboratorio_id = (int)$_GET['id'];
    
    try {
        $pdo = getDbInstance();
        $stmt = $pdo->prepare("SELECT * FROM scm_laboratori WHERE id = ?");
        $stmt->execute([$laboratorio_id]);
        $laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$laboratorio) {
            $_SESSION['error'] = 'Laboratorio non trovato';
            header('Location: lista_laboratori');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
    }
}

// Gestione form
if ($_POST) {
    $nome_laboratorio = trim($_POST['nome_laboratorio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $attivo = isset($_POST['attivo']) ? 1 : 0;
    
    $errors = [];
    
    // Validazioni
    if (empty($nome_laboratorio)) {
        $errors[] = 'Nome laboratorio è obbligatorio';
    }
    if (empty($username)) {
        $errors[] = 'Username è obbligatorio';
    }
    if (!$edit_mode && empty($password)) {
        $errors[] = 'Password è obbligatoria per nuovo laboratorio';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email non valida';
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDbInstance();
            
            if ($edit_mode) {
                // Modifica
                if (!empty($password)) {
                    // Aggiorna anche password
                    $stmt = $pdo->prepare("
                        UPDATE scm_laboratori 
                        SET nome_laboratorio = ?, email = ?, username = ?, password_hash = SHA2(?, 256), attivo = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nome_laboratorio, $email, $username, $password, $attivo, $laboratorio_id]);
                } else {
                    // Non aggiornare password
                    $stmt = $pdo->prepare("
                        UPDATE scm_laboratori 
                        SET nome_laboratorio = ?, email = ?, username = ?, attivo = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nome_laboratorio, $email, $username, $attivo, $laboratorio_id]);
                }
                $_SESSION['success'] = 'Laboratorio aggiornato con successo';
            } else {
                // Controllo username univoco
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM scm_laboratori WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = 'Username già in uso';
                } else {
                    // Creazione
                    $stmt = $pdo->prepare("
                        INSERT INTO scm_laboratori (nome_laboratorio, email, username, password_hash, attivo) 
                        VALUES (?, ?, ?, SHA2(?, 256), ?)
                    ");
                    $stmt->execute([$nome_laboratorio, $email, $username, $password, $attivo]);
                    $_SESSION['success'] = 'Laboratorio creato con successo';
                }
            }
            
            if (empty($errors)) {
                header('Location: lista_laboratori');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Errore database: ' . ($debug ? $e->getMessage() : 'Errore generico');
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-gradient-primary text-white p-3 rounded shadow-sm">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-gray-800">
                                    <?= $edit_mode ? 'Modifica' : 'Nuovo' ?> Laboratorio
                                </h1>
                                <p class="mb-0 text-gray-600">
                                    <?= $edit_mode ? 'Aggiorna i dati del laboratorio' : 'Crea un nuovo laboratorio terzista' ?>
                                </p>
                            </div>
                        </div>
                      
                    </div>
                       <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index">SCM</a></li>
                        <li class="breadcrumb-item"><a href="lista_laboratori">Lista Laboratori</a></li>
                        <li class="breadcrumb-item active">Nuovo</li>
                    </ol>
                    <!-- Form -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-<?= $edit_mode ? 'edit' : 'plus' ?> mr-2"></i>
                                        Dati Laboratorio
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="nome_laboratorio">Nome Laboratorio *</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="nome_laboratorio" 
                                                           name="nome_laboratorio" 
                                                           value="<?= htmlspecialchars($laboratorio['nome_laboratorio'] ?? $_POST['nome_laboratorio'] ?? '') ?>"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               id="attivo" 
                                                               name="attivo" 
                                                               <?= ($laboratorio['attivo'] ?? $_POST['attivo'] ?? true) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="attivo">
                                                            Laboratorio Attivo
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($laboratorio['email'] ?? $_POST['email'] ?? '') ?>">
                                            <small class="form-text text-muted">Email di contatto del laboratorio (opzionale)</small>
                                        </div>
                                        
                                        <hr>
                                        <h6 class="text-primary">Credenziali di Accesso</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username">Username *</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="username" 
                                                           name="username" 
                                                           value="<?= htmlspecialchars($laboratorio['username'] ?? $_POST['username'] ?? '') ?>"
                                                           required>
                                                    <small class="form-text text-muted">Username per accesso al sistema SCM</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password">
                                                        Password <?= $edit_mode ? '' : '*' ?>
                                                    </label>
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="password" 
                                                           name="password"
                                                           <?= $edit_mode ? '' : 'required' ?>>
                                                    <small class="form-text text-muted">
                                                        <?= $edit_mode ? 'Lascia vuoto per non modificare' : 'Password per accesso al sistema SCM' ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <a href="lista_laboratori" class="btn btn-secondary">
                                                <i class="fas fa-times mr-2"></i>Annulla
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save mr-2"></i>
                                                <?= $edit_mode ? 'Aggiorna' : 'Crea' ?> Laboratorio
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>