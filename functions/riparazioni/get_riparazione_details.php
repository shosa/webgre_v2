<?php require_once '../../config/config.php';
$recordId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if ($recordId) {
    try {
        $pdo = getDbInstance();
        $statement = $pdo->prepare("SELECT * FROM riparazioni rp  JOIN id_numerate idn  ON  rp.nu = idn.ID JOIN linee ln ON rp.LINEA = ln.Sigla WHERE rp.IDRIP = :idrip");
        $statement->bindParam(':idrip', $recordId);
        $statement->execute();
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            $details = '<div class="table-responsive border">';
            $details .= '<table class="table table-striped">';
            $details .= '<tr><th>ID:</th><td>' . $record['IDRIP'] . '</td></tr>';
            $details .= '<tr><th>LINEA:</th><td>' . $record['descrizione'] . '</td></tr>';
            $details .= '<tr><th>CODICE:</th><td>' . $record['CODICE'] . '</td></tr>';
            $details .= '<tr><th>ARTICOLO:</th><td>' . $record['ARTICOLO'] . '</td></tr>';
            $details .= '<tr><th>QTA:</th><td>' . $record['QTA'] . '</td></tr>';

            // Tabella per PXX e NXX
            $details .= '<tr><td colspan="2">';
            $details .= '<div class="table-responsive">';
            $details .= '<table class="table table-bordered">';
            $details .= '<tr>';
            for ($i = 1; $i <= 20; $i++) {
                $pKey = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $nKey = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT);
                if (!empty($record[$pKey]) && $record[$pKey] != 0) {
                    $details .= '<th class="bg-dark text-white">' . $record[$nKey] . '</th>';
                }
            }
            $details .= '</tr><tr>';
            for ($i = 1; $i <= 20; $i++) {
                $pKey = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $nKey = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT);
                if (!empty($record[$pKey]) && $record[$pKey] != 0) {
                    $details .= '<td>' . $record[$pKey] . '</td>';
                }
            }
            $details .= '</tr></table>';
            $details .= '</div>';
            $details .= '</td></tr>';

            $details .= '<tr><th>REPARTO:</th><td>' . $record['REPARTO'] . '</td></tr>';
            $details .= '<tr><th>NOTE:</th><td>' . $record['CAUSALE'] . '</td></tr>';
            $details .= '<tr><th>LABORATORIO:</th><td>' . $record['LABORATORIO'] . '</td></tr>';
            $details .= '<tr><th>UTENTE:</th><td>' . $record['UTENTE'] . '</td></tr>';
            $details .= '<tr><th>DATA:</th><td>' . $record['DATA'] . '</td></tr>';
            $details .= '</table>';
            $details .= '</div>';
            $details .= '<a href="file_preview.php?riparazione_id=' . $recordId . '" class="btn btn-block btn-warning btn-lg mt-2"><i class="fa-solid fa-print fa-lg"></i></a>';
            $details .= '<a href="#" class="btn btn-danger btn-block btn-lg delete_btn" data-toggle="modal" data-target="#confirm-delete-' . $recordId . '"><i class="fa-solid fa-trash-alt fa-lg"></i></a>';
            echo $details;
        } else {
            echo 'Record non trovato.';
        }
    } catch (PDOException $e) {
        echo "Errore: " . $e->getMessage();
    }
} else {
    echo 'ID del record non valido.';
}
?>
