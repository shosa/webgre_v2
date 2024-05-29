<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
include BASE_PATH . '/includes/header.php';
?>

<!-- CSS per le icone desktop -->
<style>
    .desktop-icons {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
    }

    .desktop-icon {
        text-align: center;
        margin: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.3s ease-in-out;
    }

    .desktop-icon:hover {
        transform: scale(1.05);
    }

    .desktop-icon-wrapper {
        text-align: center;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .desktop-icon i {
        font-size: 48px;
    }

    .icon-label {
        margin-top: 10px;
        display: block;
    }
</style>

<?php
// Percorso del file XML
$xmlFilePath = __DIR__ . '/strumenti.xml';

// Leggi il file XML
if (file_exists($xmlFilePath)) {
    $xml = simplexml_load_file($xmlFilePath);

    // Verifica se il caricamento è riuscito
    if ($xml) {
        echo '<div id="page-wrapper">';
        echo '<div class="row">';
        echo '<div class="col-lg-6">';
        echo '<h1 class="page-header page-action-links text-left">Strumenti</h1>';
        echo '</div>';
        echo '</div>';
        echo '<hr>';
        include BASE_PATH . '/includes/flash_messages.php';

        // Itera attraverso gli elementi del file XML e crea le icone
        echo '<div class="desktop-icons">';
        foreach ($xml->file as $file) {
            echo '<a href="' . $file->filename . '" class="desktop-icon">';
            echo '<div class="desktop-icon-wrapper">';
            echo '<i class="' . $file->icon . '" style="' . $file->style . '"></i>';
            echo '<span class="icon-label">' . $file->displayname . '</span>';
            echo '</div>';
            echo '</a>';
        }
        echo '</div>';

        echo '</div>';
    } else {
        echo 'Errore nel caricamento del file XML.';
    }
} else {
    echo 'Il file XML non esiste.';
}

include BASE_PATH . '/includes/footer.php';
?>

<!-- JavaScript per la finestra modale iframe -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function () {
        $(".desktop-icon").click(function (e) {
            e.preventDefault();
            var fileUrl = $(this).attr("href");
            var displayName = $(this).find('.icon-label').text(); // Ottieni il testo dell'etichetta dall'elemento cliccato
            openFileInModal(fileUrl, displayName);
        });
        var windowHeight = $(window).height();
        var targetHeight = windowHeight * 0.9;
        function openFileInModal(fileUrl, displayName) {
            var modalContent = '<iframe src="' + fileUrl + '" style="width:100%; height:100%; border:none;"></iframe>';
            $("#fileModal").html(modalContent).dialog({
                width: '90%',
                height: targetHeight,
                resizable: true,
                modal: true,
                title: displayName // Imposta il titolo della finestra con il displayname
            });
        }
    });
</script>

<!-- Div nascosto per la finestra modale iframe -->
<div id="fileModal" style="display:none;"></div>