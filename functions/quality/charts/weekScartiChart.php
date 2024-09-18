<script>
    $(document).ready(function () {
        // Funzione per formattare i numeri come valuta
        function number_format(number, decimals, dec_separator, thousands_separator) {
            decimals = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
            dec_separator = dec_separator == undefined ? "," : dec_separator;
            thousands_separator = thousands_separator == undefined ? "." : thousands_separator;
            var num = parseFloat(number || 0).toFixed(decimals);
            num = num.replace(".", dec_separator);
            var x = num.split(dec_separator);
            var x1 = x[0];
            var x2 = x.length > 1 ? dec_separator + x[1] : "";
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, "$1" + thousands_separator + "$2");
            }
            return x1 + x2;
        }
        var ctx = document.getElementById("weekScartiChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: "% Scarti",
                    lineTension: 0.3,
                    backgroundColor: "rgba(220,53,69,0.4)",
                    borderColor: "rgba(220,53,69,1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(220,53,69,1)",
                    pointBorderColor: "rgba(220,53,69,1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(220,53,69,1)",
                    pointHoverBorderColor: "rgba(220,53,69,1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: <?php echo json_encode($bin_percentage); ?>,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function (value, index, values) {
                                return number_format(value) + '%';
                            }
                        },
                        gridLines: {
                            color: "rgba(234, 236, 244, 1)",
                            zeroLineColor: "rgba(234, 236, 244, 1)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function (tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ' + number_format(tooltipItem.yLabel) + '%';
                        }
                    }
                }
            }
        });
    });
</script>