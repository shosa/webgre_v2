<?php

require_once '../../config/config.php';

require_once BASE_PATH . '/vendor/phpmailer/src/PHPMailer.php';

require_once BASE_PATH . '/vendor/phpmailer/src/SMTP.php';

require_once BASE_PATH . '/vendor/phpmailer/src/Exception.php';

require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file




use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\SMTP;

use PHPMailer\PHPMailer\Exception;



$pdf = new TCPDF("P", "mm", "A4", true, "UTF-8", false);



$pdf->SetMargins(7, 7, 7);



$pdf->SetAutoPageBreak(true, 10);



$pdf->setPrintHeader(false);



$pdf->setPrintFooter(false);



// Ricevi i parametri month e day dalla richiesta GET

$month = $_POST["month"];



$day = $_POST["day"];



// Esegui una query per ottenere i dati dalla tabella mesi

// Sostituisci questi dettagli con le tue credenziali di connessione al database



// Crea una connessione al database





// Esegui una query per ottenere i dati dal database

// Crea una connessione al database usando PDO

$conn = getDbInstance();

// Imposta l'attributo PDO::ATTR_ERRMODE su PDO::ERRMODE_EXCEPTION

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



// Esegui una query per ottenere i dati dalla tabella mesi

$sql = "SELECT * FROM prod_mesi WHERE MESE = :month AND GIORNO = :day";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':month', $month);

$stmt->bindParam(':day', $day);

$stmt->execute();



// Crea un nuovo documento PDF

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);



// Imposta il titolo del documento

$pdf->SetTitle("PRODUZIONE DEL " . $day . " " . $month);



// Aggiungi una pagina vuota

$pdf->AddPage();



// Aggiungi i dati ottenuti dalla query al PDF

if ($stmt->rowCount() > 0) {

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $week = $row["WEEK"];



        // Modifica questa parte per adattarla ai tuoi dati

        $pdf->SetLineWidth(0.5);



        $pdf->SetFillColor(255, 255, 255); //COLORE GRIGIO

        $pdf->SetFont("helvetica", "B", 20);



        $pdf->Cell(0, 3, "RAPPORTO DI PRODUZIONE " . $row["NOMEGIORNO"] . " " . $row["GIORNO"] . " " . $row["MESE"] . " 2024", 0, 1, "C", true);



        $pdf->SetFillColor(255, 255, 255); //COLORE BIANCO

        $pdf->Rect(7, 20, 196, 73, "DF"); // RIQUADRO PRODUZIONE

        $pdf->Rect(7, 25, 196, 73, "DF"); // LINEA RETTANGOLO NERO

        $pdf->SetFillColor(0, 0, 0);



        $pdf->Rect(7, 20, 62, 9.8, "DF"); //RETTANGOLO NERO

        $pdf->SetTextColor(255, 255, 255);



        $pdf->SetFont("helvetica", "B", 17);



        $pdf->Ln(1);

        $col1Width = 35; // Larghezza della prima colonna

        $col2Width = 25; // Larghezza della seconda colonna

        $col3Width = 15; // Larghezza della seconda colonna

        $pdf->Cell($col1Width, 10, "DATI GIORNALIERI", 0, 0);





        $pdf->SetTextColor(0, 0, 0);



        $pdf->Ln(10);



        $pdf->SetFillColor(255, 255, 255); //COLORE BIANCO

        $pdf->SetLineWidth(0.1);



        $isGray = false; // Stato iniziale, alternando tra bianco e grigio

        $grayColor = [240, 240, 240]; // Colore grigio molto chiaro (personalizzabile)

        for ($i = 0; $i < 8; $i++) {

            // Imposta il colore di riempimento in base allo stato

            if ($isGray) {

                $pdf->SetFillColor($grayColor[0], $grayColor[1], $grayColor[2]);

            } else {

                $pdf->SetFillColor(255, 255, 255); // Bianco



            }



            // Disegna il riquadro con il colore di riempimento

            $pdf->Rect(10, 30 + $i * 8, 190, 9, "DF");



            // Inverti lo stato per la prossima iterazione

            $isGray = !$isGray;

        }



        $pdf->SetFillColor(255, 255, 255); // Bianco

        $pdf->SetLineWidth(0.5);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "MANOVIA 1:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["MANOVIA1"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["MANOVIA1NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "MANOVIA 2:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["MANOVIA2"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["MANOVIA2NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "MANOVIA 3:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["MANOVIA3"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["MANOVIA3NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "ORLATURA 1:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["ORLATURA1"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["ORLATURA1NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "ORLATURA 2:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["ORLATURA2"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["ORLATURA2NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "ORLATURA 3:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["ORLATURA3"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["ORLATURA3NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "TAGLIO 1:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["TAGLIO1"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["TAGLIO1NOTE"], 0, 1);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col1Width, 8, "TAGLIO 2:", 0, 0);



        $pdf->SetFont("helvetica", "", 12);



        $pdf->Cell($col2Width, 8, $row["TAGLIO2"], 0, 0);



        $pdf->SetFont("helvetica", "B", 10);



        $pdf->Cell($col3Width, 8, "NOTE:", 0, 0);



        $pdf->SetFont("helvetica", "", 8);



        $pdf->Cell($col2Width, 8, $row["TAGLIO2NOTE"], 0, 1);



        $pdf->Rect(7, 100, 196, 147, "DF"); // RIQUADRO SETTIMANA

        $pdf->Rect(7, 105, 196, 77, "DF"); // LINEA RETTANGOLO NERO

        $pdf->SetFillColor(0, 0, 0);



        $pdf->Rect(7, 100, 77, 10, "DF"); //RETTANGOLO NERO

        $pdf->SetFont("helvetica", "B", 12);



        $pdf->Cell(185, 28, "SETTIMANA " . $week, 0, 0, "R");



        $pdf->SetTextColor(255, 255, 255);



        $pdf->SetFont("helvetica", "B", 17);



        $pdf->Ln(7);



        $pdf->Cell($col1Width, 10, "RIEPILOGO SETTIMANA", 0, 0);



        $pdf->SetTextColor(0, 0, 0);



        $sql2 = "SELECT ID, MESE, GIORNO, NOMEGIORNO, MANOVIA1, MANOVIA1NOTE, MANOVIA2, MANOVIA2NOTE, MANOVIA3, MANOVIA3NOTE, ORLATURA1, ORLATURA1NOTE, ORLATURA2, ORLATURA2NOTE, ORLATURA3, ORLATURA3NOTE, TAGLIO1, TAGLIO1NOTE, TAGLIO2, TAGLIO2NOTE, TOTALITAGLIO, TOTALIORLATURA, TOTALIMONTAGGIO

        FROM prod_mesi

        WHERE (((WEEK)='" . $week . "')) And Not NOMEGIORNO='DOMENICA'

        ORDER BY ID;";

        $stmt2 = $conn->prepare($sql2);





        $stmt2->execute();



        // ...

        $pdf->Ln(14);



        // Verifica se ci sono risultati

        $pdf->SetLineWidth(0.3);



        if ($stmt2->rowCount() > 0) {

            $pdf->SetFont("helvetica", "B", 12);



            // Intestazione della tabella

            // Imposta il colore del testo a nero

            $pdf->SetTextColor(0, 0, 0); // Nero

            // Imposta il colore di sfondo a grigio chiaro

            $pdf->SetFillColor(192, 192, 192); // Grigio chiaro

            $pdf->SetFont("helvetica", "B", 8);



            $pdf->Cell(14, 6, "DATA", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

            $pdf->Cell(25, 6, "GIORNO", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "MANOVIA 1", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "MANOVIA 2", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "MANOVIA 3", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 1", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 2", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 3", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "TAGLIO 1", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "TAGLIO 2", 1, 1, "C", 1);



            // Ripristina il colore del testo e dello sfondo predefiniti

            $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

            $pdf->SetFillColor(255, 255, 255); // Ripristina il colore di sfondo predefinito (bianco)

            $pdf->SetFont("helvetica", "", 10);



            $pdf->SetFillColor(255, 255, 255); //COLORE BIANCO

            // Stampa i dati della tabella

            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {

                $pdf->Cell(14, 8, $row2["GIORNO"], 1, 0, "C");



                $pdf->Cell(25, 8, $row2["NOMEGIORNO"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["MANOVIA1"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["MANOVIA2"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["MANOVIA3"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["ORLATURA1"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["ORLATURA2"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["ORLATURA3"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TAGLIO1"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TAGLIO2"], 1, 1, "C");

            }



            $sql3 = "SELECT SUM(MANOVIA1) AS TOTALEMANOVIA1 , SUM(MANOVIA2) AS TOTALEMANOVIA2, SUM(MANOVIA3) AS TOTALEMANOVIA3, SUM(ORLATURA1) AS TOTALEORLATURA1, SUM(ORLATURA2) AS TOTALEORLATURA2, SUM(ORLATURA3) AS TOTALEORLATURA3, SUM(TAGLIO1) AS TOTALETAGLIO1, SUM(TAGLIO2) AS TOTALETAGLIO2



            FROM prod_mesi



            WHERE (((WEEK)='" . $week . "')) And Not NOMEGIORNO='DOMENICA'



            ORDER BY ID;";



            $stmt3 = $conn->prepare($sql3);

            $stmt3->execute();



            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {

                $pdf->SetTextColor(0, 0, 0); // Nero

                // Imposta il colore di sfondo a grigio chiaro

                $pdf->SetFillColor(192, 192, 192); // Grigio chiaroF

                $pdf->SetTextColor(0, 0, 0); // Nero

                // Imposta il colore di sfondo a grigio chiaro solo per la cella "TOTALI"

                $pdf->SetFillColor(192, 192, 192); // Grigio chiaro

                $pdf->Cell(39, 6, "TOTALI SETTIMANA", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

                // Ripristina il colore del testo e dello sfondo predefiniti

                $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

                $pdf->SetFillColor(255, 255, 255);



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA1"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA2"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA3"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA1"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA2"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA3"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALETAGLIO1"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALETAGLIO2"], 1, 1, "C");

            }



            $pdf->SetLineWidth(0.5);

        } else {

        }



        $pdf->SetFillColor(0, 0, 0);



        $pdf->Rect(7, 178, 60, 10, "DF"); //RETTANGOLO NERO

        $pdf->SetTextColor(255, 255, 255);



        $pdf->SetFont("helvetica", "B", 17);



        $pdf->Ln(4);



        $pdf->Cell($col1Width, 10, "RIEPILOGO MESE", 0, 0);



        $pdf->SetTextColor(0, 0, 0);



        $pdf->SetTextColor(0, 0, 0);



        $pdf->SetFont("helvetica", "B", 12);



        $pdf->Cell(150, 16, $month, 0, 0, "R");



        $sql2 = "SELECT SETTIMANAMESE, Sum(MANOVIA1) AS TOTALEMANOVIA1, 



        Sum(MANOVIA2) AS TOTALEMANOVIA2, Sum(MANOVIA3) AS TOTALEMANOVIA3,



         Sum(ORLATURA1) AS TOTALEORLATURA1, Sum(ORLATURA2) AS TOTALEORLATURA2,



         Sum(ORLATURA3) AS TOTALEORLATURA3, Sum(TAGLIO1) AS TOTALETAGLIO1,



          Sum(TAGLIO2) AS TOTALETAGLIO2, Max(GIORNO) AS MaxDiGIORNO, Min(GIORNO) AS MinDiGIORNO



        FROM prod_mesi



        WHERE (((MESE)='" . $month . "'))



        GROUP BY SETTIMANAMESE



        HAVING (((SETTIMANAMESE)<>0));";



        $stmt2 = $conn->prepare($sql2);

        $stmt2->execute();



        // ...

        $pdf->Ln(14);



        // Verifica se ci sono risultati

        $pdf->SetLineWidth(0.3);



        if ($stmt2->rowCount() > 0) {

            $pdf->SetFont("helvetica", "B", 12);



            // Intestazione della tabella

            // Imposta il colore del testo a nero

            $pdf->SetTextColor(0, 0, 0); // Nero

            // Imposta il colore di sfondo a grigio chiaro

            $pdf->SetFillColor(192, 192, 192); // Grigio chiaro

            $pdf->SetFont("helvetica", "B", 8);



            $pdf->Cell(39, 6, "SETTIMANA", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

            $pdf->Cell(18, 6, "MANOVIA 1", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "MANOVIA 2", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "MANOVIA 3", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 1", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 2", 1, 0, "C", 1);



            $pdf->Cell(20, 6, "ORLATURA 3", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "TAGLIO 1", 1, 0, "C", 1);



            $pdf->Cell(18, 6, "TAGLIO 2", 1, 1, "C", 1);



            // Ripristina il colore del testo e dello sfondo predefiniti

            $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

            $pdf->SetFillColor(255, 255, 255); // Ripristina il colore di sfondo predefinito (bianco)

            // Aggiungi altre intestazioni qui...

            $pdf->SetFont("helvetica", "", 10);



            $pdf->SetFillColor(255, 255, 255); //COLORE BIANCO

            // Stampa i dati della tabella

            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {

                $pdf->Cell(39, 8, $row2["SETTIMANAMESE"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TOTALEMANOVIA1"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TOTALEMANOVIA2"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TOTALEMANOVIA3"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["TOTALEORLATURA1"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["TOTALEORLATURA2"], 1, 0, "C");



                $pdf->Cell(20, 8, $row2["TOTALEORLATURA3"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TOTALETAGLIO1"], 1, 0, "C");



                $pdf->Cell(18, 8, $row2["TOTALETAGLIO2"], 1, 1, "C");

            }



            $sql3 = "SELECT SUM(MANOVIA1) AS TOTALEMANOVIA1 , SUM(MANOVIA2) AS TOTALEMANOVIA2,



             SUM(MANOVIA3) AS TOTALEMANOVIA3, SUM(ORLATURA1) AS TOTALEORLATURA1,



             SUM(ORLATURA2) AS TOTALEORLATURA2, SUM(ORLATURA3) AS TOTALEORLATURA3,



              SUM(TAGLIO1) AS TOTALETAGLIO1, SUM(TAGLIO2) AS TOTALETAGLIO2



            FROM prod_mesi



            WHERE (((MESE)='" . $month . "'))



            ORDER BY ID;";



            $stmt3 = $conn->prepare($sql3);

            $stmt3->execute();



            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {

                $pdf->SetTextColor(0, 0, 0); // Nero

                // Imposta il colore di sfondo a grigio chiaro solo per la cella "TOTALI"

                $pdf->SetFillColor(192, 192, 192); // Grigio chiaro

                $pdf->Cell(39, 6, "TOTALI", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

                // Ripristina il colore del testo e dello sfondo predefiniti

                $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

                $pdf->SetFillColor(255, 255, 255);



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA1"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA2"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALEMANOVIA3"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA1"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA2"], 1, 0, "C");



                $pdf->Cell(20, 6, $row3["TOTALEORLATURA3"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALETAGLIO1"], 1, 0, "C");



                $pdf->Cell(18, 6, $row3["TOTALETAGLIO2"], 1, 1, "C");

            }



            $pdf->SetLineWidth(0.5);

        } else {

        }



        $pdf->SetFont("helvetica", "", 13);



        $pdf->Ln(8);



        // $pdf->Cell(0, 10, 'TOTALI TAGLIO: ' . $row['TOTALITAGLIO'], 0);

        // $pdf->Cell(0, 10, 'TOTALI ORLATURA: ' . $row['TOTALIORLATURA'], 0);

        // $pdf->Cell(0, 10, 'TOTALI MONTAGGIO: ' . $row['TOTALIMONTAGGIO'], 0);

        $col1Width = 48; // Larghezza della prima colonna

        $col2Width = 17; // Larghezza della seconda colonna

        $pdf->SetTextColor(0, 0, 0);



        // Calcola la coordinata x per centrare gli elementi sulla pagina

        $pagewidth = $pdf->getPageWidth();



        $totalwidth = ($col1Width + $col2Width) * 3;



        $x = ($pagewidth - $totalwidth) / 2;



        // Imposta la coordinata x iniziale

        $pdf->SetX($x);



        // Imposta il colore di riempimento, la larghezza della linea e il font

        $pdf->SetFillColor(255, 255, 255); //COLORE BIANCO

        $pdf->SetLineWidth(0.1);



        $pdf->SetLineWidth(0.5);



        $pdf->SetFont("helvetica", "B", 14);



        // Stampa gli elementi centrati

        // Imposta il colore di testo a bianco

        $pdf->SetTextColor(255, 255, 255); // Bianco

        $pdf->SetFillColor(0, 0, 0); // Nero per lo sfondo

        $pdf->SetFont("helvetica", "B", 14);



        $pdf->Cell($col1Width, 10, "TOT. TAGLIO:", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

        // Ripristina il colore di testo predefinito

        $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

        // Ora disegna il valore all'interno del riquadro "TOT. TAGLIO"

        $pdf->SetFont("helvetica", "", 17);



        $pdf->Cell($col2Width, 10, $row["TOTALITAGLIO"], 1, 0, "C");



        // Ripeti lo stesso processo per gli altri riquadri

        $pdf->SetTextColor(255, 255, 255); // Bianco

        $pdf->SetFillColor(0, 0, 0); // Nero per lo sfondo

        $pdf->SetFont("helvetica", "B", 14);



        $pdf->Cell($col1Width, 10, "TOT. ORLATURA:", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

        // Ripristina il colore di testo predefinito

        $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

        // Ora disegna il valore all'interno del riquadro "TOT. TAGLIO"

        $pdf->SetFont("helvetica", "", 17);



        $pdf->Cell($col2Width, 10, $row["TOTALIORLATURA"], 1, 0, "C");



        $pdf->SetTextColor(255, 255, 255); // Bianco

        $pdf->SetFillColor(0, 0, 0); // Nero per lo sfondo

        $pdf->SetFont("helvetica", "B", 14);



        $pdf->Cell($col1Width, 10, "TOT. MONTAGGIO:", 1, 0, "C", 1); // L'ultimo parametro "1" imposta lo sfondo

        // Ripristina il colore di testo predefinito

        $pdf->SetTextColor(0, 0, 0); // Ripristina il colore del testo predefinito (nero)

        // Ora disegna il valore all'interno del riquadro "TOT. TAGLIO"

        $pdf->SetFont("helvetica", "", 17);



        $pdf->Cell($col2Width, 10, $row["TOTALIMONTAGGIO"], 1, 0, "C");



        // Ripristina il colore di sfondo predefinito

        $pdf->SetFillColor(255, 255, 255); // Bianco



    }



    $pdf->Ln(5);



    $pdf->SetFont("helvetica", "B", 8);



    $pdf->Cell(190, 15, "CALZATURIFICIO EMMEGIEMME SHOES SRL", 0, 0, "R"); // L'ultimo parametro "1" imposta lo sfondo



} else {

    $pdf->SetFont("times", "", 12);



    $pdf->Cell(0, 10, "Nessun dato disponibile per questo giorno.", 0, 1);

}



// Genera il file PDF

$pdfContent = $pdf->Output("", "S");

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

    $mail = new PHPMailer();

    $mail->isSMTP();

    $mail->Host = $smtpCredentials['production_senderSMTP'];

    $mail->SMTPAuth = true;

    $mail->Username = $smtpCredentials['production_senderEmail'];

    $mail->Password = $smtpCredentials['production_senderPassword'];

    $mail->SMTPSecure = 'ssl';

    $mail->Port = $smtpCredentials['production_senderPORT'];



    // Imposta il mittente

    $mail->setFrom($smtpCredentials['production_senderEmail']);



    // Aggiungi destinatari

    $recipients = explode(';', $_POST['to']);

    foreach ($recipients as $recipient) {

        $mail->addAddress(trim($recipient));

    }



    // Imposta l'oggetto e il corpo dell'email

    $mail->Subject = $_POST['subject'];

    $body = isset($_POST['body']) ? $_POST['body'] : '';

    $body .= "\n\nCalzaturificio Emmegiemme Shoes Srl";

    $mail->Body = $body;

    $mail->addStringAttachment($pdfContent, 'PRODUZIONE.pdf');

    $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    // Invia l'email e gestisci eventuali errori

    if ($mail->send()) {

        echo "Invio";

    } else {

        echo "Errore:" . $mail->ErrorInfo;

        $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    }

} catch (PDOException $e) {

    echo "Errore nella connessione al database: " . $e->getMessage();

}

?>