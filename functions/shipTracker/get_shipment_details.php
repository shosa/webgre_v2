<style>
    .world-icon-background {
        position: absolute;
        right: -20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 80px;
        color: rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .status-icon {
        font-size: 20px;
        color: currentColor;
    }

    .card-body {
        position: relative;
        z-index: 2;
    }

    .card {
        position: relative;
        overflow: hidden;
    }
</style>
<?php
require_once '../../config/config.php';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $shipmentId = intval($_GET['id']);

    try {
        // Connessione al database
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Recupera il numero di tracciamento e il codice del corriere
        $stmt = $pdo->prepare("SELECT tracking_number, carrier_code FROM shipments WHERE id = :id");
        $stmt->execute(['id' => $shipmentId]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($shipment) {
            // Recupera i dettagli dalla API di TrackingMore
            $api_key = 'ndrk5yvj-x3jj-cbxk-8raq-nqrf0g77tvdk';
            $tracking_number = $shipment['tracking_number'];

            // Componi l'URL per la richiesta
            $tracking_info_url = "https://api.trackingmore.com/v3/trackings/get?tracking_numbers=" . urlencode($tracking_number) . "&lang=it";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $tracking_info_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Tracking-Api-Key: $api_key"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                echo "<p>Errore API: $err</p>";
            } else {
                // Decodifica la risposta JSON
                $responseData = json_decode($response, true);

                if ($responseData['code'] == 200) {
                    $trackingInfo = $responseData['data'][0];

                    $progress = getPercentage($trackingInfo['delivery_status']);
                    // Calcolo della percentuale di avanzamento basato sullo stato della consegna


                    echo "<div class='container mt-2'>";

                    // Titolo e icona di stato
                    echo "<div class='d-flex justify-content-between align-items-center mb-1'>";
                    echo "<h3 class='mb-0' style='color:black;'>Tracking #" . htmlspecialchars($trackingInfo['tracking_number']) . "</h3>";

                    // Icona di stato alla destra del titolo
                    echo "<div class='status-icon'>"; // Modifica per l'icona accanto al titolo
                    switch ($trackingInfo['delivery_status']) {
                        case 'delivered':
                            echo "<i class='fas fa-check-circle fa-3x text-success'></i>";
                            break;
                        case 'transit':
                            echo "<i class='fas fa-shipping-fast fa-3x text-primary'></i>";
                            break;
                        case 'pickup':
                            echo "<i class='fas fa-box-open fa-3x text-warning'></i>";
                            break;
                        case 'undelivered':
                            echo "<i class='fas fa-exclamation-circle fa-3x text-danger'></i>";
                            break;
                        case 'exception':
                            echo "<i class='fas fa-times-circle fa-3x text-danger'></i>";
                            break;
                        default:
                            echo "<i class='fas fa-info-circle fa-3x text-secondary'></i>";
                            break;
                    }
                    echo "</div>"; // Chiude l'icona di stato accanto al titolo
                    echo "</div>"; // Chiude il container del titolo

                    // Barra di avanzamento
                    echo "<div class='progress mb-2 border' style='height: 25px;'>";
                    echo "<div class='progress-bar bg-success' role='progressbar' style='width: $progress%;' aria-valuenow='$progress' aria-valuemin='0' aria-valuemax='100'>$progress%</div>";
                    echo "</div>";

                    // Card principale
                    echo "<div class='card shadow-lg mb-2 border-orange'>";
                    echo "<div class='card-body d-flex justify-content-between align-items-center'>";

                    // Parte sinistra con numero di tracciamento e corriere in linea
                    echo "<div>";
                    echo "<div class='world-icon-background'>";
                    echo "<i class='fas fa-box-open fa-2xl'></i>";
                    echo "</div>";

                    // Numero di tracciamento e corriere in linea
                    echo "<p><strong>Tracking e Corriere:</strong> <br>";
                    echo "<span class='badge badge-success' style='font-size:1rem !important;'>" . htmlspecialchars($trackingInfo['tracking_number']) . "</span> - ";
                    echo "<span class='text-uppercase'>" . htmlspecialchars($trackingInfo['courier_code']) . "</span>";
                    echo "</p>";

                    echo "<p><strong>Ultimo Evento:</strong><br> <span class='text-uppercase font-weight-bold text-primary'>" . htmlspecialchars($trackingInfo['latest_event']) . "</span></p>";
                    $updateDate = new DateTime($trackingInfo['lastest_checkpoint_time']);
                    echo "<p><strong>Ultimo Aggiornamento:</strong><br>" . htmlspecialchars($updateDate->format('d/m/Y H:i')) . "</p>";
                    echo "</div>"; // Chiude la sezione sinistra

                    echo "</div></div>"; // Chiude la card principale

                    // Informazioni sull'origine

                    $originInfo = $trackingInfo['origin_info'];


                    // Sottostati di Tracking
                    echo "<h3 class='mb-2 mt-1' style='color:black;'>Eventi</h3>";
                    if (!empty($originInfo['trackinfo'])) {
                        foreach ($originInfo['trackinfo'] as $checkpoint) {
                            echo "<div class='card shadow-sm mb-2  border-info border-2 position-relative'>"; // Aggiungi position-relative per la card
                            echo "<div class='card-body'>";

                            // Location a sinistra e data a destra
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<h5 style='color:black;'><strong>" . htmlspecialchars($checkpoint['location']) . "</strong></h4>";
                            $checkpointDate = new DateTime($checkpoint['checkpoint_date']);
                            echo "<p class='badge badge-info text-right mr-5 font-weight-normal' style='font-size:1rem !important;'><strong>" . htmlspecialchars($checkpointDate->format('d/m/Y H:i')) . "</strong></p>";
                            echo "</div>";

                            // Aggiungi l'icona del mondo come background, parzialmente fuori
                            echo "<div class='world-icon-background'>";
                            echo "<i class='fas fa-globe-europe'></i>";
                            echo "</div>";

                            echo "<p class='font-weight-normal h6 text-dark'><i>" . htmlspecialchars($checkpoint['tracking_detail']) . "</i></p>";
                            echo "</div></div>"; // Fine della card
                        }
                    } else {
                        echo "<p class='alert alert-warning'>Nessun dettaglio di tracking disponibile.</p>";
                    }

                    echo "</div>"; // Fine container
                } else {
                    echo "<p>Errore: " . htmlspecialchars($responseData['message']) . "</p>";
                }
            }
        } else {
            echo "<p>Spedizione non trovata.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>Errore nella connessione al database: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>ID spedizione non valido.</p>";
}

function getPercentage($stat)
{
    switch ($stat) {
        case 'pending':
            return 10;
        case 'notfound':
            return 0;
        case 'transit':
            return 50;
        case 'pickup':
            return 75;
        case 'delivered':
            return 100;
        case 'expired':
            return 0;
        case 'undelivered':
            return 40;
        case 'exception':
            return 30;
        case 'InfoReceived':
            return 20;
        default:
            return 20;
    }
}


function getTrackingSubStatus($subStatus)
{
    switch ($subStatus) {
        // Notfound SubStatus
        case 'notfound002':
            return 'Nessuna informazione di tracciamento trovata';

        // Transit SubStatus
        case 'transit001':
            return 'Pacco in viaggio verso la destinazione';
        case 'transit002':
            return 'Pacco arrivato a un hub o centro di smistamento';
        case 'transit003':
            return 'Pacco arrivato presso il centro di consegna';
        case 'transit004':
            return 'Pacco arrivato nel paese di destinazione';
        case 'transit005':
            return 'Sdoganamento completato';
        case 'transit006':
            return 'Articolo spedito';
        case 'transit007':
            return 'Partito dall\'aeroporto';

        // Pickup SubStatus
        case 'pickup001':
            return 'Il pacco è in consegna';
        case 'pickup002':
            return 'Il pacco è pronto per il ritiro';
        case 'pickup003':
            return 'Il cliente è stato contattato prima della consegna finale';

        // Delivered SubStatus
        case 'delivered001':
            return 'Pacco consegnato con successo';
        case 'delivered002':
            return 'Pacco ritirato dal destinatario';
        case 'delivered003':
            return 'Pacco ricevuto e firmato dal destinatario';
        case 'delivered004':
            return 'Pacco lasciato davanti alla porta o con il vicino';

        // Undelivered SubStatus
        case 'undelivered001':
            return 'Problemi relativi all\'indirizzo';
        case 'undelivered002':
            return 'Destinatario non a casa';
        case 'undelivered003':
            return 'Impossibile localizzare il destinatario';
        case 'undelivered004':
            return 'Non consegnato per altri motivi';

        // Exception SubStatus
        case 'exception004':
            return 'Il pacco non è stato ritirato';
        case 'exception005':
            return 'Altre eccezioni';
        case 'exception006':
            return 'Il pacco è stato trattenuto dalla dogana';
        case 'exception007':
            return 'Il pacco è stato perso o danneggiato durante la consegna';
        case 'exception008':
            return 'L\'ordine di logistica è stato annullato prima del ritiro del pacco';
        case 'exception009':
            return 'Il pacco è stato rifiutato dal destinatario';
        case 'exception010':
            return 'Il pacco è stato restituito al mittente';
        case 'exception011':
            return 'Il pacco è in fase di invio al mittente';

        // InfoReceived SubStatus
        case 'notfound001':
            return 'Il pacco è in attesa del ritiro da parte del corriere';

        // New Cases Tradotti
        case 'pending':
            return 'Nuovo pacco aggiunto, in attesa di essere tracciato';
        case 'notfound':
            return 'Le informazioni di tracciamento non sono ancora disponibili';
        case 'transit':
            return 'In viaggio verso la destinazione';
        case 'pickup':
            return 'Il pacco è in consegna o in attesa del ritiro da parte del destinatario';
        case 'delivered':
            return 'Il pacco è stato consegnato con successo';
        case 'expired':
            return 'Nessuna informazione di tracciamento per 30 giorni (servizio espresso) o 60 giorni (servizio postale)';
        case 'undelivered':
            return 'Tentativo di consegna fallito, il corriere riproverà';
        case 'exception':
            return 'Pacco perso, restituito al mittente o altre eccezioni';
        case 'InfoReceived':
            return 'Il corriere ha ricevuto la richiesta dal mittente e sta per ritirare la spedizione';

        default:
            return 'Informazione di stato non disponibile';
    }
}

?>