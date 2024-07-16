<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Utenti</h1>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Amministrazione Utenti</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Utenti</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Tabella utenti qui -->
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
                                                // Query per recuperare gli utenti dal database
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
                                                    echo "</td>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Strumenti</h6>
                                </div>
                                <div class="card-body">
                                    <button id="addUser" class="btn btn-success btn-block" data-toggle="modal"
                                        data-target="#addUserModal"><i class="far fa-user-plus"></i>
                                        AGGIUNGI</button>
                                    <button id="managePermissions" class="btn btn-pink btn-block"><i
                                            class="far fa-tasks"></i> GESTIONE PERMESSI</button>
                                    <button id="manageRoles" class="btn btn-warning btn-block"><i
                                            class="far fa-user-tag"></i> GESTIONE RUOLI</button>
                                    <button id="deleteUser" class="btn btn-danger btn-block" data-toggle="modal"
                                        data-target="#deleteUserModal"><i class="far fa-trash"></i> ELIMINA</button>
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

                    // Event handler per il pulsante di modifica
                    $('#dataTable').on('click', '.edit-btn', function () {
                        var $button = $(this);
                        var $row = $button.closest('tr');

                        if ($button.hasClass('editing')) {
                            // Salva le modifiche
                            var id = $row.find('td:eq(0)').text();
                            var username = $row.find('td:eq(1) input').val();
                            var nome = $row.find('td:eq(2) input').val();
                            var email = $row.find('td:eq(3) input').val();
                            var ruolo = $row.find('td:eq(4) input').val();

                            // Esegui una chiamata AJAX per aggiornare il database
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
                                    // Gestisci la risposta del server
                                    // Rendi di nuovo la riga non modificabile
                                    $row.find('td:eq(1)').html(username);
                                    $row.find('td:eq(2)').html(nome);
                                    $row.find('td:eq(3)').html(email);
                                    $row.find('td:eq(4)').html(ruolo);
                                    $button.removeClass('editing btn-success').html('<i class="far fa-pen"></i>');
                                }
                            });
                        } else {
                            // Rendi la riga modificabile
                            $row.find('td:eq(1)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(1)').text() + '">');
                            $row.find('td:eq(2)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(2)').text() + '">');
                            $row.find('td:eq(3)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(3)').text() + '">');
                            $row.find('td:eq(4)').html('<input type="text" class="form-control" value="' + $row.find('td:eq(4)').text() + '">');
                            $button.addClass('editing btn-success').html('<i class="far fa-save"></i>');
                        }
                    });
                });
                $('#dataTable').on('click', '.edit-password-btn', function () {
                    var $button = $(this);
                    var $row = $button.closest('tr');
                    var id = $row.find('td:eq(0)').text();

                    // Pulisci il campo della nuova password prima di aprire il modale
                    $('#newPassword').val('');

                    // Event handler per il salvataggio della nuova password
                    $('#editPasswordForm').submit(function (event) {
                        event.preventDefault();

                        var newPassword = $('#newPassword').val();

                        // Esegui una chiamata AJAX per aggiornare la password nel database
                        $.ajax({
                            url: 'update_password',
                            method: 'POST',
                            data: {
                                id: id,
                                newPassword: newPassword
                            },
                            success: function (response) {
                                // Gestisci la risposta del server
                                // Chiudi il modale e visualizza un messaggio di successo
                                $('#editPasswordModal').modal('hide');
                                location.reload();

                            }
                        });
                    });
                });
            </script>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Modale per aggiungere un nuovo utente -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Aggiungi Nuovo Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="add_user" method="POST">
                        <div class="form-group">
                            <label for="user_name">Nome Utente</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" required>
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_type">Classificatore Ruolo</label>
                            <select class="form-control" id="admin_type" name="admin_type" required>
                                <option value="Super">Super</option>
                                <option value="Utente">Utente</option>
                                <option value="Admin">Admin</option>
                                <option value="Operatore">Operatore</option>
                                <option value="Lavorante">Lavorante</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale per eliminare un utente -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger " id="deleteUserModalLabel">Elimina Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="delete_user" method="POST">
                        <div class="form-group">
                            <label for="user_id">Seleziona Utente</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <?php
                                // Query per recuperare gli utenti dal database
                                $stmt = $pdo->query("SELECT id, user_name FROM utenti");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['user_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-danger">Elimina</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<!-- Modale per cambiare password -->
<div class="modal fade" id="editPasswordModal" tabindex="-1" role="dialog" aria-labelledby="editPasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPasswordModalLabel">Modifica Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPasswordForm">
                    <div class="form-group">
                        <label for="newPassword">Nuova Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </form>
            </div>
        </div>
    </div>
</div>

</html>