<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/log_utils.php';
// Verifica se il modulo è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month = $_POST["month"];
    $day = $_POST["day"];
    $manovia1 = $_POST["manovia1"];
    $manovia1reso = $_POST["manovia1reso"];
    $manovia2 = $_POST["manovia2"];
    $manovia3 = $_POST["manovia3"];
    $orlatura1 = $_POST["orlatura1"];
    $orlatura2 = $_POST["orlatura2"];
    $orlatura3 = $_POST["orlatura3"];
    $orlatura4 = $_POST["orlatura4"];
    $tomaieEstero = $_POST["tomaieEstero"];
    try {
        // Crea una connessione al database utilizzando PDO
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sommaorlatura = (int) $orlatura1 + (int) $orlatura2;
        $sommamontaggio = (int) $manovia1 + (int) $manovia2 + (int) $manovia3;
        // Prepara la query di aggiornamento
        $sql = "UPDATE sped_mesi
                SET MANOVIA1 = :manovia1, MANOVIA1RESO = :manovia1reso,
                    MANOVIA2 = :manovia2,
                    MANOVIA3 = :manovia3,
                    ORLATURA1 = :orlatura1,
                    ORLATURA2 = :orlatura2,
                    ORLATURA3 = :orlatura3, 
                    ORLATURA4 = :orlatura4,
                    TOMESTERO = :tomaieEstero
                WHERE MESE = :month AND GIORNO = :day";
        // Prepara la dichiarazione
        $stmt = $pdo->prepare($sql);
        // Esegui la query
        $stmt->execute([
            ':manovia1' => $manovia1,
            ':manovia1reso' => $manovia1reso,
            ':manovia2' => $manovia2,
            ':manovia3' => $manovia3,
            ':orlatura1' => $orlatura1,
            ':orlatura2' => $orlatura2,
            ':orlatura3' => $orlatura3,
            ':orlatura4' => $orlatura4,
            ':tomaieEstero' => $tomaieEstero,
            ':month' => $month,
            ':day' => $day
        ]);
        echo "QUERY ESEGUITA: " . $sql;
        logActivity($_SESSION['user_id'], 'SPEDIZIONE', 'INSERIMENTO', 'Inserita spedizione', $day . ' ' . $month, '');
    } catch (PDOException $e) {
        echo "Errore nell'aggiornamento dei dati nel database: " . $e->getMessage();
    }
    // Chiudi la connessione al database
    $pdo = null;
}
?>