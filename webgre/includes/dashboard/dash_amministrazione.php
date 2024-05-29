<div class="col-lg-12">
    <h1 class="page-header page-action-links text-left">Collegamenti veloci:</h1>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card bg-primary text-white">
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <i class="fad fa-list-ol fa-5x"></i>
                </div>
                <div class="col-9 text-right">
                    <div class="h1">
                        <b>
                            <?php echo $numRiparazioni; ?>
                        </b>
                    </div>
                    <div>Riparazioni</div>
                </div>
            </div>
        </div>
        <a href="../../functions/riparazioni/riparazioni.php" class="card-footer text-white bg-white text-primary">
            <span class="float-left ">Apri Elenco</span>
            <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
            <div class="clearfix"></div>
        </a>
    </div>
</div>
<?php if ($moduloProduzioneValue == 1): ?>
    <div class="col-lg-3 col-md-6">
        <div class="card bg-primary text-white bg-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        <i class="fad fa-shipping-timed fa-5x"></i>
                    </div>
                    <div class="col-9 text-right">
                        <div class="h1">
                            <b>
                                <?php echo !empty($numDaCompletare) ? $numDaCompletare : 0; ?>
                            </b>
                        </div>
                        <div>Da lanci da completare</div>
                    </div>
                </div>
            </div>
            <a href="../../functions/lanci/wait_lanci.php" class="card-footer text-white bg-white text-warning">
                <span class="float-left">Apri Elenco</span>
                <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                <div class="clearfix"></div>
            </a>
        </div>
    </div>
<?php endif; ?>