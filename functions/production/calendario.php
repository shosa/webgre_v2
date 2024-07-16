<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once BASE_PATH . "/utils/helpers.php";
require_once BASE_PATH . "/utils/log_utils.php";

// Imposta la data attuale
$currentDate = new DateTime();

if (isset($_GET["month"]) && isset($_GET["year"])) {
    // Se sono passati mese e anno come parametri GET, li utilizziamo
    $currentDate->setDate($_GET["year"], $_GET["month"], 1);
}

// Ottieni il mese e l'anno attuali
$currentMonth = $currentDate->format("n");
$currentYear = $currentDate->format("Y");

// Calcola il numero di giorni nel mese corrente
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

// Ottieni il giorno della settimana del primo giorno del mese corrente
$firstDayOfWeek = date("N", strtotime("$currentYear-$currentMonth-01"));

// Array associativo per mappare i numeri dei mesi ai nomi dei mesi
$monthNames = [
    1 => "GENNAIO",
    2 => "FEBBRAIO",
    3 => "MARZO",
    4 => "APRILE",
    5 => "MAGGIO",
    6 => "GIUGNO",
    7 => "LUGLIO",
    8 => "AGOSTO",
    9 => "SETTEMBRE",
    10 => "OTTOBRE",
    11 => "NOVEMBRE",
    12 => "DICEMBRE",
];
?>
<?php include BASE_PATH . "/components/header.php"; ?>

<style>
    .future {
        color: #f2a5a0;
        cursor: not-allowed;
    }

    .future:hover {
        background-color: #f07067;
    }

    .giorno:hover {
        background-color: #DFF0FF;
    }

    .today {
        background-color: #B4E8C9;
        color: #439876;
        font-weight: bolder;
    }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Produzione & Spedizione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href=BASE_PATH . "/index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Calendario</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <a href="?month=<?= $currentMonth -
                                        1 ?>&year=<?= $currentYear ?>" class="btn btn-danger shadow"><i
                                            class="fad fa-chevron-double-left"></i></a>
                                    <span>
                                        <h5 class="m-0 font-weight-bold text-primary"><?= $monthNames[$currentMonth] .
                                            " " .
                                            $currentYear ?></h5>
                                    </span>
                                    <a href="?month=<?= $currentMonth +
                                        1 ?>&year=<?= $currentYear ?>" class="btn btn-danger shadow"><i
                                            class="fad fa-chevron-double-right"></i></a>
                                </div>
                                <div class="card-body">
                                    <div class="calendar">
                                        <table class="table table-bordered table-sm table-condensed">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Lun</th>
                                                    <th scope="col">Mar</th>
                                                    <th scope="col">Mer</th>
                                                    <th scope="col">Gio</th>
                                                    <th scope="col">Ven</th>
                                                    <th scope="col">Sab</th>
                                                    <th scope="col">Dom</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $dayCounter = 1;
                                                for (
                                                    $i = 1;
                                                    $i < $firstDayOfWeek;
                                                    $i++
                                                ) {
                                                    echo '<td style="background-color:#ededed"></td>';
                                                }
                                                for (
                                                    $day = 1;
                                                    $day <= $daysInMonth;
                                                    $day++
                                                ) {
                                                    // Verifica se la data è quella odierna
                                                    $isToday =
                                                        $day ==
                                                        (int) date("d") &&
                                                        $currentMonth ==
                                                        (int) date("m") &&
                                                        $currentYear ==
                                                        (int) date("Y"); // Verifica se la data è futura rispetto alla data attuale
                                                    $isFutureDate =
                                                        !$isToday &&
                                                        strtotime(
                                                            "$currentYear-$currentMonth-$day"
                                                        ) > time(); // Aggiungi la classe 'today' se è la data odierna
                                                    $cellClass = $isToday
                                                        ? "today"
                                                        : ""; // Aggiungi la classe 'future' se è una data futura
                                                    if ($isFutureDate) {
                                                        $cellClass .= " future";
                                                    } // Aggiungi l'attributo 'onclick' solo per le date passate o odierna
                                                    $clickAction = $isFutureDate
                                                        ? ""
                                                        : "onclick='generatePDF(\"$monthNames[$currentMonth]\", \"$day\")'";
                                                    echo "<td  style='height:4.5em; cursor:pointer;' class='giorno $cellClass' $clickAction>$day</td>"; // Passa alla riga successiva dopo Domenica (7 giorni)
                                                    if ($firstDayOfWeek == 7) {
                                                        echo "</tr>";
                                                        if (
                                                            $day != $daysInMonth
                                                        ) {
                                                            echo "<tr>";
                                                        }
                                                        $firstDayOfWeek = 1;
                                                    } else {
                                                        $firstDayOfWeek++;
                                                    }
                                                }
                                                while ($firstDayOfWeek <= 7) {
                                                    echo '<td style="background-color:#ededed"></td>';
                                                    $firstDayOfWeek++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Strumenti</h6>
                                </div>
                                <div class="card-body text-center">
                                    <a href="new" class="btn btn-success btn-block btn-lg"><i class="fas fa-plus"></i>
                                        REGISTRA NUOVA</a>
                                    <button class="btn btn-warning btn-block btn-lg"
                                        onclick="generatePDFMese('<?= $monthNames[$currentMonth] ?>')"><i
                                            class="fas fa-calendar-alt"></i> PRODUZIONE
                                        <?php echo $monthNames[$currentMonth] ?></button>
                                    <button class="btn btn-indigo btn-block btn-lg"
                                        onclick="generatePDFProdSped('<?= $monthNames[$currentMonth] ?>')"><i
                                            class="fas fa-calendar-alt"></i> PROD E SPED
                                        <?php echo $monthNames[$currentMonth] ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . "/components/scripts.php"; ?>
            <?php include_once BASE_PATH . "/components/footer.php"; ?>
        </div>
    </div>
</body>
<script>
    // Funzione per generare il report PDF quando una cella viene cliccata
    function generatePDF(month, day) {
        // Esegui la logica per generare il PDF con i parametri month e day
        // Ad esempio, reindirizza a generate_pdf.php con i parametri appropriati
        window.location.href = `produzione?month=${month}&day=${day}`;
    }

    function generatePDFMese(month) {
        // Esegui la logica per generare il PDF con i parametri month e day
        // Ad esempio, reindirizza a generate_pdf.php con i parametri appropriati
        window.location.href = `generate_pdf-mese?month=${month}`;
    }
    function generatePDFProdSped(month) {
        // Esegui la logica per generare il PDF con i parametri month e day
        // Ad esempio, reindirizza a generate_pdf.php con i parametri appropriati
        window.location.href = `generate_prod_sped-mese?month=${month}`;
    }
</script>