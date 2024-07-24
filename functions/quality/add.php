<?php



session_start();

require_once '../../config/config.php';

require_once BASE_PATH . '/components/auth_validate.php';

require_once '../../utils/log_utils.php';



// Funzione per ottenere l'istanza del database



// Validazione del parametro GET 'cartellino'

$cartellino = filter_input(INPUT_GET, 'cartellino', FILTER_UNSAFE_RAW);

if (!$cartellino) {

    die('Cartellino non valido');

}



// Ottenimento delle informazioni dal database

$db = getDbInstance();

$stmt = $db->prepare("SELECT * FROM dati WHERE Cartel = :cartellino");

$stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);

$stmt->execute();

$informazione = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$informazione) {

    die('Informazioni non trovate');

}



$operatore = strtoupper($_SESSION['username']);

$data = date('d/m/Y');

$orario = date('H:i');



$stmt = $db->prepare("SELECT * FROM linee WHERE sigla = :sigla");

$stmt->bindParam(':sigla', $informazione['Ln'], PDO::PARAM_STR);

$stmt->execute();

$descrizioneLinea = $stmt->fetch(PDO::FETCH_ASSOC);

$nomeLinea = $descrizioneLinea['descrizione'];



// Calcolo del nuovo valore per id

$stmt = $db->prepare("SELECT MAX(ID) AS max_id FROM cq_testid");

$stmt->execute();

$max_testid = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];

$new_testid = $max_testid + 1;



// Inclusione dell'header

require_once BASE_PATH . '/components/header.php';

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

                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>

                    </div>

                    <ol class="breadcrumb mb-4">

                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>

                        <li class="breadcrumb-item"><a href="../../functions/quality/new">Sistema CQ</a></li>

                        <li class="breadcrumb-item active">Controllo dati</li>

                    </ol>

                    <div class="card shadow mb-4">

                        <div class="card-header py-3 d-flex align-items-center">

                            <h6 class="m-0 font-weight-bold text-primary">Nuovo Test a partire da

                                #<?php echo $new_testid; ?></h6>



                        </div>

                        <h4 class="page-header page-action-links text-center font-weight-bold"
                            style="color:red;padding:1%">

                            ** Controllare il riepilogo prima di procedere, andando avanti l'operazione di controllo

                            sarà

                            registrata **

                        </h4>

                        <form class="form" action="cqtest.php" method="post" id="customer_form"
                            enctype="multipart/form-data">

                            <fieldset class="p-3">
                                <h5 class=" bg-success text-white p-2 rounded"><?php echo $nomeLinea; ?></h5>
                                <div name="intestazione" class="p-3 mb-3 bg-light rounded">

                                    <div class="row">

                                        <div class="col-md-4">

                                            <div class="form-group">

                                                <label for="Codice" class="form-label">Codice Articolo</label>

                                                <input type="text" name="codArticolo"
                                                    value="<?php echo htmlspecialchars($informazione['Articolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    placeholder="Codice" class="form-control" required id="Codice"
                                                    readonly>

                                                <input type="text" name="Codice"
                                                    value="<?php echo $descrizioneLinea["descrizione"]; ?>"
                                                    placeholder="" class="form-control" required id="descrizioneLinea"
                                                    readonly hidden>

                                            </div>

                                        </div>

                                        <div class="col-md-8">

                                            <div class="form-group">

                                                <label for="Articolo" class="form-label">Articolo</label>

                                                <input type="text" name="descArticolo"
                                                    value="<?php echo htmlspecialchars($informazione['Descrizione Articolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    placeholder="Descrizione Articolo" class="form-control" required
                                                    id="Articolo" readonly>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-3">

                                            <div class="form-group">

                                                <label for="cliente" class="form-label">Cliente</label>

                                                <input name="cliente"
                                                    value="<?php echo htmlspecialchars($informazione['Ragione Sociale'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    placeholder="Cliente" class="form-control" type="text" readonly>

                                            </div>

                                        </div>

                                        <div class="col-md-4">

                                            <div class="form-group">

                                                <label for="cartellino" class="form-label"><b>Cartellino</b></label>

                                                <input type="text" name="cartellino"
                                                    value="<?php echo htmlspecialchars($informazione['Cartel'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    placeholder="Cartellino" class="form-control" required
                                                    id="cartellino" readonly>

                                            </div>

                                        </div>

                                        <div class="col-md-4">

                                            <div class="form-group">

                                                <label for="commessa" class="form-label"><b>Commessa</b></label>

                                                <input name="commessa"
                                                    value="<?php echo htmlspecialchars($informazione['Commessa Cli'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    placeholder="Commessa non presente" class="form-control" type="text"
                                                    readonly>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-3">

                                            <div class="form-group">

                                                <label for="Idrip" class="form-label">Operazione N#</label>

                                                <input type="text" name="Idrip" value="<?php echo $new_testid; ?>"
                                                    placeholder="ID Riparazione" class="form-control" required
                                                    id="Idrip" readonly>

                                            </div>

                                        </div>

                                        <div class="col-md-3">

                                            <div class="form-group">

                                                <label for="utente" class="form-label">Operatore</label>

                                                <input name="utente" value="<?php echo $operatore; ?>"
                                                    class="form-control" type="text" readonly>

                                            </div>

                                        </div>

                                        <div class="col-md-3">

                                            <div class="form-group">

                                                <label for="data" class="form-label">Data</label>

                                                <input type="text" name="data" value="<?php echo $data; ?>"
                                                    placeholder="DD/MM/YYYY" class="form-control" id="data" readonly>

                                            </div>

                                        </div>

                                        <div class="col-md-3">

                                            <div class="form-group">

                                                <label for="orario" class="form-label">Orario</label>

                                                <input type="text" name="orario" value="<?php echo $orario; ?>"
                                                    class="form-control" id="orario" readonly>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <input type="hidden" name="nomeLinea" value="<?php echo $nomeLinea; ?>">

                                <input type="hidden" name="operatore" value="<?php echo $operatore; ?>">

                                <input type="hidden" name="data" value="<?php echo $data; ?>">

                                <input type="hidden" name="orario" value="<?php echo $orario; ?>">

                                <input type="hidden" name="new_testid" value="<?php echo $new_testid; ?>">

                                <input type="hidden" name="siglaLinea" value="<?php echo $informazione["Ln"]; ?>">

                                <input type="hidden" name="paia" value="<?php echo $informazione["Tot"]; ?>">

                                <div class="form-group floating-button">

                                    <button type="submit" class="btn btn-primary btn-lg btn-block">INIZIA TEST <i
                                            class="fas fa-play"></i></button>

                                </div>

                            </fieldset>

                        </form>

                    </div>

                </div>

            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>

        </div>

    </div>

</body>