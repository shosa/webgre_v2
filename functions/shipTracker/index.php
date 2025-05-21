<?php
session_start();
require_once '../../config/config.php';
include(BASE_PATH . "/components/header.php");

$utente = $_SESSION["user_id"];
$couriers = json_decode(file_get_contents('couriers.json'), true);
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tracker Spedizioni</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tracker Spedizioni</li>
                    </ol>
                    <div class="row">
                        <!-- Form per creare una nuova spedizione -->
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Crea e Traccia Spedizione</h6>
                                </div>
                                <div class="card-body text-center">
                                    <form method="POST" action="">
                                        <div class="form-group">
                                            <label for="tracking_number">Inserisci i dati</label>
                                            <input type="text" name="tracking_number" class="form-control"
                                                placeholder="Numero Spedizione" required>
                                            <input type="text" name="name" class="form-control mt-1"
                                                placeholder="Nome (opzionale)" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="carrier_code">Seleziona Corriere</label>
                                            <select name="carrier_code" class="form-control" id="carrier-select"
                                                onchange="updateLogo()">
                                                <?php foreach ($couriers['data'] as $index => $courier): ?>
                                                    <option value="<?= $courier['courier_code'] ?>" <?= $index === 0 ? 'selected' : '' ?>>
                                                        <?= $courier['courier_name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div id="carrier-logo"
                                                style="margin-top: 10px; display: flex; justify-content: center;">
                                                <img id="selected-carrier-logo"
                                                    src="<?= $couriers['data'][0]['courier_logo'] ?>" alt=""
                                                    style="height: 50px; display: block; margin: auto;">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Aggiungi e
                                            Traccia</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <link rel="stylesheet" href="custom.css">
                        <!-- Elenco delle spedizioni create -->
                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Elenco Spedizioni</h6>
                                </div>
                                <div class="card-body">

                                    <?php
                                    include(BASE_PATH . "/utils/alerts.php");
                                    try {
                                        // Connessione al database
                                        $pdo = getDbInstance();


                                        // Se il modulo è stato inviato
                                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tracking_number'], $_POST['carrier_code'])) {
                                            $tracking_number = $_POST['tracking_number'];
                                            $carrier_code = $_POST['carrier_code'];
                                            $name = $_POST['name'];

                                            // Controllo se la spedizione esiste già
                                            $stmt = $pdo->prepare("SELECT * FROM shipments WHERE tracking_number = :tracking_number AND carrier_code = :carrier_code");
                                            $stmt->execute(['tracking_number' => $tracking_number, 'carrier_code' => $carrier_code]);
                                            $existingShipment = $stmt->fetch(PDO::FETCH_ASSOC);

                                            if ($existingShipment) {
                                                $_SESSION['warning'] = "La spedizione esiste già nel sistema!";
                                            } else {
                                                $api_key = 'ndrk5yvj-x3jj-cbxk-8raq-nqrf0g77tvdk';
                                                $create_shipment_url = "https://api.trackingmore.com/v3/trackings/create";

                                                // Creazione del payload con i dati obbligatori
                                                $postData = json_encode([
                                                    [
                                                        'tracking_number' => $tracking_number,
                                                        'courier_code' => $carrier_code,
                                                        'lang' => "it",
                                                    ]
                                                ]);

                                                $curl = curl_init();
                                                curl_setopt_array($curl, array(
                                                    CURLOPT_URL => $create_shipment_url,
                                                    CURLOPT_RETURNTRANSFER => true,
                                                    CURLOPT_POST => true,
                                                    CURLOPT_POSTFIELDS => $postData,
                                                    CURLOPT_HTTPHEADER => array(
                                                        "Content-Type: application/json",
                                                        "Tracking-Api-Key: $api_key"
                                                    ),
                                                ));

                                                $response = curl_exec($curl);
                                                $err = curl_error($curl);
                                                curl_close($curl);

                                                if ($err) {
                                                    echo "Errore API: $err";
                                                } else {
                                                    $shipment_info = json_decode($response, true);
                                                    if (isset($shipment_info['code']) && $shipment_info['code'] == 200) {
                                                        // Memorizza la spedizione nel database
                                                        $stmt = $pdo->prepare("INSERT INTO shipments (createdby_userid, name, tracking_number, carrier_code, status, updated_at) VALUES (:createdby_userid, :name, :tracking_number, :carrier_code, :status, :updated_at)");
                                                        $stmt->execute([
                                                            'createdby_userid' => $utente,
                                                            'name' => $name,
                                                            'tracking_number' => $tracking_number,
                                                            'carrier_code' => $carrier_code,
                                                            'status' => 'In transito',
                                                            'updated_at' => date('Y-m-d H:i:s')
                                                        ]);
                                                        $_SESSION['success'] = "Spedizione creata con successo!";
                                                    } else {
                                                        $_SESSION['danger'] = "Si è verificato un errore:" . $shipment_info['message'];
                                                    }
                                                }
                                            }
                                        }
                                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_tracking_id'])) {
                                            $tracking_id = $_POST['delete_tracking_id'];

                                            // Recupera i dati della spedizione dal database
                                            $stmt = $pdo->prepare("SELECT tracking_number, carrier_code FROM shipments WHERE id = :id");
                                            $stmt->execute(['id' => $tracking_id]);
                                            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

                                            if ($shipment) {
                                                // Parametri per la chiamata all'API di eliminazione
                                                $api_key = 'ndrk5yvj-x3jj-cbxk-8raq-nqrf0g77tvdk';
                                                $delete_shipment_url = "https://api.trackingmore.com/v3/trackings/delete";
                                                $postData = json_encode([
                                                    'tracking_number' => $shipment['tracking_number'],
                                                    'courier_code' => $shipment['carrier_code']
                                                ]);

                                                // Richiesta cURL per eliminare la spedizione
                                                $curl = curl_init();
                                                curl_setopt_array($curl, array(
                                                    CURLOPT_URL => $delete_shipment_url,
                                                    CURLOPT_RETURNTRANSFER => true,
                                                    CURLOPT_POST => true,
                                                    CURLOPT_POSTFIELDS => $postData,
                                                    CURLOPT_HTTPHEADER => array(
                                                        "Content-Type: application/json",
                                                        "Tracking-Api-Key: $api_key"
                                                    ),
                                                ));

                                                $response = curl_exec($curl);
                                                $err = curl_error($curl);
                                                curl_close($curl);

                                                if ($err) {
                                                    $_SESSION['danger'] = "Errore API: $err";
                                                } else {
                                                    $delete_info = json_decode($response, true);
                                                    if (isset($delete_info['code']) && $delete_info['code'] == 200) {
                                                        // Elimina la spedizione dal database
                                                        $stmt = $pdo->prepare("DELETE FROM shipments WHERE id = :id");
                                                        $stmt->execute(['id' => $tracking_id]);

                                                        $_SESSION['success'] = "Spedizione eliminata con successo!";
                                                    } else {
                                                        $_SESSION['danger'] = "Errore nell'eliminazione della spedizione: " . $delete_info['message'];
                                                    }
                                                }
                                            } else {
                                                $_SESSION['danger'] = "Spedizione non trovata!";
                                            }
                                        }
                                        // Recupero e visualizzo le spedizioni memorizzate
                                        $stmt = $pdo->prepare("SELECT shipments.*, utenti.user_name AS creatore_user FROM shipments LEFT JOIN utenti ON shipments.createdby_userid = utenti.id ORDER BY shipments.updated_at DESC");
                                        $stmt->execute();
                                        $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if ($stmt->rowCount() > 0) {
                                            foreach ($shipments as $shipment) {
                                                // Recupero il logo del corriere
                                                $courier = array_filter($couriers['data'], function ($c) use ($shipment) {
                                                    return $c['courier_code'] === $shipment['carrier_code'];
                                                });
                                                $courier = array_shift($courier);
                                                $courierLogo = $courier['courier_logo'] ?? '';
                                            
                                                echo "<div class='shipment-box shadow-sm d-flex justify-content-between align-items-center'>";
                                                // Logo del corriere
                                                echo "<img class='shipment-logo' src='" . htmlspecialchars($courierLogo) . "' alt='" . htmlspecialchars($shipment['carrier_code']) . "'>";
                                            
                                                // Informazioni sulla spedizione
                                                echo "<div class='shipment-info'>";
                                                echo "<h5>" . htmlspecialchars($shipment['name']) . "</h5>";  // Titolo della spedizione
                                                echo "<p>" . htmlspecialchars($shipment['tracking_number']) . "</p>";  // Numero di tracking
                                                echo "</div>";
                                            
                                                // Nome dell'utente creatore, allineato a destra
                                                echo "<div class='ml-auto text-right'>";
                                                echo "<span class='mb-0 text-muted'><i>" . htmlspecialchars($shipment['creatore_user']) . "</i></span>";
                                            
                                                // Pulsante Dettagli
                                                echo "<button class='btn btn-light btn-circle btn-outline-info btn-sm btn-details ml-5' data-toggle='modal' data-target='#shipmentModal' data-id='" . $shipment['id'] . "'><i class='fa fa-list'></i></button>";
                                                // Pulsante Elimina
                                                echo "<button class='btn btn-light btn-circle btn-outline-danger ml-2 btn-sm btn-delete' data-id='" . $shipment['id'] . "'><i class='fa fa-trash'></i></button>";
                                                echo "</div>";  // Fine della div allineata a destra
                                            
                                                echo "</div>";  // Fine della shipment-box
                                            }
                                        } else {
                                            echo "<p>Nessuna spedizione trovata.</p>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "Errore nella connessione al database: " . $e->getMessage();
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <!-- Modale per visualizzare i dettagli della spedizione -->
    <div class="modal fade" id="shipmentModal" tabindex="-1" role="dialog" aria-labelledby="shipmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content bg-gradient-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="shipmentModalLabel">Dettagli Spedizione</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Caricamento dettagli...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">

    <script>
        const couriers = <?= json_encode($couriers['data']) ?>;

        function updateLogo() {
            const select = document.getElementById('carrier-select');
            const logoImg = document.getElementById('selected-carrier-logo');

            const selectedValue = select.value;
            const selectedCourier = couriers.find(c => c.courier_code === selectedValue);

            if (selectedCourier) {
                logoImg.src = selectedCourier.courier_logo;
                logoImg.alt = selectedCourier.courier_name;
                logoImg.style.display = 'block'; // Mostra l'immagine
            } else {
                logoImg.style.display = 'none'; // Nasconde l'immagine se non trovato
            }
        }
        // Caricamento dei dettagli nel modale
        $('#shipmentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Pulsante che ha attivato il modale
            var shipmentId = button.data('id'); // Estrae l'id della spedizione

            // Esegui una richiesta AJAX per recuperare i dettagli della spedizione
            $.ajax({
                url: 'get_shipment_details?id=' + shipmentId,
                method: 'GET',
                success: function (data) {
                    $('.modal-body').html(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('.modal-body').html("<p>Errore nel caricamento dei dettagli: " + textStatus + "</p>");
                }
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const trackingId = this.getAttribute('data-id');
                if (confirm("Sei sicuro di voler eliminare questa spedizione?")) {
                    const formData = new FormData();
                    formData.append('delete_tracking_id', trackingId);

                    fetch('', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.text())
                        .then(data => {
                            location.reload(); // Ricarica la pagina per aggiornare la lista delle spedizioni
                        }).catch(error => console.error('Errore:', error));
                }
            });
        });
    </script>