<?php
require_once '../../config/config.php';
$pdo = getDbInstance();
$id = $_POST['id'];
$permissions = $_POST['permissions'];
$setClause = [];
foreach ($permissions as $key => $value) {
    $setClause[] = "$key = :$key";
}
$setClause = implode(', ', $setClause);
$sql = "UPDATE permessi SET $setClause WHERE id_utente = :id";
$stmt = $pdo->prepare($sql);
$params = array_merge($permissions, ['id' => $id]);
$stmt->execute($params);
echo json_encode(['success' => true]);
?>