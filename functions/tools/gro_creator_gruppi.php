<?php
session_start();
require_once '../../config/config.php';
// Include il file di gestione del caricamento
include BASE_PATH . '/includes/upload.php';
include BASE_PATH . '/includes/header-nomenu.php';
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Crea Gruppi</h1>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="excel_file" class="col-form-label">Seleziona il file Excel:</label>
                    <div class="custom-file">
                        <input type="file" name="excel_file" id="excel_file" class="custom-file-input" required>
                        <label class="custom-file-label" for="excel_file" id="fileLabel">Scegli file</label>
                    </div>
                </div>
                <button type="submit" name="upload" class="btn btn-primary">Carica</button>
            </form>

            <script>
                // Aggiungi un ascoltatore per l'evento change sull'input file
                document.getElementById('excel_file').addEventListener('change', function (e) {
                    // Ottieni il nome del file selezionato
                    var fileName = e.target.files[0].name;

                    // Aggiorna il testo dell'etichetta del file con il nome del file
                    document.getElementById('fileLabel').innerText = fileName;
                });
            </script>
            <!-- Accordion per il riepilogo e la tabella -->


            <!-- Riepilogo del Lancio -->
            <?php if (isset($lancio) && isset($cartelliniLetti) && isset($articolo) && isset($descrizione) && isset($paiaTotali)): ?>
                <div class="well text-left filter-form"
                    style="margin-top:5%;background-color: #fceeda;border: 1px solid #f9b24b;padding:2%">
                    <h2 class="mb-0">
                        Dati letti:
                    </h2>
                    <div>
                        <div>
                            <label for="lancio">Lancio:</label>
                            <input type="text" class="form-control" id="lancio" value="<?php echo $lancio; ?>" readonly>
                        </div>
                        <div>
                            <label for="cartellini">Cartellini:</label>
                            <input type="text" class="form-control" id="cartellini" value="<?php echo $cartelliniLetti; ?>"
                                readonly>
                        </div>
                        <div>
                            <label for="articolo">Articolo:</label>
                            <input type="text" class="form-control" id="articolo" value="<?php echo $articolo; ?>" readonly>
                        </div>
                        <div>
                            <label for="descrizione">Descrizione:</label>
                            <input type="text" class="form-control" id="descrizione" value="<?php echo $descrizione; ?>"
                                readonly>
                        </div>
                        <div>
                            <label for="paiaTotali">Paia Totali:</label>
                            <input type="text" class="form-control" id="paiaTotali" value="<?php echo $paiaTotali; ?>"
                                readonly>
                        </div>
                        <hr>
                        <div>
                            <button class="btn btn-success text-right" id="caricaDatabase">AVANTI <i
                                    class="fad fa-file-import"></i></button>
                        </div>
                        <?php
                        $_SESSION['sheetData'] = $sheetData;
                        $_SESSION['lancio_data'] = [
                            'lancio' => $lancio,
                            'cartelliniLetti' => $cartelliniLetti,
                            'articolo' => $articolo,
                            'descrizione' => $descrizione,
                            'paiaTotali' => $paiaTotali,
                        ];
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <hr>
    <?php endif; ?>
    <div class="accordion" id="accordionExample">
        <!-- Tabella -->
        <?php if (isset($sheetData)): ?>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h2 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                            data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <h2>Visualizza contenuto del file caricato</h2>
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="container-fluid">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <?php foreach ($sheetData[0] as $header): ?>
                                                <th>
                                                    <?php echo $header; ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 1; $i < count($sheetData); $i++): ?>
                                            <tr>
                                                <?php foreach ($sheetData[$i] as $cell): ?>
                                                    <td>
                                                        <?php echo $cell; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
<div class="col-lg-6">
    <!-- Aggiungi qui eventuali elementi aggiuntivi nella seconda colonna -->
</div>
</div>

<?php include BASE_PATH . '/includes/flash_messages.php'; ?>
</div>

<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
<script>
    $(document).ready(function () {
        $('#caricaDatabase').click(function () {
            $.ajax({
                url: 'gro_carica_database.php',
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Redirect alla pagina cut_lancio.php
                        window.location.href = 'gro_cut_lancio.php';
                    } else {
                        alert('Si è verificato un errore durante il caricamento nel database.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>