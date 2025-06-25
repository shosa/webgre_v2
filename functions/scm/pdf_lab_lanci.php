<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../vendor/autoload.php'; // Composer autoload per mPDF

// Verifica parametri
$laboratorio_id = $_GET['laboratorio_id'] ?? '';

if (empty($laboratorio_id) || !is_numeric($laboratorio_id)) {
    die('ID laboratorio non valido');
}

try {
    $pdo = getDbInstance();
    
    // Ottieni informazioni del laboratorio
    $stmt = $pdo->prepare("SELECT nome_laboratorio FROM scm_laboratori WHERE id = ? AND attivo = TRUE");
    $stmt->execute([$laboratorio_id]);
    $laboratorio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$laboratorio) {
        die('Laboratorio non trovato');
    }
    
    // Query per i lanci in lavorazione del laboratorio
    $stmt = $pdo->prepare("
        SELECT 
            l.id,
            l.numero_lancio,
            l.data_lancio,
            l.note
        FROM scm_lanci l
        WHERE l.laboratorio_id = ? 
        AND l.stato_generale = 'IN_LAVORAZIONE'
        ORDER BY l.data_lancio ASC
    ");
    $stmt->execute([$laboratorio_id]);
    $lanci = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($lanci)) {
        die('Nessun lancio in lavorazione trovato per questo laboratorio');
    }
    
    // Per ogni lancio, ottieni articoli e ultimo avanzamento
    for ($i = 0; $i < count($lanci); $i++) {
        // Articoli del lancio
        $stmt_articoli = $pdo->prepare("
            SELECT 
                a.id,
                a.codice_articolo,
                a.quantita_totale,
                a.quantita_completata
            FROM scm_articoli_lancio a
            WHERE a.lancio_id = ?
            ORDER BY a.ordine_articolo
        ");
        $stmt_articoli->execute([$lanci[$i]['id']]);
        $lanci[$i]['articoli'] = $stmt_articoli->fetchAll(PDO::FETCH_ASSOC);
        
        // Ultimo avanzamento per ogni articolo
        for ($j = 0; $j < count($lanci[$i]['articoli']); $j++) {
            $stmt_avanzamento = $pdo->prepare("
                SELECT 
                    av.data_aggiornamento,
                    av.note_avanzamento,
                    f.nome_fase,
                    av.stato_fase
                FROM scm_avanzamento av
                JOIN scm_fasi_lancio f ON av.fase_id = f.id
                WHERE av.lancio_id = ? AND av.articolo_id = ?
                ORDER BY av.data_aggiornamento DESC, av.id DESC
                LIMIT 1
            ");
            $stmt_avanzamento->execute([$lanci[$i]['id'], $lanci[$i]['articoli'][$j]['id']]);
            $ultimo_avanzamento = $stmt_avanzamento->fetch(PDO::FETCH_ASSOC);
            
            $lanci[$i]['articoli'][$j]['ultimo_avanzamento'] = $ultimo_avanzamento;
        }
    }
    
    // Creazione del PDF con mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 20,
        'margin_bottom' => 20,
        'margin_header' => 10,
        'margin_footer' => 10
    ]);
    
    // Impostazioni del documento
    $mpdf->SetTitle('Lanci in Lavorazione - ' . $laboratorio['nome_laboratorio']);
    $mpdf->SetAuthor('Sistema SCM');
    $mpdf->SetCreator('Sistema SCM');
    
    // CSS per il styling
    $stylesheet = '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 18px;
        }
        
        .header .subtitle {
            color: #666;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .lancio-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .lancio-header {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .articoli-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .articoli-table th {
            background-color: #e9ecef;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }
        
        .articoli-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .articoli-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .paia-info {
            font-size: 10px;
        }
        
        .paia-completate {
            color: #28a745;
            font-weight: bold;
        }
        
        .paia-totali {
            color: #6c757d;
        }
        
        .avanzamento-info {
            font-size: 10px;
        }
        
        .avanzamento-data {
            font-weight: bold;
            color: #007bff;
        }
        
        .avanzamento-fase {
            color: #28a745;
            font-style: italic;
        }
        
        .avanzamento-note {
            color: #666;
            margin-top: 2px;
        }
        
        .footer-info {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        
        .no-avanzamento {
            color: #dc3545;
            font-style: italic;
        }
        
        .stato-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: white;
        }
        
        .stato-COMPLETATO {
            background-color: #28a745;
        }
        
        .stato-IN_CORSO {
            background-color: #ffc107;
            color: #000;
        }
        
        .stato-NON_INIZIATA {
            background-color: #6c757d;
        }
        
        .stato-BLOCCATO {
            background-color: #dc3545;
        }
    </style>';
    
    // Contenuto HTML
    $html = $stylesheet;
    
    $html .= '<div class="header">';
    $html .= '<h1>LANCI IN LAVORAZIONE</h1>';
    $html .= '<div class="subtitle">Laboratorio: <strong>' . htmlspecialchars($laboratorio['nome_laboratorio']) . '</strong></div>';
    $html .= '<div class="subtitle">Generato il: ' . date('d/m/Y H:i') . '</div>';
    $html .= '</div>';
    
    // Contenuto per ogni lancio
    foreach ($lanci as $lancio) {
        $html .= '<div class="lancio-section">';
        
        // Header del lancio
        $html .= '<div class="lancio-header">';
        $html .= '# LANCIO: ' . htmlspecialchars($lancio['numero_lancio']);
        $html .= ' (ID: ' . $lancio['id'] . ')';
        $html .= ' - Data: ' . date('d/m/Y', strtotime($lancio['data_lancio']));
        $html .= '</div>';
        
        // Tabella articoli
        if (!empty($lancio['articoli'])) {
            $html .= '<table class="articoli-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="width: 35%;">ARTICOLO</th>';
            $html .= '<th style="width: 15%;">PAIA</th>';
            $html .= '<th style="width: 30%;">ULTIMO AVANZAMENTO</th>';
            $html .= '<th style="width: 20%;">DATA/FASE</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($lancio['articoli'] as $articolo) {
                $html .= '<tr>';
                
                // Colonna Articolo
                $html .= '<td>';
                $html .= '<strong>' . htmlspecialchars($articolo['codice_articolo']) . '</strong>';
                $html .= '</td>';
                
                // Colonna Paia
                $html .= '<td class="paia-info">';
     
                $html .= '<span class="paia-totali">' . number_format($articolo['quantita_totale']) . '</span>';
         
                $html .= '</td>';
                
                // Colonna Ultimo Avanzamento
                $html .= '<td class="avanzamento-info">';
                if ($articolo['ultimo_avanzamento']) {
                    if (!empty($articolo['ultimo_avanzamento']['note_avanzamento'])) {
                        $html .= htmlspecialchars($articolo['ultimo_avanzamento']['note_avanzamento']);
                    } else {
                        $html .= '<em>Nessuna nota specifica</em>';
                    }
                    
                    // Stato della fase
                    if (!empty($articolo['ultimo_avanzamento']['stato_fase'])) {
                        $stato_classe = 'stato-' . $articolo['ultimo_avanzamento']['stato_fase'];
                        $html .= '<br><span class="stato-badge ' . $stato_classe . '">';
                        $html .= str_replace('_', ' ', $articolo['ultimo_avanzamento']['stato_fase']);
                        $html .= '</span>';
                    }
                } else {
                    $html .= '<span class="no-avanzamento">Nessun avanzamento registrato</span>';
                }
                $html .= '</td>';
                
                // Colonna Data/Fase
                $html .= '<td class="avanzamento-info">';
                if ($articolo['ultimo_avanzamento']) {
                    $html .= '<div class="avanzamento-data">';
                    $html .= date('d/m/Y', strtotime($articolo['ultimo_avanzamento']['data_aggiornamento']));
                    $html .= '</div>';
                    if (!empty($articolo['ultimo_avanzamento']['nome_fase'])) {
                        $html .= '<div class="avanzamento-fase">';
                        $html .= htmlspecialchars($articolo['ultimo_avanzamento']['nome_fase']);
                        $html .= '</div>';
                    }
                } else {
                    $html .= '<span class="no-avanzamento">-</span>';
                }
                $html .= '</td>';
                
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        } else {
            $html .= '<p><em>Nessun articolo trovato per questo lancio</em></p>';
        }
        
        // Note del lancio (se presenti)
        if (!empty($lancio['note'])) {
            $html .= '<div style="margin-top: 10px; padding: 8px; background-color: #fff3cd; border-left: 3px solid #ffc107;">';
            $html .= '<strong>Note Lancio:</strong> ' . nl2br(htmlspecialchars($lancio['note']));
            $html .= '</div>';
        }
        
        $html .= '</div>'; // Fine lancio-section
    }
    
    // Footer
    $html .= '<div class="footer-info">';
    $html .= 'Documento generato automaticamente dal Sistema SCM - ' . date('d/m/Y H:i:s');
    $html .= '<br>Totale lanci in lavorazione: ' . count($lanci);
    $total_articoli = 0;
    foreach ($lanci as $lancio) {
        $total_articoli += count($lancio['articoli']);
    }
    $html .= ' | Totale articoli: ' . $total_articoli;
    $html .= '</div>';
    
    // Scrivi il contenuto nel PDF
    $mpdf->WriteHTML($html);
    
    // Nome del file
    $filename = 'Lanci_Lavorazione_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $laboratorio['nome_laboratorio']) . '_' . date('Y-m-d') . '.pdf';
    
    // Output del PDF
    $mpdf->Output($filename, 'D'); // 'D' = Download, 'I' = Inline nel browser
    
} catch (PDOException $e) {
    die('Errore database: ' . ($debug ?? false ? $e->getMessage() : 'Errore generico'));
} catch (\Mpdf\MpdfException $e) {
    die('Errore generazione PDF: ' . $e->getMessage());
} catch (Exception $e) {
    die('Errore: ' . $e->getMessage());
}
?>