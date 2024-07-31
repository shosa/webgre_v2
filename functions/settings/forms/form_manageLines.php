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

<div class="p-4 border rounded shadow-sm bg-light" id="formManageLines">

    <table class="table table-bordered bg-white">
        <p class="text-info font-weight-bold">* Invio per salvare le modifiche</p>
        <thead>
            <tr>
            <th width="3%">ID</th>
                <th width="10%">Sigla</th>
                <th>Descrizione</th>
                <th width="5%"></th>
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