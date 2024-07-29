<?php



session_start();

require_once '../../config/config.php';

require_once BASE_PATH . '/components/auth_validate.php';

require_once '../../utils/log_utils.php';



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

                    <?php include (BASE_PATH . "/utils/alerts.php"); ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">

                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>

                    </div>

                    <ol class="breadcrumb mb-4">

                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>

                        <li class="breadcrumb-item active">Tracking</li>

                    </ol>

                    <!-- Griglia di 6 bottoni -->

                    <div class="row text-center my-5">

                        <div class="col-md-4 mb-4">

                            <a href="multiSearch"
                                class="btn btn-success btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-magnifying-glass-plus fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Associa per Ricerca</b></h5>

                                    <i>Utilizza i campi di ricerca per selezionare i cartellini da associare ai

                                        lotti</i>

                                </div>

                            </a>

                        </div>

                        <div class="col-md-4 mb-4">

                            <a href="orderSearch"
                                class="btn btn-success btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-link fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Associa per Cartellini</b></h5>

                                    <i>Inserisci manualmente i singoli da cartellini da associare ai lotti</i>

                                </div>

                            </a>

                        </div>


                        <div class="col-md-4 mb-4">

                            <a href="treeView"
                                class="btn btn-info btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-folder-tree fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Albero Dettagli</b></h5>

                                    <i>Visualizza e Modifica la mappatura dei lotti / cartellini</i>

                                </div>

                            </a>

                        </div>




                        <div class="col-md-4 mb-4">

                            <a href="lotDetailManager"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-file-invoice fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h4><b>Dettagli Lotti</b></h4>

                                    <i>Aggiungi i riferimenti di consegna per i lotti memorizzati</i>

                                </div>

                            </a>

                        </div>

                        <div class="col-md-4 mb-4">

                            <a href="skuManager"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-barcode fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Dettagli SKU</b></h5>

                                    <i>Aggiungi i riferimenti dell'articolo cliente (SKU)</i>

                                </div>

                            </a>

                        </div>
                        <div class="col-md-4 mb-4">

                            <a href="orderDateManager"
                                class="btn btn-dark btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-calendar fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Dettagli Ordini/Date</b></h5>

                                    <i>Aggiungi i riferimenti della data degli ordini.</i>

                                </div>

                            </a>

                        </div>


                        <div class="col-md-4 mb-4">

                            <a href="makePackingList"
                                class="btn btn-warning btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-list fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Packing List</b></h5>

                                    <i>Genera un dettaglio dei lotti utilizzati per i cartellini richiesti</i>

                                </div>

                            </a>

                        </div>


                        <div class="col-md-4 mb-4">

                            <a href="makeFiches"
                                class="btn btn-indigo btn-lg btn-block shadow d-flex align-items-center justify-content-center"
                                style="height: 150px;">

                                <i class="fal fa-tags fa-3x mr-3"></i>

                                <div class="text-left">

                                    <h5><b>Stampa Fiches</b></h5>

                                    <i>Genera le fiches da stampare su i retro dei Cartellini.</i>

                                </div>

                            </a>

                        </div>




                    </div>

                </div>

            </div>

            <?php include_once BASE_PATH . '/components/scripts.php'; ?>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>

        </div>

    </div>

</body>