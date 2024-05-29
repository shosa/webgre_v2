<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/vendor/autoload.php';

use MysqliDb\MysqliDb;

$db = getDbInstance();

// Funzione per visualizzare tutte le tabelle nel database
function visualizzaTabelle()
{
    global $db;
    $tabelle = $db->rawQuery('SHOW TABLES');
    return $tabelle;
}

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Main container -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2>Tabelle del Database</h2>
                <ul>
                    <?php foreach (visualizzaTabelle() as $tabella): ?>
                        <li><a href="#" class="tabella-link"
                                data-nome-tabella="<?php echo current($tabella); ?>"><?php echo current($tabella); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="tabella-dati" class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2>Dati della Tabella</h2>
                <div class="table-responsive">
                    <table id="dati-tabella" class="table table-bordered"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //Main container -->

<!-- Div per visualizzare la tabella dei dati -->


<!-- Script per la gestione della richiesta AJAX -->
<script>
    $(document).ready(function () {
        // Gestisci il clic sui link delle tabelle
        $('.tabella-link').on('click', function (e) {
            e.preventDefault(); // Impedisci il comportamento predefinito del link
            var nomeTabella = $(this).data('nome-tabella'); // Ottieni il nome della tabella
            // Effettua una richiesta AJAX per ottenere le righe della tabella
            $.ajax({
                url: 'azioni_database.php',
                type: 'POST',
                data: { azione: 'ottieni_righe', tabella: nomeTabella },
                success: function (response) {
                    var righe = JSON.parse(response); // Converti la risposta JSON in un array di oggetti
                    var tabellaHTML = '<thead><tr>';
                    // Aggiungi intestazioni della tabella
                    for (var key in righe[0]) {
                        tabellaHTML += '<th>' + key + '</th>';
                    }
                    tabellaHTML += '</tr></thead><tbody>';
                    // Aggiungi righe della tabella
                    for (var i = 0; i < righe.length; i++) {
                        tabellaHTML += '<tr>';
                        for (var key in righe[i]) {
                            tabellaHTML += '<td>' + righe[i][key] + '</td>';
                        }
                        tabellaHTML += '</tr>';
                    }
                    tabellaHTML += '</tbody>';
                    // Inserisci la tabella HTML nel div della tabella
                    $('#dati-tabella').html(tabellaHTML);
                    // Scrolle automaticamente verso il div della tabella
                    $('html, body').animate({
                        scrollTop: $("#tabella-dati").offset().top
                    }, 500);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>