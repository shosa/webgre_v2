<?php
if ($_SESSION["admin_type"] !== "operatore"): ?>
    <?php if ($_SESSION["admin_type"] !== "lavorante"):
        $queryCountGruppi =
            "SELECT COUNT(DISTINCT gruppo) AS countGruppi FROM temp_dati_gruppi";
        $resultCountGruppi = $db->rawQuery(
            $queryCountGruppi
        );
        $countGruppi = !empty(
            $resultCountGruppi
        )
            ? $resultCountGruppi[0][
                "countGruppi"
            ]
            : 0;
        ?>
        <li class="nav-item">
            <a class="nav-link" href="../../functions/tools/tools.php"> <i class="fad fa-toolbox"></i> Strumenti</a>
        </li>

    <?php endif; ?>
<?php endif; ?>