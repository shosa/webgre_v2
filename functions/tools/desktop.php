<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .desktop {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .file-icon {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }

        .file-icon:hover {
            transform: scale(1.05);
        }

        #fileModal {
            display: none;
        }

        #fileIframe {
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>

    <div class="container desktop">

        <div class="row">

            <?php
            $directory = __DIR__; // La directory corrente, puoi cambiarla con il percorso della tua cartella
            
            // Leggi il file XML
            $xml = simplexml_load_file('strumenti.xml');

            // Mostra un'icona per ogni file nel file XML
            foreach ($xml->file as $fileInfo) {
                $filename = (string) $fileInfo->filename;
                $iconClass = (string) $fileInfo->icon;
                $displayName = (string) $fileInfo->displayname;

                echo '<div class="col-md-3">';
                echo '<div class="file-icon" onclick="openWindow(\'' . $filename . '\', \'' . $displayName . '\')">';
                echo '<i class="' . $iconClass . ' fa-3x"></i>'; // Utilizza l'icona Font Awesome specificata nel file XML
                echo '<p>' . $displayName . '</p>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

    </div>

    <!-- Finestra modale -->
    <div id="fileModal" title="Contenuto del file">
        <iframe id="fileIframe" src="" frameborder="0"></iframe>
    </div>

    <script>
        function openWindow(file, displayName) {
            // Imposta l'URL del file nell'iframe
            document.getElementById('fileIframe').src = file;

            // Calcola l'altezza del 70% della finestra
            var windowHeight = $(window).height();
            var targetHeight = windowHeight * 0.7;

            // Apri la finestra modale con jQuery UI e imposta il titolo e l'altezza
            var modal = $('#fileModal').dialog({
                width: '70%',
                height: targetHeight,
                resizable: true,
                modal: true,
                title: displayName, // Imposta il titolo della finestra con il displayname
                resize: function (event, ui) {
                    // Aggiorna l'altezza dell'iframe durante il ridimensionamento della finestra modale
                    var iframeHeight = modal.height() - modal.find('.ui-dialog-titlebar').outerHeight();
                    $('#fileIframe').height(iframeHeight);
                }
            });
        }
    </script>

</body>

</html>