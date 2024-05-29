<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <canvas id="scartoPerArticoloChart" width="400" height="400"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="scartoTotalePaiaChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
    $(document).ready(function () {
        // Recupera i dati per il grafico dello scarto per articolo
        $.ajax({
            url: 'get_data_for_chart.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                var ctx = document.getElementById('scartoPerArticoloChart').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: '% Scarto per Articolo',
                            data: data.scartoPerArticolo,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });

        // Recupera i dati per il grafico dello scarto totale paia
        $.ajax({
            url: 'get_data_for_chart.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                var ctx = document.getElementById('scartoTotalePaiaChart').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Accettati', 'Scarti'],
                        datasets: [{
                            label: '% Scarto Totale Paia',
                            data: data.scartoTotalePaia,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(255, 99, 132, 0.2)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {}
                });
            }
        });
    });
</script>
