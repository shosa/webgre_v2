<?php
// export_excel.php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Carica direttamente il composer autoload
require_once BASE_PATH . '/vendor/autoload.php';

// Controlla esplicitamente se PhpSpreadsheet esiste dopo il caricamento dell'autoload
if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    $_SESSION['error'] = "Libreria PhpSpreadsheet non disponibile. Controlla l'installazione composer.";
    header("Location: lista_macchinari");
    exit;
}

// Importazioni a livello di file
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    // Ottieni l'istanza del database
    $pdo = getDbInstance();

    // Recupera i filtri di ricerca dalla sessione o dalla query
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $tipologia = isset($_GET['tipologia']) ? $_GET['tipologia'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'data_creazione';
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    // Query di base
    $query = "SELECT id, matricola, tipologia, fornitore, modello, data_acquisto, rif_fattura, note, data_creazione, data_aggiornamento FROM mac_anag WHERE 1=1";
    $params = [];

    // Aggiunta dei filtri
    if (!empty($search)) {
        $query .= " AND (matricola LIKE ? OR fornitore LIKE ? OR modello LIKE ? OR note LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }

    if (!empty($tipologia)) {
        $query .= " AND tipologia = ?";
        $params[] = $tipologia;
    }

    // Ordinamento
    $validSortColumns = ['matricola', 'tipologia', 'fornitore', 'modello', 'data_acquisto', 'data_creazione'];
    $validSortOrders = ['ASC', 'DESC'];

    if (!in_array($sort, $validSortColumns)) {
        $sort = 'data_creazione';
    }

    if (!in_array($order, $validSortOrders)) {
        $order = 'DESC';
    }

    $query .= " ORDER BY $sort $order";

    // Esecuzione query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Genera il file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Macchinari');
    
    // Intestazione con logo o titolo
    $sheet->setCellValue('A1', 'ELENCO MACCHINARI AZIENDALI');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Data di generazione
    $sheet->setCellValue('A2', 'Generato il: ' . date('d/m/Y H:i'));
    $sheet->mergeCells('A2:H2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
    // Filtri applicati
    $filtriText = 'Filtri: ';
    if (!empty($search)) {
        $filtriText .= "Ricerca \"$search\" | ";
    }
    if (!empty($tipologia)) {
        $filtriText .= "Tipologia \"$tipologia\" | ";
    }
    $filtriText .= "Ordinamento per \"$sort\" " . strtolower($order);
    
    $sheet->setCellValue('A3', $filtriText);
    $sheet->mergeCells('A3:H3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    
    // Aggiungi un po' di spazio
    $sheet->getRowDimension(4)->setRowHeight(15);
    
    // Intestazioni colonne
    $headers = [
        'ID', 'Matricola', 'Tipologia', 'fornitore', 'Modello', 'Data Acquisto', 'Riferimento Fattura', 'Note'
    ];
    
    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col, 5, $header);
        $col++;
    }
    
    // Stile intestazioni
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4e73df'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    
    $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);
    $sheet->getRowDimension(5)->setRowHeight(20);
    
    // Larghezza colonne
    $sheet->getColumnDimension('A')->setWidth(10);   // ID
    $sheet->getColumnDimension('B')->setWidth(25);   // Matricola
    $sheet->getColumnDimension('C')->setWidth(25);   // Tipologia
    $sheet->getColumnDimension('D')->setWidth(25);   // fornitore
    $sheet->getColumnDimension('E')->setWidth(25);   // Modello
    $sheet->getColumnDimension('F')->setWidth(15);   // Data Acquisto
    $sheet->getColumnDimension('G')->setWidth(25);   // Riferimento Fattura
    $sheet->getColumnDimension('H')->setWidth(40);   // Note
    
    // Dati macchinari
    $row = 6;
    
    // Stile alternato per le righe
    $evenRowStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F8F9FC'],
        ]
    ];
    
    $oddRowStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFFFF'],
        ]
    ];
    
    // Stile bordi per tutte le celle
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E3E6F0'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ]
    ];
    
    foreach ($macchinari as $index => $macchinario) {
        $sheet->setCellValue('A' . $row, $macchinario['id']);
        $sheet->setCellValue('B' . $row, $macchinario['matricola']);
        $sheet->setCellValue('C' . $row, $macchinario['tipologia']);
        $sheet->setCellValue('D' . $row, $macchinario['fornitore']);
        $sheet->setCellValue('E' . $row, $macchinario['modello']);
        
        // Formattazione data
        $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($macchinario['data_acquisto'])));
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        
        $sheet->setCellValue('G' . $row, $macchinario['rif_fattura']);
        $sheet->setCellValue('H' . $row, $macchinario['note']);
        
        // Applica stile alternato
        if ($index % 2 == 0) {
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($evenRowStyle);
        } else {
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($oddRowStyle);
        }
        
        // Applica bordi e allineamento
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
        
        // Allineamento specifico per colonne
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Imposta altezza riga
        $sheet->getRowDimension($row)->setRowHeight(18);
        
        $row++;
    }
    
    // Bordi attorno a tutte le celle con dati
    $sheet->getStyle('A5:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    // Abilita filtro automatico
    $sheet->setAutoFilter('A5:H5');
    
    // Aggiungi piè di pagina con conteggio
    $row += 1;
    $sheet->setCellValue('A' . $row, 'Totale macchinari: ' . count($macchinari));
    $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
    // Nome file
    $fileName = 'macchinari_' . date('Ymd_His') . '.xlsx';
    
    // Imposta gli header HTTP per il download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    
    // Salva il file Excel
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    // Log dettagliato dell'errore per il debug
    error_log("Errore esportazione Excel: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    $_SESSION['error'] = "Errore nell'esportazione Excel: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}