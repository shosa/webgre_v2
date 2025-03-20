<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Get database instance
$pdo = getDbInstance();

// Variables for messages
$successMessage = '';
$errorMessage = '';
$formData = []; // To keep form data in case of error

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save form data for restoring in case of error
    $formData = $_POST;

    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Update existing maintenance type
        try {
            // Check if name already exists (excluding the current ID)
            $checkStmt = $pdo->prepare("SELECT id FROM mac_manutenzioni_tipi WHERE nome = ? AND id != ?");
            $checkStmt->execute([$_POST['edit_nome'], $_POST['edit_id']]);
            if ($checkStmt->fetch()) {
                throw new PDOException("Nome duplicato", 1062);
            }

            $stmt = $pdo->prepare("UPDATE mac_manutenzioni_tipi SET 
                nome = ?,
                descrizione = ?,
                colore = ?,
                is_programmata = ?,
                intervallo_giorni = ?
                WHERE id = ?");

            $result = $stmt->execute([
                $_POST['edit_nome'],
                $_POST['edit_descrizione'] ?? null,
                $_POST['edit_colore'],
                isset($_POST['edit_is_programmata']) ? 1 : 0,
                $_POST['edit_intervallo_giorni'] ?? null,
                $_POST['edit_id']
            ]);

            if ($result) {
                $successMessage = "Tipo di manutenzione aggiornato con successo!";
            } else {
                $errorMessage = "Errore durante l'aggiornamento del tipo di manutenzione.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 1062 || $e->errorInfo[1] == 1062) {
                $errorMessage = "Errore: Il nome '<strong>" . htmlspecialchars($_POST['edit_nome']) . "</strong>' è già in uso. Ogni tipo di manutenzione deve avere un nome unico.";
            } else {
                $errorMessage = "Errore database: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Delete maintenance type
        try {
            $stmt = $pdo->prepare("DELETE FROM mac_manutenzioni_tipi WHERE id = ?");
            $result = $stmt->execute([$_POST['delete_id']]);

            if ($result) {
                $successMessage = "Tipo di manutenzione eliminato con successo!";
            } else {
                $errorMessage = "Errore durante l'eliminazione del tipo di manutenzione.";
            }
        } catch (PDOException $e) {
            // Check if foreign key constraint violation
            if ($e->getCode() == 23000) {
                $errorMessage = "Impossibile eliminare il tipo di manutenzione perché è utilizzato in una o più manutenzioni.";
            } else {
                $errorMessage = "Errore database: " . $e->getMessage();
            }
        }
    } else {
        // Add new maintenance type
        try {
            // Check if name already exists
            $checkStmt = $pdo->prepare("SELECT id FROM mac_manutenzioni_tipi WHERE nome = ?");
            $checkStmt->execute([$_POST['nome']]);
            if ($checkStmt->fetch()) {
                throw new PDOException("Nome duplicato", 1062);
            }

            $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni_tipi (nome, descrizione, colore, is_programmata, intervallo_giorni) 
                                VALUES (?, ?, ?, ?, ?)");

            $result = $stmt->execute([
                $_POST['nome'],
                $_POST['descrizione'] ?? null,
                $_POST['colore'],
                isset($_POST['is_programmata']) ? 1 : 0,
                $_POST['intervallo_giorni'] ?? null
            ]);

            if ($result) {
                $successMessage = "Tipo di manutenzione '<strong>" . htmlspecialchars($_POST['nome']) . "</strong>' inserito con successo!";
                $formData = []; // Clear form data after success
            } else {
                $errorMessage = "Errore durante l'inserimento del tipo di manutenzione.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 1062 || $e->errorInfo[1] == 1062) {
                $errorMessage = "Errore: Il nome '<strong>" . htmlspecialchars($_POST['nome']) . "</strong>' esiste già nel database. Ogni tipo di manutenzione deve avere un nome unico.";
            } else {
                $errorMessage = "Errore database: " . $e->getMessage();
            }
        }
    }
}

// Load all maintenance types
$stmt = $pdo->query("SELECT * FROM mac_manutenzioni_tipi ORDER BY nome");
$tipi_manutenzione = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once BASE_PATH . '/components/header.php';
?>

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
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> <?= $successMessage ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i> <?= $errorMessage ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Gestione Tipi di Manutenzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item active">Tipi di Manutenzione</li>
                    </ol>

                    <div class="row">
                        <!-- Form Card -->
                        <div class="col-xl-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aggiungi Nuovo Tipo</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" id="tipoForm">
                                        <div class="form-group">
                                            <label for="nome"><strong>Nome Tipo *</strong></label>
                                            <input type="text" name="nome" id="nome"
                                                class="form-control <?= !empty($errorMessage) && strpos($errorMessage, 'nome') !== false ? 'is-invalid' : '' ?>"
                                                required value="<?= htmlspecialchars($formData['nome'] ?? '') ?>"
                                                placeholder="Es. Manutenzione Ordinaria">
                                        </div>

                                        <div class="form-group">
                                            <label for="descrizione">Descrizione</label>
                                            <textarea name="descrizione" id="descrizione" class="form-control"
                                                rows="3"><?= htmlspecialchars($formData['descrizione'] ?? '') ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="colore"><strong>Colore *</strong></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text colorPreview" id="colorPreview">&nbsp;</span>
                                                </div>
                                                <select name="colore" id="colore" class="form-control" required>
                                                    <option value="#007bff" style="background-color: #007bff; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#007bff') ? 'selected' : '' ?>>Blu</option>
                                                    <option value="#28a745" style="background-color: #28a745; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#28a745') ? 'selected' : '' ?>>Verde</option>
                                                    <option value="#dc3545" style="background-color: #dc3545; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#dc3545') ? 'selected' : '' ?>>Rosso</option>
                                                    <option value="#ffc107" style="background-color: #ffc107;" <?= (isset($formData['colore']) && $formData['colore'] == '#ffc107') ? 'selected' : '' ?>>Giallo</option>
                                                    <option value="#6c757d" style="background-color: #6c757d; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#6c757d') ? 'selected' : '' ?>>Grigio</option>
                                                    <option value="#17a2b8" style="background-color: #17a2b8; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#17a2b8') ? 'selected' : '' ?>>Ciano</option>
                                                    <option value="#6f42c1" style="background-color: #6f42c1; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#6f42c1') ? 'selected' : '' ?>>Viola</option>
                                                    <option value="#fd7e14" style="background-color: #fd7e14; color: white;" <?= (isset($formData['colore']) && $formData['colore'] == '#fd7e14') ? 'selected' : '' ?>>Arancione</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_programmata" name="is_programmata" <?= isset($formData['is_programmata']) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="is_programmata">Manutenzione Programmata</label>
                                            </div>
                                            <small class="form-text text-muted">Abilita se questo tipo di manutenzione può essere programmata.</small>
                                        </div>

                                        <div class="form-group" id="intervalloGroup" style="display: <?= isset($formData['is_programmata']) ? 'block' : 'none' ?>">
                                            <label for="intervallo_giorni">Intervallo Predefinito (giorni)</label>
                                            <input type="number" name="intervallo_giorni" id="intervallo_giorni"
                                                class="form-control" min="1" max="1825"
                                                value="<?= htmlspecialchars($formData['intervallo_giorni'] ?? '90') ?>"
                                                placeholder="Es. 90">
                                            <small class="form-text text-muted">Intervallo predefinito tra le manutenzioni (in giorni).</small>
                                        </div>

                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-plus-circle mr-2"></i>Aggiungi Tipo
                                            </button>
                                            <button type="button" id="clearBtn" class="btn btn-warning btn-block mt-2">
                                                <i class="fas fa-eraser mr-2"></i>Pulisci Form
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- List Card -->
                        <div class="col-xl-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Tipi di Manutenzione Disponibili
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (count($tipi_manutenzione) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="tipiTable" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Nome</th>
                                                        <th>Descrizione</th>
                                                        <th>Colore</th>
                                                        <th>Programmata</th>
                                                        <th>Intervallo</th>
                                                        <th>Azioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tipi_manutenzione as $tipo): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($tipo['nome']) ?></td>
                                                            <td><?= htmlspecialchars($tipo['descrizione'] ?? '-') ?></td>
                                                            <td>
                                                                <span class="badge badge-pill text-white" style="background-color: <?= htmlspecialchars($tipo['colore']) ?>">
                                                                    <?= htmlspecialchars($tipo['colore']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($tipo['is_programmata']): ?>
                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                <?php else: ?>
                                                                    <i class="fas fa-times-circle text-danger"></i>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= $tipo['intervallo_giorni'] ? $tipo['intervallo_giorni'] . ' giorni' : '-' ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                                                    data-id="<?= $tipo['id'] ?>"
                                                                    data-nome="<?= htmlspecialchars($tipo['nome']) ?>"
                                                                    data-descrizione="<?= htmlspecialchars($tipo['descrizione'] ?? '') ?>"
                                                                    data-colore="<?= htmlspecialchars($tipo['colore']) ?>"
                                                                    data-is_programmata="<?= $tipo['is_programmata'] ?>"
                                                                    data-intervallo_giorni="<?= htmlspecialchars($tipo['intervallo_giorni'] ?? '') ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                                    data-id="<?= $tipo['id'] ?>"
                                                                    data-nome="<?= htmlspecialchars($tipo['nome']) ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i> Nessun tipo di manutenzione definito. Utilizza il form per aggiungere il primo tipo.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Modifica Tipo di Manutenzione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="edit_nome"><strong>Nome Tipo *</strong></label>
                                    <input type="text" name="edit_nome" id="edit_nome" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit_descrizione">Descrizione</label>
                                    <textarea name="edit_descrizione" id="edit_descrizione" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="edit_colore"><strong>Colore *</strong></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text editColorPreview" id="editColorPreview">&nbsp;</span>
                                        </div>
                                        <select name="edit_colore" id="edit_colore" class="form-control" required>
                                            <option value="#007bff" style="background-color: #007bff; color: white;">Blu</option>
                                            <option value="#28a745" style="background-color: #28a745; color: white;">Verde</option>
                                            <option value="#dc3545" style="background-color: #dc3545; color: white;">Rosso</option>
                                            <option value="#ffc107" style="background-color: #ffc107;">Giallo</option>
                                            <option value="#6c757d" style="background-color: #6c757d; color: white;">Grigio</option>
                                            <option value="#17a2b8" style="background-color: #17a2b8; color: white;">Ciano</option>
                                            <option value="#6f42c1" style="background-color: #6f42c1; color: white;">Viola</option>
                                            <option value="#fd7e14" style="background-color: #fd7e14; color: white;">Arancione</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="edit_is_programmata" name="edit_is_programmata">
                                        <label class="custom-control-label" for="edit_is_programmata">Manutenzione Programmata</label>
                                    </div>
                                    <small class="form-text text-muted">Abilita se questo tipo di manutenzione può essere programmata.</small>
                                </div>

                                <div class="form-group" id="edit_intervalloGroup">
                                    <label for="edit_intervallo_giorni">Intervallo Predefinito (giorni)</label>
                                    <input type="number" name="edit_intervallo_giorni" id="edit_intervallo_giorni" class="form-control" min="1" max="1825" placeholder="Es. 90">
                                    <small class="form-text text-muted">Intervallo predefinito tra le manutenzioni (in giorni).</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Conferma Eliminazione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="delete_id" id="delete_id">
                            <div class="modal-body">
                                <p>Sei sicuro di voler eliminare il tipo di manutenzione "<span id="delete_nome"></span>"?</p>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Attenzione:</strong> L'eliminazione non sarà possibile se il tipo è già utilizzato in una o più manutenzioni.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-danger">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <script>
                $(document).ready(function() {
                    // Update color preview on select change
                    $('#colore').change(function() {
                        $('#colorPreview').css('background-color', $(this).val());
                    });
                    
                    // Initialize color preview
                    $('#colorPreview').css('background-color', $('#colore').val());
                    
                    // Toggle interval field based on scheduled checkbox
                    $('#is_programmata').change(function() {
                        if ($(this).is(':checked')) {
                            $('#intervalloGroup').fadeIn();
                        } else {
                            $('#intervalloGroup').fadeOut();
                        }
                    });
                    
                    // Clear form button
                    $('#clearBtn').click(function() {
                        $('#tipoForm')[0].reset();
                        $('#colorPreview').css('background-color', $('#colore').val());
                        $('#intervalloGroup').hide();
                    });
                    
                    // Edit button click
                    $('.edit-btn').click(function() {
                        $('#edit_id').val($(this).data('id'));
                        $('#edit_nome').val($(this).data('nome'));
                        $('#edit_descrizione').val($(this).data('descrizione'));
                        $('#edit_colore').val($(this).data('colore'));
                        $('#editColorPreview').css('background-color', $(this).data('colore'));
                        
                        if ($(this).data('is_programmata') == 1) {
                            $('#edit_is_programmata').prop('checked', true);
                            $('#edit_intervalloGroup').show();
                        } else {
                            $('#edit_is_programmata').prop('checked', false);
                            $('#edit_intervalloGroup').hide();
                        }
                        
                        $('#edit_intervallo_giorni').val($(this).data('intervallo_giorni'));
                        
                        $('#editModal').modal('show');
                    });
                    
                    // Edit modal color preview
                    $('#edit_colore').change(function() {
                        $('#editColorPreview').css('background-color', $(this).val());
                    });
                    
                    // Toggle edit interval field based on scheduled checkbox
                    $('#edit_is_programmata').change(function() {
                        if ($(this).is(':checked')) {
                            $('#edit_intervalloGroup').fadeIn();
                        } else {
                            $('#edit_intervalloGroup').fadeOut();
                        }
                    });
                    
                    // Delete button click
                    $('.delete-btn').click(function() {
                        $('#delete_id').val($(this).data('id'));
                        $('#delete_nome').text($(this).data('nome'));
                        $('#deleteModal').modal('show');
                    });
                    
                    // Initialize DataTable if available
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#tipiTable').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json'
                            },
                            "ordering": true,
                            "paging": true,
                            "info": true,
                            "searching": true,
                            "columnDefs": [
                                { "orderable": false, "targets": [5] }
                            ]
                        });
                    }
                });
            </script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>