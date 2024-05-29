<?php
require_once '../../config/config.php';
$db = getDbInstance();
$progressivo = $_POST['progressivo'];

$articoli = $db->where('id_documento', $progressivo)->get('exp_dati_articoli', null, 'DISTINCT(voce_doganale) as voce_doganale');

$uniqueDoganale = [];
foreach ($articoli as $articolo) {
    $uniqueDoganale[] = $articolo['voce_doganale'];
}

echo json_encode($uniqueDoganale);
