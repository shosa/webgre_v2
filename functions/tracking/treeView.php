<?php

session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <div class="container-fluid">
                    <?php include (BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Albero Dettagli</li>
                    </ol>

                    <!-- Search form -->
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-3">
                            <form id="searchForm" method="GET" action="#">
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                        placeholder="Inserisci cartellino o numero lotto" name="search_query">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Cerca</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Placeholder for tree view -->
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <div id="treeViewPlaceholder">
                                        <!-- Tree view results will be displayed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>

<style>
    .card {
        margin-top: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 20px;
    }

    #treeViewPlaceholder ul {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    #treeViewPlaceholder ul ul {
        display: none;
        margin-left: 20px;
        padding-left: 20px;
    }

    #treeViewPlaceholder li {
        cursor: pointer;
        padding: 8px 10px;
        border-bottom: 1px solid #ddd;
        position: relative;
    }

    #treeViewPlaceholder li:last-child {
        border-bottom: none;
    }

    #treeViewPlaceholder li:before {
        content: "\f054"; /* Icona freccia destra (FontAwesome) */
        font-family: FontAwesome;
        margin-right: 10px;
        color: #777;
    }

    #treeViewPlaceholder li.collapsed:before {
        content: "\f054"; /* Icona freccia destra (FontAwesome) */
    }

    #treeViewPlaceholder li.expanded:before {
        content: "\f078"; /* Icona freccia giù (FontAwesome) */
    }

    #treeViewPlaceholder li.leaf:before {
        content: "\f111"; /* Icona foglia (FontAwesome) */
        color: #6c757d;
    }

    #treeViewPlaceholder li.collapsed.leaf:before {
        content: "\f111"; /* Icona foglia (FontAwesome) */
    }

    #treeViewPlaceholder li.expanded.leaf:before {
        content: "\f111"; /* Icona foglia (FontAwesome) */
    }

    /* Stili per rendere il testo del lotto non cliccabile */
    #treeViewPlaceholder li.leaf {
        cursor: default !important;
    }

    /* Stile per il timestamp */
    #treeViewPlaceholder li .timestamp {
        color: #777;
        font-size: 90%;
        float: right; /* Allinea il timestamp a destra */
        margin-left: 10px;
    }
</style>

<script>
    $(document).ready(function () {
        // Funzione per gestire la richiesta di ricerca tramite AJAX
        $('#searchForm').submit(function (event) {
            event.preventDefault(); // Previene la sottomissione predefinita del form

            var formData = $(this).serialize(); // Serializza i dati del form
            $.ajax({
                type: 'GET',
                url: 'getTree.php', // Script PHP che gestisce la ricerca
                data: formData,
                dataType: 'html', // Si aspetta una risposta HTML
                success: function (response) {
                    $('#treeViewPlaceholder').html(response); // Sostituisce il placeholder con i dati ricevuti
                    initializeTreeView(); // Inizializza la vista ad albero
                },
                error: function () {
                    alert('Errore durante la ricerca.'); // Gestisce eventuali errori
                }
            });
        });

        // Funzione per inizializzare la vista ad albero (espansione e riduzione)
        function initializeTreeView() {
            $('#treeViewPlaceholder ul ul').hide(); // Nasconde tutti i sotto-alberi

            $('#treeViewPlaceholder li > ul').parent().addClass('collapsed'); // Aggiunge classe "collapsed" ai nodi con sotto-alberi

            $('#treeViewPlaceholder li').click(function (event) {
                event.stopPropagation(); // Evita la propagazione dell'evento click

                // Controlla se il nodo è un nodo foglia (lotto) per evitare l'espansione
                if (!$(this).hasClass('leaf')) {
                    $(this).toggleClass('collapsed expanded'); // Alterna le classi "collapsed" e "expanded"
                    $(this).children('ul').slideToggle(); // Alterna la visibilità del sotto-albero
                }
            });
        }
    });
</script>