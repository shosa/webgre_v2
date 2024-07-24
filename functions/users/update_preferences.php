<?php
include ("../../config/config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $card_riparazioni = $_POST['card_riparazioni'];
    $card_myRiparazioni = $_POST['card_myRiparazioni'];
    $card_quality = $_POST['card_quality'];
    $card_production = $_POST['card_production'];
    $card_productionMonth = $_POST['card_productionMonth'];


    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $queryUpdatePreferenze = "UPDATE utenti_preferenze SET 
            card_riparazioni = :card_riparazioni, 
            card_myRiparazioni = :card_myRiparazioni, 
            card_quality = :card_quality ,
            card_produzione = :card_production ,
            card_produzioneMese= :card_productionMonth
            WHERE user_id = :user_id";

        $stmtUpdatePreferenze = $pdo->prepare($queryUpdatePreferenze);
        $stmtUpdatePreferenze->execute([
            ':user_id' => $user_id,
            ':card_riparazioni' => $card_riparazioni,
            ':card_myRiparazioni' => $card_myRiparazioni,
            ':card_production' => $card_production,
            ':card_productionMonth' => $card_productionMonth,
            ':card_quality' => $card_quality
        ]);

        echo 'success';
    } catch (PDOException $e) {
        echo 'error';
    }
}
?>