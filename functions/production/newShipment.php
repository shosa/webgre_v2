<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/config.php';
require_once '../../helpers/helpers.php';
require_once '../../utils/log_utils.php';
?>
<style>

</style>
<?php include (BASE_PATH . "/components/header.php"); ?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Spedizione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="calendario">Calendario</a></li>
                        <li class="breadcrumb-item active">Nuova Spedizione</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-10 col-lg-9">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Inserimento</h6>
                                </div>
                                <div class="card-body">
                                    <form id="produzioneForm" action="process.php" method="POST">
                                        <div class="form-group row" style="background-color: #f2f2f2; padding: 15px;">
                                            <label for="month" class="col-sm-2 col-form-label">Mese:</label>
                                            <div class="col-sm-4">
                                                <select id="month" name="month" class="form-control">
                                                    <option value="GENNAIO">GENNAIO</option>
                                                    <option value="FEBBRAIO">FEBBRAIO</option>
                                                    <option value="MARZO">MARZO</option>
                                                    <option value="APRILE">APRILE</option>
                                                    <option value="MAGGIO">MAGGIO</option>
                                                    <option value="GIUGNO">GIUGNO</option>
                                                    <option value="LUGLIO">LUGLIO</option>
                                                    <option value="AGOSTO">AGOSTO</option>
                                                    <option value="SETTEMBRE">SETTEMBRE</option>
                                                    <option value="OTTOBRE">OTTOBRE</option>
                                                    <option value="NOVEMBRE">NOVEMBRE</option>
                                                    <option value="DICEMBRE">DICEMBRE</option>
                                                </select>
                                            </div>
                                            <label for="day" class="col-sm-2 col-form-label">Giorno:</label>
                                            <div class="col-sm-4">
                                                <select id="day" name="day" class="form-control">
                                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                                        <option
                                                            value="<?= $i < 10 ? strval($i) : str_pad($i, 2, '0', STR_PAD_LEFT) ?>">
                                                            <?= $i < 10 ? strval($i) : str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" id="year" name="year">

                                        <!-- MANOVIA1 -->
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="manovia1">MANOVIA1:</label>
                                                <input type="number" id="manovia1" name="manovia1" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="manovia1">MANOVIA1 RESO:</label>
                                                <input type="number" id="manovia1reso" name="manovia1reso"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- MANOVIA2 -->
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="manovia2">MANOVIA2:</label>
                                                <input type="number" id="manovia2" name="manovia2" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- MANOVIA3 -->
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="manovia3">MANOVIA3:</label>
                                                <input type="number" id="manovia3" name="manovia3" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- ORLATURA1 -->
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="orlatura1">ORLATURA1:</label>
                                                <input type="number" id="orlatura1" name="orlatura1"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- ORLATURA2 -->
                                        <div class="form-group row border-bottom ">
                                            <div class="col-md-12">
                                                <label for="orlatura2">ORLATURA2:</label>
                                                <input type="number" id="orlatura2" name="orlatura2"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- ORLATURA3 -->
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="orlatura3">ORLATURA3:</label>
                                                <input type="number" id="orlatura3" name="orlatura3"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="orlatura4">ORLATURA4:</label>
                                                <input type="number" id="orlatura4" name="orlatura4"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <label for="orlatura4">TOMAIE ESTERO:</label>
                                                <input type="number" id="tomaieEstero" name="tomaieEstero"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-1">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Opzioni</h6>
                                </div>
                                <div class="card-body">
                                    <button type="button" onclick="inviaDati()"
                                        class="btn btn-success mt-3 btn-block btn-square shadow form-control">
                                        <i class="fal fa-layer-plus"></i> Registra
                                    </button>
                                    <button type="button" onclick="inviaDati()"
                                        class="btn btn-danger mt-3 btn-block btn-square shadow form-control">
                                        <i class="fal fa-empty-set"></i> Svuota Campi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>
            <?php include_once (BASE_PATH . '/components/scripts.php'); ?>

            <?php include_once (BASE_PATH . '/components/footer.php'); ?>
        </div>
    </div>
</body>
<script>
    $(document).ready(function () {
        var currentDate = new Date();
        var currentMonth = currentDate.getMonth();
        var monthNames = ["GENNAIO", "FEBBRAIO", "MARZO", "APRILE", "MAGGIO", "GIUGNO", "LUGLIO", "AGOSTO", "SETTEMBRE", "OTTOBRE", "NOVEMBRE", "DICEMBRE"];
        $("#month").val(monthNames[currentMonth]);

        var currentDay = currentDate.getDate();
        $("#day").val(currentDay);

        $('.form-container').addClass('show');
    });

    function getCurrentYear() {
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        document.getElementById("year").value = currentYear;
    }

    getCurrentYear();

    function inviaDati() {
        var month = $("#month").val();
        var day = $("#day").val();
        var manovia1 = $("#manovia1").val();
        var manovia1reso = $("#manovia1reso").val();
        var manovia2 = $("#manovia2").val();
        var manovia3 = $("#manovia3").val();
        var orlatura1 = $("#orlatura1").val();
        var orlatura2 = $("#orlatura2").val();
        var orlatura3 = $("#orlatura3").val();
        var orlatura4 = $("#orlatura4").val();
        var tomaieEstero = $("#tomaieEstero").val();
        $.ajax({
            type: "POST",
            url: "processShipment.php",
            data: {
                month: month,
                day: day,
                manovia1: manovia1,
                manovia1reso: manovia1reso,
                manovia2: manovia2,
                manovia3: manovia3,
                orlatura1: orlatura1,
                orlatura2: orlatura2,
                orlatura3: orlatura3,
                orlatura4: orlatura4,
                tomaieEstero: tomaieEstero,
            },
            success: function (response) {
                console.log("Risposta del server: " + response);
                Swal.fire({
                    icon: 'success',
                    title: 'Successo',
                    text: 'I dati sono stati aggiornati con successo!',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'calendario.php';
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error("Errore durante la chiamata AJAX: " + error);
            }
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>

</html>