<div class="row">
    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardRiparazioni):
        $queryNumRiparazioni = "SELECT SUM(QTA) FROM riparazioni";
        $stmtNumRiparazioni = $pdo->query($queryNumRiparazioni);
        $numRiparazioni = $stmtNumRiparazioni->fetchColumn();

        // Query per ottenere il confronto con il mese precedente
        $queryMesePrec = "SELECT COUNT(*) FROM riparazioni WHERE MONTH(data) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        $stmtMesePrec = $pdo->query($queryMesePrec);
        $riparazioniMesePrec = $stmtMesePrec->fetchColumn();

        $queryMeseCorr = "SELECT COUNT(*) FROM riparazioni WHERE MONTH(data) = MONTH(CURRENT_DATE)";
        $stmtMeseCorr = $pdo->query($queryMeseCorr);
        $riparazioniMeseCorr = $stmtMeseCorr->fetchColumn();

        // Calcola la variazione percentuale
        $variazione = 0;
        $classeVariazione = 'text-success';
        $iconaVariazione = 'fa-arrow-up';

        if ($riparazioniMesePrec > 0) {
            $variazione = round((($riparazioniMeseCorr - $riparazioniMesePrec) / $riparazioniMesePrec) * 100, 1);
            if ($variazione < 0) {
                $classeVariazione = 'text-danger';
                $iconaVariazione = 'fa-arrow-down';
                $variazione = abs($variazione);
            } else if ($variazione == 0) {
                $classeVariazione = 'text-muted';
                $iconaVariazione = 'fa-equals';
            }
        }
        ?>
        <!-- CARD RIPARAZIONI -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-primary" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary-light text-primary mr-3">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h6 class="font-weight-bold text-primary text-uppercase mb-0">
                                Riparazioni attive
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza tutte
                                </a>
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/riparazioni/add_step1">
                                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Aggiungi nuova
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo empty($numRiparazioni) ? '0' : number_format($numRiparazioni, 0, ',', '.'); ?>
                        </div>
                        <div class="mt-1 <?php echo $classeVariazione; ?> small">
                            <i class="fas <?php echo $iconaVariazione; ?> mr-1"></i>
                            <?php echo abs($variazione); ?>% rispetto al mese precedente
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Aggiornato: <?php echo date('d/m/Y H:i'); ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni" class="small text-primary">
                        Dettagli <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CARD RIPARAZIONI PERSONALI -->
    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardMyRiparazioni):
        $queryNumRiparazioniPersonali = $pdo->prepare("SELECT SUM(QTA) FROM riparazioni WHERE utente = :username");
        $queryNumRiparazioniPersonali->execute([':username' => $_SESSION['username']]);
        $numRiparazioniPersonali = $queryNumRiparazioniPersonali->fetchColumn();

        // Poiché non c'è un campo stato (le riparazioni completate vengono eliminate)
        // mostreremo solo il numero totale di riparazioni attive
        // e aggiungeremo un'informazione sul numero di riparazioni gestite nell'ultimo mese
    
        $queryRiparazioniMensili = $pdo->prepare("SELECT COUNT(*) FROM riparazioni WHERE utente = :username AND data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $queryRiparazioniMensili->execute([':username' => $_SESSION['username']]);
        $riparazioniMensili = $queryRiparazioniMensili->fetchColumn();
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-success" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success-light text-success mr-3">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <h6 class="font-weight-bold text-success text-uppercase mb-0">
                                Riparazioni Personali
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink2">
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/riparazioni/myRiparazioni">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza tutte
                                </a>
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/riparazioni/add_step1">
                                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Aggiungi nuova
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo empty($numRiparazioniPersonali) ? '0' : number_format($numRiparazioniPersonali, 0, ',', '.'); ?>
                        </div>
                        <div class="mt-1 small text-success">
                            <i class="fas fa-clipboard-list mr-1"></i>
                            Riparazioni attive
                        </div>

                        <!-- Informazioni aggiuntive sulle riparazioni mensili -->
                        <?php if ($riparazioniMensili > 0): ?>
                            <div class="mt-3 px-3 py-2 rounded-lg bg-light d-flex justify-content-between align-items-center">
                                <div class="small text-muted">Nell'ultimo mese:</div>
                                <div class="font-weight-bold"><?php echo $riparazioniMensili; ?> riparazioni</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Aggiornato: <?php echo date('d/m/Y H:i'); ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/riparazioni/myRiparazioni" class="small text-success">
                        Dettagli <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CARD CQ DI OGGI -->
    <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1 && $mostraCardQuality):
        try {
            // Query per contare i record con la data odierna
            $sql = "SELECT COUNT(*) AS num_records FROM cq_records WHERE data = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_oggi]);
            // Ottieni il risultato della query
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $num_cq_records = $row['num_records'];

            // Query per i test superati/falliti
            $sqlSuperati = "SELECT COUNT(*) AS superati FROM cq_records WHERE data = ? AND risultato = 'OK'";
            $stmtSuperati = $pdo->prepare($sqlSuperati);
            $stmtSuperati->execute([$data_oggi]);
            $testSuperati = $stmtSuperati->fetch(PDO::FETCH_ASSOC)['superati'];

            $sqlFalliti = "SELECT COUNT(*) AS falliti FROM cq_records WHERE data = ? AND risultato = 'KO'";
            $stmtFalliti = $pdo->prepare($sqlFalliti);
            $stmtFalliti->execute([$data_oggi]);
            $testFalliti = $stmtFalliti->fetch(PDO::FETCH_ASSOC)['falliti'];

            // Calcola la percentuale di successo
            $percentualeSuccesso = 0;
            if ($num_cq_records > 0) {
                $percentualeSuccesso = round(($testSuperati / $num_cq_records) * 100);
            }
        } catch (PDOException $e) {
            // Gestione degli errori
            $num_cq_records = 0;
            $testSuperati = 0;
            $testFalliti = 0;
            $percentualeSuccesso = 0;
        } ?>
        <!-- CARD CQ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-info" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info-light text-info mr-3">
                                <i class="fas fa-vials"></i>
                            </div>
                            <h6 class="font-weight-bold text-info text-uppercase mb-0">
                                Test Qualità Oggi
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink3" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink3">
                                <a class="dropdown-item"
                                    href="<?php echo BASE_URL ?>/functions/quality/detail?date=<?php echo $data_oggi ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza dettagli
                                </a>
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/quality/addRecord">
                                    <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Aggiungi test
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo empty($num_cq_records) ? '0' : number_format($num_cq_records, 0, ',', '.'); ?>
                        </div>
                        <?php if ($num_cq_records > 0): ?>
                            <div class="mt-3 d-flex justify-content-center">
                                <div class="px-3 py-1 rounded-lg mr-2 bg-success-light">
                                    <span class="font-weight-bold text-success"><?php echo $testSuperati; ?></span>
                                    <span class="small text-muted ml-1">OK</span>
                                </div>
                                <div class="px-3 py-1 rounded-lg bg-danger-light">
                                    <span class="font-weight-bold text-danger"><?php echo $testFalliti; ?></span>
                                    <span class="small text-muted ml-1">KO</span>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: <?php echo $percentualeSuccesso; ?>%"
                                    aria-valuenow="<?php echo $percentualeSuccesso; ?>" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Data: <?php echo $data_oggi; ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/quality/detail?date=<?php echo $data_oggi ?>"
                        class="small text-info">
                        Dettagli <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- CARD CQ HERMES DI OGGI -->
    <?php if (isset($_SESSION['permessi_cq']) && $_SESSION['permessi_cq'] == 1 && $mostraCardQuality):
        try {
            // Query per contare i record con la data odierna
            $sql = "SELECT COUNT(*) AS num_records FROM cq_hermes_records WHERE data = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_oggi]);
            // Ottieni il risultato della query
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $num_cq_hermes_records = $row['num_records'];

            // Query per i test superati/falliti
            $sqlHermesSuperati = "SELECT COUNT(*) AS superati FROM cq_hermes_records WHERE data = ? AND risultato = 'OK'";
            $stmtHermesSuperati = $pdo->prepare($sqlHermesSuperati);
            $stmtHermesSuperati->execute([$data_oggi]);
            $testHermesSuperati = $stmtHermesSuperati->fetch(PDO::FETCH_ASSOC)['superati'];

            $sqlHermesFalliti = "SELECT COUNT(*) AS falliti FROM cq_hermes_records WHERE data = ? AND risultato = 'KO'";
            $stmtHermesFalliti = $pdo->prepare($sqlHermesFalliti);
            $stmtHermesFalliti->execute([$data_oggi]);
            $testHermesFalliti = $stmtHermesFalliti->fetch(PDO::FETCH_ASSOC)['falliti'];

            // Calcola la percentuale di successo
            $percentualeHermesSuccesso = 0;
            if ($num_cq_Hermes_records > 0) {
                $percentualeHermesSuccesso = round(($testHermesSuperati / $num_cq_records) * 100);
            }
        } catch (PDOException $e) {
            // Gestione degli errori
            $num_cq_Hermes_records = 0;
            $testHermesSuperati = 0;
            $testHermesFalliti = 0;
            $percentualeHermesSuccesso = 0;
        } ?>
        <!-- CARD CQ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-orange" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-orange text-white mr-3">
                                <i class="fas fa-vials"></i>
                            </div>
                            <h6 class="font-weight-bold text-orange text-uppercase mb-0">
                                Test Hermes Oggi
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink3" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink3">
                              

                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo empty($num_cq_Hermes_records) ? '0' : number_format($num_cq_Hermes_records, 0, ',', '.'); ?>
                        </div>
                        <?php if ($num_cq_Hermes_records > 0): ?>
                            <div class="mt-3 d-flex justify-content-center">
                                <div class="px-3 py-1 rounded-lg mr-2 bg-success-light">
                                    <span class="font-weight-bold text-success"><?php echo $testHermesSuperati; ?></span>
                                    <span class="small text-muted ml-1">OK</span>
                                </div>
                                <div class="px-3 py-1 rounded-lg bg-danger-light">
                                    <span class="font-weight-bold text-danger"><?php echo $testHermesFalliti; ?></span>
                                    <span class="small text-muted ml-1">KO</span>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: <?php echo $percentualeHermesSuccesso; ?>%"
                                    aria-valuenow="<?php echo $percentualeHermesSuccesso; ?>" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Data: <?php echo $data_oggi; ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/quality/manageHermes"
                        class="small text-info">
                        Vedi <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CARD PRODUZIONE DI OGGI -->
    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1 && $mostraCardProduzione):
        try {
            $giorno_odierno = date('d'); // Ottieni il giorno odierno
            $mese_odierno_inglese = date('F'); // Ottieni il mese odierno in formato testo (es. January)
    
            // Array di conversione da inglese a italiano per i mesi
            $mesi = array(
                "January" => "GENNAIO",
                "February" => "FEBBRAIO",
                "March" => "MARZO",
                "April" => "APRILE",
                "May" => "MAGGIO",
                "June" => "GIUGNO",
                "July" => "LUGLIO",
                "August" => "AGOSTO",
                "September" => "SETTEMBRE",
                "October" => "OTTOBRE",
                "November" => "NOVEMBRE",
                "December" => "DICEMBRE"
            );
            // Traduci il mese in italiano
            $mese_odierno = $mesi[$mese_odierno_inglese];
            $sqlTotali = "SELECT TOTALITAGLIO, TOTALIORLATURA, TOTALIMONTAGGIO FROM prod_mesi WHERE MESE = ? AND GIORNO = ?";
            $stmtTotali = $pdo->prepare($sqlTotali);
            $stmtTotali->execute([$mese_odierno, $giorno_odierno]);
            // Ottieni il risultato della query
            $totali = $stmtTotali->fetch(PDO::FETCH_ASSOC);
            $totaleTaglio = $totali['TOTALITAGLIO'] ?? 0;
            $totaleOrlatura = $totali['TOTALIORLATURA'] ?? 0;
            $totaleMontaggio = $totali['TOTALIMONTAGGIO'] ?? 0;

            // Calcola il totale complessivo
            $totaleComplessivo = $totaleTaglio + $totaleOrlatura + $totaleMontaggio;

            // Calcola le percentuali
            $percTaglio = ($totaleComplessivo > 0) ? round(($totaleTaglio / $totaleComplessivo) * 100) : 0;
            $percOrlatura = ($totaleComplessivo > 0) ? round(($totaleOrlatura / $totaleComplessivo) * 100) : 0;
            $percMontaggio = ($totaleComplessivo > 0) ? round(($totaleMontaggio / $totaleComplessivo) * 100) : 0;

        } catch (PDOException $e) {
            // Gestione degli errori
            $totaleTaglio = $totaleOrlatura = $totaleMontaggio = $totaleComplessivo = 0;
            $percTaglio = $percOrlatura = $percMontaggio = 0;
        }
        ?>
        <!-- CARD PRODUZIONE OGGI -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-danger" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-danger-light text-danger mr-3">
                                <i class="fas fa-industry"></i>
                            </div>
                            <h6 class="font-weight-bold text-danger text-uppercase mb-0">
                                Produzione Oggi
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink4" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink4">
                                <a class="dropdown-item"
                                    href="<?php echo BASE_URL ?>/functions/production/produzione?month=<?php echo $mese_odierno; ?>&day=<?php echo $giorno_odierno; ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza dettagli
                                </a>
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/production/calendario">
                                    <i class="fas fa-calendar fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza calendario
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($totaleComplessivo, 0, ',', '.'); ?>
                            </div>
                            <div class="small text-muted">
                                Totale produzione
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div class="text-center p-2">
                                <div class="h5 mb-0 font-weight-bold text-danger">
                                    <?php echo number_format($totaleTaglio, 0, ',', '.'); ?></div>
                                <small class="text-muted font-weight-bold d-block mt-1">TAGLIO</small>
                                <?php if ($totaleComplessivo > 0): ?>
                                    <div class="progress progress-sm mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-danger" style="width: <?php echo $percTaglio; ?>%"></div>
                                    </div>
                                    <div class="small mt-1"><?php echo $percTaglio; ?>%</div>
                                <?php endif; ?>
                            </div>
                            <div class="text-center p-2">
                                <div class="h5 mb-0 font-weight-bold text-primary">
                                    <?php echo number_format($totaleOrlatura, 0, ',', '.'); ?></div>
                                <small class="text-muted font-weight-bold d-block mt-1">ORLATURA</small>
                                <?php if ($totaleComplessivo > 0): ?>
                                    <div class="progress progress-sm mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $percOrlatura; ?>%"></div>
                                    </div>
                                    <div class="small mt-1"><?php echo $percOrlatura; ?>%</div>
                                <?php endif; ?>
                            </div>
                            <div class="text-center p-2">
                                <div class="h5 mb-0 font-weight-bold text-success">
                                    <?php echo number_format($totaleMontaggio, 0, ',', '.'); ?></div>
                                <small class="text-muted font-weight-bold d-block mt-1">MONTAGGIO</small>
                                <?php if ($totaleComplessivo > 0): ?>
                                    <div class="progress progress-sm mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $percMontaggio; ?>%">
                                        </div>
                                    </div>
                                    <div class="small mt-1"><?php echo $percMontaggio; ?>%</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Data: <?php echo $data_oggi; ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/production/produzione?month=<?php echo $mese_odierno; ?>&day=<?php echo $giorno_odierno; ?>"
                        class="small text-danger">
                        Dettagli <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CARD PRODUZIONE DEL MESE -->
    <?php if (isset($_SESSION['permessi_produzione']) && $_SESSION['permessi_produzione'] == 1 && $mostraCardProduzioneMese):
        try {
            $mese_odierno_inglese = date('F'); // Ottieni il mese odierno in formato testo (es. January)
            // Array di conversione da inglese a italiano per i mesi
            $mesi = array(
                "January" => "GENNAIO",
                "February" => "FEBBRAIO",
                "March" => "MARZO",
                "April" => "APRILE",
                "May" => "MAGGIO",
                "June" => "GIUGNO",
                "July" => "LUGLIO",
                "August" => "AGOSTO",
                "September" => "SETTEMBRE",
                "October" => "OTTOBRE",
                "November" => "NOVEMBRE",
                "December" => "DICEMBRE"
            );
            // Traduci il mese in italiano
            $mese_odierno = $mesi[$mese_odierno_inglese];
            $sqlTotali = "SELECT SUM(TOTALITAGLIO) as tot_taglio, SUM(TOTALIORLATURA) as tot_orlatura, SUM(TOTALIMONTAGGIO) as tot_montaggio FROM prod_mesi WHERE MESE = ? ";
            $stmtTotali = $pdo->prepare($sqlTotali);
            $stmtTotali->execute([$mese_odierno]);
            // Ottieni il risultato della query
            $totali = $stmtTotali->fetch(PDO::FETCH_ASSOC);
            $totaleTaglio = $totali['tot_taglio'] ?? 0;
            $totaleOrlatura = $totali['tot_orlatura'] ?? 0;
            $totaleMontaggio = $totali['tot_montaggio'] ?? 0;

            // Calcola il totale complessivo
            $totaleComplessivo = $totaleTaglio + $totaleOrlatura + $totaleMontaggio;

            // Ottieni la media giornaliera
            $giornoAttuale = date('d');
            $mediaGiornaliera = ($giornoAttuale > 0) ? round($totaleComplessivo / $giornoAttuale) : 0;

            // Confronto con il mese precedente
            $mesePrecedente = date('F', strtotime('-1 month'));
            $mesePrecedenteITA = $mesi[$mesePrecedente];
            $sqlTotaliPrecedente = "SELECT SUM(TOTALITAGLIO) + SUM(TOTALIORLATURA) + SUM(TOTALIMONTAGGIO) as totale FROM prod_mesi WHERE MESE = ? ";
            $stmtTotaliPrecedente = $pdo->prepare($sqlTotaliPrecedente);
            $stmtTotaliPrecedente->execute([$mesePrecedenteITA]);
            $totalePrecedente = $stmtTotaliPrecedente->fetchColumn() ?? 0;

            // Calcola la variazione percentuale
            $variazione = 0;
            $classeVariazione = 'text-success';
            $iconaVariazione = 'fa-arrow-up';

            if ($totalePrecedente > 0) {
                $variazione = round((($totaleComplessivo - $totalePrecedente) / $totalePrecedente) * 100, 1);
                if ($variazione < 0) {
                    $classeVariazione = 'text-danger';
                    $iconaVariazione = 'fa-arrow-down';
                    $variazione = abs($variazione);
                } else if ($variazione == 0) {
                    $classeVariazione = 'text-muted';
                    $iconaVariazione = 'fa-equals';
                }
            }

        } catch (PDOException $e) {
            // Gestione degli errori
            $totaleTaglio = $totaleOrlatura = $totaleMontaggio = $totaleComplessivo = $mediaGiornaliera = 0;
            $variazione = 0;
            $classeVariazione = 'text-muted';
            $iconaVariazione = 'fa-equals';
        }
        ?>
        <!-- CARD PRODUZIONE MESE -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover rounded-lg overflow-hidden">
                <div class="card-progress-bar bg-warning" style="height: 4px;"></div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning-light text-warning mr-3">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h6 class="font-weight-bold text-warning text-uppercase mb-0">
                                Produzione <?php echo $mese_odierno; ?>
                            </h6>
                        </div>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink5" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink5">
                                <a class="dropdown-item" href="<?php echo BASE_URL ?>/functions/production/calendario">
                                    <i class="fas fa-calendar fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Visualizza calendario
                                </a>

                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($totaleComplessivo, 0, ',', '.'); ?>
                        </div>
                        <div class="mt-1 <?php echo $classeVariazione; ?> small">
                            <i class="fas <?php echo $iconaVariazione; ?> mr-1"></i>
                            <?php echo abs($variazione); ?>% rispetto a <?php echo $mesePrecedenteITA; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <div class="text-center flex-fill">
                                <div class="small text-muted mb-1">TAGLIO</div>
                                <div class="h5 mb-0 font-weight-bold text-danger">
                                    <?php echo number_format($totaleTaglio, 0, ',', '.'); ?></div>
                            </div>
                            <div class="text-center flex-fill">
                                <div class="small text-muted mb-1">ORLATURA</div>
                                <div class="h5 mb-0 font-weight-bold text-primary">
                                    <?php echo number_format($totaleOrlatura, 0, ',', '.'); ?></div>
                            </div>
                            <div class="text-center flex-fill">
                                <div class="small text-muted mb-1">MONTAGGIO</div>
                                <div class="h5 mb-0 font-weight-bold text-success">
                                    <?php echo number_format($totaleMontaggio, 0, ',', '.'); ?></div>
                            </div>
                        </div>

                        <div class="mt-3 px-3 py-2 rounded-lg bg-light d-flex justify-content-between align-items-center">
                            <div class="small text-muted">Media giornaliera:</div>
                            <div class="font-weight-bold"><?php echo number_format($mediaGiornaliera, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Dati <?php echo $mese_odierno; ?></span>
                    <a href="<?php echo BASE_URL ?>/functions/production/calendario" class="small text-warning">
                        Dettagli <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Stili aggiuntivi per le card contatori migliorate */
    .card-hover {
        transition: all 0.3s ease;
        border: none !important;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-progress-bar {
        width: 0;
        animation: progress-animation 1s ease-in-out forwards;
    }

    @keyframes progress-animation {
        0% {
            width: 0;
        }

        100% {
            width: 100%;
        }
    }

    /* Colori di sfondo per le icone */
    .bg-primary-light {
        background-color: rgba(78, 115, 223, 0.1);
    }

    .bg-success-light {
        background-color: rgba(28, 200, 138, 0.1);
    }

    .bg-info-light {
        background-color: rgba(54, 185, 204, 0.1);
    }

    .bg-warning-light {
        background-color: rgba(246, 194, 62, 0.1);
    }

    .bg-danger-light {
        background-color: rgba(231, 74, 59, 0.1);
    }

    /* Dropdown menu personalizzato */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    .dropdown-item:hover {
        background-color: #f8f9fc;
    }

    /* Miglioramenti per i progressi bar */
    .progress {
        background-color: #eaecf4;
        height: 0.5rem;
        border-radius: 0.25rem;
    }

    .rounded-lg {
        border-radius: 0.5rem !important;
    }

    .bg-light {
        background-color: #f8f9fc !important;
    }
</style>