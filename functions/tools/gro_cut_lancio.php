<?php

require_once '../../config/config.php';
require_once BASE_PATH . '/functions/tools/gro_carica_database.php';
include BASE_PATH . '/includes/header-nomenu.php';

$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paiaPerGruppo = filter_input(INPUT_POST, 'paia_per_gruppo', FILTER_VALIDATE_INT);

    if ($paiaPerGruppo !== false && $paiaPerGruppo > 0) {
        // Raggruppa le righe in base al criterio specificato
        raggruppaPerPaia($db, $paiaPerGruppo);
    }
}

function raggruppaPerPaia($db, $paiaPerGruppo)
{
    // Ottieni dati dalla tabella temp_dati_gruppi ordinati per ID in ordine crescente
    $db->orderBy('id', 'ASC');
    $righe = $db->get('temp_dati_gruppi');

    // Inizializza variabili
    $gruppoCorrente = 1;
    $paiaRimanenti = $paiaPerGruppo;
    $sommaTotaleGruppo = 0;

    foreach ($righe as $riga) {
        $qta = (int) $riga['qta'];

        // Controlla se aggiungere la riga supererà la somma massima del gruppo
        if ($sommaTotaleGruppo + $qta > $paiaPerGruppo) {
            // Se la somma massima è stata superata, passa al prossimo gruppo
            $gruppoCorrente++;
            $sommaTotaleGruppo = 0;
        }

        // Assegna al gruppo corrente e aggiorna paia rimanenti
        $db->where('id', $riga['id']);
        $db->update('temp_dati_gruppi', ['Gruppo' => sprintf('%03d', $gruppoCorrente)]);
        $sommaTotaleGruppo += $qta;
    }

    // Ridireziona alla stessa pagina dopo il raggruppamento
    header('Location: gro_view_groups.php');
    exit();
}
include BASE_PATH . '/includes/header.php';
$lancioData = isset($_SESSION['lancio_data']) ? $_SESSION['lancio_data'] : null;
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Raggruppa per Paia</h1>
            <hr>
            <!-- Form per l'input paia per gruppo -->
            <form action="" method="post">
                <div class="form-group">
                    <label for="paia_per_gruppo">Paia per Gruppo:</label>
                    <input type="number" name="paia_per_gruppo" id="paia_per_gruppo" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Raggruppa</button>
            </form>
            <hr>
            <?php if ($lancioData): ?>
                <div class="well text-left filter-form"
                    style="margin-top:5%;background-color: #fceeda;border: 1px solid #f9b24b;padding:2%">
                    <h2 class="mb-0">
                        Riepilogo dati:
                    </h2>
                    <div>
                        <div>
                            <label for="lancio">Lancio:</label>
                            <input type="text" class="form-control" id="lancio" value="<?php echo $lancioData['lancio']; ?>"
                                readonly>
                        </div>
                        <div>
                            <label for="cartellini">Cartellini:</label>
                            <input type="text" class="form-control" id="cartellini"
                                value="<?php echo $lancioData['cartelliniLetti']; ?>" readonly>
                        </div>
                        <div>
                            <label for="articolo">Articolo:</label>
                            <input type="text" class="form-control" id="articolo"
                                value="<?php echo $lancioData['articolo']; ?>" readonly>
                        </div>
                        <div>
                            <label for="descrizione">Descrizione:</label>
                            <input type="text" class="form-control" id="descrizione"
                                value="<?php echo $lancioData['descrizione']; ?>" readonly>
                        </div>
                        <div>
                            <label for="paiaTotali">Paia Totali:</label>
                            <input type="text" class="form-control" id="paiaTotali"
                                value="<?php echo $lancioData['paiaTotali']; ?>" readonly>
                        </div>
                        <hr>



                    </div>


                <?php endif; ?>

            </div>
            <hr>
            <div class="col-lg-6">
                <!-- Aggiungi qui eventuali elementi aggiuntivi nella seconda colonna -->
            </div>
            <hr>
        </div>


        <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    </div>

    <!-- //Main container -->
    <?php include BASE_PATH . '/includes/footer.php'; ?>