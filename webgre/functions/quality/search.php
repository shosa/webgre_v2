<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/includes/header.php';

// Recupera i criteri di ricerca dal form
$search_criteria = isset($_GET['search']) ? $_GET['search'] : '';

// Connessione al database
$db = getDbInstance();

// Aggiungi condizioni di ricerca se i criteri sono stati forniti
if ($search_criteria) {
    $db->where('testid', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('cartellino', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('commessa', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('articolo', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('cod_articolo', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('linea', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('reparto', '%' . $search_criteria . '%', 'LIKE')
        ->orWhere('data', '%' . $search_criteria . '%', 'LIKE');

    // Recupera i dati dal database solo se i criteri di ricerca sono forniti
    $data = $db->get('cq_records');
} else {
    $data = null; // Nessun dato se non ci sono criteri di ricerca
}

?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Ricerca registrazioni:</h2>
        </div>
    </div>
    <hr>

    <form method="get" action="">
        <div class="form-group">
            <input type="text" name="search" class="form-control"
                placeholder="Cerca per N° Test, Reparto, Cartellino, Commessa, Articolo, Linea o Data"
                value="<?php echo htmlspecialchars($search_criteria); ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Cerca <i class="far fa-search"></i> </button>
    </form>
    <hr>
    <div class="row">
        <div class="col-lg-12">
            <?php if ($search_criteria && $data): ?>
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Cartellino</th>
                            <th>Commessa</th>
                            <th>Reparto</th>
                            <th>Articolo</th>
                            <th>Modello</th>
                            <th>Calzata</th>
                            <th>Esito</th>
                            <th>Dettagli</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $record): ?>
                            <tr>
                                <td><?php echo $record['testid']; ?></td>
                                <td><?php echo $record['data']; ?></td>
                                <td><?php echo $record['orario']; ?></td>
                                <td><?php echo $record['cartellino']; ?></td>
                                <td><?php echo $record['commessa']; ?></td>
                                <td><?php echo $record['reparto']; ?></td>
                                <td><?php echo $record['cod_articolo']; ?></td>
                                <td><?php echo $record['articolo']; ?></td>
                                <td><?php echo $record['calzata']; ?></td>
                                <td <?php echo ($record['esito'] == 'V') ? 'style="text-align:center; background-color: #b8ffba; color: green;"' : 'style="text-align:center;background-color: #ffb8c1; color: red;"'; ?>>
                                    <?php echo ($record['esito'] == 'V') ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-detail" data-testid="<?php echo $record['testid']; ?>"
                                        data-toggle="modal" data-target="#detailModal">
                                        <i class="fal fa-search-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($search_criteria): ?>
                <div class="alert alert-warning">Nessuna registrazione trovata.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modale per i dettagli -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Dettagli del Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <span class="badge bg-success text-white" style="margin-left: 10px;" id="detail-cartellino"></span>
                <span class="badge bg-primary text-white" style="margin-left: 10px;" id="detail-commessa"></span>
                <p><strong>Operatore:</strong> <span id="detail-operatore"></span></p>
                <p><strong>Articolo:</strong> <span id="detail-articolo"></span></p>
                <p><strong>Test:</strong> <span id="detail-test"></span></p>
                <p><strong>Note:</strong> <span id="detail-note"></span></p>
                <p><strong>Calzata:</strong> <span id="detail-calzata"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.btn-detail');
        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const testid = this.getAttribute('data-testid');
                fetch('get_record_details.php?testid=' + testid)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('detail-operatore').textContent = data.operatore;
                        document.getElementById('detail-articolo').textContent = data.articolo;
                        document.getElementById('detail-cartellino').textContent = data.cartellino;
                        document.getElementById('detail-commessa').textContent = data.commessa;
                        document.getElementById('detail-test').textContent = data.test;
                        document.getElementById('detail-note').textContent = data.note;
                        document.getElementById('detail-calzata').textContent = data.calzata;
                    })
                    .catch(error => console.error('Errore:', error));
            });
        });
    });
</script>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>