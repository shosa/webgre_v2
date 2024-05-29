<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$progressivo = $_GET['progressivo'];

require_once BASE_PATH . '/includes/header.php';

require_once BASE_PATH . '/vendor/autoload.php';
// Percorso della cartella temp
$dir = 'temp/';

// Leggi tutti i file nella cartella temp
$files = scandir($dir);

// Rimuovi . e ..
$files = array_diff($files, array('.', '..'));

function getDetailsFromExcel($filePath)
{
    $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
    $worksheet = $reader->load($filePath)->getActiveSheet();

    $details = [
        'LANCIO' => $worksheet->getCell('B2')->getValue(),
        'PAIA_DA_PRODURRE' => $worksheet->getCell('B3')->getValue()
    ];

    return $details;
}

?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left"><a style="color:#0d6efd;">STEP 3 </a> > Elenco Articoli
                DDT n°
                <?php echo $progressivo; ?>
            </h2>
        </div>
    </div>
    <hr>

    <!-- Pulsante Genera DDT -->


    <!-- Elenco dei file Excel -->
    <div class="row">
        <div class="col-lg-12">
            <ul class="list-group">
                <?php foreach ($files as $file): ?>
                    <?php $details = getDetailsFromExcel($dir . $file); ?>
                    <li class="list-group-item" onclick="showExcelContent('<?php echo $file; ?>')"
                        style="cursor:pointer;width:100%;border:1px solid #007BFF;background-color:#E6F2FF;">
                        <span style="font-weight: bold;"><i class="fad fa-eye"></i>
                            <?php echo $file; ?>
                        </span>
                        <span class="float-end">LANCIO:
                            <?php echo $details['LANCIO']; ?> | PAIA DA PRODURRE:
                            <?php echo $details['PAIA_DA_PRODURRE']; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-12 text-right">
            <button class="btn btn-success" onclick="generaDDT()">Genera DDT</button>
        </div>
    </div>
    <!-- Modale per visualizzare il contenuto dell'Excel -->
    <div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="excelModalLabel">Contenuto Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="excelContent"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>
<script>
    function showExcelContent(fileName) {
        fetch(`temp/${fileName}`)
            .then(response => response.arrayBuffer())
            .then(data => {
                let workbook = new ExcelJS.Workbook();
                return workbook.xlsx.load(data);
            })
            .then(workbook => {
                let worksheet = workbook.worksheets[0];
                let headers = worksheet.getRow(5).values;
                let rows = worksheet.getSheetValues();

                // Rimuovi l'intestazione ripetuta
                rows = rows.slice(6);

                let tableContent = `
                    <table class="table">
                        <thead>
                            <tr>
                                ${headers.map(header => `<th>${header}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.map(row => {
                    let highlight = '';
                    if (row[0] === 'TAGLIO' || row[0] === 'ORLATURA') {
                        highlight = 'style="background-color: #ffcc00;"';
                    }
                    return `<tr ${highlight}>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`;
                }).join('')}
                        </tbody>
                    </table>
                `;

                document.getElementById('excelContent').innerHTML = tableContent;
                $('#excelModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nel caricamento del file Excel.');
            });
    }

    function generaDDT() {
        fetch('genera_ddt.php?progressivo=<?php echo $progressivo; ?>')
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text);
                    });
                }
                return response.text();  // Cambiato da response.json() a response.text()
            })
            .then(text => {
                try {
                    let data = JSON.parse(text);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'DDT generati con successo!',
                            text: data.message,
                        }).then((result) => {
                            window.location.href = `continue_ddt.php?progressivo=<?php echo $progressivo; ?>`;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            text: data.message,
                        });
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    alert('Errore nella generazione dei DDT: ' + text);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nella generazione dei DDT: ' + error.message);
            });
    }
</script>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>