<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$tipoUtente = $_SESSION['admin_type'];
// Get DB instance. The function is defined in config.php
$db = getDbInstance();

// Include the necessary chart.js library
include_once(BASE_PATH . '/includes/header.php');
?>
<div id="page-wrapper">
    <?php
    if ($tipoUtente != "lavorante"):
        $result = $db->rawQuery("SELECT l.id_lab, l.LINEA as siglaLinea, SUM(l.paia) AS somma_paia, lin.descrizione AS nomeLinea FROM lanci AS l INNER JOIN linee AS lin ON l.linea = lin.sigla GROUP BY l.linea");
        foreach ($result as $row):
            $nomeLinea = $row['nomeLinea'];
            $siglaLinea = $row['siglaLinea'];
            $sommadellepaia = $row['somma_paia'];
            $inLavoroTotal = $db->where('linea', $row['siglaLinea'])->getValue('lanci', 'SUM(paia)');
            $riparazioniTotal = $db->where('LINEA', $row['siglaLinea'])->getValue('riparazioni', 'SUM(QTA)');
            ?>
            <div class="row" style="background-color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); padding:20px;">
                <div class="col-lg-12">
                    <h3 class="page-header">
                        <b>
                            <?php echo $nomeLinea; ?>
                        </b>
                    </h3>
                </div>
                <div class="col-lg-12 col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Dettaglio Avanzamenti:</h3>
                        </div>
                        <div class="panel-body">
                            <canvas id="graficoReparti-<?php echo $row['siglaLinea']; ?>" width="150" height="300"></canvas>
                        </div>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">Paia Totali in <b>Lavoro:</b></h3>
                        </div>
                        <div class="panel-body">
                            <?php echo $inLavoroTotal; ?>
                        </div>
                    </div>
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <h3 class="panel-title">Paia Totali in <b>Riparazione:</b></h3>
                        </div>
                        <div class="panel-body">
                            <?php echo empty($riparazioniTotal) ? '0' : $riparazioniTotal; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12" style="text-align: left; background-color: #ededed; border-radius: 10px;">
                    <a href="monitor_lab_lanci.php?operation=viewBrand&marchio=<?php echo $nomeLinea; ?>"
                        style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; text-decoration: none; height: 50px; line-height: 50px;">
                        <div>
                            <h3 style="margin: 0;">Situazione in Dettaglio</h3>
                        </div>
                        <div>
                            <i style="font-size: 20pt;" class="fad fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
            <hr>
        <?php endforeach; endif; ?>

</div>
<?php include_once(BASE_PATH . '/includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    <?php foreach ($result as $row): ?>
        <?php
        // Calcola i totali per il laboratorio corrente
        $nessunoTotal = $db->where('linea', $row['siglaLinea'])->where('avanzamento', 'NESSUNO')->where('taglio', 0)->where('preparazione', 0)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $attesaTotal = $db->where('linea', $row['siglaLinea'])->where('stato', 'IN ATTESA')->getValue('lanci', 'SUM(paia)');
        $taglioTotal = $db->where('linea', $row['siglaLinea'])->where('taglio', 1)->where('preparazione', 0)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $preparazioneTotal = $db->where('linea', $row['siglaLinea'])->where('taglio', 1)->where('preparazione', 1)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $orlaturaTotal = $db->where('linea', $row['siglaLinea'])->where('taglio', 1)->where('preparazione', 1)->where('orlatura', 1)->getValue('lanci', 'SUM(paia)');
        ?>

        // Dati dei reparti per il grafico
        var reparti_<?php echo $row['siglaLinea']; ?> = ['IN PREPARAZIONE', 'NESSUNO', 'TAGLIO', 'PREPARAZIONE', 'ORLATURA'];
        var datiReparti_<?php echo $row['siglaLinea']; ?> = [
            <?php echo $attesaTotal; ?>,
            <?php echo $nessunoTotal; ?>,
            <?php echo $taglioTotal; ?>,
            <?php echo $preparazioneTotal; ?>,
            <?php echo $orlaturaTotal; ?>
        ];

        // Ottenere il riferimento al canvas
        var ctx_<?php echo $row['siglaLinea']; ?> = document.getElementById('graficoReparti-<?php echo $row['siglaLinea']; ?>').getContext('2d');

        // Creare il grafico
        var graficoReparti_<?php echo $row['siglaLinea']; ?> = new Chart(ctx_<?php echo $row['siglaLinea']; ?>, {
            type: 'bar',
            data: {
                labels: reparti_<?php echo $row['siglaLinea']; ?>,
                datasets: [{
                    label: 'PAIA',
                    data: datiReparti_<?php echo $row['siglaLinea']; ?>,
                    backgroundColor: [
                        'orange',
                        '#eb4034',
                        '#8bc8e0', // Colore per TAGLIO
                        '#e0b88b', // Colore per PREPARAZIONE
                        '#cc8be0'  // Colore per ORLATURA
                    ],
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {

                    legend: {
                        display: false // Nasconde la legenda
                    }
                },
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function (value, context) {
                            return value;
                        }
                    }
                }
            }
        });
    <?php endforeach; ?>
</script>