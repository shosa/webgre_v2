<?php
require_once '../../config/config.php';

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/includes/header.php';

$progressivo = $_GET['progressivo'];
$dir = 'src/' . $progressivo;

// Recupera i file Excel presenti nella directory
$files = glob($dir . '/*.xlsx');

$db = getDbInstance();

// Recupera i dati del documento 
$documento = $db->where('id', $progressivo)->getOne('exp_documenti');

// Recupera gli articoli
$articoli = $db->where('id_documento', $progressivo)->get('exp_dati_articoli');

// Recupera i dati del terzista
$terzista = $db->where('id', $documento['id_terzista'])->getOne('exp_terzisti');

// Recupera il piede
$piede = $db->where('id_documento', $progressivo)->getOne('exp_piede_documenti');

// Recupera i dati mancanti se presenti
$datiMancanti = [];
if ($db->where('id_documento', $progressivo)->has('exp_dati_mancanti')) {
    $datiMancanti = $db->where('id_documento', $progressivo)->get('exp_dati_mancanti');
    $datiMancanti = array_map(function ($row) use ($db) {
        $articolo = $db->where('codice_articolo', $row['codice_articolo'])->getOne('exp_dati_articoli');
        $row['descrizione'] = $articolo['descrizione'] ?? '';
        $row['um'] = $articolo['um'] ?? '';
        $row['voce_doganale'] = $articolo['voce_doganale'] ?? '';
        return $row;
    }, $datiMancanti);
}

// Calcola il totale
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
    </style>
</head>

<body>
    <div class="container mt-6">
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
        <h2 class="mt-3">DDT VALORIZZATO n° <?php echo $progressivo; ?></h2>
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
                            <td colspan="3"><?php echo $terzista['consegna']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
                            <th>COSTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dati del DDT -->
                        <?php foreach ($articoli as $articolo): ?>
                            <?php
                            if ($articolo['qta_reale'] > 0) {
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

                        <!-- Righe vuote e Materiali Mancanti -->
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
                            <td colspan="6"></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center"><strong>RIEPILOGO PESI</strong></td>
                        </tr>
                        <?php for ($i = 1; $i <= 15; $i++): ?>
                            <?php if (!empty($piede['voce_' . $i]) && !empty($piede['peso_' . $i])): ?>
                                <tr>
                                    <td></td>
                                    <td style="text-align:right;">N.C. <?php echo $piede['voce_' . $i]; ?> PESO NETTO KG.</td>
                                    <td> <?php echo $piede['peso_' . $i]; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Righe per autorizzazione -->
                        <tr>
                            <td colspan="6"></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo $piede['autorizzazione']; ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Totale in €:</strong></td>
                            <td><?php echo number_format($total, 2, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
                <p><strong>Materiale consegnato per la realizzazione di:</strong></p>
                <ul>
                    <?php
                    $lanci = $db->where('id_doc', $progressivo)->get('exp_dati_lanci_ddt', null, ['lancio', 'articolo', 'paia']);
                    foreach ($lanci as $lancio):
                        ?>
                        <li><strong>#</strong> <?php echo $lancio['lancio']; ?> | <strong>Articolo:</strong>
                            <?php echo $lancio['articolo']; ?> | <strong>Paia:</strong> <?php echo $lancio['paia']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>