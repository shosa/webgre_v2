<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$db = getDbInstance();

// Only super admin is allowed to access this page
if ($_SESSION['admin_type'] == 'utente') {
    // show permission denied message
    echo 'Permessi insufficienti per visualizzare questa sezione!';
    exit();
}

$lastLancio = $db->orderBy('ID', 'DESC')->getOne('lanci', 'ID');
$newID = ($lastLancio['ID'] ?? 0) + 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_to_store = filter_input_array(INPUT_POST);

    // Verifica se ci sono dati validi da inserire
    if (count($data_to_store['id_modello']) > 0) {
        // Itera su ciascun set di dati e inserisci separatamente
        for ($i = 0; $i < count($data_to_store['id_modello']); $i++) {
            $data = array(
                'lancio' => $data_to_store['lancio'][$i],
                'data' => date('d/m/Y'),
                'linea' => $data_to_store['linea'][$i],
                'id_modello' => $data_to_store['id_modello'][$i],
                'id_variante' => $data_to_store['id_variante'][$i],
                'paia' => $data_to_store['paia'][$i],
                'stato' => $data_to_store['stato'][$i],
                'avanzamento' => "NESSUNO"
            );

            $last_id = $db->insert('lanci', $data);
        }

        if ($last_id) {
            $_SESSION['success'] = "Lancio inserito !";
            header('location: lanci.php');
            exit();
        }
    } else {
        $_SESSION['failure'] = "Inserire almeno un lancio valido!";
        header('location: lanci.php');
        exit();
    }
}


$edit = false;

require_once BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Creazione nuovo lancio</h2>
        </div>
    </div>
    <hr>
    <?php
    include_once(BASE_PATH . '/includes/flash_messages.php');
    ?>
    <form class="well form-horizontal" action=" " method="post" id="contact_form" enctype="multipart/form-data">
        <?php include_once 'forms/new_lancio_form.php'; ?>
    </form>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>