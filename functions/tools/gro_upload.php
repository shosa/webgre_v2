<?php
session_start();
require_once '../../config/config.php';

// Percorso in cui salvare l'immagine ritagliata
$destinationPath = BASE_PATH . '/src/img_group/immagine.jpg';

// Ricevi l'immagine ritagliata
$croppedImageData = $_POST['image'];

// Decodifica l'immagine base64 e la salva nel percorso desiderato
file_put_contents($destinationPath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $croppedImageData)));

// Aggiungi il percorso dell'immagine alla tabella temp_dati_gruppi

// Aggiorna il percorso dell'immagine nella colonna path_to_img
$db = getDbInstance();

$data = array('path_to_img' => '/src/img_group/immagine.jpg');
$db->update('temp_dati_gruppi', $data);

echo 'IMMAGINE CARICATA CON SUCCESSO';


?>