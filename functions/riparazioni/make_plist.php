<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = getDbInstance();
    $idrip_values = array();

    // Ciclo per raccogliere i valori da $_POST
    for ($i = 1; $i <= 100; $i++) {
        $input_name = 'idrip' . $i;
        if (isset($_POST[$input_name])) {
            $idrip_values[] = $_POST[$input_name];
        }
    }

    // Esegui la query solo se ci sono valori IDRIP da raccogliere
    if (!empty($idrip_values)) {
        try {
            // Costruisci il segnaposto per la query
            $placeholders = rtrim(str_repeat('?,', count($idrip_values)), ',');

            // Esegui la query per raccogliere i dati dalla tabella riparazioni
            $sql = "SELECT * FROM riparazioni WHERE IDRIP IN ($placeholders)";
            $stmt = $db->prepare($sql);
            $stmt->execute($idrip_values);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ora hai i dati da utilizzare per generare il PDF con TCPDF

            require_once (BASE_PATH . '/assets/tcpdf/tcpdf.php');
            require_once (BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php');

            // Inizializza un nuovo oggetto TCPDF con orientamento orizzontale
            $pdf = new TCPDF('L');

            // Imposta le informazioni del documento PDF
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('WebGRE');
            $pdf->SetTitle('Packing List');
            $pdf->SetSubject('Packing List');
            $pdf->SetKeywords('Packing List, PDF');

            // Aggiungi una pagina vuota
            $pdf->AddPage();

            // Imposta il formato del testo per le intestazioni
            $font = 'helvetica'; // Sostituisci con il font desiderato
            $pdf->SetFont($font, '', 8);

            // Imposta le larghezze delle colonne
            $columnWidths = array(10, 40, 115, 10, 15, 35, 35, 20);

            // Imposta l'array per l'intestazione della tabella
            $header = array('#', 'Codice Articolo', 'Descrizione', 'Qta', 'Cartellino', 'Commessa', 'Laboratorio', 'Data');

            // Imposta i colori per le righe
            $fill = 0; // 0 = Bianco, 1 = Grigio chiaro

            // Aggiungi l'intestazione
            $pdf->SetFillColor(211, 211, 211); // Grigio per l'intestazione
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(0);
            $pdf->SetLineWidth(0.1);

            for ($i = 0; $i < count($header); $i++) {
                $pdf->Cell($columnWidths[$i], 10, $header[$i], 1, 0, 'C', 1);
            }

            $pdf->Ln(); // Vai a una nuova riga

            // Imposta il formato del testo per le righe dei dati
            $pdf->SetFont($font, '', 8);

            // Aggiungi il contenuto al PDF utilizzando i dati da $results
            foreach ($results as $row) {
                $data = array(
                    $row['IDRIP'],
                    $row['CODICE'],
                    $row['ARTICOLO'],
                    $row['QTA'],
                    $row['CARTELLINO'],
                    $row['COMMESSA'],
                    $row['LABORATORIO'],
                    $row['DATA']
                );

                $pdf->SetFillColor($fill == 1 ? 245 : 255); // Cambia colore di sfondo
                $pdf->SetTextColor(0); // Resetta il colore del testo
                $pdf->SetDrawColor(0); // Resetta il colore dei bordi
                $pdf->SetLineWidth(0.1);

                for ($i = 0; $i < count($data); $i++) {
                    $pdf->Cell($columnWidths[$i], 10, $data[$i], 1, 0, 'C', 1);
                }

                $fill = 1 - $fill; // Alterna il colore delle righe

                $pdf->Ln(); // Vai a una nuova riga
            }

            // Salva il PDF o visualizzalo
            $pdf->Output('packing_list.pdf', 'D');
        } catch (PDOException $e) {
            // Gestisci eventuali eccezioni
            echo json_encode(['error' => 'Errore nel recupero dei dati: ' . $e->getMessage()]);
        }
    }
}

require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Crea Packing List</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Crea Packing List</h6>
                        </div>
                        <div class="card-body">

                            <form class="well form-horizontal" action=" " method="post" id="contact_form"
                                enctype="multipart/form-data">
                                <fieldset>
                                    <legend>Inserisci i numeri cedola da includere</legend>
                                    <div class="form-group">
                                        <table class="table table">
                                            <tbody>
                                                <?php for ($row = 1; $row <= 10; $row++): ?>
                                                    <tr style="width:60%">
                                                        <?php for ($col = 1; $col <= 10; $col++): ?>
                                                            <td><input style="padding:4px;" type="number"
                                                                    name="idrip<?php echo ($row - 1) * 10 + $col; ?>"
                                                                    class="form-control"></td>
                                                        <?php endfor; ?>
                                                    </tr>
                                                <?php endfor; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <button type="submit" style="width:100%;font-weight:bold;"
                                                class="btn btn-info">GENERA <i class="fad fa-download"></i></button>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>