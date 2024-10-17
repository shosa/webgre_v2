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


                    echo "<div class='container mt-4'>";
                    echo "<h2 class='mb-4'>Informazioni sul Tracciamento</h2>";

                    // Barra di avanzamento
                    echo "<div class='progress mb-3' style='height: 25px;'>";
                    echo "<div class='progress-bar bg-success' role='progressbar' style='width: $progress%;' aria-valuenow='$progress' aria-valuemin='0' aria-valuemax='100'>$progress%</div>";
                    echo "</div>";

                    // Card con i dettagli principali
                    echo "<div class='card shadow-lg mb-2 border-info'>";
                    echo "<div class='card-body d-flex justify-content-between align-items-center'>";

                    // Dettagli a sinistra
                    echo "<div>";
                    echo "<p><strong>Numero di Tracciamento:</strong><br>" . htmlspecialchars($trackingInfo['tracking_number']) . "</p>";
                    echo "<p><strong>Corriere:</strong><br> <span class='text-uppercase'>" . htmlspecialchars($trackingInfo['courier_code']) . "</span></p>";
                    echo "<p><strong>Stato della Consegna:</strong><br> <span class='text-uppercase'>" . htmlspecialchars($trackingInfo['delivery_status']) . "</span></p>";
                    $updateDate = new DateTime($trackingInfo['update_date']);
                    echo "<p><strong>Ultimo Aggiornamento:</strong><br>" . htmlspecialchars($updateDate->format('d/m/Y H:i')) . "</p>";
                    echo "</div>";

                    // Icona in base allo stato di consegna
                    echo "<div>";
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
                    echo "</div>";

                    echo "</div></div>"; // Fine della card principale

                    // Informazioni sull'origine

                    $originInfo = $trackingInfo['origin_info'];


                    // Sottostati di Tracking
                    echo "<h3 class='mb-3'>Eventi Tracking</h3>";
                    if (!empty($originInfo['trackinfo'])) {
                        foreach ($originInfo['trackinfo'] as $checkpoint) {
                            echo "<div class='card shadow-sm mb-2  border-info border-2'>";
                            echo "<div class='card-body'>";
                            echo "<h4 style='color:black;'><strong>" . htmlspecialchars($checkpoint['location']) . "</strong> </h4>";
                            $checkpointDate = new DateTime($checkpoint['checkpoint_date']);
                            echo "<p><strong>" . htmlspecialchars($checkpointDate->format('d/m/Y H:i')) . "</strong> <i class='fas fa-globe-europe float-right'></i></p>";
                          
                            echo "<p><strong>Stato:</strong> " . htmlspecialchars(getTrackingSubStatus($checkpoint['checkpoint_delivery_status'])) . "</p>";
                            echo "<p><strong>Note:</strong> " . htmlspecialchars(getTrackingSubStatus($checkpoint['checkpoint_delivery_substatus'])) . "</p>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>Nessun dettaglio di tracking disponibile.</p>";
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
            return 'Il corriere ha ritirato il pacco, in viaggio verso la destinazione';
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