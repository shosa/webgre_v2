<?php
require_once '../../../config/config.php';

$conn = getDbInstance();

function getLines($conn)
{
    $sql = "SELECT * FROM linee";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isSiglaInDati($conn, $sigla)
{
    $sql = "SELECT COUNT(*) FROM dati WHERE Ln = :sigla";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sigla', $sigla);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

$lines = getLines($conn);
?>

<div class="container p-4 border rounded shadow-sm bg-light" id="formManageLines">

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sigla</th>
                <th>Descrizione</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td><?= htmlspecialchars($line['ID']) ?></td>
                    <td><?= htmlspecialchars($line['sigla']) ?></td>
                    <td><input type="text" class="form-control" value="<?= htmlspecialchars($line['descrizione']) ?>"
                            data-id="<?= $line['ID'] ?>" data-field="descrizione"></td>
                    <td>
                        <?php if (!isSiglaInDati($conn, $line['sigla'])): ?>
                            <button class="btn btn-danger btn-delete-line" data-id="<?= $line['ID'] ?>"><i
                                    class="fal fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-primary btn-block" id="addLineBtn">Aggiungi Linea</button>
</div>