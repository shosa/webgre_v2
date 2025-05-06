<?php
require_once '../../config/config.php';
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/components/header.php';

$progressivo = $_GET['progressivo'];
$dir = 'src/' . $progressivo;
$files = glob($dir . '/*.xlsx');

$db = getDbInstance();

// Documento
$stmt = $db->prepare("SELECT * FROM exp_documenti WHERE id = ?");
$stmt->execute([$progressivo]);
$documento = $stmt->fetch(PDO::FETCH_ASSOC);

// Articoli
$stmt = $db->prepare("SELECT * FROM exp_dati_articoli WHERE id_documento = ? ORDER BY voce_doganale ASC, codice_articolo ASC");
$stmt->execute([$progressivo]);
$articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Terzista
$stmt = $db->prepare("SELECT * FROM exp_terzisti WHERE id = ?");
$stmt->execute([$documento['id_terzista']]);
$terzista = $stmt->fetch(PDO::FETCH_ASSOC);

// Piede
$stmt = $db->prepare("SELECT * FROM exp_piede_documenti WHERE id_documento = ?");
$stmt->execute([$progressivo]);
$piede = $stmt->fetch(PDO::FETCH_ASSOC);

// Dati mancanti
$datiMancanti = [];
$stmt = $db->prepare("SELECT * FROM exp_dati_mancanti WHERE id_documento = ?");
$stmt->execute([$progressivo]);
$datiMancanti = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($datiMancanti as &$row) {
    $stmt = $db->prepare("SELECT descrizione, um, voce_doganale FROM exp_dati_articoli WHERE codice_articolo = ?");
    $stmt->execute([$row['codice_articolo']]);
    $articolo = $stmt->fetch(PDO::FETCH_ASSOC);

    $row['descrizione'] = $articolo['descrizione'] ?? '';
    $row['um'] = $articolo['um'] ?? '';
    $row['voce_doganale'] = $articolo['voce_doganale'] ?? '';
}

$total = 0;
foreach ($articoli as $articolo) {
    if ($articolo['qta_reale'] > 0) {
        $total += round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
    }
}

function getUniqueDoganaleCodes($articoli)
{
    $codes = [];
    foreach ($articoli as $articolo) {
        if (!in_array($articolo['voce_doganale'], $codes)) {
            $codes[] = $articolo['voce_doganale'];
        }
    }
    return $codes;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDT n° <?php echo $progressivo; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                margin-top: 0.5cm;
                margin-bottom: 0.5cm;
                margin-left: 1cm;
                margin-right: 1cm;
            }

            /* Nasconde il totale in tutte le pagine tranne l'ultima */
            tfoot {
                display: table-row-group !important;
            }

            tfoot tr:last-child {
                display: table-row !important;
            }
            
            /* Per contenuti ripetuti su ogni pagina */
            thead {
                display: table-header-group;
            }
        }

        .right-align {
            text-align: right;
        }

        .no-border-right {
            border-right: none;
        }

        .no-border-left {
            border-left: none;
        }

        /* Riduci la larghezza delle celle per il terzista */
        .terzista-table td {
            width: 50%;
        }

        /* Riduci l'altezza delle righe */
        .table-bordered tbody tr {
            line-height: 1;
            height: 20px !important;
        }

        .table-bordered thead tr {
            line-height: 1;
            height: 20px !important;
        }
        
        /* Stile per la firma */
        .signature-block {
            margin-top: 30px;
            padding-top: 10px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container mt-6">
        <!-- Logo e dati terzista -->
        <div class="row">
            <div class="col-md-12 left-align">
                <table class="table table-bordered terzista-table">
                    <tbody>
                        <tr>
                            <td>
                                <div class="text-left">
                                    <img src="img/top_logo.jpg" alt="Logo" style="max-width: 400px;">
                                </div>
                            </td>
                            <td>
                                <h5>SPETT.LE:</h5>
                                <p>
                                <h4><?php echo $terzista['ragione_sociale']; ?></h4>
                                </p>
                                <p><?php echo $terzista['indirizzo_1']; ?></p>
                                <?php if ($terzista['indirizzo_2']): ?>
                                    <p><?php echo $terzista['indirizzo_2']; ?></p>
                                <?php endif; ?>
                                <?php if ($terzista['indirizzo_3']): ?>
                                    <p><?php echo $terzista['indirizzo_3']; ?></p>
                                <?php endif; ?>
                                <p><?php echo $terzista['nazione']; ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Titolo del documento -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="mt-3">
                DDT VALORIZZATO n° <?php echo $progressivo; ?>
                <?php if (isset($documento['stato']) && $documento['stato'] == 'Aperto'): ?>
                    <span class="text-danger font-weight-bold ml-3">PROVVISORIO DA CHIUDERE</span>
                <?php endif; ?>
            </h2>
        </div>
        
        <!-- Informazioni del documento -->
        <div class="row mt-4">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td><strong>TIPO DOCUMENTO:</strong></td>
                            <td>DDT VALORIZZATO</td>
                            <td><strong>N° DOCUMENTO:</strong></td>
                            <td><?php echo $progressivo; ?></td>
                            <td><strong>DATA DOCUMENTO:</strong></td>
                            <td><?php echo $documento['data']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>TRASPORTATORE:</strong></td>
                            <td colspan="3"><?php echo $piede['trasportatore']; ?></td>
                            <td><strong>CONSEGNA:</strong></td>
                            <td colspan="1"><?php echo $terzista['consegna']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tabella articoli -->
        <div class="row mt-4">
            <div class="col-md-12">
                <table class="table table-bordered" style="font-size:9pt;">
                    <thead>
                        <tr>
                            <th>ARTICOLO</th>
                            <th class="no-border-right">DESCRIZIONE</th>
                            <th class="no-border-left">NOM.COM.</th>
                            <th>UM</th>
                            <th>QTA</th>
                            <th>VALORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dati del DDT (articoli normali) -->
                        <?php foreach ($articoli as $articolo): ?>
                            <?php
                            if ($articolo['qta_reale'] > 0 && $articolo['is_mancante'] == 0) {
                                $subtotal = round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
                                ?>
                                <tr>
                                    <td><?php echo $articolo['codice_articolo']; ?></td>
                                    <td class="no-border-right"><?php echo $articolo['descrizione']; ?></td>
                                    <td class="no-border-left"><?php echo $articolo['voce_doganale']; ?></td>
                                    <td><?php echo $articolo['um']; ?></td>
                                    <td><?php echo $articolo['qta_reale']; ?></td>
                                    <td><?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                                </tr>
                            <?php } ?>
                        <?php endforeach; ?>

                        <!-- Raggruppa i mancanti per DDT di origine -->
                        <?php
                        $mancantiByDDT = [];
                        foreach ($articoli as $articolo) {
                            if ($articolo['qta_reale'] > 0 && $articolo['is_mancante'] == 1) {
                                $rif = $articolo['rif_mancante'] ?: 'Senza riferimento';
                                if (!isset($mancantiByDDT[$rif])) {
                                    $mancantiByDDT[$rif] = [];
                                }
                                $mancantiByDDT[$rif][] = $articolo;
                            }
                        }
                        ?>

                        <!-- Visualizza i mancanti raggruppati per DDT di origine -->
                        <?php foreach ($mancantiByDDT as $rif => $mancanti): ?>
                            <tr>
                                <td colspan="1"></td>
                                <td class="no-border-right" colspan="5"><strong>MANCANTI SU <?php echo $rif; ?></strong></td>
                            </tr>
                            <?php foreach ($mancanti as $articolo):
                                $subtotal = round($articolo['qta_reale'] * $articolo['prezzo_unitario'], 2);
                                ?>
                                <tr>
                                    <td><?php echo $articolo['codice_articolo']; ?></td>
                                    <td class="no-border-right"><?php echo $articolo['descrizione']; ?></td>
                                    <td class="no-border-left"><?php echo $articolo['voce_doganale']; ?></td>
                                    <td><?php echo $articolo['um']; ?></td>
                                    <td><?php echo $articolo['qta_reale']; ?></td>
                                    <td><?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                        <!-- Righe vuote e Materiali Mancanti (sezione esistente per i mancanti) -->
                        <?php if (!empty($datiMancanti)): ?>
                            <tr>
                                <td colspan="6"></td>
                            </tr>
                            <tr>
                                <td colspan="6"><strong>MATERIALI MANCANTI</strong></td>
                            </tr>
                            <?php foreach ($datiMancanti as $mancante): ?>
                                <tr>
                                    <td><?php echo $mancante['codice_articolo']; ?></td>
                                    <td class="no-border-right"><?php echo $mancante['descrizione']; ?></td>
                                    <td class="no-border-left"><?php echo $mancante['voce_doganale']; ?></td>
                                    <td><?php echo $mancante['um']; ?></td>
                                    <td><?php echo $mancante['qta_mancante']; ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Righe per voce e peso -->
                        <tr>
                            <td colspan="6"> </td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center"><strong>RIEPILOGO PESI</strong></td>
                        </tr>
                        <?php for ($i = 1; $i <= 15; $i++): ?>
                            <?php if (!empty($piede['voce_' . $i]) && !empty($piede['peso_' . $i])): ?>
                                <tr>
                                    <td></td>
                                    <td style="text-align:right;">N.C. <?php echo $piede['voce_' . $i]; ?> PESO NETTO KG.</td>
                                    <td><?php echo $piede['peso_' . $i]; ?></td>
                                    <?php if ($i == 1): ?>
                                        <td rowspan="<?php echo count(array_filter(range(1, 15), function ($j) use ($piede) {
                                            return !empty($piede['voce_' . $j]) && !empty($piede['peso_' . $j]);
                                        })); ?>" colspan="3" style="vertical-align: middle; text-align: center;">
                                            <strong>TOT. COLLI <?php echo $piede['n_colli']; ?>
                                                <?php echo $piede['aspetto_colli']; ?></strong><br><br><br><br>
                                            Tot. Peso Lordo kg. <?php echo $piede['tot_peso_lordo']; ?><br><br>
                                            Tot. Peso Netto kg. <?php echo $piede['tot_peso_netto']; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Righe per autorizzazione -->
                        <tr>
                            <td colspan="6">
                                <p><strong>Materiale consegnato per la realizzazione di:</strong></p>
                                <ul>
                                    <?php
                                    try {
                                        $stmt = $db->prepare("SELECT lancio, articolo, paia FROM exp_dati_lanci_ddt WHERE id_doc = :id_doc");
                                        $stmt->execute([':id_doc' => $progressivo]);
                                        $lanci = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($lanci as $lancio):
                                            ?>
                                            <li><strong>#</strong> <?php echo $lancio['lancio']; ?> | <strong>Articolo:</strong>
                                                <?php echo $lancio['articolo']; ?> | <strong>Paia:</strong>
                                                <?php echo $lancio['paia']; ?></li>
                                            <?php
                                        endforeach;
                                    } catch (PDOException $e) {
                                        echo "<li>Errore nel recupero dei dati: " . htmlspecialchars($e->getMessage()) . "</li>";
                                    }
                                    ?>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo $piede['autorizzazione']; ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Valore totale in €:</strong></td>
                            <td><?php echo number_format($total, 2, ',', '.'); ?></td>
                        </tr>
                        <!-- Riga per la firma -->
                        <tr>
                            <td colspan="6" class="text-right">
                                <div class="signature-block">
                                    <div style="display: inline-block; margin-top: 50px; border-top: 1px solid #000; width: 250px; text-align: center;">
                                        <p style="margin-top: 5px;">Firma per accettazione</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
</body>
</html>