<?php
session_start();
require_once '../../config/config.php';
$pdo = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'getNextCode':
            if (isset($_POST['category_id'])) {
                $categoryId = $_POST['category_id'];
                
                // Trova la sigla della categoria
                $stmt = $pdo->prepare("SELECT sigla FROM att_category WHERE ID = ?");
                $stmt->execute([$categoryId]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$category) {
                    echo '0001';
                    exit;
                }
                
                $sigla = $category['sigla'];
                
                // Trova l'ultimo codice della categoria
                $stmt = $pdo->prepare("SELECT cod FROM att_anag WHERE category_id = ? ORDER BY cod DESC LIMIT 1");
                $stmt->execute([$categoryId]);
                $lastEquipment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($lastEquipment) {
                    $lastNumber = (int) substr($lastEquipment['cod'], -4);
                    $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $nextNumber = '0001';
                }
                
                echo $nextNumber;
                exit;
            }
            break;

        case 'addEquipment':
            if (!empty($_POST['category_id']) && !empty($_POST['cod']) && !empty($_POST['descrizione'])) {
                $stmt = $pdo->prepare("INSERT INTO att_anag (category_id, cod, descrizione) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['category_id'], $_POST['cod'], $_POST['descrizione']]);
                echo json_encode(['status' => 'success']);
                exit;
            }
            echo json_encode(['status' => 'error', 'message' => 'Dati mancanti']);
            exit;

        case 'editEquipment':
            if (!empty($_POST['id']) && !empty($_POST['category_id']) && !empty($_POST['cod']) && !empty($_POST['descrizione'])) {
                $stmt = $pdo->prepare("UPDATE att_anag SET category_id = ?, cod = ?, descrizione = ? WHERE ID = ?");
                $stmt->execute([$_POST['category_id'], $_POST['cod'], $_POST['descrizione'], $_POST['id']]);
                echo json_encode(['status' => 'success']);
                exit;
            }
            echo json_encode(['status' => 'error', 'message' => 'Dati mancanti']);
            exit;

        case 'deleteEquipment':
            if (!empty($_POST['id'])) {
                $stmt = $pdo->prepare("DELETE FROM att_anag WHERE ID = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['status' => 'success']);
                exit;
            }
            echo json_encode(['status' => 'error', 'message' => 'ID mancante']);
            exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Richiesta non valida']);
exit;
?>
