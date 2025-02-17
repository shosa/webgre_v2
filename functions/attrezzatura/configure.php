<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
$pdo = getDbInstance();

// Recupero categorie
$stmt = $pdo->query("SELECT * FROM att_category ORDER BY descrizione ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupero attrezzature
$stmt = $pdo->query("SELECT a.*, c.descrizione AS categoria_descrizione, c.sigla FROM att_anag a 
                     LEFT JOIN att_category c ON a.category_id = c.ID ORDER BY a.descrizione ASC");
$equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Configuratore Attrezzature</h1>
                    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEquipmentModal">Aggiungi
                        Attrezzatura</button>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Codice</th>
                                        <th>Descrizione</th>
                                        <th>Categoria</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipments as $equipment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($equipment['ID']) ?></td>
                                            <td><?= htmlspecialchars($equipment['cod']) ?></td>
                                            <td><?= htmlspecialchars($equipment['descrizione']) ?></td>
                                            <td><?= htmlspecialchars($equipment['categoria_descrizione']) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                    data-target="#editEquipmentModal"
                                                    data-id="<?= $equipment['ID'] ?>">Modifica</button>
                                                <button class="btn btn-danger btn-sm delete-equipment"
                                                    data-id="<?= $equipment['ID'] ?>">Elimina</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Modale Aggiunta Attrezzatura -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi Attrezzatura</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addEquipmentForm">
                        <div class="form-group">
                            <label>Categoria</label>
                            <select class="form-control" name="category_id" id="categorySelect">
                                <option value="">Seleziona una categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['ID'] ?>" data-sigla="<?= $category['sigla'] ?>">
                                        <?= htmlspecialchars($category['descrizione']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Codice</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="categoryCodePrefix" readonly style="width: 60px;">
                                <input type="text" class="form-control" name="cod" id="generatedCode" required maxlength="4" pattern="\d{4}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Descrizione</label>
                            <input type="text" class="form-control" name="descrizione" required>
                        </div>
                        <button type="submit" class="btn btn-success">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#categorySelect').change(function () {
                let selectedOption = $(this).find(':selected');
                let categoryId = selectedOption.val();
                let sigla = selectedOption.data('sigla');
                $('#categoryCodePrefix').val(sigla);
                
                if (categoryId) {
                    $.post('processing', { action: 'getLastCode', category_id: categoryId }, function (response) {
                        let lastCode = response ? parseInt(response) + 1 : 1;
                        $('#generatedCode').val(('000' + lastCode).slice(-4));
                    });
                } else {
                    $('#generatedCode').val('');
                }
            });
        });
    </script>

    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
</body>