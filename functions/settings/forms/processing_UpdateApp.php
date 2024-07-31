<?php
require_once '../../../config/config.php';
header('Content-Type: text/plain');  // Set header to plain text for better logging output

$pdo = getDbInstance();
$repoOwner = 'shosa';
$repoName = 'webgre_v2';
$branch = "main";
$tempDir = 'temp_update'; 
$baseDir = BASE_PATH;  // Usa BASE_PATH per puntare alla root della web app

echo "Log aggiornamento:\n";

// URL per ottenere l'ultima release
$zipUrl = "https://api.github.com/repos/$repoOwner/$repoName/zipball/$branch";

// Crea un contesto di stream per includere l'header di autenticazione
$accessToken = $pdo->query("SELECT value FROM settings WHERE item = 'github_token'")->fetchColumn();
$options = [
    'http' => [
        'header' => "User-Agent: PHP\r\n" .
                    "Authorization: token $accessToken\r\n"
    ]
];

$context = stream_context_create($options);
$zipFile = 'latest.zip';

// Scarica l'archivio zip del branch
echo "Scaricamento dell'archivio...\n";
file_put_contents($zipFile, fopen($zipUrl, 'r', false, $context));
echo "Download completato.\n";

// Estrai il contenuto dell'archivio zip nella directory temporanea
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    }
    $zip->extractTo($tempDir);
    $zip->close();
    echo "Estrazione completata con successo.\n";
} else {
    echo "Errore durante l'apertura del file zip.\n";
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
                        echo "<span style='background-color: lime; color: black;'>Creata directory: $destPath</span>\n";
                    }
                    updateFiles($srcPath, $destPath);
                }
            } else {
                if (!file_exists($destPath)) {
                    echo "<span style='background-color: lime; color: black;'>Aggiunto: $destPath</span>\n";
                    copy($srcPath, $destPath);
                } else if (md5_file($srcPath) != md5_file($destPath)) {
                    echo "<span style='background-color: lime; color: black;'>Sovrascritto: $destPath</span>\n";
                    copy($srcPath, $destPath);
                } else {
                    echo "Non modificato: $destPath\n";
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
    $success = true;  // Variabile per tenere traccia del successo
    foreach (glob($dir . '/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file) {
        if (is_dir($file)) {
            if (!rrmdir($file)) {
                $success = false;  // Se la rimozione di una sottodirectory fallisce
            }
        } else {
            if (!unlink($file)) {
                echo "Errore durante l'eliminazione di: $file\n";
                $success = false;  // Se il file non può essere eliminato
            }
        }
    }
    if (is_dir($dir) && !rmdir($dir)) {
        echo "Errore durante l'eliminazione della directory: $dir\n";
        $success = false;  // Se la directory non può essere eliminata
    }
    return $success;  // Restituisce false se ci sono stati errori
}

// Utilizza la funzione migliorata per rimuovere la directory temporanea
if (rrmdir($tempDir)) {
    echo "Contenuto temporaneo eliminato.\n";
} else {
    echo "Errore durante l'eliminazione del contenuto temporaneo.\n";
}
unlink($zipFile);
echo "Eliminato: $zipFile\n";

echo "Aggiornamento completato con successo.\n";
?>
