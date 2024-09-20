<?php
include("config/config.php");
session_start();
require_once BASE_PATH . '/components/auth_validate.php';
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdo->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();
$tipoUtente = $_SESSION['admin_type'];
$data_oggi = date('d/m/Y');
$queryPreferenze = $pdo->prepare("SELECT * FROM utenti_cards WHERE user_id = :user_id");
$queryPreferenze->execute([':user_id' => $_SESSION['user_id']]);
$preferenze = $queryPreferenze->fetch(PDO::FETCH_ASSOC);
$mostraCardRiparazioni = isset($preferenze['card_riparazioni']) && $preferenze['card_riparazioni'] == 1;
$mostraCardMyRiparazioni = isset($preferenze['card_myRiparazioni']) && $preferenze['card_myRiparazioni'] == 1;
$mostraCardQuality = isset($preferenze['card_quality']) && $preferenze['card_quality'] == 1;
$mostraCardProduzione = isset($preferenze['card_produzione']) && $preferenze['card_produzione'] == 1;
$mostraCardProduzioneMese = isset($preferenze['card_produzioneMese']) && $preferenze['card_produzioneMese'] == 1;
$queryCheckPreferenze = "SELECT COUNT(*) FROM utenti_cards WHERE user_id = :user_id";
$stmtCheckPreferenze = $pdo->prepare($queryCheckPreferenze);
$stmtCheckPreferenze->execute([':user_id' => $_SESSION['user_id']]);
$preferenzeEsistenti = $stmtCheckPreferenze->fetchColumn();
if ($preferenzeEsistenti == 0) {
    // Crea un nuovo record con valori predefiniti
    $queryInsertPreferenze = "INSERT INTO utenti_cards (user_id, card_riparazioni, card_myRiparazioni, card_quality, card_produzione, card_produzioneMese) VALUES (:user_id, 1, 1, 1, 1, 1)";
    $stmtInsertPreferenze = $pdo->prepare($queryInsertPreferenze);
    $stmtInsertPreferenze->execute([':user_id' => $_SESSION['user_id']]);
}
?>
<?php include("components/header.php"); ?>

<body id="page-top">
    <div id="wrapper">
        <?php include("components/navbar.php"); //INCLUSIONE NAVBAR ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include("components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>
                    <!-- INIZIO ROW CARDS -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="h3 mb-0 text-<?php echo $colore; ?>">Contatori</h3>
                        <hr>
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle text-<?php echo $colore; ?>" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fal fa-sliders-h fa-2x text-<?php echo $colore; ?>"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <form id="preferencesForm" class="px-2 py-2">
                                    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
                                        <div class="h5 form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardRiparazioni"
                                                name="cardRiparazioni" <?php echo $mostraCardRiparazioni ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardRiparazioni">Riparazioni</label>
                                        </div>
                                        <div class="h5 form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardMyRiparazioni"
                                                name="cardMyRiparazioni" <?php echo $mostraCardMyRiparazioni ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardMyRiparazioni">Riparazioni
                                                Personali</label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
                                        <div class="h5 form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardQuality"
                                                name="cardQuality" <?php echo $mostraCardQuality ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardQuality">Controllo Qualità</label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1): ?>
                                        <div class="h5 form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="cardProduction"
                                                name="cardProduction" <?php echo $mostraCardProduzione ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cardProduction">Produzione di Oggi</label>
                                        </div>
                                        <div class="h5 form-check form-switch mb-2">
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
                    <hr>
                    <?php include("components/homeCards.php"); ?>
                    <!-- CHIUSURA ROW CARDS -->
                </div>
            </div>
            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php include(BASE_PATH . "/components/scripts.php"); ?>
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
        fetch('functions/users/update_cards.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                user_id: <?php echo $_SESSION['user_id']; ?>,
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