<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/includes/header.php';

// Recupera la data selezionata, se presente
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Recupera i dati relativi alla data selezionata dal database
$db = getDbInstance();
$db->where('data', $date);
$data = $db->get('cq_records');

?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h2 class="page-header page-action-links text-left">Test effettuati in data <span
                    class="badge bg-primary text-white" style="margin-left: 10px;"><?php echo $date; ?> </h2>
            </span>
            </h2>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="generate_pdf.php?date=<?php echo $date; ?>" class="btn btn-warning" style="font-size:18pt;"><i
                        class="fas fa-print"></i> STAMPA REPORT</a>

            </div>
        </div>
    </div>
    <hr>

    <!-- Mostra i dati relativi alla data selezionata -->
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Ora</th>
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
                                <button class="btn btn-primary btn-detail" data-testid="<?php echo $record['testid']; ?>"
                                    data-toggle="modal" data-target="#detailModal">
                                    <i class="fal fa-search-plus"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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