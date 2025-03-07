<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Include header
require_once BASE_PATH . '/components/header.php';
?>
<style>
    .btn-wrapper {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
    }

    .btn-block {
        display: inline-block;
        width: 100%;
        margin-right: 10px;
        /* Aggiunto margine per separare i pulsanti */
    }
</style>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include BASE_PATH . "/components/navbar.php"; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include BASE_PATH . "/components/topbar.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php include BASE_PATH . "/utils/alerts.php"; ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Monitoraggio Lotti di Produzione</h1>
                        <div id="selection-info" class="text-right bg-primary p-1 rounded text-white shadow">Cartellini:
                            <span id="selected-count">0</span> | Paia: <span id="total-tot">0</span>
                        </div>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Tracking</a></li>
                        <li class="breadcrumb-item active">Associazione per Ricerca</li>
                    </ol>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Ricerca</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="cartel-filter" class="form-control"
                                        placeholder="Cartellino (Inizia per..)">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="commessa-filter" class="form-control"
                                        placeholder="Commessa Cliente (Inizia per..)">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="articolo-filter" class="form-control"
                                        placeholder="Codice Articolo">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="descrizione-articolo-filter" class="form-control"
                                        placeholder="Descrizione Articolo">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="ln-filter" class="form-control" placeholder="Linea">
                                </div>
                                <div class="col-md-6 mb-6">
                                    <input type="text" id="ragione-sociale-filter" class="form-control"
                                        placeholder="Ragione Sociale">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" id="ordine-filter" class="form-control" placeholder="Ordine">
                                </div>
                            </div>
                            <div class="btn-wrapper">
                                <button class="btn btn-success mb-2 btn-block" style="width: 75%;"
                                    onclick="searchCommesse()">Cerca</button>
                                <button id="proceed-btn" class="btn btn-primary mb-2 btn-block" style="width: 25%;"
                                    onclick="processSelected()" disabled>PROCEDI</button>
                            </div>
                            <hr>
                            <div id="results-container">
                                <!-- Risultati della ricerca verranno inseriti qui -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- Modulo nascosto per inviare i dati selezionati -->
    <form id="selected-form" action="processLink.php" method="POST" style="display: none;">
        <input type="hidden" name="selectedCartels" id="selectedCartels">
    </form>

    <!-- CSS Styles -->
    <style>
        #selection-info {
            position: fixed;
            font-size: 20pt;
            padding: 5%;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .group-header {
            cursor: pointer;
            color: black;
            padding: 5px;
        }

        .group-body {
            display: none;
            margin-left: 20px;
            transition: all 0.3s ease-in-out;
        }

        .group-header.selected {
            font-weight: bold;
        }

        .group-header:hover {
            background-color: #f0f0f0;
        }

        .item {
            cursor: pointer;
            padding: 5px;
            transition: background-color 0.3s ease;
        }

        .item:hover {
            background-color: #d3e5f5;
        }
    </style>

    <!-- JavaScript -->
    <script>
        var selectedCartels = [];
        var selectedTot = 0;

        function searchCommesse() {
            var cartel = document.getElementById('cartel-filter').value;
            var commessa = document.getElementById('commessa-filter').value;
            var articolo = document.getElementById('articolo-filter').value;
            var descrizioneArticolo = document.getElementById('descrizione-articolo-filter').value;
            var ln = document.getElementById('ln-filter').value;
            var ragioneSociale = document.getElementById('ragione-sociale-filter').value;
            var ordine = document.getElementById('ordine-filter').value;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'searchDati.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response;
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (e) {
                            console.error('Errore di parsing JSON:', e);
                            response = [];
                        }

                        if (!Array.isArray(response)) {
                            console.error('La risposta non è un array:', response);
                            response = [];
                        }

                        var resultsContainer = document.getElementById('results-container');
                        resultsContainer.innerHTML = '<h5 class="text-dark">Risultati:</h5>';

                        var groupedResults = groupResultsByDescrizioneArticolo(response);

                        for (var descrizioneArticolo in groupedResults) {
                            var group = groupedResults[descrizioneArticolo];
                            var groupElement = createGroupElement(descrizioneArticolo, group);
                            resultsContainer.appendChild(groupElement);
                        }
                        console.log('Dati ricevuti dal server:', response);
                    } else {
                        console.error('Errore nella ricerca delle commesse');
                    }
                }
            };
            xhr.send('cartel=' + encodeURIComponent(cartel) +
                '&commessa=' + encodeURIComponent(commessa) +
                '&articolo=' + encodeURIComponent(articolo) +
                '&descrizioneArticolo=' + encodeURIComponent(descrizioneArticolo) +
                '&ln=' + encodeURIComponent(ln) +
                '&ragioneSociale=' + encodeURIComponent(ragioneSociale) +
                '&ordine=' + encodeURIComponent(ordine));
        }

        function groupResultsByDescrizioneArticolo(results) {
            var groupedResults = {};
            results.forEach(function (row) {
                var descrizioneArticolo = row['Descrizione Articolo'];
                if (!groupedResults[descrizioneArticolo]) {
                    groupedResults[descrizioneArticolo] = [];
                }
                groupedResults[descrizioneArticolo].push(row);
            });
            return groupedResults;
        }

        function createGroupElement(descrizioneArticolo, group) {
            var groupDiv = document.createElement('div');
            groupDiv.className = 'group';
            var groupHeader = document.createElement('div');
            groupHeader.className = 'group-header';
            groupHeader.setAttribute('data-expanded', 'false');
            groupHeader.innerHTML = '<span class="toggle">+ </span><span class="group-name"><b>' + descrizioneArticolo + ' </b><i>(<u>' + group[0].Articolo + '</u>)(' + group.length + ' Voci)</i></span>';

            groupHeader.onclick = function () {
                toggleGroup(groupBody, groupHeader);
            };
            groupDiv.appendChild(groupHeader);
            var groupBody = document.createElement('div');
            groupBody.className = 'group-body';
            groupBody.style.display = 'none'; // Inizia compresso
            group.forEach(function (row) {
                var item = document.createElement('div');
                item.className = 'item';
                item.setAttribute('data-cartel', row.Cartel);
                item.innerHTML = '<input type="checkbox" class="form-check-input" onchange="selectRow(this, \'' + row.Cartel + '\')">' +
                    '<label class="form-check-label text-dark">' + row.Cartel + ' (' + (row['Commessa Cli'] ? row['Commessa Cli'] : '') + ') | PAIA ' + row.Tot + '</label>';
                item.onclick = function (e) {
                    if (e.target.tagName.toLowerCase() !== 'input') {
                        var checkbox = this.querySelector('.form-check-input');
                        checkbox.checked = !checkbox.checked;
                        selectRow(checkbox, row.Cartel);
                    }
                };
                groupBody.appendChild(item);
            });

            // Aggiungi il link "Seleziona Tutto" all'inizio del gruppo quando è espanso
            var selectAllLink = document.createElement('a');
            selectAllLink.href = 'javascript:void(0);';
            selectAllLink.textContent = 'Seleziona Tutto';
            selectAllLink.onclick = function (e) {
                e.stopPropagation(); // Evita che il click si propaga al gruppo
                selectAllInGroup(groupBody);
            };
            groupBody.insertBefore(selectAllLink, groupBody.firstChild);

            groupDiv.appendChild(groupBody);
            return groupDiv;
        }

        function toggleGroup(groupBody, groupHeader) {
            var expanded = groupHeader.getAttribute('data-expanded') === 'true';
            if (!expanded) {
                groupBody.style.display = 'block';
                groupHeader.querySelector('.toggle').textContent = '- ';
                groupHeader.setAttribute('data-expanded', 'true');
            } else {
                groupBody.style.display = 'none';
                groupHeader.querySelector('.toggle').textContent = '+ ';
                groupHeader.setAttribute('data-expanded', 'false');
            }
        }

        function selectRow(checkbox, cartel) {
            var totMatch = checkbox.parentNode.textContent.match(/PAIA (\d+)/);
            if (totMatch) {
                var tot = parseInt(totMatch[1]);
                if (checkbox.checked) {
                    selectedCartels.push(cartel);
                    selectedTot += tot;
                } else {
                    selectedCartels = selectedCartels.filter(function (item) {
                        return item !== cartel;
                    });
                    selectedTot -= tot;
                }
                updateSelectionInfo();
            }
        }


        function selectAllInGroup(groupBody) {
            var checkboxes = groupBody.getElementsByClassName('form-check-input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (!checkboxes[i].checked) {
                    checkboxes[i].checked = true;
                    var cartel = checkboxes[i].parentNode.getAttribute('data-cartel');
                    var totMatch = checkboxes[i].parentNode.textContent.match(/PAIA (\d+)/);
                    if (totMatch && !selectedCartels.includes(cartel)) {
                        selectedCartels.push(cartel);
                        selectedTot += parseInt(totMatch[1]);
                    }
                }
            }
            updateSelectionInfo();
        }

        function selectAll() {
            var checkboxes = document.getElementsByClassName('form-check-input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (!checkboxes[i].checked) {
                    checkboxes[i].checked = true;
                    var cartel = checkboxes[i].parentNode.getAttribute('data-cartel');
                    var totMatch = checkboxes[i].parentNode.textContent.match(/PAIA (\d+)/);
                    if (totMatch && !selectedCartels.includes(cartel)) {
                        selectedCartels.push(cartel);
                        selectedTot += parseInt(totMatch[1]);
                    }
                }
            }
            updateSelectionInfo();
        }


        function updateSelectionInfo() {
            var selectedCount = document.getElementById('selected-count');
            var totalTot = document.getElementById('total-tot');
            var proceedBtn = document.getElementById('proceed-btn');

            selectedCount.textContent = selectedCartels.length;
            totalTot.textContent = selectedTot;

            // Abilita o disabilita il pulsante "PROCEDI" in base al numero di cartellini selezionati
            proceedBtn.disabled = selectedCartels.length === 0;
        }

        function processSelected() {
            var selectedCartelsInput = document.getElementById('selectedCartels');
            selectedCartelsInput.value = JSON.stringify(selectedCartels);
            document.getElementById('selected-form').submit();
        }
    </script>

</body>