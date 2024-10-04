<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
$pdo = getDbInstance();

// Ricevi la stringa di riparazione_id e dividila in un array
$idrip_str = filter_input(INPUT_GET, 'ids');
$idrip_array = explode(';', $idrip_str);

include(BASE_PATH . "/components/header.php");
require_once BASE_PATH . '/vendor/autoload.php';
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/riparazioni/riparazioni">Elenco
                                Riparazioni</a></li>
                        <li class="breadcrumb-item active">Stampa Cedole</li>
                    </ol>

                    <?php
                    $filename = 'CEDOLA.pdf';
                    $pdfPath = BASE_PATH . '/temp/pdf/' . $filename;
                    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                    $pdf->SetMargins(7, 7, 7);
                    $pdf->SetAutoPageBreak(true, 10);
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                    echo '<div class="pdf-container">';
                    foreach ($idrip_array as $idrip) {
                        $stmt = $pdo->prepare("SELECT * FROM riparazioni
        LEFT JOIN id_numerate ON riparazioni.NU = id_numerate.ID
        WHERE IDRIP = :idrip");
                        $stmt->bindParam(':idrip', $idrip);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($stmt->rowCount() == 1) {

                            //// Aggiungi una nuova pagina
                    
                            $pdf->AddPage();
                            $pdf->SetFont('helvetica', 'B', 30);
                            // $pdf->Image($barcode, 100, 50, 100, 50);
                    
                            $pdf->SetLineWidth(0.7);
                            $pdf->SetFillColor(214, 220, 229);
                            $pdf->Rect(7, 7, 196, 25, 'DF');
                            $pdf->Rect(7, 7, 46, 25, 'D');
                            $pdf->MultiCell(180, 10, "CEDOLA DI RIPARAZIONE", 0, 'L', false, 1, 60, 12, true, 0, false, true, 0, 'T', false);
                            $pdf->SetY(12);
                            // -----------------------------------------------------------------------------
                    
                            $pdf->SetFont('helvetica', '', 10);
                            $style = array(
                                'position' => '',
                                'align' => 'C',
                                'stretch' => false,
                                'fitwidth' => true,
                                'cellfitalign' => '',
                                'border' => false,
                                'hpadding' => 'auto',
                                'vpadding' => 'auto',
                                'fgcolor' => array(0, 0, 0),
                                'bgcolor' => false,
                                'text' => true,
                                'font' => 'helvetica',
                                'fontsize' => 10,
                                'stretchtext' => 3,
                            );
                            $pdf->write1DBarcode($idrip, 'C39', '', '', '', 18, 0.4, $style, 'N');
                            $pdf->SetFont('helvetica', 'B', 30);
                            //$pdf->Cell(50, 25, $idrip, 1, 0, 'C', true);
                    
                            $current_x = $pdf->GetX();
                            $pdf->SetFont('helvetica', 'B', 30);
                            //$pdf->Cell(0, 25, 'CEDOLA DI RIPARAZIONE\n', 1, 1, 'C', true);
                    
                            $pdf->Ln(4);
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->SetFont('helvetica', 'B', 10);
                            $pdf->Cell(0, 3, 'CALZATURIFICIO EMMEGIEMME SHOES S.R.L', 1, 1, 'C', true);
                            $pdf->SetMargins(9, 7, 7);
                            $pdf->SetFont('helvetica', 'N', 12);
                            $pdf->Ln(1);
                            // CONTENUTO
                    
                            $pdf->Rect(7, 41, 196, 60, 'D');
                            $pdf->Cell(155, 10, 'LABORATORIO:', 0, 0);
                            $pdf->Cell(50, 10, 'REPARTO:', 0, 1);
                            $pdf->SetFont('helvetica', 'B', 25);
                            $pdf->Cell(155, 10, $row['LABORATORIO'], 0, 0);
                            $pdf->SetFont('helvetica', 'B', 16);
                            $pdf->Cell(50, 10, $row['REPARTO'], 0, 1);
                            $pdf->SetFont('helvetica', 'N', 12);
                            $pdf->Cell(50, 10, 'CARTELLINO:', 0, 0);
                            $pdf->Cell(70, 10, 'COMMESSA:', 0, 0);
                            $pdf->Cell(35, 10, 'QTA:', 0, 0);
                            $pdf->Cell(50, 10, 'LINEA:', 0, 1);
                            $pdf->SetFont('helvetica', 'B', 16);
                            $pdf->Cell(50, 10, $row['CARTELLINO'], 0, 0);
                            $pdf->Cell(70, 10, $row['COMMESSA'], 0, 0);
                            $pdf->Cell(35, 10, $row['QTA'], 0, 0);
                            $pdf->Cell(50, 10, $row['LINEA'], 0, 1);
                            $pdf->Ln(1);
                            $pdf->SetFont('helvetica', 'N', 12);
                            $pdf->SetMargins(7, 7, 7);
                            $pdf->Cell(120, 10, 'ARTICOLO:', 0, 0);
                            $pdf->Cell(35, 10, 'URGENZA:', 0, 0);
                            $pdf->SetFont('helvetica', 'B', 16);
                            $pdf->Cell(70, 10, $row['URGENZA'], 0, 1);
                            $pdf->SetFont('helvetica', 'B', 20);
                            $pdf->SetFillColor(0, 0, 0); // imposto il colore di riempimento della cella
                    
                            $pdf->SetTextColor(255, 255, 255); // imposto il colore del testo
                    
                            $pdf->Cell(196, 10, $row['ARTICOLO'], 0, 1, 'C', true, '', 1);
                            $pdf->SetFillColor(255, 255, 255); // ripristino il colore di riempimento predefinito
                    
                            $pdf->SetTextColor(0, 0, 0); // ripristino il colore del testo predefinito
                    
                            $pdf->Ln(3);
                            $pdf->Rect(7, 104, 196, 35, 'D');
                            $pdf->SetMargins(13, 5, 5);
                            $pdf->SetFont('helvetica', 'B', 15);
                            $pdf->Ln(3);
                            $pdf->Cell(10, 5, 'NUMERATA DA RIPARARE:', 0, 1);
                            $pdf->SetFont('helvetica', '', 13);
                            $pdf->SetFillColor(240, 240, 240);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->SetFont('helvetica', 'B', 10);
                            $html = '<table style="border-collapse: collapse;"><tr style="background-color: #f2f2f2; vertical-allign:center; text-align: center; font-weight: bold;">';
                            $html = '<table style="border-collapse: collapse;"><tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">';
                            for ($i = 1; $i <= 20; $i++) {
                                $n_value = $row['N' . str_pad($i, 2, '0', STR_PAD_LEFT)];
                                $html .= '<td style="border: 1px solid black; padding: 0px; text-align: center; vertical-align: middle;" width="26" height="20">' . $n_value . '</td>';
                            }
                            $html .= '</tr><tr>';
                            for ($i = 1; $i <= 20; $i++) {
                                $p_value = $row['P' . str_pad($i, 2, '0', STR_PAD_LEFT)];
                                $html .= '<td style="border: 1px solid black; padding: 0px; text-align: center; vertical-align: middle;" width="26" height="20">';
                                $html .= ($p_value == 0) ? '&nbsp;' : $p_value;
                                $html .= '</td>';
                            }
                            $html .= '</tr></table>';
                            $pdf->Ln(5);
                            $pdf->writeHTML($html, true, false, true, false, '');
                            $pdf->Ln(5); // nuova riga.
                    
                            $pdf->SetFont('helvetica', 'B', 15);
                            $pdf->Rect(7, 142, 196, 77, 'D');
                            $pdf->Cell(20, 10, 'MOTIVO RIPARAZIONE', 0, 1);
                            $pdf->SetFont('helvetica', '', 15);
                            $pdf->SetCellPaddings(0, 0, 0, 0);
                            if ($row['URGENZA'] === 'ALTA') {
                                $pdf->SetTextColor(180, 180, 180);
                                $pdf->SetFont('helvetica', 'B', 30);
                                $pdf->Cell(10, 120, 'URGENTE', 0, 0);
                                $pdf->SetTextColor(0, 0, 0);
                                $pdf->SetFont('helvetica', '', 13);
                            }
                            $pdf->MultiCell(155, 20, $row['CAUSALE'], 0, 'L', false, 0, '', '', true, 0, false, true, 0, 'T', false);
                            $pdf->SetMargins(7, 7, 7);
                            $pdf->SetFillColor(0, 0, 0); // imposto il colore di riempimento della cella
                    
                            $pdf->SetTextColor(255, 255, 255); // imposto il colore del testo
                    
                            $pdf->SetFont('helvetica', 'B', 20);
                            $pdf->Ln(70); // nuova riga.
                    
                            $pdf->SetMargins(13, 5, 5);
                            $pdf->Cell(196, 10, $row['CODICE'], 0, 1, 'C', true, '', 1);
                            $pdf->SetFillColor(255, 255, 255); // ripristino il colore di riempimento predefinito
                    
                            $pdf->SetTextColor(0, 0, 0); // ripristino il colore del testo predefinito
                    
                            $pdf->Ln(5); // nuova riga.
                    
                            $pdf->SetFont('helvetica', 'B', 16);
                            $pdf->SetTextColor(115, 115, 115);
                            $pdf->Cell(60, 10, 'RIPARAZIONE N°:', 0, 0);
                            $pdf->SetFont('helvetica', '', 25);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->SetDrawColor(128, 128, 128);
                            $pdf->Cell(30, 10, $row['IDRIP'], 1, 0, 'C', true);
                            $pdf->SetDrawColor(0, 0, 0);
                            $pdf->Cell(30, 10, '', 0, 0);
                            $pdf->SetFillColor(222, 222, 222);
                            $pdf->Cell(60, 10, $row['REPARTO'], 1, 1, 'C', true);
                            $pdf->SetMargins(7, 7, 7);
                            $pdf->SetFont('helvetica', '', 12);
                            $pdf->Ln(5); // nuova riga.
                    
                            $pdf->Cell(50, 10, 'CEDOLA CREATA IL:', 0, 0, 'R');
                            $pdf->Cell(60, 10, $row['DATA'], 0, 0, 'R');
                            $pdf->Line(10, 263, 200, 263);
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->SetFont('helvetica', 'B', 12);
                            $pdf->Cell(80, 10, $row['UTENTE'], 0, 1, 'R');
                            $pdf->Ln(3); // nuova riga.
                    
                            $pdf->SetFont('helvetica', '', 12);
                            $url = $dominio . '/functions/mobile/mobile.php?idrip=' . $idrip;
                            // Genera il QR code
                    
                            $qrCodeSize = 20;  // Dimensione del QR code
                    
                            $qrCodeX = 95;     // Coordinata X del QR code
                    
                            $qrCodeY = 265;    // Coordinata Y del QR code
                    
                            $style = array(
                                'border' => 0,
                                'vpadding' => 'auto',
                                'hpadding' => 'auto',
                                'fgcolor' => array(0, 0, 0),
                                'bgcolor' => false,
                                'module_width' => 0.7,  // width of a single module in points
                    
                                'module_height' => 0.7  // height of a single module in points
                    
                            );
                            $pdf->write2DBarcode($url, 'QRCODE,L', $qrCodeX, $qrCodeY, $qrCodeSize, $qrCodeSize, $style, 'N');
                            // Invia il PDF al browser
                    

                            // Visualizza il PDF nel div "anteprima"
                    

                        } else {
                            echo "<p>Nessuna riparazione trovata per ID: $idrip</p>";
                        }

                    }

                    $pdfData = $pdf->Output($pdfPath, 'F');
                    echo '<iframe src="'.BASE_PATH . '/vendor/pdfjs/web/viewer.html?file=' . urlencode($pdfPath) . '" width="100%" height="600px"></iframe>';
                    ?>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>

    </div>
</body>



<?php include_once BASE_PATH . '/components/scripts.php'; ?>