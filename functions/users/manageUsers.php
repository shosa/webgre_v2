<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Utenti</h1>
                    </div>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Amministrazione Utenti</li>
                    </ol>
                    <div class="row">
                        <div class="col-lg-9 col-xl-9">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Utenti</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Username</th>
                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Ruolo</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $pdo = getDbInstance();
                                                $stmt = $pdo->query("SELECT * FROM utenti");
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['user_name'] ?? '') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['nome'] ?? '') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['admin_type'] ?? '') . "</td>";
                                                    echo "<td class='text-center'>";
                                                    echo "<button class='btn btn-primary btn-sm btn-circle edit-btn shadow'><i class='far fa-pen'></i></button>";
                                                    echo "<button class='btn btn-warning btn-sm btn-circle edit-password-btn shadow' style='margin-left:4px;' data-toggle='modal' data-target='#editPasswordModal'><i class='far fa-key'></i></button>";
                                                    echo "<button class='btn btn-info btn-sm btn-circle edit-permissions-btn shadow' style='margin-left:4px;' data-toggle='modal' data-target='#managePermissionsModal' data-user-id='" . htmlspecialchars($row['id'] ?? '') . "'><i class='far fa-tasks'></i></button>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-xl-3">
                            <div class="mb-4 card shadow">
                                <div class="align-items-center card-header d-flex py-3">
                                    <h6 class="font-weight-bold m-0 text-primary">Strumenti</h6>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-block btn-success" id="addUser" data-target="#addUserModal"
                                        data-toggle="modal">
                                        <i class="far fa-user-plus"></i> AGGIUNGI
                                    </button>
                                    <button class="btn btn-block btn-warning" id="manageRoles">
                                        <i class="far fa-user-tag"></i> GESTIONE RUOLI
                                    </button>
                                    <button class="btn btn-block btn-danger" id="deleteUser"
                                        data-target="#deleteUserModal" data-toggle="modal">
                                        <i class="far fa-trash"></i> ELIMINA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <script>
                $(document).ready(function () {
                    $.fn.dataTable.ext.type.order['num-html-pre'] = function (data) {
                        var num = data.replace(/<.*?>/g, '');
                        return parseFloat(num);
                    };
                    $('#dataTable').DataTable({
                        "columnDefs": [
                            { "type": "num-html", "targets": 0 }
                        ],
                        "info": true,
                        "colReorder": true,
                        "order": [[0, "desc"]],
                        dom: '<"top"Bf>rt<"bottom"lip><"clear">',
                        buttons: [
                            { extend: 'copy', text: '<i class="fas fa-copy"></i> COPIA', className: 'btn-primary' },
                            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> EXCEL', className: 'btn-primary' },
                            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-primary' },
                            { extend: 'print', text: '<i class="fas fa-print"></i> STAMPA', className: 'btn-primary' },
                        ],
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json"
                        },
                    });

                    $('#dataTable').on('click', '.edit-btn', function () {
                        var $button = $(this);
                        var $row = $button.closest('tr');
                        if ($button.hasClass('editing')) {
                            var id = $row.find('td:eq(0)').text();
                            var username = $row.find('td:eq(1) input').val();
                            var nome = $row.find('td:eq(2) input').val();
                            var email = $row.find('td:eq(3) input').val();
                            var ruolo = $row.find('td:eq(4) input').val();
                            $.ajax({
                                url: 'update_user',
                                method: 'POST',
                                data: {
                                    id: id,
                                    user_name: username,
                                    nome: nome,
                                    email: email,
                                    admin_type: ruolo
                                },
                                success: function (response) {
                                    $row.find('td:eq(1)').html(username);
                                    $row.find('td:eq(2)').html(nome);
                                    $row.find('td:eq(3)').html(email);
                                    $row.find('td:eq(4)').html(ruolo);
                                    $button.removeClass('editing btn-success').html('<i class="far fa-pen"></i>');
                                }
                            });
                        } else {
                            $row.find('td:eq(1)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(1)').text() + '">');
                            $row.find('td:eq(2)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(2)').text() + '">');
                            $row.find('td:eq(3)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(3)').text() + '">');
                            $row.find('td:eq(4)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(4)').text() + '">');
                            $button.addClass('editing btn-success').html('<i class="far fa-save"></i>');
                        }
                    });

                    $('#dataTable').on('click', '.edit-password-btn', function () {
                        var $button = $(this);
                        var $row = $button.closest('tr');
                        var id = $row.find('td:eq(0)').text();
                        $('#editPasswordModal').data('user-id', id); // Salva l'ID utente nel modale

                        // Mostra il modale
                        $('#editPasswordModal').modal('show');
                    });

                    $('#editPasswordForm').submit(function (event) {
                        event.preventDefault();
                        var userId = $('#editPasswordModal').data('user-id');
                        var changePassword = $('#changePassword').val();

                        $.ajax({
                            url: 'update_password.php',
                            method: 'POST',
                            data: {
                                id: userId,
                                changeassword: changePassword
                            },
                            success: function (response) {
                                $('#editPasswordModal').modal('hide');
                                location.reload();
                            },
                            error: function (xhr, status, error) {
                                alert('Errore durante il cambiamento della password: ' + error);
                            }
                        });
                    });
                    $('#dataTable').on('click', '.edit-permissions-btn', function () {
                        var userId = $(this).data('user-id');
                        $('#selectUserPermissions').val(userId).trigger('change');
                        $('#managePermissionsModal').modal('show');
                    });

                    $('#selectUserPermissions').on('change', function () {
                        var userId = $(this).val();
                        $.ajax({
                            url: 'getUserPermissions.php',
                            method: 'GET',
                            data: { id: userId },
                            success: function (response) {
                                var permissions = JSON.parse(response);
                                $('input[name="permission"]').each(function () {
                                    var permName = $(this).attr('data-permission');
                                    $(this).prop('checked', permissions[permName] == 1);
                                });
                            }
                        });
                    });

                    $('#savePermissionsBtn').on('click', function () {
                        var userId = $('#selectUserPermissions').val();
                        var permissions = {};
                        $('input[name="permission"]').each(function () {
                            var permName = $(this).attr('data-permission');
                            permissions[permName] = $(this).is(':checked') ? 1 : 0;
                        });
                        $.ajax({
                            url: 'updateUserPermissions.php',
                            method: 'POST',
                            data: {
                                id: userId,
                                permissions: permissions
                            },
                            success: function (response) {
                                $('#managePermissionsModal').modal('hide');
                                location.reload();
                            }
                        });
                    });

                    $('#saveNewUserBtn').on('click', function () {
                        var newUserName = $('#newUserName').val();
                        var newNome = $('#newNome').val();
                        var newPassword = $('#newPassword').val();
                        var newAdminType = $('#newAdminType').val();

                        $.ajax({
                            url: 'add_user.php',
                            method: 'POST',
                            data: {
                                user_name: newUserName,
                                nome: newNome,
                                password: newPassword,
                                admin_type: newAdminType
                            },
                            success: function (response) {
                                $('#addUserModal').modal('hide');
                                location.reload();
                            },
                            error: function (xhr, status, error) {
                                alert('Errore durante la creazione dell\'utente: ' + error);
                            }
                        });
                    });
                    $('#deleteUserBtn').on('click', function () {
                        var userId = $('#selectUserDelete').val();
                        if (confirm('Sei sicuro di voler eliminare questo utente?')) {
                            $.ajax({
                                url: 'delete_user.php',
                                method: 'POST',
                                data: {
                                    id: userId
                                },
                                success: function (response) {
                                    $('#deleteUserModal').modal('hide');
                                    location.reload();
                                },
                                error: function (xhr, status, error) {
                                    alert('Errore durante l\'eliminazione dell\'utente: ' + error);
                                }
                            });
                        }
                    });
                });
            </script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Modale per gestione permessi -->
    <div class="modal fade" id="managePermissionsModal" tabindex="-1" role="dialog"
        aria-labelledby="managePermissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="managePermissionsModalLabel">Gestione Permessi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="permissionsForm">
                        <div class="form-group">
                            <label for="selectUserPermissions">Seleziona Utente</label>
                            <select class="form-control" id="selectUserPermissions" name="user_id" required>
                                <?php
                                $stmt = $pdo->query("SELECT id, user_name FROM utenti");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['user_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Permessi</label>
                            <?php
                            $permStmt = $pdo->query("SHOW COLUMNS FROM permessi WHERE Field NOT IN ('id', 'id_utente')");
                            while ($perm = $permStmt->fetch(PDO::FETCH_ASSOC)) {
                                $permName = htmlspecialchars($perm['Field']);
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="permission" data-permission="' . $permName . '">';
                                echo '<label class="form-check-label">' . strtoupper($permName) . '</label>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <button type="button" class="btn btn-success btn-block" id="savePermissionsBtn">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale per Aggiungere Utente -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Aggiungi Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="form-group">
                            <label for="newUserName">Username</label>
                            <input type="text" class="form-control" id="newUserName" name="user_name" required>
                        </div>
                        <div class="form-group">
                            <label for="newNome">Nome</label>
                            <input type="text" class="form-control" id="newNome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">Password</label>
                            <input type="password" class="form-control" id="newPassword" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="newAdminType">Tipo di Amministratore</label>
                            <input type="text" class="form-control" id="newAdminType" name="admin_type" required>
                        </div>
                        <button type="button" class="btn btn-primary btn-block" id="saveNewUserBtn">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale per cambio password -->
    <div class="modal fade" id="editPasswordModal" tabindex="-1" role="dialog" aria-labelledby="editPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPasswordModalLabel">Cambio Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editPasswordForm">
                        <div class="form-group">
                            <label for="changePassword">Nuova Password</label>
                            <input type="password" class="form-control" id="changePassword" name="changePassword" required>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modale per eliminare utente -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Elimina Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="deleteUserForm">
                        <div class="form-group">
                            <label for="selectUserDelete">Seleziona Utente</label>
                            <select class="form-control" id="selectUserDelete" name="user_id" required>
                                <?php
                                $stmt = $pdo->query("SELECT id, user_name FROM utenti");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['user_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-danger btn-block" id="deleteUserBtn">Elimina</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


</body>