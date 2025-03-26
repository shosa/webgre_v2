<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$uploadDir = BASE_PATH . '/uploads/';

// Allowed file types (you can customize this list)
$allowedTypes = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', 
    'pdf', 'doc', 'docx', 'txt', 'rtf',
    'mp4', 'avi', 'mov', 'mkv', 
    'mp3', 'wav', 'ogg'
];

// Max file size (10MB)
$maxFileSize = 10 * 1024 * 1024; 

try {
    if (!isset($_FILES['files'])) {
        throw new Exception('Nessun file caricato');
    }

    $uploadedFiles = [];
    $errorMessages = [];

    // Process each uploaded file
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
        $fileName = basename($_FILES['files']['name'][$i]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileTmpName = $_FILES['files']['tmp_name'][$i];
        $fileSize = $_FILES['files']['size'][$i];

        // Validate file type
        if (!in_array($fileExt, $allowedTypes)) {
            $errorMessages[] = "Tipo di file non consentito: $fileName";
            continue;
        }

        // Validate file size
        if ($fileSize > $maxFileSize) {
            $errorMessages[] = "File troppo grande: $fileName";
            continue;
        }

        // Generate a unique filename to prevent overwriting
        $uniqueFileName = uniqid() . '_' . $fileName;
        $destination = $uploadDir . $uniqueFileName;

        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $destination)) {
            $uploadedFiles[] = $uniqueFileName;
        } else {
            $errorMessages[] = "Impossibile caricare il file: $fileName";
        }
    }

    // Prepare response
    $response = [
        'uploaded' => $uploadedFiles
    ];

    if (!empty($errorMessages)) {
        $response['errors'] = $errorMessages;
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;