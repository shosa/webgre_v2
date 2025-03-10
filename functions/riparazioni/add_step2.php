<?php
session_start();
require_once '../../utils/log_utils.php';

require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
// Connessione al database usando PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
// Ottieni il valore del cartellino dalla richiesta GET
$cartellino = filter_input(INPUT_GET, 'cartellino', FILTER_UNSAFE_RAW);
// Prepara la query per ottenere le informazioni del cartellino
$query = "SELECT * FROM dati WHERE Cartel = :cartellino";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':cartellino', $cartellino, PDO::PARAM_STR);
$stmt->execute();
$informazione = $stmt->fetch(PDO::FETCH_ASSOC);
// Serve POST method, After successful insert, redirect to riparazioni.php page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartellino = filter_input(INPUT_POST, 'cartellino', FILTER_UNSAFE_RAW);
    $data_to_store = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // Controlla i campi da P01 a P20 e imposta a 0 se vuoti
    for ($i = 1; $i <= 20; $i++) {
        $field = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisce il nome del campo (es. P01, P02, ..., P20)
        if (empty($data_to_store[$field])) {
            $data_to_store[$field] = 0;
        }
    }
    // Prepara la query di inserimento
    $columns = implode(", ", array_keys($data_to_store));
    $values = ":" . implode(", :", array_keys($data_to_store));
    $insert_query = "INSERT INTO riparazioni ($columns) VALUES ($values)";
    $insert_stmt = $pdo->prepare($insert_query);
    // Esegui la query di inserimento
    $esito = $insert_stmt->execute($data_to_store);
    // Debug query eseguita
    echo $insert_stmt->debugDumpParams();
    if ($esito) {
        // QUI DOBBIAMO CAPIRE COME LOGGARE L ATTIVITA SIA LA QUERY CHE L'ID RIPARAZIONE
        $update_query = "UPDATE tabid SET id = id + 1";
        $pdo->exec($update_query);
        $_SESSION['success'] = "Riparazione inserita!";
        $stmt = $pdo->query("SELECT MAX(ID) AS max_id FROM tabid");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxid = $result['max_id'];
        $real_query = replacePlaceholders($pdo, $insert_query, $data_to_store);
        logActivity($_SESSION['user_id'], 'RIPARAZIONI', 'CREA', 'Inserimento Cedola', '#' . $maxid, $real_query);
        header('Location: riparazioni.php');
        exit();
    }
}
require_once BASE_PATH . '/components/header.php';
// Prepara la query per ottenere il valore massimo di ID da tabid
$max_query = "SELECT MAX(ID) AS max_id FROM tabid";
$max_stmt = $pdo->query($max_query);
$max_tabid = $max_stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
// Calcola il nuovo valore per id
$new_id = $max_tabid + 1;
?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once '../../utils/alerts.php'; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="../../functions/riparazioni/add_step1">Nuova
                                Riparazione</a></li>
                        <li class="breadcrumb-item active">Inserimento dettagli</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Nuova Cedola #<?php echo $new_id; ?></h6>
                        </div>
                        <div class="card-body">
                            <form class="form" action="" method="post" id="customer_form" enctype="multipart/form-data">
                                <fieldset>
                                    <?php
                                    // Connessione al database usando PDO
                                    try {
                                        $pdo = getDbInstance();
                                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    } catch (PDOException $e) {
                                        die("Errore di connessione al database: " . $e->getMessage());
                                    }
                                    // Ottieni il valore massimo di ID dalla tabella 'tabid'
                                    $stmt = $pdo->query("SELECT MAX(ID) AS max_id FROM tabid");
                                    $max_tabid = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
                                    $new_id = $max_tabid + 1;
                                    ?>
                                    <div name="intestazione" style="padding:10px;">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label for="Codice">Codice Articolo</label>
                                                    <input type="text" name="Codice"
                                                        value="<?php echo htmlspecialchars($informazione['Articolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Codice" class="form-control" required="required"
                                                        id="Codice" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Cliente</label>
                                                    <input name="cliente"
                                                        value="<?php echo htmlspecialchars($informazione['Ragione Sociale'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Birth date" class="form-control" type="cliente"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-11">
                                                <div class="form-group">
                                                    <label for="Articolo">Articolo</label>
                                                    <input type="text" name="Articolo"
                                                        value="<?php echo htmlspecialchars($informazione['Descrizione Articolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Descrizione Articolo" class="form-control"
                                                        required="required" id="Articolo" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>Linea</label>
                                                    <input name="linea"
                                                        value="<?php echo htmlspecialchars($informazione['Ln'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Birth date" class="form-control" type="linea"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="cartellino">Cartellino</label>
                                                    <input type="text" name="cartellino"
                                                        value="<?php echo htmlspecialchars($informazione['Cartel'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Last Name" class="form-control" required="required"
                                                        id="cartellino" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Commessa</label>
                                                    <input name="commessa"
                                                        value="<?php echo htmlspecialchars($informazione['Commessa Cli'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="Commessa non presente" class="form-control"
                                                        type="commessa" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div name="inserimento"
                                        style="background-color:#f4f7f9; border-radius:10px; padding:10px;">
                                        <div class="form-group">
                                            <label>Numerata</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-condensed text-center">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <?php
                                                            // Recupera i dati dalla tabella id_numerate utilizzando PDO
                                                            // Debug: Verifica il valore di $informazione['Nu']
                                                            $nuValue = (string) $informazione['Nu'];
                                                            $query = "SELECT * FROM id_numerate WHERE ID = :id";
                                                            $stmt = $pdo->prepare($query);
                                                            $stmt->bindParam(':id', $nuValue, PDO::PARAM_STR);
                                                            $stmt->execute();
                                                            $idNumerateData = $stmt->fetch(PDO::FETCH_ASSOC);
                                                            // Cicla attraverso i campi N01, N02, ecc. e crea le celle della tabella
                                                            for ($i = 1; $i <= 20; $i++) {
                                                                $fieldName = 'N' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisci il nome del campo N01, N02, ecc.
                                                                echo '<th><span>' . htmlspecialchars($idNumerateData[$fieldName], ENT_QUOTES, 'UTF-8') . '</span></th>';
                                                            }
                                                            ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <!--CARICAMENTO VALORI P01...P20-->
                                                            <?php
                                                            for ($i = 1; $i <= 20; $i++) {
                                                                $fieldName = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisci il nome del campo P01, P02, ecc.
                                                                $fieldValue = isset($informazione[$fieldName]) ? htmlspecialchars($informazione[$fieldName], ENT_QUOTES, 'UTF-8') : '';
                                                                echo '<td style="width:50px;"><span>' . $fieldValue . '</span></td>';
                                                            }
                                                            ?>
                                                        </tr>
                                                        <tr>
                                                            <!--CARICAMENTO VALORI P01...P20-->
                                                            <?php
                                                            for ($i = 1; $i <= 20; $i++) {
                                                                $fieldName = 'P' . str_pad($i, 2, '0', STR_PAD_LEFT); // Costruisci il nome del campo P01, P02, ecc.
                                                                echo '<td style="width:50px;"><input type="number" name="' . $fieldName . '" value="" class="form-control"></td>';
                                                            }
                                                            ?>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Urgenza</label>
                                            <select name="urgenza" class="form-control selectpicker" required>
                                                <option value="BASSA">BASSA</option>
                                                <option value="MEDIA">MEDIA</option>
                                                <option value="ALTA">ALTA</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="causale">Causale</label>
                                            <textarea name="causale" placeholder="Inserisci le note della riparazione"
                                                class="form-control" id="causale"></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Reparto</label>
                                                    <?php
                                                    // Esegui una query per ottenere tutti i valori dalla colonna "Nome" della tabella "reparti"
                                                    $stmt = $pdo->query("SELECT Nome FROM reparti");
                                                    $reparti = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    echo '<select name="reparto" class="form-control selectpicker" required>';
                                                    // Aggiungi un'opzione vuota come valore predefinito
                                                    echo '<option value="" disabled selected>Seleziona un reparto</option>';
                                                    // Genera le opzioni basate sui risultati della query
                                                    foreach ($reparti as $reparto) {
                                                        $value = $reparto['Nome'];
                                                        echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
                                                    }
                                                    echo '</select>';
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Laboratorio</label>
                                                    <?php
                                                    // Esegui una query per ottenere i valori distinti dalla colonna "Nome" della tabella "laboratori"
                                                    $stmt = $pdo->query("SELECT DISTINCT Nome FROM laboratori ORDER BY Nome ASC");
                                                    $laboratori = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    echo '<select name="laboratorio" class="form-control selectpicker" required>';
                                                    // Aggiungi un'opzione vuota come valore predefinito
                                                    echo '<option value="" disabled selected>Seleziona un laboratorio</option>';
                                                    // Genera le opzioni basate sui risultati della query
                                                    foreach ($laboratori as $laboratorio) {
                                                        $value = $laboratorio['Nome'];
                                                        echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
                                                    }
                                                    echo '</select>';
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div name="finale" style="padding:10px;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Articolo">ID Riparazione</label>
                                                    <input type="text" name="Idrip" value="<?php echo $new_id ?>"
                                                        placeholder="Idrip" class="form-control" required="required"
                                                        id="Idrip" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="nu">Numerata</label>
                                                    <input name="nu"
                                                        value="<?php echo htmlspecialchars($informazione['Nu'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        placeholder="" class="form-control" type="text" id="nu"
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Utente</label>
                                                    <input name="utente"
                                                        value="<?php echo strtoupper($_SESSION['username']); ?>"
                                                        placeholder="Birth date" class="form-control" type="utente"
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="data">Data</label>
                                                    <input type="text" name="data" value="<?php echo date('d/m/Y'); ?>"
                                                        placeholder="DD/MM/YYYY" class="form-control" id="data"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group floating-button">
                                            <label></label>
                                            <button type="submit" class="btn btn-warning btn-lg btn-block"><i
                                                    class="fas fa-save"></i> Salva</button>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>