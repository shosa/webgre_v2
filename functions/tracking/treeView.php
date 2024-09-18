<?php session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
$pdo = getDbInstance();
$sql = "SELECT COUNT(*) AS count FROM track_links";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$assocCount = $row['count'];
$sql2 = "SELECT COUNT(DISTINCT (cartel) ) AS count FROM track_links";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute();
$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
$cartelCount = $row2['count'];
require_once BASE_PATH . '/components/header.php'; ?>
<body id="page-top">
    <div id="wrapper"><?php include (BASE_PATH . "/components/navbar.php"); ?>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content"><?php include (BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid"><?php include (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="mb-4 align-items-center d-sm-flex justify-content-between">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="mb-4 breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Albero Dettagli</li>
                    </ol>
                    <div class="mb-4 row">
                        <div class="col-md-6 offset-md-3">
                            <form action="#" id="searchForm">
                                <div class="input-group"><input class="form-control" name="search_query"
                                        placeholder="Inserisci cartellino, commessa o numero lotto (usa * per visualizzare tutto)">
                                    <div class="input-group-append"><button class="btn btn-primary" type="submit"><i
                                                class="fa-solid fa-magnifying-glass"></i></button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="align-items-center justify-content-between d-flex"><span class="text-muted">Sono
                                    presenti <span
                                        class="font-weight-bold text-success"><?php echo $assocCount; ?></span> Associazioni
                                    per <span
                                        class="font-weight-bold text-primary"><?php echo $cartelCount; ?></span> Cartellini.
                                </span><a href="getXlsx.php" class="btn btn-success"><i
                                        class="fa-solid fa-download"></i> EXCEL</a></div>
                            <div class="card">
                                <div class="mt-2 text-center" id="loader">
                                    <div class="spinner"></div>
                                </div>
                                <div class="card-body">
                                    <div id="treeViewPlaceholder">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?><?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
<style>
    .spinner {
        display: none;
        width: 50px;
        height: 50px;
        margin: 0 auto;
        border: 3px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #333;
        animation: spin 1s infinite linear
    }
    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }
    .card {
        margin-top: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, .1)
    }
    .card-body {
        padding: 20px
    }
    #treeViewPlaceholder ul {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 0
    }
    #treeViewPlaceholder ul ul {
        display: none;
        margin-left: 20px;
        padding-left: 20px
    }
    #treeViewPlaceholder li {
        cursor: pointer;
        padding: 8px 10px;
        border-bottom: 1px solid #ddd;
        position: relative
    }
    #treeViewPlaceholder li:last-child {
        border-bottom: none
    }
    #treeViewPlaceholder li:before {
        content: "\f054";
        font-family: FontAwesome;
        margin-right: 10px;
        color: #777
    }
    #treeViewPlaceholder li.collapsed:before {
        content: "\f054"
    }
    #treeViewPlaceholder li.expanded:before {
        content: "\f078"
    }
    #treeViewPlaceholder li.leaf:before {
        content: "\f111";
        color: #6c757d
    }
    #treeViewPlaceholder li.collapsed.leaf:before {
        content: "\f111"
    }
    #treeViewPlaceholder li.expanded.leaf:before {
        content: "\f111"
    }
    #treeViewPlaceholder li.leaf {
        cursor: default !important
    }
    #treeViewPlaceholder li .timestamp {
        color: #777;
        font-size: 90%;
        float: right;
        margin-left: 10px
    }
</style>
<script>
    $(document).ready(function () {
        $('#searchForm').submit(function (event) {
            event.preventDefault(); // Previene la sottomissione predefinita del form
            var searchQuery = $('input[name="search_query"]').val();
            if (searchQuery.trim() === '') {
                searchQuery = '*'; // Se la ricerca è vuota, impostiamo '*' per ottenere tutti i risultati
            }
            $('#loader .spinner').show();
            $.ajax({
                type: 'GET',
                url: 'getTree.php', // Script PHP che gestisce la ricerca
                data: { search_query: searchQuery },
                dataType: 'html', // Si aspetta una risposta HTML
                success: function (response) {
                    $('#treeViewPlaceholder').html(response); // Sostituisce il placeholder con i dati ricevuti
                    initializeTreeView(); // Inizializza la vista ad albero
                    attachEventHandlers(); // Attacca gli handler per edit e delete
                    $('#loader .spinner').hide();
                },
                error: function () {
                    alert('Errore durante la ricerca.'); // Gestisce eventuali errori
                }
            });
        });
        function initializeTreeView() {
            $('#treeViewPlaceholder ul ul').hide(); // Nasconde tutti i sotto-alberi
            $('#treeViewPlaceholder li > ul').parent().addClass('collapsed'); // Aggiunge classe "collapsed" ai nodi con sotto-alberi
            $('#treeViewPlaceholder li').click(function (event) {
                event.stopPropagation(); // Evita la propagazione dell'evento click
                if (!$(this).hasClass('leaf')) {
                    $(this).toggleClass('collapsed expanded'); // Alterna le classi "collapsed" e "expanded
                    $(this).children('ul').slideToggle(); // Alterna la visibilità del sotto-albero
                }
            });
        }
        function attachEventHandlers() {
            $('.edit-lot').click(function (event) {
                event.stopPropagation();
                var lotId = $(this).data('id');
                var newLotValue = prompt("Inserisci il nuovo valore del lotto:");
                if (newLotValue) {
                    $.ajax({
                        type: 'POST',
                        url: 'updateLot.php',
                        data: { id: lotId, lot: newLotValue },
                        success: function (response) {
                            alert('Lotto aggiornato con successo.');
                            $('#searchForm').submit(); // Ricarica i risultati della ricerca
                        },
                        error: function () {
                            alert('Errore durante l\'aggiornamento del lotto.');
                        }
                    });
                }
            });
            $('.delete-lot').click(function (event) {
                event.stopPropagation();
                var lotId = $(this).data('id');
                if (confirm("Sei sicuro di voler cancellare questo lotto?")) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteLot.php',
                        data: { id: lotId },
                        success: function (response) {
                            alert('Lotto cancellato con successo.');
                            $('#searchForm').submit(); // Ricarica i risultati della ricerca
                        },
                        error: function () {
                            alert('Errore durante la cancellazione del lotto.');
                        }
                    });
                }
            });
        }
    });
</script>