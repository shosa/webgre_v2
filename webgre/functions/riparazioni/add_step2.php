<?php

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$db = getDbInstance();
$cartellino = filter_input(INPUT_GET, 'cartellino');
$db->where('Cartel', $cartellino);
$query = $db->getLastQuery();
echo $query;
$informazione = $db->getOne("dati");
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_UNSAFE_RAW);
    $data_to_store = filter_input_array(INPUT_POST);

    // Controlla i campi da P01 a P20 e imposta a 0 se vuoti
    for ($i = 1; $i <= 20; $i++) {
        $field = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisce il nome del campo (es. P01, P02, ..., P20)
        if (empty($data_to_store[$field])) {
            $data_to_store[$field] = 0;
        }
    }

    $db = getDbInstance();
    $esito = $db->insert('riparazioni', $data_to_store);
    print_r($db->getLastQuery());

    if ($esito) {
        $db->rawQuery("UPDATE tabid SET id = id + 1");
        $_SESSION['success'] = "Riparazione inserita!";
        header('location: riparazioni.php');
        exit();
    }
}





require_once BASE_PATH . '/includes/header.php';
$db->orderBy("ID", "DESC");
$max_tabid = $db->getValue("tabid", "MAX(ID)");

// Calcola il nuovo valore per id
$new_id = $max_tabid + 1;
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Nuova Riparazione #
                <?php echo $new_id; ?>
            </h2>
        </div>


    </div>
    <hr>
    <form class="form" action="" method="post" id="customer_form" enctype="multipart/form-data">
        <?php include_once('forms/new_step2_form.php'); ?>
    </form>
</div>


<?php include_once BASE_PATH . '/includes/footer.php'; ?>