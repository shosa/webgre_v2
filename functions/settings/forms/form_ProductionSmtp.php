<?php
require_once '../../../config/config.php';

$conn = getDbInstance();

function getSetting($conn, $item) {
    $sql = "SELECT value FROM settings WHERE item = :item";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':item', $item);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$production_senderEmail = getSetting($conn, 'production_senderEmail');
$production_senderPassword = getSetting($conn, 'production_senderPassword');
$production_senderSMTP = getSetting($conn, 'production_senderSMTP');
$production_senderPORT = getSetting($conn, 'production_senderPORT');
$production_recipients = getSetting($conn, 'production_recipients');
?>

<form method="post" id="settingsEmailForm" class="p-4 border rounded shadow-sm bg-light">
    <div class="form-group">
        <label for="production_senderEmail">Indirizzo E-Mail del Mittente</label>
        <input type="email" class="form-control" id="production_senderEmail" name="production_senderEmail"
            value="<?= htmlspecialchars($production_senderEmail) ?>">
    </div>
    <div class="form-group">
        <label for="production_senderPassword">Password</label>
        <input type="password" class="form-control" id="production_senderPassword" name="production_senderPassword"
            value="<?= htmlspecialchars($production_senderPassword) ?>">
    </div>
    <div class="form-group">
        <label for="production_senderSMTP">Server SMTP</label>
        <input type="text" class="form-control" id="production_senderSMTP" name="production_senderSMTP"
            value="<?= htmlspecialchars($production_senderSMTP) ?>">
    </div>
    <div class="form-group">
        <label for="production_senderPORT">Server PORT</label>
        <input type="text" class="form-control" id="production_senderPORT" name="production_senderPORT"
            value="<?= htmlspecialchars($production_senderPORT) ?>">
    </div>
    <div class="form-group">
        <label for="production_recipients">Destinatari (Separare indirizzi E-Mail da ";")</label>
        <textarea class="form-control" id="production_recipients" name="production_recipients"
            rows="3"><?= htmlspecialchars($production_recipients) ?></textarea>
    </div>
    <button type="submit" class="btn btn-warning btn-block">SALVA</button>
</form>
