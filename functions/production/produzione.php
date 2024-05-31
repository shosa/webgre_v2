<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/helpers/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

try {
    $conn = getDbInstance();
    // Impostare l'attributo per segnalare gli errori
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $month = $_GET['month'];
    $day = $_GET['day'];

    // Query SQL
    $sql = "SELECT * FROM prod_mesi WHERE MESE = :month AND GIORNO = :day";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':month', $month);
    $stmt->bindParam(':day', $day);
    $stmt->execute();

    $row = $stmt->fetch();

    if ($row) {
        // Estrai i valori desiderati dal risultato della query
        $MANOVIA1 = $row['MANOVIA1'];
        $MANOVIA1NOTE = $row['MANOVIA1NOTE'];
        $MANOVIA2 = $row['MANOVIA2'];
        $MANOVIA2NOTE = $row['MANOVIA2NOTE'];
        $MANOVIA3 = $row['MANOVIA3'];
        $MANOVIA3NOTE = $row['MANOVIA3NOTE'];
        $ORLATURA1 = $row['ORLATURA1'];
        $ORLATURA1NOTE = $row['ORLATURA1NOTE'];
        $ORLATURA2 = $row['ORLATURA2'];
        $ORLATURA2NOTE = $row['ORLATURA2NOTE'];
        $ORLATURA3 = $row['ORLATURA3'];
        $ORLATURA3NOTE = $row['ORLATURA3NOTE'];
        $TAGLIO1 = $row['TAGLIO1'];
        $TAGLIO1NOTE = $row['TAGLIO1NOTE'];
        $TAGLIO2 = $row['TAGLIO2'];
        $TAGLIO2NOTE = $row['TAGLIO2NOTE'];
    } else {
        echo "Nessun risultato trovato per il mese $month e il giorno $day";
    }
} catch (PDOException $e) {
    echo "Connessione al database fallita: " . $e->getMessage();
}

include (BASE_PATH . "/components/header.php");
?>



<script>
    $(document).ready(function () {
        $('.container').addClass('show');
    });

    function generatePDF(month, day) {
        window.location.href = `generate_pdf.php?month=${month}&day=${day}`;
    }
</script>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="calendario">Calendario</a></li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo $day; ?> <?php echo $month; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-container">
                                        <table class="table table-bordered table-striped table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>MANOVIA 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA1NOTE; ?>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>MANOVIA 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA2NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>MANOVIA 3</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $MANOVIA3; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $MANOVIA3NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>ORLATURA 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA1NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>ORLATURA 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA2NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>ORLATURA 3</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $ORLATURA3; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $ORLATURA3NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>TAGLIO 1</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $TAGLIO1; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $TAGLIO1NOTE; ?>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td>TAGLIO 2</td>
                                                    <td class="text-wrap text-center">
                                                        <?php echo $TAGLIO2; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>NOTE</td>
                                                    <td class="note text-wrap">
                                                        <?php echo $TAGLIO2NOTE; ?>
                                                    </td>
                                                </tr>
                                                <!-- Ripeti per le altre attività -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Strumenti</h6>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-danger btn-lg btn-block shadow"
                                        onclick='generatePDF("<?php echo $month; ?>", "<?php echo $day; ?>")'>
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                    <button class="btn btn-primary btn-lg btn-block shadow" onclick='#'>
                                        <i class="fas fa-share-square"></i> INVIA MAIL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
        </div>
    </div>
</body>