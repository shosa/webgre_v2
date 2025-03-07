<?php
require_once '../../../config/config.php';
$conn = getDbInstance();
function getTabId($conn)
{
    $sql = "SELECT ID FROM tabid LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
$tabId = getTabId($conn);
?>
<div class="p-4 border rounded shadow-sm bg-light" id="formManageTabId">
    <table class="table table-bordered bg-white">
        <p class="text-info font-weight-bold">* Invio per salvare le modifiche</p>
        <thead>
            <tr>
                <th>Ultima Cedola</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <tr>
                <td><input type="text" class="form-control" value="<?= htmlspecialchars($tabId['ID']) ?>"
                        data-field="ID"></td>
            </tr>
        </tbody>
    </table>
</div>
