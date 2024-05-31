<?php

// Verifica se il modulo è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recupera i dati dal modulo
    $month = $_POST["month"];
    $day = $_POST["day"];
    $manovia1 = $_POST["manovia1"];
    $note1 = $_POST["note1"];
    $manovia2 = $_POST["manovia2"];
    $note2 = $_POST["note2"];
    $manovia3 = $_POST["manovia3"];
    $note3 = $_POST["note3"];
    $orlatura1 = $_POST["orlatura1"];
    $note4 = $_POST["note4"];
    $orlatura2 = $_POST["orlatura2"];
    $note5 = $_POST["note5"];
    $orlatura3 = $_POST["orlatura3"];
    $note8 = $_POST["note8"];
    $taglio1 = $_POST["taglio1"];
    $note6 = $_POST["note6"];
    $taglio2 = $_POST["taglio2"];
    $note7 = $_POST["note7"];

    // Esegui la query di aggiornamento nel database (assicurati di avere una connessione al database)
    require ("../../config/config.php");

    try {
        // Crea una connessione al database utilizzando PDO
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sommataglio = $taglio1 + $taglio2;
        $sommaorlatura = $orlatura1 + $orlatura2;
        $sommamontaggio = $manovia1 + $manovia2 + $manovia3;

        // Prepara la query di aggiornamento
        $sql = "UPDATE prod_mesi
                SET MANOVIA1 = :manovia1, MANOVIA1NOTE = :note1, 
                    MANOVIA2 = :manovia2, MANOVIA2NOTE = :note2, 
                    MANOVIA3 = :manovia3, MANOVIA3NOTE = :note3, 
                    ORLATURA1 = :orlatura1, ORLATURA1NOTE = :note4, 
                    ORLATURA2 = :orlatura2, ORLATURA2NOTE = :note5, 
                    ORLATURA3 = :orlatura3, ORLATURA3NOTE = :note8, 
                    TAGLIO1 = :taglio1, TAGLIO1NOTE = :note6, 
                    TAGLIO2 = :taglio2, TAGLIO2NOTE = :note7
                WHERE MESE = :month AND GIORNO = :day";

        // Prepara la dichiarazione
        $stmt = $pdo->prepare($sql);

        // Esegui la query
        $stmt->execute([
            ':manovia1' => $manovia1,
            ':note1' => $note1,
            ':manovia2' => $manovia2,
            ':note2' => $note2,
            ':manovia3' => $manovia3,
            ':note3' => $note3,
            ':orlatura1' => $orlatura1,
            ':note4' => $note4,
            ':orlatura2' => $orlatura2,
            ':note5' => $note5,
            ':orlatura3' => $orlatura3,
            ':note8' => $note8,
            ':taglio1' => $taglio1,
            ':note6' => $note6,
            ':taglio2' => $taglio2,
            ':note7' => $note7,
            ':month' => $month,
            ':day' => $day
        ]);

        echo "QUERY ESEGUITA: " . $sql;
    } catch (PDOException $e) {
        echo "Errore nell'aggiornamento dei dati nel database: " . $e->getMessage();
    }

    // Chiudi la connessione al database
    $pdo = null;
}
?>