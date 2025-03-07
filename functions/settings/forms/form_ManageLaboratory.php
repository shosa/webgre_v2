<?php
require_once '../../../config/config.php';
$conn = getDbInstance();
function getLines($conn)
{
    $sql = "SELECT * FROM laboratori";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function isLabInRiparazioni($conn, $nome)
{
    $sql = "SELECT COUNT(*) FROM riparazioni WHERE LABORATORIO = :nome";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}
$labs = getLines($conn);
?>
<div class="p-4 border rounded shadow-sm bg-light" id="formManageLaboratory">
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
            <?php foreach ($labs as $lab): ?>
                <tr>
                    <td><?= htmlspecialchars($lab['ID']) ?></td>
                    <td><input type="text" class="form-control" value="<?= htmlspecialchars($lab['Nome']) ?>"
                            data-id="<?= $lab['ID'] ?>" data-field="Nome"></td>
                    <td>
                        <?php if (!isLabInRiparazioni($conn, $lab['Nome'])): ?>
                            <button class="btn btn-light btn-circle text-danger btn btn-delete-lab"
                                data-id="<?= $lab['ID'] ?>"><i class="fal fa-trash"></i></button>

                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-primary btn-block" id="addLabBtn">Aggiungi Laboratorio</button>
</div>