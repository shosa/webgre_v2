<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
include(BASE_PATH . "/components/header.php");

// Funzione per verificare se esiste un'immagine del profilo
function getProfileImage($userId)
{
    $imageExtensions = ['png', 'jpeg', 'jpg'];
    $imagePath = BASE_PATH . '/img/users/';

    foreach ($imageExtensions as $ext) {
        $filePath = $imagePath . $userId . '.' . $ext;
        if (file_exists($filePath)) {
            return BASE_URL . '/img/users/' . $userId . '.' . $ext; // Restituisci il percorso dell'immagine
        }
    }

    return false; // Nessuna immagine trovata
}
$limit = 5; // Numero di notifiche per pagina
$page = isset($_GET['notification']) ? (int) $_GET['notification'] : 1; // Ottieni la pagina corrente
$offset = ($page - 1) * $limit; // Calcola l'offset

try {
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ottieni il totale delle notifiche per la paginazione
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id");
    $stmtTotal->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtTotal->execute();
    $total = $stmtTotal->fetchColumn();

} catch (PDOException $e) {
    $_SESSION['danger'] = "Errore durante il recupero delle notifiche.";
}


$userImage = getProfileImage($_SESSION['user_id']);

?>
<style>
    .change-photo-btn {
        position: absolute;
        top: 0;
        right: 0;
        background-color: #ffc107;
        border-radius: 50%;
        padding: 5px;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .change-photo-btn i {
        color: white;
    }

    .change-photo-btn:hover {
        background-color: #e0a800;
        cursor: pointer;
    }
</style>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Log Attività</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Log Attività</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow mb-4">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Profilo personale</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div style="position: relative; display: inline-block;">
                                        <!-- Immagine del profilo o icona utente -->
                                        <?php if ($userImage): ?>
                                            <img src="<?php echo $userImage; ?>" alt="Immagine profilo"
                                                class="rounded-circle mb-3 border-<?php echo $colore ?> border "
                                                style="width: 150px; height: 150px; object-fit: cover; border-width: 2pt!important;">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle fa-8x mb-3" style="color: #74C0FC;"></i>
                                        <?php endif; ?>

                                        <!-- Bottone per cambiare immagine (con icona penna) -->
                                        <button class="btn btn-sm btn-warning btn-circle change-photo-btn "
                                            data-toggle="modal" data-target="#profileImageModal">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-dark text-white border-dark"
                                                id="basic-addon1"><i class="fal fa-user"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-white" placeholder=""
                                            aria-label="Username" aria-describedby="basic-addon1" readonly
                                            value="<?php echo $_SESSION['nome']; ?> ">
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-dark text-white border-dark"
                                                id="basic-addon1"><i class="fal fa-envelope"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-white" placeholder=""
                                            aria-label="Username" aria-describedby="basic-addon1" readonly
                                            value="<?php echo (isset($_SESSION["mail"]) && !empty($_SESSION["mail"])) ? $_SESSION["mail"] : ""; ?>">
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend w-20">
                                            <span class="input-group-text bg-dark text-white border-dark"
                                                id="basic-addon1"><i class="fal fa-fingerprint"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-lightgrey" placeholder=""
                                            aria-label="Username" aria-describedby="basic-addon1" readonly
                                            value="<?php echo $_SESSION['username']; ?>">
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-dark text-white border-dark"
                                                id="basic-addon1"><i class="fal fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-lightgrey" placeholder=""
                                            aria-label="Username" aria-describedby="basic-addon1" readonly
                                            value="<?php echo $_SESSION['user_id']; ?>">
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-dark text-white border-dark"
                                                id="basic-addon1"><i class="fal fa-layer-group"></i></span>
                                        </div>
                                        <input type="text" class="form-control bg-lightgrey" placeholder=""
                                            aria-label="Username" aria-describedby="basic-addon1" readonly
                                            value="<?php echo $_SESSION['tipo']; ?>">
                                    </div>

                                </div>
                            </div>
                            <div class="card shadow mb-4" id="notifiche">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Notifiche</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <?php foreach ($notifiche as $notifica): ?>
                                            <span
                                                class="list-group-item list-group-item-action border-left-<?php echo $notifica['type']; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?php echo $notifica['message']; ?></h5>
                                                    <small><?php echo $notifica['timestamp']; ?></small>
                                                </div>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?notification=<?php echo $page - 1; ?>"
                                                        aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php
                                            $totalPages = ceil($total / $limit);
                                            for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link"
                                                        href="?notification=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?notification=<?php echo $page + 1; ?>"
                                                        aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-9 col-lg-8">
                            <div class="card shadow mb-4" style="font-size:10pt;">
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Registro Attività </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="dataTable" width="100%"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Categoria</th>
                                                    <th>Tipo</th>
                                                    <th>Descrizione</th>
                                                    <th>Note</th>
                                                    <?php if ($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') {
                                                        echo " <th>Query</th>";
                                                    } ?>
                                                    <th>Timestamp</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Connessione al database utilizzando PDO
                                                $conn = getDbInstance(); // Suppongo che questa funzione restituisca un'istanza di PDO già configurata
                                                
                                                if ($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') {
                                                    $sql = "SELECT * FROM activity_log WHERE user_id = :user_id ORDER BY id DESC";
                                                } else {
                                                    $sql = "SELECT id, category, activity_type, description, note, created_at FROM activity_log WHERE user_id = :user_id ORDER BY id DESC";
                                                }
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                                                $stmt->execute();
                                                $activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                // Iterazione attraverso le righe del risultato della query
                                                foreach ($activity_logs as $log) {
                                                    echo "<tr>";
                                                    echo "<td>{$log['id']}</td>";
                                                    echo "<td>{$log['category']}</td>";
                                                    echo "<td>{$log['activity_type']}</td>";
                                                    echo "<td>{$log['description']}</td>";
                                                    echo "<td>{$log['note']}</td>";
                                                    // Visualizza la colonna "Query" solo per Admin e Super se il campo "text_query" non è vuoto
                                                    if (($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') && !empty($log['text_query'])) {
                                                        echo '<td class="text-center">';
                                                        echo "<i class='fal fa-search view-query' style='cursor: pointer; color: #007bff;' data-query-id='{$log['id']}' data-toggle='modal' data-target='#queryModal'></i>";
                                                        echo '</td>';
                                                    }
                                                    if (($_SESSION['tipo'] == 'Admin' || $_SESSION['tipo'] == 'Super') && empty($log['text_query'])) {
                                                        echo '<td>';
                                                        echo "</td>";
                                                    }
                                                    echo "<td>{$log['created_at']}</td>";
                                                    echo "</tr>";
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
                <!-- MODALE QUERY -->
                <div class="modal fade" id="queryModal" tabindex="-1" role="dialog" aria-labelledby="queryModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="queryModalLabel">Query Dettagliata</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <textarea id="queryText" class="form-control" rows="10" readonly></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
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
                <!-- End of Footer -->
            </div>
            <!-- End of Content Wrapper -->
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>
<!-- Modale per aggiornare l'immagine del profilo -->
<!-- Modal per cambiare l'immagine del profilo -->
<div class="modal fade" id="profileImageModal" tabindex="-1" role="dialog" aria-labelledby="profileImageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileImageModalLabel">Cambia Immagine del Profilo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Sezione di upload dell'immagine -->
                <div class="form-group">
                    <label for="profileImageInput" class="btn btn-light text-info border border-info btn-block">
                        <i class="fad fa-image"></i> Scegli un'immagine
                        <input type="file" id="profileImageInput" accept="image/*" class="d-none">
                    </label>
                    <div class="mt-3 text-center">
                        <img id="profileImagePreview"
                            style="max-width: 100%; border-radius: 8px; border: 1px solid #ddd; display: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="button" id="saveProfileImage" class="btn btn-success">Salva</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const viewButtons = document.querySelectorAll('.view-query');
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const queryId = this.dataset.queryId;
                // Effettua una richiesta AJAX per ottenere il testo completo della query
                $.ajax({
                    url: 'get_query.php',
                    method: 'POST',
                    data: { query_id: queryId },
                    success: function (response) {
                        document.getElementById('queryText').textContent = response;
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
        let cropper;
        const profileImageInput = document.getElementById('profileImageInput');
        const profileImagePreview = document.getElementById('profileImagePreview');
        const saveButton = document.getElementById('saveProfileImage');

        // Al caricamento dell'immagine
        profileImageInput.addEventListener('change', function (event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();

                reader.onload = function (e) {
                    profileImagePreview.src = e.target.result;
                    profileImagePreview.style.display = 'block';

                    // Inizializza Cropper.js
                    if (cropper) {
                        cropper.destroy(); // Rimuovi eventuale cropper esistente
                    }
                    cropper = new Cropper(profileImagePreview, {
                        aspectRatio: 1, // Mantenere proporzioni 1:1
                        viewMode: 3,
                        preview: '.preview',
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        // Salva immagine ritagliata
        saveButton.addEventListener('click', function () {
            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
            });

            canvas.toBlob(function (blob) {
                const formData = new FormData();
                formData.append('profile_image', blob, 'profile.png');

                // Esegui una richiesta AJAX per caricare l'immagine sul server
                fetch('updateImage.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Aggiorna l'immagine del profilo nella pagina
                            location.reload();
                        } else {
                            alert('Errore durante il caricamento dell\'immagine');
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                    });
            });
        });
    });
</script>