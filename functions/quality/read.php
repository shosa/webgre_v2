<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

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
<style>
    .future {
        color: #f2a5a0;
        cursor: not-allowed;
    }

    .future:hover {
        background-color: #f07067;
    }

    .today {
        background-color: #B4E8C9;
        color: #439876;
        font-weight: bolder;
    }
</style>

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Controllo Qualità</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Consultazione Date</li>
                    </ol>

                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <a href="?month=<?= $currentMonth - 1 ?>&year=<?= $currentYear ?>"
                                    class="btn btn-danger shadow"><i class="fad fa-chevron-double-left"></i></a>
                                <span>
                                    <h5 class="m-0 font-weight-bold text-primary">
                                        <?= $monthNames[$currentMonth] . " " . $currentYear ?>
                                    </h5>
                                </span>
                                <a href="?month=<?= $currentMonth + 1 ?>&year=<?= $currentYear ?>"
                                    class="btn btn-danger shadow"><i class="fad fa-chevron-double-right"></i></a>
                            </div>
                            <div class="card-body">
                                <div class="calendar">
                                    <table class="table table-bordered table-sm table-condensed ">
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
                                            for ($i = 1; $i < $firstDayOfWeek; $i++) {
                                                echo '<td style="background-color:#ededed"></td>';
                                            }
                                            $formattedMonth = str_pad($currentMonth, 2, "0", STR_PAD_LEFT);
                                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                                // Aggiungi lo zero iniziale se il giorno è minore di 10
                                                $formattedDay = str_pad($day, 2, "0", STR_PAD_LEFT);
                                                // Costruisci la data nel formato GG/MM/AAAA
                                                $date = "$formattedDay/$formattedMonth/$currentYear";
                                                // Aggiungi il link alla pagina di dettaglio
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
                                                    : "href='detail?date=$date'";
                                                echo "<td style='height:4.5em;' class='$cellClass'><a style='text-decoration:none;color:inherit;display:block; height:100%' $clickAction>$day</td>"; // Passa alla riga successiva dopo Domenica (7 giorni)
                                                // Passa alla riga successiva dopo Domenica (7 giorni)
                                                if ($firstDayOfWeek == 7) {
                                                    echo "</tr>";
                                                    if ($day != $daysInMonth) {
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

                </div>
            </div>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
</body>