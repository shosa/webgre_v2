<?php
// Ottieni il termine di ricerca dalla richiesta GET
session_start();
require_once 'config/config.php';
$terms = filter_input(INPUT_GET, 'terms', FILTER_UNSAFE_RAW);

// Inizializza un array per memorizzare i risultati della ricerca
$results = [];

// Carica e analizza il file XML
$xml = simplexml_load_file('indice.xml');

if ($xml) {
    // Itera attraverso ogni elemento 'pagina' nel file XML
    foreach ($xml->pagina as $pagina) {
        // Recupera e separa i tag
        $tags = explode(';', (string) $pagina->tag);
        $found = false;

        // Verifica se il termine di ricerca è presente nei tag
        foreach ($tags as $tag) {
            if (stripos($tag, $terms) !== false) {
                $found = true;
                break;
            }
        }

        // Verifica se il termine di ricerca è presente nei campi 'nome' o 'link'
        if ($found || stripos((string) $pagina->nome, $terms) !== false || stripos((string) $pagina->link, $terms) !== false) {
            // Aggiungi il risultato della ricerca all'array dei risultati
            $results[] = [
                'tag' => (string) $pagina->tag,
                'descrizione' => (string) $pagina->descrizione,
                'nome' => (string) $pagina->nome,
                'link' => (string) $pagina->link,
                'icona' => (string) $pagina->icona,
                'color' => getRandomColor()
            ];
        }
    }
}

include (BASE_PATH . "/components/header.php");

function getRandomColor()
{
    $colors = array('primary', 'success', 'info', 'warning', 'danger');
    $index = array_rand($colors);
    return $colors[$index];
}
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include (BASE_PATH . "/components/navbar.php"); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <?php include (BASE_PATH . "/components/topbar.php"); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Ricerca Universale</h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Risultati per "<?php echo $terms; ?>"</h6>
                        </div>

                        <?php if (!empty($results)): ?>
                            <div class="card-body">
                                <?php $counter = 0; ?>
                                <div class="row">
                                    <?php foreach ($results as $result): ?>
                                        <div class="col-xl-3 col-md-6 mb-4">
                                            <div class="card border-left-<?php echo $result['color']; ?> shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-<?php echo $result['color']; ?> text-uppercase mb-1">
                                                                <?php echo htmlspecialchars($result['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                                <?php echo htmlspecialchars($result['descrizione'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="<?php echo $result['icona']; ?>"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="<?php echo $result['link']; ?>" class="card-footer text-white bg-white text-<?php echo $result['color']; ?>">
                                                    <span class="float-left">Vai alla pagina</span>
                                                    <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                    <div class="clearfix"></div>
                                                </a>
                                            </div>
                                        </div>
                                        <?php $counter++; ?>
                                        <?php if ($counter % 4 == 0): ?>
                                            </div><div class="row">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-body">
                                <p>Nessun risultato trovato per "<?php echo htmlspecialchars($terms, ENT_QUOTES, 'UTF-8'); ?>".</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once BASE_PATH . '/components/scripts.php'; ?>
    <?php include_once BASE_PATH . '/components/footer.php'; ?>
</body>
