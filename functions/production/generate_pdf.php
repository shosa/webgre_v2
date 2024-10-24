<?php require ("../../config/config.php");
require_once BASE_PATH . '/vendor/autoload.php';
$pdf = new TCPDF("P", "mm", "A4", true, "UTF-8", false);
$pdf->SetMargins(7, 7, 7);
$pdf->SetAutoPageBreak(true, 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$month = $_GET["month"];
$day = $_GET["day"];
$conn = getDbInstance();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT * FROM prod_mesi WHERE MESE = :month AND GIORNO = :day";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':day', $day);
$stmt->execute();
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
$pdf->SetTitle("PRODUZIONE DEL " . $day . " " . $month);
$pdf->AddPage();
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $week = $row["WEEK"];
        $pdf->SetLineWidth(0.5);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont("helvetica", "B", 20);
        $pdf->Cell(0, 3, "RAPPORTO DI PRODUZIONE " . $row["NOMEGIORNO"] . " " . $row["GIORNO"] . " " . $row["MESE"] . " 2024", 0, 1, "C", true);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(7, 20, 196, 78, "DF");
        $pdf->SetFillColor(0, 0, 0);
        $pdf->Rect(7, 20, 62, 9.8, "DF");
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont("helvetica", "B", 17);
        $pdf->Ln(1);
        $col1Width = 35;
        $col2Width = 25;
        $col3Width = 15;
        $pdf->Cell($col1Width, 10, "DATI GIORNALIERI", 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetLineWidth(0.1);
        $isGray = false;
        $grayColor = [240, 240, 240];
        for ($i = 0; $i < 9; $i++) {
            if ($isGray) {
                $pdf->SetFillColor($grayColor[0], $grayColor[1], $grayColor[2]);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }
            $pdf->Rect(10, 30 + $i * 7, 190, 9, "DF");
            $isGray = !$isGray;
        }
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "MANOVIA 1:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["MANOVIA1"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["MANOVIA1NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "MANOVIA 2:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["MANOVIA2"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["MANOVIA2NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "ORLATURA 1:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["ORLATURA1"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["ORLATURA1NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "ORLATURA 2:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["ORLATURA2"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["ORLATURA2NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "ORLATURA 3:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["ORLATURA3"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["ORLATURA3NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "ORLATURA 4:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["ORLATURA4"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["ORLATURA4NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "TAGLIO 1:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["TAGLIO1"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["TAGLIO1NOTE"], 0, 1);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col1Width, 7, "TAGLIO 2:", 0, 0);
        $pdf->SetFont("helvetica", "", 11);
        $pdf->Cell($col2Width, 7, $row["TAGLIO2"], 0, 0);
        $pdf->SetFont("helvetica", "B", 9);
        $pdf->Cell($col3Width, 7, "NOTE:", 0, 0);
        $pdf->SetFont("helvetica", "", 7);
        $pdf->Cell($col2Width, 7, $row["TAGLIO2NOTE"], 0, 1);
        $pdf->Rect(7, 100, 196, 147, "DF");
        $pdf->SetFillColor(0, 0, 0);
        $pdf->Rect(7, 100, 77, 10, "DF");
        $pdf->SetFont("helvetica", "B", 12);
        $pdf->Ln(5);
        $pdf->Cell(185, 28, "SETTIMANA " . $week, 0, 0, "R");
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont("helvetica", "B", 17);
        $pdf->Ln(10);
        $pdf->Cell($col1Width, 10, "RIEPILOGO SETTIMANA", 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $sql2 = "SELECT ID, MESE, GIORNO, NOMEGIORNO, MANOVIA1, MANOVIA1NOTE, MANOVIA2, MANOVIA2NOTE, ORLATURA1, ORLATURA1NOTE, ORLATURA2, ORLATURA2NOTE, ORLATURA3, ORLATURA3NOTE, ORLATURA4, ORLATURA4NOTE, TAGLIO1, TAGLIO1NOTE, TAGLIO2, TAGLIO2NOTE, TOTALITAGLIO, TOTALIORLATURA, TOTALIMONTAGGIO\n\n        FROM prod_mesi\n\n        WHERE (((WEEK)='" . $week . "')) And Not NOMEGIORNO='DOMENICA'\n\n        ORDER BY ID;";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $pdf->Ln(14);
        $pdf->SetLineWidth(0.3);
        if ($stmt2->rowCount() > 0) {
            $pdf->SetFont("helvetica", "B", 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetFont("helvetica", "B", 8);
            $pdf->Cell(14, 6, "DATA", 1, 0, "C", 1);
            $pdf->Cell(30, 6, "GIORNO", 1, 0, "C", 1);
            $pdf->Cell(25, 6, "MANOVIA 1", 1, 0, "C", 1);
            $pdf->Cell(25, 6, "MANOVIA 2", 1, 0, "C", 1);
   
            $pdf->Cell(15, 6, "ORL 1", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 2", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 3", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 4", 1, 0, "C", 1);
            $pdf->Cell(18, 6, "TAGLIO 1", 1, 0, "C", 1);
            $pdf->Cell(18, 6, "TAGLIO 2", 1, 1, "C", 1);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont("helvetica", "", 10);
            $pdf->SetFillColor(255, 255, 255);
            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $pdf->Cell(14, 8, $row2["GIORNO"], 1, 0, "C");
                $pdf->Cell(30, 8, $row2["NOMEGIORNO"], 1, 0, "C");
                $pdf->Cell(25, 8, $row2["MANOVIA1"], 1, 0, "C");
                $pdf->Cell(25, 8, $row2["MANOVIA2"], 1, 0, "C");
              
                $pdf->Cell(15, 8, $row2["ORLATURA1"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["ORLATURA2"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["ORLATURA3"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["ORLATURA4"], 1, 0, "C");
                $pdf->Cell(18, 8, $row2["TAGLIO1"], 1, 0, "C");
                $pdf->Cell(18, 8, $row2["TAGLIO2"], 1, 1, "C");
            }
            $sql3 = "SELECT SUM(MANOVIA1) AS TOTALEMANOVIA1 , SUM(MANOVIA2) AS TOTALEMANOVIA2,  SUM(ORLATURA1) AS TOTALEORLATURA1, SUM(ORLATURA2) AS TOTALEORLATURA2, SUM(ORLATURA3) AS TOTALEORLATURA3,SUM(ORLATURA4) AS TOTALEORLATURA4, SUM(TAGLIO1) AS TOTALETAGLIO1, SUM(TAGLIO2) AS TOTALETAGLIO2\n\n\n\n            FROM prod_mesi\n\n\n\n            WHERE (((WEEK)='" . $week . "')) And Not NOMEGIORNO='DOMENICA'\n\n\n\n            ORDER BY ID;";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->execute();
            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(44, 6, "TOTALI SETTIMANA", 1, 0, "C", 1);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Cell(25, 6, $row3["TOTALEMANOVIA1"], 1, 0, "C");
                $pdf->Cell(25, 6, $row3["TOTALEMANOVIA2"], 1, 0, "C");
  
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA1"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA2"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA3"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA4"], 1, 0, "C");
                $pdf->Cell(18, 6, $row3["TOTALETAGLIO1"], 1, 0, "C");
                $pdf->Cell(18, 6, $row3["TOTALETAGLIO2"], 1, 1, "C");
            }
            $pdf->SetLineWidth(0.5);
        } else {
        }
        $pdf->SetFillColor(0, 0, 0);
        $pdf->Rect(7, 178, 60, 10, "DF");
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont("helvetica", "B", 17);
        $pdf->Ln(4);
        $pdf->Cell($col1Width, 10, "RIEPILOGO MESE", 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("helvetica", "B", 12);
        $pdf->Cell(150, 16, $month, 0, 0, "R");
        $sql2 = "SELECT SETTIMANAMESE, Sum(MANOVIA1) AS TOTALEMANOVIA1, \n\n\n\n        Sum(MANOVIA2) AS TOTALEMANOVIA2,\n\n\n\n         Sum(ORLATURA1) AS TOTALEORLATURA1, Sum(ORLATURA2) AS TOTALEORLATURA2,\n\n\n\n         Sum(ORLATURA3) AS TOTALEORLATURA3, Sum(ORLATURA4) AS TOTALEORLATURA4, Sum(TAGLIO1) AS TOTALETAGLIO1,\n\n\n\n          Sum(TAGLIO2) AS TOTALETAGLIO2, Max(GIORNO) AS MaxDiGIORNO, Min(GIORNO) AS MinDiGIORNO\n\n\n\n        FROM prod_mesi\n\n\n\n        WHERE (((MESE)='" . $month . "'))\n\n\n\n        GROUP BY SETTIMANAMESE\n\n\n\n        HAVING (((SETTIMANAMESE)<>0));";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $pdf->Ln(14);
        $pdf->SetLineWidth(0.3);
        if ($stmt2->rowCount() > 0) {
            $pdf->SetFont("helvetica", "B", 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetFont("helvetica", "B", 8);
            $pdf->Cell(45, 6, "SETTIMANA", 1, 0, "C", 1);
            $pdf->Cell(25, 6, "MANOVIA 1", 1, 0, "C", 1);
            $pdf->Cell(25, 6, "MANOVIA 2", 1, 0, "C", 1);

            $pdf->Cell(15, 6, "ORL 1", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 2", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 3", 1, 0, "C", 1);
            $pdf->Cell(15, 6, "ORL 4", 1, 0, "C", 1);
            $pdf->Cell(18, 6, "TAGLIO 1", 1, 0, "C", 1);
            $pdf->Cell(18, 6, "TAGLIO 2", 1, 1, "C", 1);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont("helvetica", "", 10);
            $pdf->SetFillColor(255, 255, 255);
            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $pdf->Cell(45, 8, $row2["SETTIMANAMESE"], 1, 0, "C");
                $pdf->Cell(25, 8, $row2["TOTALEMANOVIA1"], 1, 0, "C");
                $pdf->Cell(25, 8, $row2["TOTALEMANOVIA2"], 1, 0, "C");

                $pdf->Cell(15, 8, $row2["TOTALEORLATURA1"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["TOTALEORLATURA2"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["TOTALEORLATURA3"], 1, 0, "C");
                $pdf->Cell(15, 8, $row2["TOTALEORLATURA4"], 1, 0, "C");
                $pdf->Cell(18, 8, $row2["TOTALETAGLIO1"], 1, 0, "C");
                $pdf->Cell(18, 8, $row2["TOTALETAGLIO2"], 1, 1, "C");
            }
            $sql3 = "SELECT SUM(MANOVIA1) AS TOTALEMANOVIA1 , SUM(MANOVIA2) AS TOTALEMANOVIA2,\n\n\n\n             SUM(ORLATURA1) AS TOTALEORLATURA1,\n\n\n\n             SUM(ORLATURA2) AS TOTALEORLATURA2, SUM(ORLATURA3) AS TOTALEORLATURA3, SUM(ORLATURA4) AS TOTALEORLATURA4,\n\n\n\n              SUM(TAGLIO1) AS TOTALETAGLIO1, SUM(TAGLIO2) AS TOTALETAGLIO2\n\n\n\n            FROM prod_mesi\n\n\n\n            WHERE (((MESE)='" . $month . "'))\n\n\n\n            ORDER BY ID;";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->execute();
            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(192, 192, 192);
                $pdf->Cell(45, 6, "TOTALI", 1, 0, "C", 1);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Cell(25, 6, $row3["TOTALEMANOVIA1"], 1, 0, "C");
                $pdf->Cell(25, 6, $row3["TOTALEMANOVIA2"], 1, 0, "C");

                $pdf->Cell(15, 6, $row3["TOTALEORLATURA1"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA2"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA3"], 1, 0, "C");
                $pdf->Cell(15, 6, $row3["TOTALEORLATURA4"], 1, 0, "C");
                $pdf->Cell(18, 6, $row3["TOTALETAGLIO1"], 1, 0, "C");
                $pdf->Cell(18, 6, $row3["TOTALETAGLIO2"], 1, 1, "C");
            }
            $pdf->SetLineWidth(0.5);
        } else {
        }
        $pdf->SetFont("helvetica", "", 13);
        $pdf->Ln(8);
        $col1Width = 48;
        $col2Width = 17;
        $pdf->SetTextColor(0, 0, 0);
        $pagewidth = $pdf->getPageWidth();
        $totalwidth = ($col1Width + $col2Width) * 3;
        $x = ($pagewidth - $totalwidth) / 2;
        $pdf->SetX($x);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetLineWidth(0.1);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont("helvetica", "B", 14);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetFont("helvetica", "B", 14);
        $pdf->Cell($col1Width, 10, "TOT. TAGLIO:", 1, 0, "C", 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("helvetica", "", 17);
        $pdf->Cell($col2Width, 10, $row["TOTALITAGLIO"], 1, 0, "C");
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetFont("helvetica", "B", 14);
        $pdf->Cell($col1Width, 10, "TOT. ORLATURA:", 1, 0, "C", 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("helvetica", "", 17);
        $pdf->Cell($col2Width, 10, $row["TOTALIORLATURA"], 1, 0, "C");
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetFont("helvetica", "B", 14);
        $pdf->Cell($col1Width, 10, "TOT. MONTAGGIO:", 1, 0, "C", 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont("helvetica", "", 17);
        $pdf->Cell($col2Width, 10, $row["TOTALIMONTAGGIO"], 1, 0, "C");
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->Ln(5);
    $pdf->SetFont("helvetica", "B", 8);
    $pdf->Cell(190, 15, "CALZATURIFICIO EMMEGIEMME SHOES SRL", 0, 0, "R");
} else {
    $pdf->SetFont("times", "", 12);
    $pdf->Cell(0, 10, "Nessun dato disponibile per questo giorno.", 0, 1);
}
$pdf->Output("PRODUZIONE.pdf", "I"); ?>