<fieldset>



    <input type="hidden" id="lancio_id" name="lancio_id" value="<?php echo $dati_lanci[0]['lancio']; ?>">

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr style="background-color:#337ab7; color:white;">
                    <th width="5%">Immagine</th>
                    <th width="20%">Articolo</th>
                    <th width="5%">Paia</th>
                    <th width="20%">Avanzamento</th>
                    <th width="30%" style="text-align:center;"><i class="fad fa-comments-alt"></i></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($dati_lanci as $row): ?>
                    <tr>
                        <input type="hidden" id="riga_id-<?php echo $row['ID'] ?>" name="riga_id"
                            value="<?php echo $row['ID'] ?>">
                        <td style="vertical-align: middle; text-align: center;">
                            <?php
                            $imageSrc = empty($row['path_to_image']) ? '../../' . '/src/img/default.jpg' : '../../' . $row['path_to_image'];
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc) ?>" alt="Immagine"
                                style="width: 80px; height: 80px; border: 1px solid lightgrey;">
                        </td>
                        <td style="vertical-align: middle;"><b>
                                <?php echo $row['nome_completo'] ?>
                            </b></td>
                        <td style="vertical-align: middle; text-align:center">
                            <?php echo $row['paia'] ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center; position: relative;color:white;
                                <?php
                                switch ($row['avanzamento']) {
                                    case 'NESSUNO':
                                        echo 'background-color: #54585e;'; // Imposta il colore rosso per TAGLIO
                                        break;
                                    case 'TAGLIO':
                                        echo 'background-color: #8bc8e0;'; // Imposta il colore rosso per TAGLIO
                                        break;
                                    case 'PREPARAZIONE':
                                        echo 'background-color: #e0b88b;'; // Imposta il colore viola per PREPARAZIONE
                                        break;
                                    case 'ORLATURA':
                                        echo 'background-color: #cc8be0;'; // Imposta il colore giallo per ORLATURA
                                        break;
                                    case 'SPEDIZIONE':
                                        echo 'background-color: #8be096;'; // Imposta il colore verde per SPEDIZIONE
                                        break;
                                    default:
                                        // Imposta uno stile di fallback qui, se necessario
                                        break;
                                }
                                ?>
                            ">
                            <?php
                            echo '<span style="font-size:15pt;"><i>' . $row['avanzamento'] . '</i></span>';
                            ?>
                        </td>


                        <td>
                            <div>
                                <span id="noteText-<?php echo $row['ID'] ?>">
                                    <?php echo $row['note'] ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                    <td colspan="5">
                        <?php $progressValue = ($row['taglio'] + $row['preparazione'] + $row['orlatura'] + $row['spedizione']) * 25; ?>
                        <div class="progress" style="height: 100%; border: solid 1pt black;">
                            <div class="progress-bar progress-bar-info" role="progressbar"
                                style="font-size:10pt; min-width: 2em; width: <?php echo $progressValue; ?>%;"
                                aria-valuenow="<?php echo $progressValue; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $progressValue; ?>%
                            </div>
                        </div>
                    </td>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</fieldset>

<!-- JavaScript -->
<script>
</script>