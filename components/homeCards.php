<div class="row">
    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardRiparazioni):
        $queryNumRiparazioni = "SELECT SUM(QTA) FROM riparazioni";
        $stmtNumRiparazioni = $pdo->query($queryNumRiparazioni);
        $numRiparazioni = $stmtNumRiparazioni->fetchColumn(); ?>
        <!-- CARD RIPARAZIONI -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-primary">
                <a href="<?php echo BASE_URL ?>/functions/riparazioni/riparazioni"
                    class="stretched-link text-decoration-none"></a>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Riparazioni attive</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo empty($numRiparazioni) ? '0' : $numRiparazioni; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fal fa-tools fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- CARD RIPARAZIONI PERSONALI -->
    <?php if (isset($_SESSION['permessi_riparazioni']) && $_SESSION['permessi_riparazioni'] == 1 && $mostraCardMyRiparazioni):
        $queryNumRiparazioniPersonali = $pdo->prepare("SELECT SUM(QTA) FROM riparazioni WHERE utente = :username");
        $queryNumRiparazioniPersonali->execute([':username' => $_SESSION['username']]);
        $numRiparazioniPersonali = $queryNumRiparazioniPersonali->fetchColumn(); ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-success">
                <a href="<?php echo BASE_URL ?>/functions/riparazioni/myRiparazioni"
                    class="stretched-link text-decoration-none"></a>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Riparazioni attive Personali</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo empty($numRiparazioniPersonali) ? '0' : $numRiparazioniPersonali; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fal fa-user-clock fa-3x text-success"></i>
                        </div>
                    </div>
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
        } catch (PDOException $e) {
            // Gestione degli errori
            echo "Errore durante l'esecuzione della query: " . $e->getMessage();
            $num_cq_records = 0; // Imposta il numero di record a 0 in caso di errore
        } ?>
        <!-- CARD CQ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-info">
                <a href="<?php echo BASE_URL ?>/functions/quality/detail?date=<?php echo $data_oggi ?>"
                    class="stretched-link text-decoration-none"></a>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Test
                                eseguiti oggi</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo empty($num_cq_records) ? '0' : $num_cq_records; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fal fa-vials fa-3x text-info"></i>
                        </div>
                    </div>
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
            $totaleTaglio = $totali['TOTALITAGLIO'];
            $totaleOrlatura = $totali['TOTALIORLATURA'];
            $totaleMontaggio = $totali['TOTALIMONTAGGIO'];
        } catch (PDOException $e) {
            // Gestione degli errori
            echo "Errore durante l'esecuzione della query: " . $e->getMessage();
            $totaleTaglio = $totaleOrlatura = $totaleMontaggio = '0'; // Imposta i totali a 0 in caso di errore
        }
        ?>
        <!-- CARD CQ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-danger">
                <a href="<?php echo BASE_URL ?>/functions/production/produzione?month=<?php echo $mese_odierno; ?>&day=<?php echo $giorno_odierno; ?>"
                    class="stretched-link text-decoration-none"></a>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Totali Produzione (Oggi)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                T: <?php echo $totaleTaglio; ?> |
                                O: <?php echo $totaleOrlatura; ?> |
                                M: <?php echo $totaleMontaggio; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fal fa-chart-bar fa-3x text-danger"></i>
                        </div>
                    </div>
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
            $sqlTotali = "SELECT SUM(TOTALITAGLIO), SUM(TOTALIORLATURA), SUM(TOTALIMONTAGGIO) FROM prod_mesi WHERE MESE = ? ";
            $stmtTotali = $pdo->prepare($sqlTotali);
            $stmtTotali->execute([$mese_odierno]);
            // Ottieni il risultato della query
            $totali = $stmtTotali->fetch(PDO::FETCH_ASSOC);
            $totaleTaglio = $totali['SUM(TOTALITAGLIO)'];
            $totaleOrlatura = $totali['SUM(TOTALIORLATURA)'];
            $totaleMontaggio = $totali['SUM(TOTALIMONTAGGIO)'];
        } catch (PDOException $e) {
            // Gestione degli errori
            echo "Errore durante l'esecuzione della query: " . $e->getMessage();
            $totaleTaglio = $totaleOrlatura = $totaleMontaggio = '0'; // Imposta i totali a 0 in caso di errore
        }
        ?>
        <!-- CARD CQ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 card-hover rounded border-bottom-warning">
                <a href="<?php echo BASE_URL ?>/functions/production/calendario"
                    class="stretched-link text-decoration-none"></a>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Totali Produzione (<?php echo $mese_odierno; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                T: <?php echo $totaleTaglio; ?> |
                                O: <?php echo $totaleOrlatura; ?> |
                                M: <?php echo $totaleMontaggio; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fal fa-chart-bar fa-3x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>