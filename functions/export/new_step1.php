<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$edit = false;
$db = getDbInstance();
// Recupera l'ultimo id + 1 dalla tabella exp_documenti
$db->orderBy("id", "DESC");
$lastDocument = $db->getOne("exp_documenti");
$newId = $lastDocument['id'] + 1;

// Recupera i dati dalla tabella exp_terzisti
$terzisti = $db->get("exp_terzisti");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_terzista = $_POST['terzista'];
    $data = date("Y-m-d");
    $stato = "Aperto";

    $data_to_insert = [
        'id' => $newId,
        'id_terzista' => $id_terzista,
        'data' => $data,
        'stato' => $stato
    ];

    $insert = $db->insert('exp_documenti', $data_to_insert);

    if ($insert) {
        header("Location: new_step2.php?progressivo=$newId");
        exit();
    } else {
        $error = "Errore durante l'inserimento del nuovo documento";
    }
}

require_once BASE_PATH . '/includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left"><a style="color:#0d6efd;">STEP 1 </a> > Inserimento
                Dettagli</h2>
        </div>
    </div>
    <hr>

    <form action="" method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="terzista">Terzista</label>
                    <select class="form-control" id="terzista" name="terzista">
                        <?php foreach ($terzisti as $terzista): ?>
                            <option value="<?php echo $terzista['id']; ?>">
                                <?php echo $terzista['ragione_sociale']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="progressivo">PROGRESSIVO</label>
                    <input type="text" class="form-control" id="progressivo" name="progressivo"
                        value="<?php echo $newId; ?>" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary">Avanti</button>
            </div>
        </div>
    </form>

    <?php if (isset($error)): ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>