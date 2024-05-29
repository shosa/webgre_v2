<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$tipoUtente = $_SESSION['admin_type'];
//Get DB instance. function is defined in config.php
$db = getDbInstance();

//Get Dashboard information


include_once(BASE_PATH . '/includes/header.php');
?>
<div id="page-wrapper">
    <?php
    if ($tipoUtente != "lavorante"):
        $result = $db->rawQuery("SELECT l.id_lab, SUM(l.paia) AS somma_paia, lab.nome AS nomelaboratorio FROM lanci AS l INNER JOIN laboratori AS lab ON l.id_lab = lab.ID GROUP BY l.id_lab");
        foreach ($result as $row):
            $result4 = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE id_lab=" . $row['id_lab'] . " AND stato != 'IN ATTESA'");
            $nomelaboratorio = $row['nomelaboratorio'];
            $sommadellepaia = $result4[0]['somma_paia'];
            $inLavoroTotal = $db->where('id_lab', $row['id_lab'])->getValue('lanci', 'SUM(paia)');
            $riparazioniTotal = $db->where('LABORATORIO', $row['nomelaboratorio'])->getValue('riparazioni', 'SUM(QTA)');
            ?>
            <div class="row" style="background-color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); padding:20px;">
                <div class="col-lg-12">
                    <h3 class="page-header">
                        <b>
                            <?php echo $nomelaboratorio; ?>
                        </b>
                    </h3>
                </div>
                <div class="col-lg-12 col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Dettaglio Avanzamenti:</h3>
                        </div>
                        <div class="panel-body">
                            <canvas id="graficoReparti-<?php echo $row['id_lab']; ?>" width="150" height="300"></canvas>
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
                    <a href="monitor_lab_lanci.php?operation=viewLab&laboratorio=<?php echo $nomelaboratorio; ?>"
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
        <?php endforeach;
    endif; ?>



</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    <?php foreach ($result as $row): ?>
        <?php
        // Calcola i totali per il laboratorio corrente
        $nessunoTotal = $db->where('id_lab', $row['id_lab'])->where('avanzamento', 'NESSUNO')->where('taglio', 0)->where('preparazione', 0)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $attesaTotal = $db->where('id_lab', $row['id_lab'])->where('stato', 'IN ATTESA')->getValue('lanci', 'SUM(paia)');
        $taglioTotal = $db->where('id_lab', $row['id_lab'])->where('taglio', 1)->where('preparazione', 0)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $preparazioneTotal = $db->where('id_lab', $row['id_lab'])->where('taglio', 1)->where('preparazione', 1)->where('orlatura', 0)->getValue('lanci', 'SUM(paia)');
        $orlaturaTotal = $db->where('id_lab', $row['id_lab'])->where('taglio', 1)->where('preparazione', 1)->where('orlatura', 1)->getValue('lanci', 'SUM(paia)');

        ?>

        // Dati dei reparti per il grafico
        var reparti_<?php echo $row['id_lab']; ?> = ['IN PREPARAZIONE', 'NESSUNO', 'TAGLIO', 'PREPARAZIONE', 'ORLATURA'];
        var datiReparti_<?php echo $row['id_lab']; ?> = [
            <?php echo $attesaTotal; ?>,
            <?php echo $nessunoTotal; ?>,
            <?php echo $taglioTotal; ?>,
            <?php echo $preparazioneTotal; ?>,
            <?php echo $orlaturaTotal; ?>
        ];

        // Ottenere il riferimento al canvas
        var ctx_<?php echo $row['id_lab']; ?> = document.getElementById('graficoReparti-<?php echo $row['id_lab']; ?>').getContext('2d');

        // Creare il grafico
        var graficoReparti_<?php echo $row['id_lab']; ?> = new Chart(ctx_<?php echo $row['id_lab']; ?>, {
            type: 'bar',
            data: {
                labels: reparti_<?php echo $row['id_lab']; ?>,
                datasets: [{
                    label: 'PAIA',
                    data: datiReparti_<?php echo $row['id_lab']; ?>,
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
<?php include_once(BASE_PATH . '/includes/footer.php'); ?>