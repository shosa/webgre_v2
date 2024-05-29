<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';


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
        // Costruisci il segnaposto per la query
        $placeholders = rtrim(str_repeat('?,', count($idrip_values)), ',');

        // Esegui la query per raccogliere i dati dalla tabella riparazioni
        $sql = "SELECT * FROM riparazioni WHERE IDRIP IN ($placeholders)";

        // Esegui la query utilizzando MySQLiDB
        $results = $db->rawQuery($sql, $idrip_values);

        // Ora hai i dati da utilizzare per generare il PDF con TCPDF

        require_once(BASE_PATH . '/assets/tcpdf/tcpdf.php');
        require_once(BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php');

        // Inizializza un nuovo oggetto TCPDF
        // ...

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
        $header = array('IDRIP', 'Codice Articolo', 'Descrizione', 'Qta', 'Cartellino', 'Commessa', 'Laboratorio', 'Data');

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

    }
}



require_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Crea Packing list</h2>
        </div>
    </div>
    <hr>
    <?php
    include_once(BASE_PATH . '/includes/flash_messages.php');
    ?>
    <form class="well form-horizontal" action=" " method="post" id="contact_form" enctype="multipart/form-data">
        <?php include_once './forms/make_plist_form.php'; ?>
    </form>

    <?php include_once BASE_PATH . '/includes/footer.php'; ?>