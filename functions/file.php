<?php
session_start();
require_once '../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/components/header.php';

// Define the upload directory
$uploadDir = BASE_PATH . '/uploads/hermes/';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <div class="container-fluid">
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">File Manager</h1>
                        <div>
                            <button class="btn btn-sm btn-primary shadow-sm mr-2" id="uploadFileBtn">
                                <i class="fas fa-upload fa-sm text-white-50"></i> Upload File
                            </button>
                            <button class="btn btn-sm btn-info shadow-sm" id="refreshFiles">
                                <i class="fas fa-sync fa-sm text-white-50"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">File Manager</li>
                    </ol>
                    <div class="row">
                        <div class="col-xl-3 col-lg-3">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Cartella Corrente</h6>
                                    <span class="badge badge-primary p-2" id="total-files">0 Files</span>
                                </div>
                                <div class="card-body">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="fileSearch"
                                            placeholder="Cerca file...">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                                                title="Pulisci ricerca">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="file-list-container">
                                        <ul id="files-list" class="list-group"></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Informazioni Cartella</h6>
                                </div>
                                <div class="card-body">
                                    <div id="folder-info">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-folder mr-2"></i>Percorso:</span>
                                            <span class="font-weight-bold" id="folder-path">/uploads/hermes/</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-hdd mr-2"></i>Spazio Utilizzato:</span>
                                            <span id="total-size">0 MB</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fas fa-file-alt mr-2"></i>Tipi File:</span>
                                            <span id="file-types">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-9">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <span id="current-file-name">Seleziona un file</span>
                                    </h6>
                                    <div class="ml-auto">
                                        <button type="button" id="downloadFileBtn"
                                            class="btn btn-success btn-sm d-none mr-2">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button type="button" id="deleteFileBtn" class="btn btn-danger btn-sm d-none">
                                            <i class="fas fa-trash"></i> Elimina
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="nav nav-tabs" id="fileTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="preview-tab" data-toggle="tab"
                                                href="#preview" role="tab">
                                                <i class="fas fa-eye mr-1"></i>Anteprima
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="details-tab" data-toggle="tab" href="#details"
                                                role="tab">
                                                <i class="fas fa-info-circle mr-1"></i>Dettagli
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="options-tab" data-toggle="tab" href="#options"
                                                role="tab">
                                                <i class="fas fa-tools mr-1"></i>Opzioni
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content p-3" id="tabContent">
                                        <div class="tab-pane fade show active" id="preview" role="tabpanel">
                                            <div id="file-preview" class="text-center py-5 text-muted">
                                                <i class="fas fa-file fa-3x mb-3"></i>
                                                <p>Seleziona un file per visualizzare l'anteprima</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="details" role="tabpanel">
                                            <div id="file-details">
                                                <div class="text-center py-5 text-muted">
                                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                                    <p>Seleziona un file per visualizzare i dettagli</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="options" role="tabpanel">
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="card border-primary mb-3">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-file-export mr-1"></i>Esportazione
                                                        </div>
                                                        <div class="card-body">
                                                            <button id="copyLinkBtn"
                                                                class="btn btn-outline-primary btn-block mb-2">
                                                                <i class="fas fa-link mr-1"></i>Copia Link
                                                            </button>
                                                            <button id="shareFileBtn"
                                                                class="btn btn-outline-info btn-block">
                                                                <i class="fas fa-share-alt mr-1"></i>Condividi
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border-warning mb-3">
                                                        <div class="card-header bg-warning text-white">
                                                            <i class="fas fa-shield-alt mr-1"></i>Sicurezza
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="makePrivateToggle">
                                                                    <label class="custom-control-label"
                                                                        for="makePrivateToggle">Rendi Privato</label>
                                                                </div>
                                                            </div>
                                                            <button id="generateLinkBtn"
                                                                class="btn btn-outline-warning btn-block">
                                                                <i class="fas fa-key mr-1"></i>Genera Link Privato
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload File Modal -->
                    <div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-upload mr-2"></i>Carica File
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="custom-file mb-3">
                                            <input type="file" class="custom-file-input" id="fileUpload" name="files[]"
                                                multiple>
                                            <label class="custom-file-label" for="fileUpload">Scegli file</label>
                                        </div>
                                        <div id="fileList" class="mt-3"></div>
                                        <div class="progress mt-3" style="display: none;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-primary" id="startUploadBtn">
                                        <i class="fas fa-cloud-upload-alt mr-1"></i> Carica
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Modal -->
                    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>Conferma operazione
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="confirmModalBody">
                                    Sei sicuro di voler procedere con questa operazione?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Annulla</button>
                                    <button type="button" class="btn btn-danger" id="confirmActionBtn">Conferma</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
            <?php include(BASE_PATH . "/components/footer.php"); ?>
        </div>
    </div>

    <style>
        /* Stili simili a quelli della pagina di gestione database */
        .file-list-container {
            max-height: 400px !important;
            overflow-y: auto !important;
            border: 1px solid #e3e6f0 !important;
            border-radius: 4px !important;
        }

        .file-item {
            cursor: pointer !important;
            transition: all 0.2s !important;
            border-radius: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            padding: 10px 15px !important;
            font-size: 0.9rem !important;
            border-bottom: 1px solid #f1f1f1 !important;
        }

        .file-item:first-child {
            border-top: 0 !important;
        }

        .file-item:last-child {
            border-bottom: 0 !important;
        }

        .file-item:hover {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
        }

        .file-item.active {
            background-color: #4e73df !important;
            color: white !important;
            width: 100% !important;
        }

        /* File type badges */
        .type-badge {
            font-size: 85% !important;
            padding: 0.25em 0.6em !important;
            border-radius: 3px !important;
            font-weight: 500 !important;
        }

        .type-image {
            background-color: #d1f5ea !important;
            color: #0d6e4e !important;
        }

        .type-document {
            background-color: #cfe3ff !important;
            color: #1e429f !important;
        }

        .type-video {
            background-color: #ffefd1 !important;
            color: #a16207 !important;
        }

        .type-audio {
            background-color: #d6f5d6 !important;
            color: #16953c !important;
        }

        .type-other {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        /* File preview styles */
        #file-preview img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }

        #file-preview video {
            max-width: 100%;
            max-height: 400px;
        }

        #file-preview audio {
            width: 100%;
        }
    </style>
    <script>
        // Configuration and utility functions
        const uploadDir = '<?php echo BASE_URL . "/uploads/hermes/"; ?>';
        let currentFile = null;

        function showAlert(message, type = 'success') {
            const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`);

            alertDiv.prependTo('.container-fluid');

            setTimeout(() => {
                alertDiv.alert('close');
            }, 5000);
        }

        function confirmAction(message, callback) {
            $('#confirmModalBody').text(message);
            $('#confirmActionBtn').off('click').on('click', function () {
                callback();
                $('#confirmModal').modal('hide');
            });
            $('#confirmModal').modal('show');
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            const docExts = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
            const videoExts = ['mp4', 'avi', 'mov', 'mkv', 'webm'];
            const audioExts = ['mp3', 'wav', 'ogg', 'flac'];

            if (imageExts.includes(ext)) return '<i class="fas fa-file-image text-info"></i>';
            if (docExts.includes(ext)) return '<i class="fas fa-file-alt text-primary"></i>';
            if (videoExts.includes(ext)) return '<i class="fas fa-file-video text-warning"></i>';
            if (audioExts.includes(ext)) return '<i class="fas fa-file-audio text-success"></i>';
            return '<i class="fas fa-file text-secondary"></i>';
        }

        function getFileType(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            const docExts = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
            const videoExts = ['mp4', 'avi', 'mov', 'mkv', 'webm'];
            const audioExts = ['mp3', 'wav', 'ogg', 'flac'];

            if (imageExts.includes(ext)) return { type: 'image', badge: 'type-image' };
            if (docExts.includes(ext)) return { type: 'document', badge: 'type-document' };
            if (videoExts.includes(ext)) return { type: 'video', badge: 'type-video' };
            if (audioExts.includes(ext)) return { type: 'audio', badge: 'type-audio' };
            return { type: 'other', badge: 'type-other' };
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function loadFolderInfo() {
            $.ajax({
                url: 'get_folder_info.php',
                method: 'GET',
                success: function (response) {
                    try {
                        const info = JSON.parse(response);
                        if (info.error) {
                            console.error("Error fetching folder info:", info.error);
                            return;
                        }

                        $('#total-files').text(`${info.totalFiles} Files`);
                        $('#total-size').text(formatFileSize(info.totalSize));
                        $('#file-types').text(info.fileTypes.join(', ') || '-');
                    } catch (e) {
                        console.error("Failed to parse folder info response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching folder info.");
                }
            });
        }

        function loadFiles() {
            $.ajax({
                url: 'get_files.php',
                method: 'GET',
                success: function (response) {
                    try {
                        const files = JSON.parse(response);
                        if (files.error) {
                            console.error("Error fetching files:", files.error);
                            $('#files-list').html(`<li class="list-group-item text-danger">${files.error}</li>`);
                            return;
                        }

                        const filesList = $('#files-list');
                        filesList.empty();

                        if (files.length === 0) {
                            filesList.append('<li class="list-group-item text-muted">Nessun file trovato</li>');
                            return;
                        }

                        files.forEach(file => {
                            const fileInfo = getFileType(file.name);
                            const fileItem = $(`
                                <li class="list-group-item file-item" data-filename="${file.name}">
                                    ${getFileIcon(file.name)} ${file.name}
                                    <span class="badge ${fileInfo.badge} float-right">${fileInfo.type.toUpperCase()}</span>
                                </li>
                            `);
                            filesList.append(fileItem);
                        });
                    } catch (e) {
                        console.error("Failed to parse files response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching files.");
                }
            });
        }

        function previewFile(filename) {
            const fileInfo = getFileType(filename);
            const preview = $('#file-preview');
            const details = $('#file-details');

            preview.html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Caricamento...</span></div></div>');
            details.html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Caricamento...</span></div></div>');

            $.ajax({
                url: 'get_file_details.php',
                method: 'GET',
                data: { filename: filename },
                success: function (response) {
                    try {
                        const fileDetails = JSON.parse(response);
                        if (fileDetails.error) {
                            showAlert(fileDetails.error, 'danger');
                            return;
                        }

                        // Preview
                        let previewHtml = '';
                        switch (fileInfo.type) {
                            case 'image':
                                previewHtml = `<img src="${uploadDir}${filename}" alt="${filename}" class="img-fluid">`;
                                break;
                            case 'video':
                                previewHtml = `<video controls><source src="${uploadDir}${filename}" type="video/${filename.split('.').pop()}"></video>`;
                                break;
                            case 'audio':
                                previewHtml = `<audio controls><source src="${uploadDir}${filename}" type="audio/${filename.split('.').pop()}"></audio>`;
                                break;
                            case 'document':
                                previewHtml = `<div class="alert alert-info"><i class="fas fa-file-alt mr-2"></i>Anteprima non disponibile per questo tipo di file</div>`;
                                break;
                            default:
                                previewHtml = `<div class="alert alert-secondary"><i class="fas fa-file mr-2"></i>Anteprima non supportata</div>`;
                        }
                        preview.html(previewHtml);

                        // Details
                        const detailsHtml = `
                            <table class="table">
                                <tr>
                                    <th>Nome File</th>
                                    <td>${filename}</td>
                                </tr>
                                <tr>
                                    <th>Tipo</th>
                                    <td><span class="badge ${fileInfo.badge}">${fileInfo.type.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <th>Dimensione</th>
                                    <td>${formatFileSize(fileDetails.size)}</td>
                                </tr>
                                <tr>
                                    <th>Ultima Modifica</th>
                                    <td>${fileDetails.modified}</td>
                                </tr>
                                <tr>
                                    <th>Permessi</th>
                                    <td>${fileDetails.permissions}</td>
                                </tr>
                            </table>
                        `;
                        details.html(detailsHtml);

                        // Update action buttons
                        $('#current-file-name').text(filename);
                        $('#downloadFileBtn, #deleteFileBtn').removeClass('d-none');
                    } catch (e) {
                        console.error("Failed to parse file details response:", e);
                    }
                },
                error: function () {
                    console.error("Error fetching file details.");
                }
            });
        }

        $(document).ready(function () {
            // Initial load
            loadFolderInfo();
            loadFiles();

            // File search
            $('#fileSearch').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $("#files-list li").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Clear search
            $('#clearSearch').on('click', function () {
                $('#fileSearch').val('');
                $("#files-list li").show();
            });

            // File selection
            $(document).on('click', '.file-item', function () {
                const filename = $(this).data('filename');
                currentFile = filename;

                $('.file-item').removeClass('active');
                $(this).addClass('active');

                previewFile(filename);
            });

            // File upload
            $('#uploadFileBtn').on('click', function () {
                $('#uploadFileModal').modal('show');
            });

            // File input change (show selected files)
            $('#fileUpload').on('change', function () {
                const files = $(this)[0].files;
                const fileList = $('#fileList');
                fileList.empty();

                if (files.length === 0) {
                    $('.custom-file-label').text('Scegli file');
                    return;
                }

                $('.custom-file-label').text(`${files.length} file selezionati`);

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    fileList.append(`
                        <div class="mb-2">
                            <span class="badge badge-primary mr-2">${getFileIcon(file.name)} ${file.name}</span>
                            <small class="text-muted">(${formatFileSize(file.size)})</small>
                        </div>
                    `);
                }
            });

            // Start upload
            $('#startUploadBtn').on('click', function () {
                const files = $('#fileUpload')[0].files;
                if (files.length === 0) {
                    showAlert('Seleziona prima alcuni file', 'warning');
                    return;
                }

                const formData = new FormData($('#uploadForm')[0]);

                // Show progress bar
                $('.progress').show();
                $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);

                $.ajax({
                    url: 'upload_files.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = evt.loaded / evt.total;
                                $('.progress-bar').css('width', (percentComplete * 100) + '%')
                                    .attr('aria-valuenow', percentComplete * 100);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                showAlert('File caricati con successo', 'success');
                                $('#uploadFileModal').modal('hide');

                                // Reset file input
                                $('#fileUpload').val('');
                                $('.custom-file-label').text('Scegli file');
                                $('#fileList').empty();

                                // Refresh file list and folder info
                                loadFiles();
                                loadFolderInfo();
                            }
                        } catch (e) {
                            console.error("Failed to parse upload response:", e);
                        }
                    },
                    error: function () {
                        showAlert('Errore durante il caricamento dei file', 'danger');
                    },
                    complete: function () {
                        $('.progress').hide();
                    }
                });
            });

            // Download file
            $('#downloadFileBtn').on('click', function () {
                if (!currentFile) {
                    showAlert('Seleziona prima un file', 'warning');
                    return;
                }

                window.location.href = `download_file.php?filename=${encodeURIComponent(currentFile)}`;
            });

            // Delete file
            $('#deleteFileBtn').on('click', function () {
                if (!currentFile) {
                    showAlert('Seleziona prima un file', 'warning');
                    return;
                }

                confirmAction(`Sei sicuro di voler eliminare il file ${currentFile}?`, function () {
                    $.ajax({
                        url: 'delete_file.php',
                        method: 'POST',
                        data: { filename: currentFile },
                        success: function (response) {
                            try {
                                const result = JSON.parse(response);
                                if (result.error) {
                                    showAlert(result.error, 'danger');
                                } else {
                                    showAlert('File eliminato con successo', 'success');

                                    // Reset preview and details
                                    $('#file-preview').html('<div class="text-center py-5 text-muted"><i class="fas fa-file fa-3x mb-3"></i><p>Seleziona un file per visualizzare l\'anteprima</p></div>');
                                    $('#file-details').html('<div class="text-center py-5 text-muted"><i class="fas fa-info-circle fa-3x mb-3"></i><p>Seleziona un file per visualizzare i dettagli</p></div>');
                                    $('#current-file-name').text('Seleziona un file');
                                    $('#downloadFileBtn, #deleteFileBtn').addClass('d-none');
                                    currentFile = null;

                                    // Refresh file list and folder info
                                    loadFiles();
                                    loadFolderInfo();
                                }
                            } catch (e) {
                                console.error("Failed to parse delete response:", e);
                            }
                        },
                        error: function () {
                            showAlert('Errore durante l\'eliminazione del file', 'danger');
                        }
                    });
                });
            });

            // Refresh files
            $('#refreshFiles').on('click', function () {
                loadFiles();
                loadFolderInfo();
                showAlert('Elenco file aggiornato', 'success');
            });

            // Copy file link
            $('#copyLinkBtn').on('click', function () {
                if (!currentFile) {
                    showAlert('Seleziona prima un file', 'warning');
                    return;
                }

                const fullLink = `${uploadDir}${currentFile}`;

                // Create a temporary textarea to copy the link
                const tempInput = $('<textarea>');
                $('body').append(tempInput);
                tempInput.val(fullLink).select();
                document.execCommand('copy');
                tempInput.remove();

                showAlert('Link copiato negli appunti', 'success');
            });

            // Share file (placeholder - could be implemented with more advanced sharing logic)
            $('#shareFileBtn').on('click', function () {
                if (!currentFile) {
                    showAlert('Seleziona prima un file', 'warning');
                    return;
                }

                // Example of how you might implement sharing
                if (navigator.share) {
                    navigator.share({
                        title: currentFile,
                        text: 'Guarda questo file',
                        url: `${uploadDir}${currentFile}`
                    }).then(() => {
                        showAlert('File condiviso con successo', 'success');
                    }).catch((error) => {
                        showAlert('Errore durante la condivisione', 'danger');
                    });
                } else {
                    showAlert('Condivisione non supportata sul tuo dispositivo', 'warning');
                }
            });

            // Generate private link
            $('#generateLinkBtn').on('click', function () {
                if (!currentFile) {
                    showAlert('Seleziona prima un file', 'warning');
                    return;
                }

                $.ajax({
                    url: 'generate_private_link.php',
                    method: 'POST',
                    data: {
                        filename: currentFile,
                        is_private: $('#makePrivateToggle').is(':checked')
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.error) {
                                showAlert(result.error, 'danger');
                            } else {
                                // Create a temporary textarea to copy the link
                                const tempInput = $('<textarea>');
                                $('body').append(tempInput);
                                tempInput.val(result.link).select();
                                document.execCommand('copy');
                                tempInput.remove();

                                showAlert('Link privato generato e copiato', 'success');
                            }
                        } catch (e) {
                            console.error("Failed to parse private link response:", e);
                        }
                    },
                    error: function () {
                        showAlert('Errore durante la generazione del link privato', 'danger');
                    }
                });
            });
        });
    </script>
</body>

</html>