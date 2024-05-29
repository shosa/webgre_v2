<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Web G.R.E</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link href="/assets/js/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="/assets/css/webgre.css" rel="stylesheet">
    <link href="/assets/css/plus.css" rel="stylesheet">
    <link href="/assets/fonts/font-awesome/css/new.min.css" rel="stylesheet" type="text/css">
    <script src="/assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <style>
        .page-action-links {
            margin: 20px 0px 20px 0px;
        }

        .sidebar {
            margin-top: 0px;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php
        $db = getDbInstance();
        $querySettings = "SELECT value FROM settings WHERE item = 'modulo_produzione'";
        $resultSettings = $db->rawQuery($querySettings);
        $moduloProduzioneValue = !empty($resultSettings) ? intval($resultSettings[0]["value"]) : 0;
        ?>
        <!-- Navigation -->
        <?php if (isset($_SESSION["user_logged_in"]) && $_SESSION["user_logged_in"] == true): ?>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <a class="navbar-brand" href="#">
                    <i class="fad fa-users"></i>
                    <?php echo isset($_SESSION["nome"]) ? $_SESSION["nome"] : "Web - Gestionale Relazioni Emmegiemme"; ?>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../../logout.php"><i style="color:white;"
                                    class="fal fa-power-off fa-fw"></i> Esci</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="d-flex">
                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="../../index.php"><i class="fas fa-tachometer-alt fa-fw"></i>
                                    Dashboard</a>
                            </li>
                            <?php include("nav_amministrazione.php"); ?>
                            <?php include("nav_cq.php"); ?>
                            <?php include("nav_strumenti.php"); ?>
                            <?php include("nav_admin.php"); ?>
                            <?php include("nav_lavorante.php"); ?>

                        </ul>
                    </div> <!-- SIDEBAR -->
                </div>
            </div>
        <?php endif; ?>
        <!-- The End of the Header -->
    </div>