<?php
ob_start();
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/vendor/tcpdf/tcpdf.php';
require_once BASE_PATH . '/vendor/tcpdf/tcpdf_barcodes_1d.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['barcodes'])) {
    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Process barcodes input
        $barcodes = explode("\n", trim($_POST['barcodes']));
        $barcodes = array_filter(array_map('trim', $barcodes));
        $barcodes = array_map(function ($barcode) {
            return str_replace("MGM", "", str_replace("mgm", "", $barcode));
        }, $barcodes);
        $barcodeCounts = array_count_values($barcodes);
        $uniqueBarcodes = array_keys($barcodeCounts);

        // Prepare the SQL statement
        $placeholders = implode(',', array_fill(0, count($uniqueBarcodes), '?'));
        $sql = "SELECT DISTINCT barcode, art, des FROM inv_anagrafiche WHERE barcode IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($uniqueBarcodes);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $azione = $_POST['azione'] ?? 'PRELIEVO'; // Default to 'PRELIEVO' if not specified
        $dicitura = ($azione === 'VERSAMENTO') ? 'Distinta di Versamento a Magazzino' : 'Distinta di Prelievo di Magazzino';

        if ($results) {
            $pdf = new TCPDF();
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->AddPage();

            // Add logo
            $logoPath = '../../img/logo.png'; // Make sure the path is correct
            $pdf->Image($logoPath, 10, 10, 50, '', 'PNG');

            // Add document notation
            $pdf->SetXY(60, 15); // Adjust these coordinates as needed
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, 'DOCUMENTO AD USO INTERNO', 0, 1, 'R');

            // Add title and space for date
            $pdf->Ln(15); // Adjust space after logo and notation
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $dicitura, 0, 1, 'L');
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Del _____________    ', 0, 1, 'R');
            $pdf->Ln(5); // Adjust space after title and notation

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(240, 240, 240); // Light gray for alternating rows
            $fill = false; // Variable to alternate row fill

            // Table headers
            $pdf->Cell(50, 7, 'CODICE', 1, 0, 'L', false);
            $pdf->Cell(120, 7, 'DESCRIZIONE', 1, 0, 'L', false);
            $pdf->Cell(15, 7, 'QTA', 1, 1, 'C', false);

            foreach ($results as $row) {
                $qty = $barcodeCounts[$row['barcode']] ?? 1;
                $pdf->Cell(50, 7, $row['art'], 'LR', 0, 'L', $fill);
                $pdf->Cell(120, 7, $row['des'], 'LR', 0, 'L', $fill);
                $pdf->Cell(15, 7, $qty, 'LR', 1, 'C', $fill);
                $fill = !$fill; // Alternate row fill
            }

            // Close table
            $pdf->Cell(185, 0, '', 'T');

            ob_end_clean();
            $pdf->Output('DISTINTA DI PRELIEVO.pdf', 'I');
            exit;
        } else {
            $_SESSION['danger'] = "Errore nel reperimento informazioni o codice inesistente";
        }
    } catch (PDOException $e) {
        echo "Errore di connessione: " . $e->getMessage();
    }
}
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Etichette</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/tools/eti_index">Etichette</a></li>
                        <li class="breadcrumb-item active">Crea Lista</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Crea Lista di Carico/Prelievo</h6>
                        </div>
                        <div class="card-body">
                            <form action="eti_decode.php" method="POST">
                                <div class="form-group">
                                    <label for="azione">Azione:</label>
                                    <select class="form-control" id="azione" name="azione" required>
                                        <option value="PRELIEVO">PRELIEVO</option>
                                        <option value="VERSAMENTO">VERSAMENTO</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="barcodes">Inserisci i barcode:</label>
                                    <textarea class="form-control" id="barcodes" name="barcodes" required
                                        rows="10"></textarea>
                                </div>
                                <button class="btn btn-primary" type="submit" style="width:100%">GENERA</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include BASE_PATH . "/components/scripts.php"; ?>
            <?php include BASE_PATH . "/components/footer.php"; ?>
        </div>

    </div>
</body>