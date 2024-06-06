<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/utils/log_utilsLogin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username');
    $passwd = filter_input(INPUT_POST, 'passwd');
    $remember = filter_input(INPUT_POST, 'remember');

    try {
        // Connessione al database utilizzando PDO
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare SQL statement
        $statement = $pdo->prepare("SELECT * FROM utenti WHERE user_name = :username");

        // Bind the parameter
        $statement->bindParam(':username', $username);

        // Execute SQL statement
        $statement->execute();

        // Fetch the record
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($statement->rowCount() >= 1) {
            $db_password = $row['password'];
            $user_id = $row['id'];

            if (password_verify($passwd, $db_password)) {
                $_SESSION['user_logged_in'] = TRUE;
                $_SESSION['admin_type'] = $row['admin_type'];
                $_SESSION['nome'] = $row['nome'];
                $_SESSION['username'] = $row['user_name'];
                $_SESSION['tipo'] = $row['admin_type'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['tema'] = $row['theme_color'];

                // Recupera i permessi dalla tabella `permessi`
                $permessi_statement = $pdo->prepare("SELECT riparazioni, cq, produzione, tabelle, log, etichette,dbsql,utenti FROM permessi WHERE id_utente = :user_id");
                $permessi_statement->bindParam(':user_id', $user_id);
                $permessi_statement->execute();
                $permessi = $permessi_statement->fetch(PDO::FETCH_ASSOC);

                if ($permessi) {
                    $_SESSION['permessi_riparazioni'] = $permessi['riparazioni'];
                    $_SESSION['permessi_utenti'] = $permessi['utenti'];
                    $_SESSION['permessi_cq'] = $permessi['cq'];
                    $_SESSION['permessi_produzione'] = $permessi['produzione'];
                    $_SESSION['permessi_tabelle'] = $permessi['tabelle'];
                    $_SESSION['permessi_log'] = $permessi['log'];
                    $_SESSION['permessi_etichette'] = $permessi['etichette'];
                    $_SESSION['permessi_sql'] = $permessi['dbsql'];
                }

                if ($remember) {
                    $series_id = randomString(16);
                    $remember_token = getSecureRandomToken(20);
                    $encrypted_remember_token = password_hash($remember_token, PASSWORD_DEFAULT);
                    $expiry_time = date('Y-m-d H:i:s', strtotime(' + 30 days'));
                    $expires = strtotime($expiry_time);

                    setcookie('series_id', $series_id, $expires, "/");
                    setcookie('remember_token', $remember_token, $expires, "/");

                    // Prepare SQL statement for updating remember details
                    $update_statement = $pdo->prepare("UPDATE utenti SET series_id = :series_id, remember_token = :remember_token, expires = :expires WHERE id = :user_id");

                    // Bind parameters
                    $update_statement->bindParam(':series_id', $series_id);
                    $update_statement->bindParam(':remember_token', $encrypted_remember_token);
                    $update_statement->bindParam(':expires', $expiry_time);
                    $update_statement->bindParam(':user_id', $user_id);

                    // Execute SQL statement
                    $update_statement->execute();
                }
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                // Definisci un array di stringhe che identificano i browser comuni
                $browsers = array(
                    'Chrome',
                    'Firefox',
                    'Safari',
                    'Opera',
                    'MSIE',
                    'Trident'
                );

                // Inizializza le variabili per memorizzare il browser e il dispositivo
                $browser = 'Sconosciuto';
                $device = 'Sconosciuto';

                // Cerca il browser nell'user agent
                foreach ($browsers as $browser_string) {
                    if (strpos($user_agent, $browser_string) !== false) {
                        $browser = $browser_string;
                        break;
                    }
                }

                // Controlla il tipo di dispositivo
                if (strpos($user_agent, 'Mobile') !== false) {
                    $device = 'Mobile';
                } else {
                    $device = 'Desktop';
                }
                logActivity($_SESSION['user_id'], 'LOGIN', 'ACCESSO', 'Accesso eseguito', $device . ' / ' . $browser, '');
                header('Location: index.php');
                exit;
            } else {
                $_SESSION['login_failure'] = "Username o password non validi.";
                header('Location: login.php');
                exit;
            }
        } else {
            $_SESSION['login_failure'] = "Username o password non validi.";
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        // If an error occurs, display the error message
        echo "Errore: " . $e->getMessage();
    }
} else {
    die('Method Not Allowed');
}
?>