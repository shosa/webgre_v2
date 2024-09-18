<?php
require_once '../../../config/config.php';
$conn = getDbInstance();
function getLines($conn)
{
    $sql = "SELECT * FROM reparti";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function isDepartmentInRiparazioni($conn, $nome)
{
    $sql = "SELECT COUNT(*) FROM riparazioni WHERE REPARTO = :nome";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}
$departments = getLines($conn);
?>
<div class="p-4 border rounded shadow-sm bg-light" id="formManageDepartment">
    <table class="table table-bordered bg-white">
        <p class="text-info font-weight-bold">* Invio per salvare le modifiche</p>
        <thead>
            <tr>
                <th width="3%">ID</th>
                <th>Nome</th>
                <th width="5%"></th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php foreach ($departments as $department): ?>
                <tr>
                    <td><?= htmlspecialchars($department['ID']) ?></td>
                    <td><input type="text" class="form-control" value="<?= htmlspecialchars($department['Nome']) ?>"
                            data-id="<?= $department['ID'] ?>" data-field="Nome"></td>
                    <td>
                        <?php if (!isDepartmentInRiparazioni($conn, $department['Nome'])): ?>
                            <button class="btn btn-danger btn-delete-department" data-id="<?= $department['ID'] ?>"><i
                                    class="fal fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-primary btn-block" id="addDepartmentBtn">Aggiungi Reparto</button>
</div>
