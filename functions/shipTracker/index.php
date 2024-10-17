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
                        <style>
                            .shipment-box {
                                border: 1px solid #ddd;
                                padding: 15px;
                                margin-bottom: 10px;
                                border-radius: 8px;
                                background-color: #f9f9f9;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            }

                            .shipment-logo {
                                width: 50px;
                                height: auto;
                                margin-right: 15px;
                            }

                            .shipment-info {
                                flex-grow: 1;
                                margin-right: 20px;
                            }

                            .shipment-info h5 {
                                margin: 0;
                                font-size: 1rem;
                                font-weight: bold;
                            }

                            .shipment-info p {
                                margin: 0;
                                font-size: 0.875rem;
                            }

                            .btn-details {
                                white-space: nowrap;
                            }
                        </style>
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

                                        // Recupero e visualizzo le spedizioni memorizzate
                                        $stmt = $pdo->prepare("SELECT * FROM shipments ORDER BY updated_at DESC");
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

                                                echo "<div class='shipment-box shadow-sm'>";
                                                // Logo del corriere
                                                echo "<img class='shipment-logo' src='" . htmlspecialchars($courierLogo) . "' alt='" . htmlspecialchars($shipment['carrier_code']) . "'>";

                                                // Informazioni sulla spedizione
                                                echo "<div class='shipment-info'>";
                                                echo "<h5>" . htmlspecialchars($shipment['name']) . "</h5>";  // Titolo della spedizione
                                                echo "<p>Tracking Number: " . htmlspecialchars($shipment['tracking_number']) . "</p>";  // Numero di tracking
                                                echo "<p>Stato: " . htmlspecialchars($shipment['status']) . "</p>";  // Stato della spedizione
                                                echo "</div>";

                                                // Pulsante Dettagli
                                                echo "<button class='btn btn-info btn-sm btn-details' data-toggle='modal' data-target='#shipmentModal' data-id='" . $shipment['id'] . "'>Dettagli</button>";
                                                echo "</div>";
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
            <?php include(BASE_PATH . "/components/scripts.php"); ?>
            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <style>
        .tracking-info,
        .origin-info {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
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
    </script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.colReorder.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/datatables.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <?php include(BASE_PATH . "/components/footer.php"); ?>
    <script>
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
    </script>

</body>

</html>