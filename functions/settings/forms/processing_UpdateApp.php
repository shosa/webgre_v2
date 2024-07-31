<?php
require_once '../../../config/config.php';
header('Content-Type: text/plain');  // Set header to plain text for better logging output

$pdo = getDbInstance();
$repoOwner = 'shosa';
$repoName = 'webgre_v2';
$branch = "main";
$tempDir = 'temp_update'; 
$baseDir = BASE_PATH;  // Usa BASE_PATH per puntare alla root della web app

// Recupera il token dal database
$stmt = $pdo->prepare("SELECT value FROM settings WHERE item = 'github_token'");
$stmt->execute();
$accessToken = $stmt->fetchColumn();
if (!$accessToken) {
    echo "Token non trovato nel database.\n";
    exit;
}

echo "Log aggiornamento:\n";

// URL per ottenere l'ultima release
$zipUrl = "https://api.github.com/repos/$repoOwner/$repoName/zipball/$branch";

// Crea un contesto di stream per includere l'header di autenticazione
$options = [
    'http' => [
        'header' => "User-Agent: PHP\r\n" .
                    "Authorization: token $accessToken\r\n"
    ]
];

$context = stream_context_create($options);
$zipFile = 'latest.zip';

// Scarica l'archivio zip del branch
file_put_contents($zipFile, fopen($zipUrl, 'r', false, $context));

// Estrai il contenuto dell'archivio zip nella directory temporanea
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    }
    $zip->extractTo($tempDir);
    $zip->close();
    echo "Estrazione completata con successo.\n";
    flush();
} else {
    echo "Errore durante l'apertura del file zip.\n";
    flush();
    exit;
}

// Funzione per confrontare e aggiornare i file
function updateFiles($source, $dest) {
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcPath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                if ($file != 'vendor' && $file != 'config') {
                    if (!is_dir($destPath)) {
                        mkdir($destPath);
                        echo "Creata directory: $destPath\n";
                        flush();
                    }
                    updateFiles($srcPath, $destPath);
                }
            } else {
                if (!file_exists($destPath)) {
                    copy($srcPath, $destPath);
                    echo "Aggiunto: $destPath\n";
                    flush();
                } else if (md5_file($srcPath) != md5_file($destPath)) {
                    copy($srcPath, $destPath);
                    echo "Sovrascritto: $destPath\n";
                    flush();
                } else {
                    echo "Non modificato: $destPath\n";
                    flush();
                }
            }
        }
    }
    closedir($dir);
}

// Inizia l'aggiornamento dei file
$extractedDir = glob($tempDir . '/*', GLOB_ONLYDIR)[0];  // Trova la directory estratta
updateFiles($extractedDir, $baseDir);

// Rimuovi la directory temporanea e il file zip scaricato
function rrmdir($dir) {
    foreach (glob($dir . '/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
            echo "Eliminato: $file\n";
            flush();
        }
    }
    rmdir($dir);
    echo "Eliminata directory: $dir\n";
    flush();
}

// Utilizza la funzione migliorata per rimuovere la directory temporanea
rrmdir($tempDir);
unlink($zipFile);
echo "Eliminato: $zipFile\n";
flush();

echo "Aggiornamento completato con successo.\n";
flush();
?>
