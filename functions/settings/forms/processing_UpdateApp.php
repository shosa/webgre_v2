<?php
session_start();
require_once '../../../config/config.php';

function logActivity($user_id, $category, $activity_type, $description, $note = '', $text_query = '')
{
    $db = getDbInstance();
    $sql = "INSERT INTO activity_log (user_id, category, activity_type, description, note, text_query) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $category, $activity_type, $description, $note, $text_query]);

    echo "<script>console.log('Evento di tipo $activity_type registrato nel log delle attività. Dettagli: $description');</script>";
}

function replacePlaceholders($pdo, $query, $params)
{
    foreach ($params as $key => $value) {
        $query = str_replace(":$key", $pdo->quote($value), $query);
    }
    return $query;
}

header('Content-Type: text/plain');

$pdo = getDbInstance();
$repoOwner = 'shosa';
$repoName = 'webgre_v2';
$branch = "main";
$tempDir = 'temp_update';
$baseDir = BASE_PATH;

echo "Log aggiornamento:\n";

$zipUrl = "https://api.github.com/repos/$repoOwner/$repoName/zipball/$branch";
$accessToken = $pdo->query("SELECT value FROM settings WHERE item = 'github_token'")->fetchColumn();
$options = [
    'http' => [
        'header' => "User-Agent: PHP\r\n" .
            "Authorization: token $accessToken\r\n"
    ]
];

$context = stream_context_create($options);
$zipFile = 'latest.zip';

echo "Scaricamento dell'archivio...\n";
file_put_contents($zipFile, fopen($zipUrl, 'r', false, $context));
echo "Download completato.\n";

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

$modifiedFiles = [];  // Array per memorizzare i file modificati

function updateFiles($source, $dest, &$modifiedFiles)
{
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
                    updateFiles($srcPath, $destPath, $modifiedFiles);
                }
            } else {
                if (!file_exists($destPath)) {
                    echo "<span style='background-color: lime; color: black;'>Aggiunto: $destPath</span>\n";
                    copy($srcPath, $destPath);
                    $modifiedFiles[] = $destPath;
                } else if (md5_file($srcPath) != md5_file($destPath)) {
                    echo "<span style='background-color: lime; color: black;'>Sovrascritto: $destPath</span>\n";
                    copy($srcPath, $destPath);
                    $modifiedFiles[] = $destPath;
                } else {
                    echo "Non modificato: $destPath\n";
                }
            }
        }
    }
    closedir($dir);
}

$extractedDir = glob($tempDir . '/*', GLOB_ONLYDIR)[0];
updateFiles($extractedDir, $baseDir, $modifiedFiles);

function rrmdir($dir)
{
    $success = true;
    foreach (glob($dir . '/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file) {
        if (is_dir($file)) {
            if (!rrmdir($file)) {
                $success = false;
            }
        } else {
            if (!unlink($file)) {
                echo "<span style='background-color: red; color: white;'>Errore durante l'eliminazione di: $file</span>\n";
                $success = false;
            }
        }
    }
    if (is_dir($dir) && !rmdir($dir)) {
        echo "<span style='background-color: red; color: white;'>Errore durante l'eliminazione della directory: $dir</span>\n";
        $success = false;
    }
    return $success;
}

if (rrmdir($tempDir)) {
    echo "Contenuto temporaneo eliminato.\n";
} else {
    echo "<span style='background-color: red; color: white;'>Errore durante l'eliminazione del contenuto temporaneo.</span>\n";
}
unlink($zipFile);
echo "Eliminato: $zipFile\n";

$fileList = !empty($modifiedFiles) ? implode(", ", $modifiedFiles) : "Nessuno";
logActivity($_SESSION['user_id'], 'APP', 'AGGIORNAMENTO', 'Lanciato aggiornamento', "Dettaglio File Modificati", "$fileList");

echo "<span style='background-color: lime; color: black;'>Aggiornamento completato con successo.</span>\n";
?>