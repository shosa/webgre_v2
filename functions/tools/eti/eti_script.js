(function () {
    var label;
    var _printers = [];
    // Funzione per mostrare i suggerimenti di ricerca
    function showSuggestions(suggestions, container, inputField) {
        container.innerHTML = "";
        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement("div");
            suggestionItem.className = "suggestion-item";
            suggestionItem.textContent = `${suggestion.art} | ${suggestion.des}`;
            suggestionItem.addEventListener("click", function () {
                inputField.value = suggestion.art; // Imposta il valore dell'input
                container.innerHTML = ""; // Pulisci le suggestions dopo la selezione
                fetchAndUpdateArticleDetails(inputField.value); // Aggiorna direttamente senza ritardo
            });
            container.appendChild(suggestionItem);
        });
    }
    // Funzione per recuperare e aggiornare i dettagli dell'articolo
    function fetchAndUpdateArticleDetails(artCode) {
        const url = `eti_get_article_details.php?art=${artCode}`;
        console.log("URL della richiesta:", url); // Debug: stampa l'URL per verificare
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log(data); // Debug: stampa i dati ricevuti per verificare
                updateLabelContent(data.cm, data.barcode, data.des, data.art);
            })
            .catch(error => console.error('Errore nel recupero dei dettagli articolo:', error));
    }
    // Funzione per creare una riga di dettaglio della stampante
    function createPrintersTableRow(table, name, value) {
        var row = document.createElement("tr");
        var cell1 = document.createElement("td");
        cell1.appendChild(document.createTextNode(name + ': '));
        var cell2 = document.createElement("td");
        cell2.appendChild(document.createTextNode(value));
        row.appendChild(cell1);
        row.appendChild(cell2);
        table.appendChild(row);
    }
    // Funzione per popolare i dettagli della stampante
    function populatePrinterDetail() {
        var printerDetail = document.getElementById("printerDetail");
        printerDetail.innerHTML = "";
        var myPrinter = _printers[document.getElementById("printersSelect").value];
        if (myPrinter === undefined) return;
        var table = document.createElement("table");
        table.classList.add("table", "table-bordered");
        createPrintersTableRow(table, 'Modello', myPrinter['modelName']);
        createPrintersTableRow(table, 'Locale', myPrinter['isLocal']);
        createPrintersTableRow(table, 'Connessione', myPrinter['isConnected']);
        dymo.label.framework.is550PrinterAsync(myPrinter.name).then(function (isRollStatusSupported) {
            if (isRollStatusSupported) {
                dymo.label.framework.getConsumableInfoIn550PrinterAsync(myPrinter.name).then(function (consumableInfo) {
                    createPrintersTableRow(table, 'SKU-Etichette', consumableInfo['sku']);
                    createPrintersTableRow(table, 'Nome Etichette', consumableInfo['name']);
                    createPrintersTableRow(table, 'Etichette rimanenti', consumableInfo['labelsRemaining']);
                }).thenCatch(function (e) {
                    createPrintersTableRow(table, 'SKU-Etichette', 'n/a');
                    createPrintersTableRow(table, 'Nome Etichette', 'n/a');
                    createPrintersTableRow(table, 'Etichette rimanenti', 'n/a');
                });
            } else {
                createPrintersTableRow(table, 'IsRollStatusSupported', 'False');
            }
        }).thenCatch(function (e) {
            createPrintersTableRow(table, 'IsRollStatusSupported', e.message);
        });
        printerDetail.appendChild(table);
    }
    function formatTextWithLineBreaks(text, maxLength) {
        let formattedText = '';
        while (text.length > maxLength) {
            formattedText += text.substring(0, maxLength) + '\n';
            text = text.substring(maxLength);
        }
        formattedText += text;
        return formattedText;
    }
    // Funzione per aggiornare il contenuto dell'etichetta
    function updateLabelContent(categoriaText, codiceText, descrizioneText, codiceArticoloText) {
        if (!label) {
            alert("Carica l'etichetta prima di aggiornare il contenuto");
            return;
        }
        // Formattazione del testo della descrizione
        let formattedDescrizioneText = formatTextWithLineBreaks(descrizioneText, 30);
        // Aggiorna il contenuto dell'etichetta
        label.setObjectText("CATEGORIA", categoriaText);
        label.setObjectText("CODICE", codiceText);
        label.setObjectText("CODICE_ARTICOLO", codiceArticoloText);
        label.setObjectText("BARCODE", codiceText);
        label.setObjectText("DESCRIZIONE", formattedDescrizioneText);
        // Mostra un'anteprima dell'etichetta
        var preview = document.getElementById("labelPreview");
        preview.src = "data:image/png;base64," + label.render();
        // Popola i dettagli della stampante
        populatePrinterDetail();
    }
    // Funzione per caricare in modo asincrono le stampanti
    function loadPrintersAsync() {
        _printers = [];
        dymo.label.framework.getPrintersAsync().then(function (printers) {
            if (printers.length == 0) {
                alert("Nessuna etichettatrice DYMO rilevata");
                return;
            }
            _printers = printers;
            printers.forEach(function (printer) {
                let option = document.createElement("option");
                option.value = printer["name"];
                option.appendChild(document.createTextNode(printer["name"]));
                document.getElementById('printersSelect').appendChild(option);
            });
            populatePrinterDetail();
        }).thenCatch(function (e) {
            alert("Load Printers failed: " + e);
        });
        var testAddressLabelXml = '<?xml version="1.0" encoding="utf-8"?>\
<DieCutLabel Version="8.0" Units="twips">\
	<PaperOrientation>Portrait</PaperOrientation>\
	<Id>Small30334</Id>\
	<PaperName>30334 2-1/4 in x 1-1/4 in</PaperName>\
	<DrawCommands>\
		<RoundRectangle X="0" Y="0" Width="3240" Height="1800" Rx="270" Ry="270" />\
	</DrawCommands>\
	<ObjectInfo>\
		<TextObject>\
			<Name>CATEGORIA</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<VerticalAlignment>Middle</VerticalAlignment>\
			<TextFitMode>ShrinkToFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>CM</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="12" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="2826.75" Y="1505.5" Width="281.25" Height="195.25" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<BarcodeObject>\
			<Name>BARCODE</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName>CODICE</LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<Text>9000000000</Text>\
			<Type>Code128Auto</Type>\
			<Size>Large</Size>\
			<TextPosition>None</TextPosition>\
			<TextFont Family="Lucida Console" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
			<CheckSumFont Family="Lucida Console" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
			<TextEmbedding>None</TextEmbedding>\
			<ECLevel>0</ECLevel>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
		</BarcodeObject>\
		<Bounds X="106.75" Y="779.25" Width="3076.25" Height="686.25" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<AddressObject>\
			<Name>DESCRIZIONE</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>True</IsVariable>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<VerticalAlignment>Middle</VerticalAlignment>\
			<TextFitMode>ShrinkToFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>DESCRIZIONE_ARTICOLO</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
			<ShowBarcodeFor9DigitZipOnly>False</ShowBarcodeFor9DigitZipOnly>\
			<BarcodePosition>AboveAddress</BarcodePosition>\
			<LineFonts>\
				<Font Family="Lucida Console" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
			</LineFonts>\
		</AddressObject>\
		<Bounds X="150" Y="127.5" Width="2913.75" Height="528.75" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<TextObject>\
			<Name>CODICE</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<HorizontalAlignment>Left</HorizontalAlignment>\
			<VerticalAlignment>Middle</VerticalAlignment>\
			<TextFitMode>AlwaysFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>9000000000</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="225" Y="1570.5" Width="866.25" Height="120" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<TextObject>\
			<Name>CODICE_ARTICOLO</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<HorizontalAlignment>Center</HorizontalAlignment>\
			<VerticalAlignment>Middle</VerticalAlignment>\
			<TextFitMode>AlwaysFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>CODICE_ARTICOLO</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="1222.5" Y="1566.75" Width="1492.5" Height="120" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<TextObject>\
			<Name>TESTO</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<HorizontalAlignment>Left</HorizontalAlignment>\
			<VerticalAlignment>Top</VerticalAlignment>\
			<TextFitMode>ShrinkToFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>|</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="1105.5" Y="1563" Width="120" Height="135" />\
	</ObjectInfo>\
	<ObjectInfo>\
		<TextObject>\
			<Name>TESTO_1</Name>\
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
			<LinkedObjectName></LinkedObjectName>\
			<Rotation>Rotation0</Rotation>\
			<IsMirrored>False</IsMirrored>\
			<IsVariable>False</IsVariable>\
			<HorizontalAlignment>Left</HorizontalAlignment>\
			<VerticalAlignment>Top</VerticalAlignment>\
			<TextFitMode>ShrinkToFit</TextFitMode>\
			<UseFullFontHeight>True</UseFullFontHeight>\
			<Verticalized>False</Verticalized>\
			<StyledText>\
				<Element>\
					<String>|</String>\
					<Attributes>\
						<Font Family="Lucida Console" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					</Attributes>\
				</Element>\
			</StyledText>\
		</TextObject>\
		<Bounds X="2744.25" Y="1566.75" Width="120" Height="135" />\
	</ObjectInfo>\
</DieCutLabel>';
        label = dymo.label.framework.openLabelXml(testAddressLabelXml);
    }
    // Funzione chiamata al completamento del caricamento del documento
    function onload() {
        loadPrintersAsync();
        document.getElementById('printButton').onclick = function () {
            try {
                if (!label) {
                    alert("Load label before printing");
                    return;
                }
                label.print(document.getElementById('printersSelect').value);
            } catch (e) {
                alert(e.message || e);
            }
        };
        document.getElementById('printersSelect').onchange = populatePrinterDetail;
        document.getElementById("codice_articolo").addEventListener("input", function () {
            const inputValue = this.value.trim();
            if (inputValue !== "") {
                fetch(`inv_get_suggestions.php?q=${inputValue}`)
                    .then(response => response.json())
                    .then(data => showSuggestions(data, document.getElementById("suggestions-container"), this));
            } else {
                document.getElementById("suggestions-container").innerHTML = "";
            }
        });
    }
    // Inizializza e registra l'evento di caricamento
    if (dymo.label.framework.init) {
        dymo.label.framework.init(onload);
    } else {
        onload();
    }
}());