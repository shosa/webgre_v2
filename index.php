<?php
include("config/config.php");
session_start();
require_once BASE_PATH . '/components/auth_validate.php';
$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ottieni informazioni utente
$queryNome = "SELECT nome FROM utenti WHERE user_name = :username";
$stmtNome = $pdo->prepare($queryNome);
$stmtNome->bindParam(':username', $_SESSION["username"], PDO::PARAM_STR);
$stmtNome->execute();
$nome = $stmtNome->fetchColumn();
$tipoUtente = $_SESSION['admin_type'];
$data_oggi = date('d/m/Y');

// Gestione preferenze utente
$queryPreferenze = $pdo->prepare("SELECT * FROM utenti_cards WHERE user_id = :user_id");
$queryPreferenze->execute([':user_id' => $_SESSION['user_id']]);
$preferenze = $queryPreferenze->fetch(PDO::FETCH_ASSOC);

// Verifica esistenza preferenze
$queryCheckPreferenze = "SELECT COUNT(*) FROM utenti_cards WHERE user_id = :user_id";
$stmtCheckPreferenze = $pdo->prepare($queryCheckPreferenze);
$stmtCheckPreferenze->execute([':user_id' => $_SESSION['user_id']]);
$preferenzeEsistenti = $stmtCheckPreferenze->fetchColumn();

if ($preferenzeEsistenti == 0) {
    // Crea un nuovo record con valori predefiniti
    $queryInsertPreferenze = "INSERT INTO utenti_cards (user_id, card_riparazioni, card_myRiparazioni, card_quality, card_produzione, card_produzioneMese) 
                             VALUES (:user_id, 1, 1, 1, 1, 1)";
    $stmtInsertPreferenze = $pdo->prepare($queryInsertPreferenze);
    $stmtInsertPreferenze->execute([':user_id' => $_SESSION['user_id']]);
    
    // Ricarica le preferenze
    $queryPreferenze->execute([':user_id' => $_SESSION['user_id']]);
    $preferenze = $queryPreferenze->fetch(PDO::FETCH_ASSOC);
}

// Imposta i flag per mostrare/nascondere le card
$mostraCardRiparazioni = isset($preferenze['card_riparazioni']) && $preferenze['card_riparazioni'] == 1;
$mostraCardMyRiparazioni = isset($preferenze['card_myRiparazioni']) && $preferenze['card_myRiparazioni'] == 1;
$mostraCardQuality = isset($preferenze['card_quality']) && $preferenze['card_quality'] == 1;
$mostraCardProduzione = isset($preferenze['card_produzione']) && $preferenze['card_produzione'] == 1;
$mostraCardProduzioneMese = isset($preferenze['card_produzioneMese']) && $preferenze['card_produzioneMese'] == 1;
?>

<?php include("components/header.php"); ?>

<body id="page-top" class="bg-light">
    <div id="wrapper">
        <?php include("components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include("components/topbar.php"); ?>
                <div class="container-fluid px-4">
                    <?php require_once BASE_PATH . "/utils/alerts.php"; ?>
                    
                    <!-- Intestazione Dashboard -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-3">
                        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </h1>
                        <div class="d-none d-sm-inline-block">
                            <span class="mr-2 d-none d-lg-inline text-gray-600">
                                <i class="far fa-calendar-alt mr-1"></i> <?php echo $data_oggi; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Pulsante Personalizza indipendente -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="font-weight-bold text-<?php echo $colore; ?> mb-0">
                            <i class="fas fa-chart-line mr-2"></i>Contatori
                        </h5>
                        <button class="btn btn-<?php echo $colore; ?>" 
                                id="personalizzaBtn" 
                                data-toggle="modal" 
                                data-target="#configuraContatori">
                            <i class="fas fa-cog mr-1"></i> Personalizza
                        </button>
                    </div>
                    
                    <!-- Modal per la configurazione dei contatori -->
                    <div class="modal fade" id="configuraContatori" tabindex="-1" role="dialog" aria-labelledby="configuraContatoriLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-<?php echo $colore; ?> text-white">
                                    <h5 class="modal-title" id="configuraContatoriLabel">
                                        <i class="fas fa-sliders-h mr-2"></i> Configura i tuoi contatori
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body p-4">
                                    <form id="preferencesForm">
                                        <div class="form-group mb-4">
                                            <h6 class="text-muted mb-3">CONFIGURA I TUOI CONTATORI:</h6>
                                            
                                            <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1): ?>
                                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                                    <label for="cardRiparazioni" class="mb-0 font-weight-medium">Riparazioni</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="cardRiparazioni" name="cardRiparazioni" 
                                                            <?php echo $mostraCardRiparazioni ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="cardRiparazioni"></label>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                                    <label for="cardMyRiparazioni" class="mb-0 font-weight-medium">Riparazioni Personali</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="cardMyRiparazioni" name="cardMyRiparazioni" 
                                                            <?php echo $mostraCardMyRiparazioni ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="cardMyRiparazioni"></label>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1): ?>
                                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                                    <label for="cardQuality" class="mb-0 font-weight-medium">Controllo Qualità</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="cardQuality" name="cardQuality" 
                                                            <?php echo $mostraCardQuality ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="cardQuality"></label>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1): ?>
                                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                                    <label for="cardProduction" class="mb-0 font-weight-medium">Produzione di Oggi</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="cardProduction" name="cardProduction" 
                                                            <?php echo $mostraCardProduzione ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="cardProduction"></label>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <label for="cardProductionMonth" class="mb-0 font-weight-medium">Produzione del Mese</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="cardProductionMonth" name="cardProductionMonth"
                                                            <?php echo $mostraCardProduzioneMese ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="cardProductionMonth"></label>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-right">
                                            <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">
                                                <i class="fas fa-times mr-1"></i> Annulla
                                            </button>
                                            <button type="submit" class="btn btn-<?php echo $colore; ?>">
                                                <i class="fas fa-save mr-1"></i> Salva
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Area Card Contatori -->
                    <?php include("components/homeCards.php"); ?>
                    
                    <!-- Rimosso il toast di Bootstrap per evitare errori -->
                    
                    <!-- Attività recenti dalla tabella activity_log -->
                    <div class="card shadow-sm border-0 rounded-lg mb-4 mt-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-<?php echo $colore; ?>">
                                <i class="fas fa-history mr-2"></i>Attività Recenti
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php
                                // Query per recuperare le ultime 5 attività dell'utente
                                $queryAttivita = "SELECT category, activity_type, description, created_at 
                                                 FROM activity_log 
                                                 WHERE user_id = :user_id 
                                                 ORDER BY created_at DESC 
                                                 LIMIT 5";
                                $stmtAttivita = $pdo->prepare($queryAttivita);
                                $stmtAttivita->execute([':user_id' => $_SESSION['user_id']]);
                                
                                $attivita = $stmtAttivita->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($attivita) > 0) {
                                    foreach ($attivita as $item) {
                                        // Determina l'icona e il colore in base alla categoria
                                        $iconaClasse = 'fa-cog';
                                        $coloreSfondo = 'primary';
                                        
                                        // Imposta icona e colore in base alla categoria
                                        switch (strtolower($item['category'])) {
                                            case 'riparazioni':
                                                $iconaClasse = 'fa-tools';
                                                $coloreSfondo = 'primary';
                                                break;
                                            case 'quality':
                                            case 'qualità':
                                                $iconaClasse = 'fa-check';
                                                $coloreSfondo = 'success';
                                                break;
                                            case 'produzione':
                                                $iconaClasse = 'fa-industry';
                                                $coloreSfondo = 'warning';
                                                break;
                                            case 'login':
                                            case 'sistema':
                                                $iconaClasse = 'fa-user-shield';
                                                $coloreSfondo = 'info';
                                                break;
                                            default:
                                                $iconaClasse = 'fa-cog';
                                                $coloreSfondo = 'secondary';
                                        }
                                        
                                        // Formatta la data dall'oggetto timestamp
                                        $dataFormattata = date('d M, H:i', strtotime($item['created_at']));
                                        ?>
                                        <div class="list-group-item d-flex align-items-center py-3 px-4">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-<?php echo $coloreSfondo; ?>-light text-<?php echo $coloreSfondo; ?>">
                                                    <i class="fas <?php echo $iconaClasse; ?>"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500"><?php echo $dataFormattata; ?></div>
                                                <span><?php echo htmlspecialchars($item['description']); ?></span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    // Nessuna attività trovata
                                    echo '<div class="list-group-item text-center py-4">
                                            <div class="text-muted">
                                                <i class="far fa-calendar-times mb-2 fa-2x"></i>
                                                <p class="mb-0">Nessuna attività recente da visualizzare</p>
                                            </div>
                                          </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
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
        /* Stile per le card originarie con miglioramenti */
        .card {
            transition: all 0.3s ease;
            border-radius: 0.75rem !important;
            overflow: hidden;
        }
        
        .card-hover {
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Miglioramenti per le icone */
        .icon-circle {
            height: 3rem;
            width: 3rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1);
        }
        
        .bg-success-light {
            background-color: rgba(28, 200, 138, 0.1);
        }
        
        .bg-info-light {
            background-color: rgba(54, 185, 204, 0.1);
        }
        
        .bg-warning-light {
            background-color: rgba(246, 194, 62, 0.1);
        }
        
        .bg-danger-light {
            background-color: rgba(231, 74, 59, 0.1);
        }
        
        /* Stile del modal */
        .modal-content {
            border: none;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .modal-header {
            border-bottom: none;
            padding: 1.5rem;
        }
        
        /* Custom switch con colore dinamico */
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--<?php echo $colore; ?>);
            border-color: var(--<?php echo $colore; ?>);
        }
        
        /* Nuovi stili per le card */
        .border-bottom-primary {
            border-bottom: 4px solid #4e73df !important;
        }
        
        .border-bottom-success {
            border-bottom: 4px solid #1cc88a !important;
        }
        
        .border-bottom-info {
            border-bottom: 4px solid #36b9cc !important;
        }
        
        .border-bottom-warning {
            border-bottom: 4px solid #f6c23e !important;
        }
        
        .border-bottom-danger {
            border-bottom: 4px solid #e74a3b !important;
        }
        
        /* Animazioni */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    <script>
        // Funzione per mostrare notifiche senza jQuery
        function showNotification(message, isSuccess) {
            // Crea un elemento di notifica personalizzato
            const notification = document.createElement('div');
            notification.className = `alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show fixed-bottom m-3`;
            notification.style.maxWidth = '400px';
            notification.style.right = '0';
            notification.style.bottom = '0';
            notification.style.position = 'fixed';
            notification.style.zIndex = '9999';
            notification.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            
            notification.innerHTML = `
                <strong>${isSuccess ? 'Successo!' : 'Errore!'}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            // Aggiungi la notifica al DOM
            document.body.appendChild(notification);
            
            // Rimuovi automaticamente la notifica dopo 3 secondi
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
            
            // Gestisci il pulsante di chiusura
            const closeButton = notification.querySelector('.close');
            closeButton.addEventListener('click', () => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            });
        }
        
        // Gestione del modal senza jQuery
        const modalElement = document.getElementById('configuraContatori');
        const modalBackdrop = document.createElement('div');
        modalBackdrop.className = 'modal-backdrop fade';
        
        // Funzione per chiudere il modal
        function closeModal() {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            if (document.body.contains(modalBackdrop)) {
                document.body.removeChild(modalBackdrop);
            }
            document.body.classList.remove('modal-open');
        }
        
        // Funzione per aprire il modal
        function openModal() {
            modalElement.style.display = 'block';
            setTimeout(() => {
                modalElement.classList.add('show');
                modalBackdrop.classList.add('show');
            }, 10);
            document.body.appendChild(modalBackdrop);
            document.body.classList.add('modal-open');
        }
        
        // Aggiungi un listener agli elementi di apertura del modal
        document.getElementById('personalizzaBtn').addEventListener('click', openModal);
        
        // Aggiungi listener agli elementi di chiusura del modal
        const closeButtons = modalElement.querySelectorAll('[data-dismiss="modal"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', closeModal);
        });
        
        // Chiudi il modal quando l'utente fa clic all'esterno
        modalElement.addEventListener('click', (event) => {
            if (event.target === modalElement) {
                closeModal();
            }
        });
        
        // Gestione del form di preferenze
        document.getElementById('preferencesForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Ottieni gli elementi e verifica se esistono prima di accedere alle loro proprietà
            const cardRiparazioniElement = document.getElementById('cardRiparazioni');
            const cardMyRiparazioniElement = document.getElementById('cardMyRiparazioni');
            const cardQualityElement = document.getElementById('cardQuality');
            const cardProductionElement = document.getElementById('cardProduction');
            const cardProductionMonthElement = document.getElementById('cardProductionMonth');
            
            // Prepara i dati
            const formData = new URLSearchParams({
                user_id: <?php echo $_SESSION['user_id']; ?>,
                card_riparazioni: cardRiparazioniElement ? (cardRiparazioniElement.checked ? 1 : 0) : 0,
                card_myRiparazioni: cardMyRiparazioniElement ? (cardMyRiparazioniElement.checked ? 1 : 0) : 0,
                card_quality: cardQualityElement ? (cardQualityElement.checked ? 1 : 0) : 0,
                card_production: cardProductionElement ? (cardProductionElement.checked ? 1 : 0) : 0,
                card_productionMonth: cardProductionMonthElement ? (cardProductionMonthElement.checked ? 1 : 0) : 0
            });
            
            // Invia richiesta AJAX
            fetch('functions/users/update_cards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    // Nascondi il modal
                    closeModal();
                    
                    // Mostra notifica di successo
                    showNotification('Preferenze aggiornate con successo!', true);
                    
                    // Ricarica la pagina dopo un breve ritardo
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Mostra notifica di errore
                    showNotification('Errore durante l\'aggiornamento delle preferenze.', false);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                showNotification('Si è verificato un errore di rete.', false);
            });
        });
    </script>
</body>