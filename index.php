<?php
include ("config/config.php");
session_start();
require_once BASE_PATH . '/components/auth_validate.php';
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$idUtente = $_SESSION['user_id'];
$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdo->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();
$tipoUtente = $_SESSION['admin_type'];
$data_oggi = date('d/m/Y');
$queryPreferenze = $pdo->prepare("SELECT * FROM utenti_preferenze WHERE user_id = :user_id");
$queryPreferenze->execute([':user_id' => $idUtente]);
$preferenze = $queryPreferenze->fetch(PDO::FETCH_ASSOC);
$mostraCardRiparazioni = isset($preferenze['card_riparazioni']) && $preferenze['card_riparazioni'] == 1;
$mostraCardMyRiparazioni = isset($preferenze['card_myRiparazioni']) && $preferenze['card_myRiparazioni'] == 1;
$mostraCardQuality = isset($preferenze['card_quality']) && $preferenze['card_quality'] == 1;
$mostraCardProduzione = isset($preferenze['card_produzione']) && $preferenze['card_produzione'] == 1;
$mostraCardProduzioneMese = isset($preferenze['card_produzioneMese']) && $preferenze['card_produzioneMese'] == 1;
$queryCheckPreferenze = "SELECT COUNT(*) FROM utenti_preferenze WHERE user_id = :user_id";
$stmtCheckPreferenze = $pdo->prepare($queryCheckPreferenze);
$stmtCheckPreferenze->execute([':user_id' => $idUtente]);
$preferenzeEsistenti = $stmtCheckPreferenze->fetchColumn();
if ($preferenzeEsistenti == 0) {
    // Crea un nuovo record con valori predefiniti
    $queryInsertPreferenze = "INSERT INTO utenti_preferenze (user_id, card_riparazioni, card_myRiparazioni, card_quality, card_produzione, card_produzioneMese) VALUES (:user_id, 1, 1, 1, 1, 1)";
    $stmtInsertPreferenze = $pdo->prepare($queryInsertPreferenze);
    $stmtInsertPreferenze->execute([':user_id' => $idUtente]);
}
?>
<?php include ("components/header.php"); ?>

<body id="page-top">
    <div id="wrapper">
        <?php include ("components/navbar.php"); //INCLUSIONE NAVBAR ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include ("components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-sliders-h fa-2x text-<?php echo $colore; ?>"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <form id="preferencesForm" class="px-2 py-2">
                                    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardRiparazioni"
                                                name="cardRiparazioni" <?php echo $mostraCardRiparazioni ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardRiparazioni">Riparazioni</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardMyRiparazioni"
                                                name="cardMyRiparazioni" <?php echo $mostraCardMyRiparazioni ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardMyRiparazioni">Riparazioni
                                                Personali</label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardQuality"
                                                name="cardQuality" <?php echo $mostraCardQuality ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardQuality">Controllo Qualità</label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1): ?>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardProduction"
                                                name="cardProduction" <?php echo $mostraCardProduzione ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardProduction">Produzione di Oggi</label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardProductionMonth"
                                                name="cardProductionMonth" <?php echo $mostraCardProduzioneMese ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardProductionMonth">Produzione del
                                                Mese</label>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit"
                                        class="btn btn-<?php echo $colore; ?> btn-block mt-2">Salva</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- INIZIO ROW CARDS -->
                    <div class="row">
                        <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardRiparazioni):
                            $queryNumRiparazioni = "SELECT SUM(QTA) FROM riparazioni";
                            $stmtNumRiparazioni = $pdo->query($queryNumRiparazioni);
                            $numRiparazioni = $stmtNumRiparazioni->fetchColumn(); ?>
                            <!-- CARD RIPARAZIONI -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-primary">
                                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Riparazioni attive</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($numRiparazioni) ? '0' : $numRiparazioni; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-tools fa-3x text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- CARD RIPARAZIONI PERSONALI -->
                        <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardMyRiparazioni):
                            $queryNumRiparazioniPersonali = $pdo->prepare("SELECT SUM(QTA) FROM riparazioni WHERE utente = :username");
                            $queryNumRiparazioniPersonali->execute([':username' => $_SESSION['username']]);
                            $numRiparazioniPersonali = $queryNumRiparazioniPersonali->fetchColumn(); ?>
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-success">
                                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/myRiparazioni"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Riparazioni attive Personali</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($numRiparazioniPersonali) ? '0' : $numRiparazioniPersonali; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-user-clock fa-3x text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- CARD CQ DI OGGI -->
                        <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1 && $mostraCardQuality):
                            try {
                                // Query per contare i record con la data odierna
                                $sql = "SELECT COUNT(*) AS num_records FROM cq_records WHERE data = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$data_oggi]);
                                // Ottieni il risultato della query
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $num_cq_records = $row['num_records'];
                            } catch (PDOException $e) {
                                // Gestione degli errori
                                echo "Errore durante l'esecuzione della query: " . $e->getMessage();
                                $num_cq_records = 0; // Imposta il numero di record a 0 in caso di errore
                            } ?>
                            <!-- CARD CQ -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-info">
                                    <a href="<?php echo BASE_URL ?>/functions/quality/detail?date=<?php echo $data_oggi ?>"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Test
                                                    eseguiti oggi</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo empty($num_cq_records) ? '0' : $num_cq_records; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-vials fa-3x text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- CARD PRODUZIONE DI OGGI -->
                        <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1 && $mostraCardProduzione):
                            try {
                                $giorno_odierno = date('d'); // Ottieni il giorno odierno
                                $mese_odierno_inglese = date('F'); // Ottieni il mese odierno in formato testo (es. January)
                        
                                // Array di conversione da inglese a italiano per i mesi
                                $mesi = array(
                                    "January" => "GENNAIO",
                                    "February" => "FEBBRAIO",
                                    "March" => "MARZO",
                                    "April" => "APRILE",
                                    "May" => "MAGGIO",
                                    "June" => "GIUGNO",
                                    "July" => "LUGLIO",
                                    "August" => "AGOSTO",
                                    "September" => "SETTEMBRE",
                                    "October" => "OTTOBRE",
                                    "November" => "NOVEMBRE",
                                    "December" => "DICEMBRE"
                                );
                                // Traduci il mese in italiano
                                $mese_odierno = $mesi[$mese_odierno_inglese];
                                $sqlTotali = "SELECT TOTALITAGLIO, TOTALIORLATURA, TOTALIMONTAGGIO FROM prod_mesi WHERE MESE = ? AND GIORNO = ?";
                                $stmtTotali = $pdo->prepare($sqlTotali);
                                $stmtTotali->execute([$mese_odierno, $giorno_odierno]);
                                // Ottieni il risultato della query
                                $totali = $stmtTotali->fetch(PDO::FETCH_ASSOC);
                                $totaleTaglio = $totali['TOTALITAGLIO'];
                                $totaleOrlatura = $totali['TOTALIORLATURA'];
                                $totaleMontaggio = $totali['TOTALIMONTAGGIO'];
                            } catch (PDOException $e) {
                                // Gestione degli errori
                                echo "Errore durante l'esecuzione della query: " . $e->getMessage();
                                $totaleTaglio = $totaleOrlatura = $totaleMontaggio = '0'; // Imposta i totali a 0 in caso di errore
                            }
                            ?>
                            <!-- CARD CQ -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-danger">
                                    <a href="<?php echo BASE_URL ?>/functions/production/produzione?month=<?php echo $mese_odierno; ?>&day=<?php echo $giorno_odierno; ?>"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    Totali Produzione (Oggi)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    T: <?php echo $totaleTaglio; ?> |
                                                    O: <?php echo $totaleOrlatura; ?> |
                                                    M: <?php echo $totaleMontaggio; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-chart-bar fa-3x text-danger"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- CARD PRODUZIONE DEL MESE -->
                        <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1 && $mostraCardProduzioneMese):
                            try {
                                $mese_odierno_inglese = date('F'); // Ottieni il mese odierno in formato testo (es. January)
                                // Array di conversione da inglese a italiano per i mesi
                                $mesi = array(
                                    "January" => "GENNAIO",
                                    "February" => "FEBBRAIO",
                                    "March" => "MARZO",
                                    "April" => "APRILE",
                                    "May" => "MAGGIO",
                                    "June" => "GIUGNO",
                                    "July" => "LUGLIO",
                                    "August" => "AGOSTO",
                                    "September" => "SETTEMBRE",
                                    "October" => "OTTOBRE",
                                    "November" => "NOVEMBRE",
                                    "December" => "DICEMBRE"
                                );

                                // Traduci il mese in italiano
                                $mese_odierno = $mesi[$mese_odierno_inglese];
                                $sqlTotali = "SELECT SUM(TOTALITAGLIO), SUM(TOTALIORLATURA), SUM(TOTALIMONTAGGIO) FROM prod_mesi WHERE MESE = ? ";
                                $stmtTotali = $pdo->prepare($sqlTotali);
                                $stmtTotali->execute([$mese_odierno]);
                                // Ottieni il risultato della query
                                $totali = $stmtTotali->fetch(PDO::FETCH_ASSOC);
                                $totaleTaglio = $totali['SUM(TOTALITAGLIO)'];
                                $totaleOrlatura = $totali['SUM(TOTALIORLATURA)'];
                                $totaleMontaggio = $totali['SUM(TOTALIMONTAGGIO)'];
                            } catch (PDOException $e) {
                                // Gestione degli errori
                                echo "Errore durante l'esecuzione della query: " . $e->getMessage();
                                $totaleTaglio = $totaleOrlatura = $totaleMontaggio = '0'; // Imposta i totali a 0 in caso di errore
                            }
                            ?>
                            <!-- CARD CQ -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-warning">
                                    <a href="<?php echo BASE_URL ?>/functions/production/calendario"
                                        class="stretched-link text-decoration-none"></a>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Totali Produzione (<?php echo $mese_odierno; ?>)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    T: <?php echo $totaleTaglio; ?> |
                                                    O: <?php echo $totaleOrlatura; ?> |
                                                    M: <?php echo $totaleMontaggio; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fal fa-chart-bar fa-3x text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- CHIUSURA ROW CARDS -->
                </div>
            </div>
            <?php include (BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php include (BASE_PATH . "/components/scripts.php"); ?>
    <style>
        .card-hover {
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;

        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);

        }

        .card-hover a {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
    </style>

</body>
<script>
    document.getElementById('preferencesForm').addEventListener('submit', function (event) {
        event.preventDefault();
        // Ottieni gli elementi e verifica se esistono prima di accedere alle loro proprietà
        const cardRiparazioniElement = document.getElementById('cardRiparazioni');
        const cardMyRiparazioniElement = document.getElementById('cardMyRiparazioni');
        const cardQualityElement = document.getElementById('cardQuality');
        const cardProductionElement = document.getElementById('cardProduction');
        const cardProductionMonthElement = document.getElementById('cardProductionMonth');
        const cardRiparazioni = cardRiparazioniElement ? cardRiparazioniElement.checked ? 1 : 0 : 0;
        const cardMyRiparazioni = cardMyRiparazioniElement ? cardMyRiparazioniElement.checked ? 1 : 0 : 0;
        const cardQuality = cardQualityElement ? cardQualityElement.checked ? 1 : 0 : 0;
        const cardProduction = cardProductionElement ? cardProductionElement.checked ? 1 : 0 : 0;
        const cardProductionMonth = cardProductionMonthElement ? cardProductionMonthElement.checked ? 1 : 0 : 0;

        fetch('functions/users/update_preferences.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                user_id: <?php echo $idUtente; ?>,
                card_riparazioni: cardRiparazioni,
                card_myRiparazioni: cardMyRiparazioni,
                card_quality: cardQuality,
                card_production: cardProduction,
                card_productionMonth: cardProductionMonth
            })
        })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    window.location.reload();
                } else {
                    alert('Errore durante l\'aggiornamento delle preferenze.');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
            });
    });
</script>