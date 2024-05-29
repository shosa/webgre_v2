<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$tipoUtente = $_SESSION['admin_type'];
// Get DB instance. Function is defined in config.php
$db = getDbInstance();

// Include header
include_once(BASE_PATH . '/includes/header.php');
?>

<div id="page-wrapper">
    <?php
    if ($tipoUtente != "lavorante"):
        // Calcola i totali per i paia in lavorazione, in preparazione e in riparazione
        $result_lavorazione = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci");
        $result_preparazione = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE stato = 'IN ATTESA'");
        $result_riparazione = $db->rawQuery("SELECT SUM(QTA) AS somma_paia FROM riparazioni");

        // Dati per il grafico a barre
        $datiBarre = [
            (isset($result_lavorazione[0]['somma_paia']) ? $result_lavorazione[0]['somma_paia'] : 0),
            (isset($result_preparazione[0]['somma_paia']) ? $result_preparazione[0]['somma_paia'] : 0),
            (isset($result_riparazione[0]['somma_paia']) ? $result_riparazione[0]['somma_paia'] : 0)
        ];

        $etichette = ['IN LAVORO - (' . $result_lavorazione[0]['somma_paia'] . ' PA)', 'IN PREPARAZIONE LANCIO - (' . $result_preparazione[0]['somma_paia'] . ' PA)', 'RIPARAZIONI - (' . $result_riparazione[0]['somma_paia'] . ' PA)'];
        ?>
        <div class="row" style="background-color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); padding:20px;">
            <div class="col-lg-12">
                <h3 class="page-header page-action-links text-left">
                    <b>
                        Vista Generale Produzione
                    </b>
                </h3>
            </div>
          
            <div class="col-lg-12 col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Generico <b>LANCIO IN LAVORO</b></h3>
                    </div>
                    <div class="panel-body">
                        <canvas id="graficoSituazioneGenerale" width="150" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Secondo pannello con il secondo grafico -->
            <div class="col-lg-12 col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Generico <b>LANCIO BASATO SULL'AVANZAMENTO</b></h3>
                    </div>
                    <div class="panel-body">
                        <canvas id="graficoAvanzamento" width="150" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>  <hr>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dati per il grafico a barre
    var datiBarre = <?php echo json_encode($datiBarre); ?>;
    var etichette = <?php echo json_encode($etichette); ?>;

    // Ottenere il riferimento al canvas
    var ctx = document.getElementById('graficoSituazioneGenerale').getContext('2d');

    // Creare il grafico a barre
    var graficoBarre = new Chart(ctx, {
        type: 'bar',

        data: {
            labels: etichette,
            datasets: [{
                label: 'PAIA',
                data: datiBarre,
                backgroundColor: [
                    '#53e692', // Colore per In lavorazione
                    '#f7cc68', // Colore per In preparazione
                    '#cf4c59'  // Colore per In riparazione
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

    // Dati per il secondo grafico a barre
    var datiBarreAvanzamento = [<?php
        $result_avanzamento_nessuno = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE avanzamento = 'NESSUNO'");
        $result_avanzamento_taglio = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE avanzamento = 'TAGLIO'");
        $result_avanzamento_preparazione = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE avanzamento = 'PREPARAZIONE'");
        $result_avanzamento_orlatura = $db->rawQuery("SELECT SUM(paia) AS somma_paia FROM lanci WHERE avanzamento = 'ORLATURA'");

        echo (isset($result_avanzamento_nessuno[0]['somma_paia']) ? $result_avanzamento_nessuno[0]['somma_paia'] : 0) . ', ' .
             (isset($result_avanzamento_taglio[0]['somma_paia']) ? $result_avanzamento_taglio[0]['somma_paia'] : 0) . ', ' .
             (isset($result_avanzamento_preparazione[0]['somma_paia']) ? $result_avanzamento_preparazione[0]['somma_paia'] : 0) . ', ' .
             (isset($result_avanzamento_orlatura[0]['somma_paia']) ? $result_avanzamento_orlatura[0]['somma_paia'] : 0);
    ?>];
    var etichetteAvanzamento = ['NESSUNO', 'TAGLIO', 'PREPARAZIONE', 'ORLATURA'];

    // Ottenere il riferimento al canvas del secondo grafico
    var ctxAvanzamento = document.getElementById('graficoAvanzamento').getContext('2d');

    // Creare il secondo grafico a barre
    var graficoBarreAvanzamento = new Chart(ctxAvanzamento, {
        type: 'bar',
        data: {
            labels: etichetteAvanzamento,
            datasets: [{
                label: 'PAIA',
                data: datiBarreAvanzamento,
                backgroundColor: [
                    'orange',  // Colore per NESSUNO
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
</script>

<?php include_once(BASE_PATH . '/includes/footer.php'); ?>
