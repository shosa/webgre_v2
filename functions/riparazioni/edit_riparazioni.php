<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

// Sanitize input
$riparazione_id = filter_input(INPUT_GET, 'riparazione_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation', FILTER_UNSAFE_RAW);
$edit = ($operation == 'edit') ? true : false;

try {
    // Handle update request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get customer id from query string parameter
        $riparazione_id = filter_input(INPUT_GET, 'riparazione_id', FILTER_VALIDATE_INT);

        // Get input data
        $data_to_update = filter_input_array(INPUT_POST);
        unset($data_to_update['numerata']);

        // Create PDO instance
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare update statement
        $stmt = $pdo->prepare("UPDATE riparazioni SET column1 = :column1, column2 = :column2, ... WHERE IDRIP = :idrip");

        // Bind parameters
        $stmt->bindParam(':column1', $data_to_update['column1']);
        $stmt->bindParam(':column2', $data_to_update['column2']);
        // Bind other parameters as needed

        $stmt->bindParam(':idrip', $riparazione_id);

        // Execute the statement
        $stmt->execute();

        // Check if update was successful
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Riparazione aggiornata correttamente!";
            // Redirect to the listing page
            header('location: riparazioni');
            exit();
        }
    }

    // If edit variable is set, we are performing the update operation
    if ($edit) {
        // Create PDO instance
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare select statement
        $stmt = $pdo->prepare("SELECT * FROM riparazioni WHERE IDRIP = :idrip");

        // Bind parameter
        $stmt->bindParam(':idrip', $riparazione_id);

        // Execute the statement
        $stmt->execute();

        // Fetch data
        $riparazione = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Handle PDO exception
    echo "Errore: " . $e->getMessage();
}

include_once BASE_PATH . '/components/header.php';
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
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Riparazioni</h1>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Modifica cedolda
                                #<?php echo $riparazione_id; ?></h6>
                        </div>
                        <div class="card-body">

                            <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
                                <?php
                                // Include the common form for add and edit  
                                require_once ('forms/edit_riparazioni_form.php');
                                ?>
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