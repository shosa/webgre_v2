<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php'; // Include la libreria PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Crea una connessione PDO
try {
    $db = getDbInstance();
    // Imposta PDO per segnalare gli errori
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recupera le credenziali SMTP dalla tabella 'settings'
    $smtpSettings = array();
    $stmt = $db->prepare("SELECT item, value FROM settings WHERE item LIKE 'production_sender%'");
    $stmt->execute();
    $smtpSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crea un array associativo per le credenziali SMTP
    $smtpCredentials = array();
    foreach ($smtpSettings as $setting) {
        $smtpCredentials[$setting['item']] = $setting['value'];
    }

    // Crea un nuovo oggetto PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = $smtpCredentials['production_senderSMTP'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpCredentials['production_senderEmail'];
    $mail->Password = $smtpCredentials['production_senderPassword'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $smtpCredentials['production_senderPORT'];

    // Imposta i destinatari, l'oggetto e il corpo dell'email
    $mail->setFrom($smtpCredentials['production_senderEmail'], 'Il tuo nome');
    $mail->addAddress($_GET['to']);
    $mail->addCC($_GET['cc']);
    $mail->Subject = $_GET['subject'];
    $mail->Body = $_GET['body'];

    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    // Invia l'email e gestisci eventuali errori
    if ($mail->send()) {
        echo "Email inviata con successo";
    } else {
        echo "Errore nell'invio dell'email: " . $mail->ErrorInfo;
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    }
} catch (PDOException $e) {
    echo "Errore nella connessione al database: " . $e->getMessage();
}
?>