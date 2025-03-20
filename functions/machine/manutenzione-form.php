<?php
session_start();
require_once '../../config/config.php';
require_once '../../utils/log_utils.php';

// Get database instance
$pdo = getDbInstance();

// Check if ID was passed
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['token']) || empty($_GET['token'])) {
    die("Accesso non autorizzato o link non valido.");
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$token = $_GET['token'];

if (!$id) {
    die("ID macchinario non valido.");
}

// Get machine details and validate token
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$macchinario) {
        die("Macchinario non trovato.");
    }
    
    // Validate token (simple hash based on machine serial + id)
    $expectedToken = md5($macchinario['matricola'] . $id);
    if ($token !== $expectedToken) {
        die("Token non valido. Accesso non autorizzato.");
    }
    
    // Log QR code scan
    $stmt = $pdo->prepare("INSERT INTO mac_qrcode_logs (mac_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([
        $id, 
        $_SERVER['REMOTE_ADDR'], 
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    // Get maintenance types
    $stmt = $pdo->query("SELECT * FROM mac_manutenzioni_tipi ORDER BY nome");
    $tipi_manutenzione = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

// Process form submission
$successMessage = '';
$errorMessage = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save form data for restoring in case of error
    $formData = $_POST;
    
    try {
        // Validate required fields
        if (empty($_POST['tipo_id']) || empty($_POST['operatore']) || empty($_POST['descrizione'])) {
            throw new Exception("I campi contrassegnati con * sono obbligatori.");
        }
        
        // Prepare file upload if any
        $uploadedFiles = [];
        if (!empty($_FILES['allegati']['name'][0])) {
            $uploadDir = '../../uploads/manutenzioni/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($_FILES['allegati']['name'] as $key => $name) {
                if ($_FILES['allegati']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['allegati']['tmp_name'][$key];
                    $fileName = time() . '_' . basename($name);
                    $filePath = $uploadDir . $fileName;
                    
                    // Check file type (optional: add more secure validation)
                    $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception("Tipo di file non supportato: " . $fileType);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $uploadedFiles[] = [
                            'nome_file' => $name,
                            'percorso_file' => 'uploads/manutenzioni/' . $fileName,
                            'tipo_file' => $fileType,
                            'dimensione' => $_FILES['allegati']['size'][$key]
                        ];
                    } else {
                        throw new Exception("Errore durante il caricamento del file.");
                    }
                }
            }
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Check if it's a scheduled maintenance
        $is_programmata = 0;
        if (isset($_POST['scheduled_id']) && !empty($_POST['scheduled_id'])) {
            // Validate scheduled maintenance
            $stmtCheck = $pdo->prepare("SELECT * FROM mac_manutenzioni_programmate WHERE id = ? AND mac_id = ?");
            $stmtCheck->execute([$_POST['scheduled_id'], $id]);
            if ($stmtCheck->fetch()) {
                $is_programmata = 1;
            }
        }
        
        <?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        
        // Ottieni l'istanza del databpasted<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        
        // Ottieni l'istanza del databpasted<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        
        // Ottieni l'istanza del databpasted<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        
        // Ottieni l'istanza del databpastedSDROP TABLE IF EXISTS mac_anag;
        CREATE TABLE mac_anag (
          id int(11) NOT NULL AUTO_INCREMENT,
          matricola varchar(50) NOT NULL,
          tipologia varchar(100) NOT NULL,
          data_acquisto date NOT NULL,
          rif_fattura varchar(100) DEFAULT NULL,
          fornitore varchar(100) DEFAULT NULL,
          modello varchar(100) NOT NULL,
          note text DEFAULT NULL,
          data_creazione timestamp NOT NULL DEFAULT current_timestamp(),
          data_aggiornamento timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (id),
          UNIQUE KEY matricola (matricola)
        ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;ti ho condiviso alcune pagine e la tabella dati di questo mio modulo di controllo dei macchinari aziendali , voglio che mi dai anche delle funzioni e pagine e tabelle relative per la gestione delle manutenzioni , la mia intenzione era generare un barcode per ogni macchina da attaccare fisicamente sulla macchina , dove il manutentore una volta scansionato va a compilare la scheda di intervento che arriva direttamente nel mio software  e io posso poi revisionare e approvare in modo da registrare le manutenzioni , e poi stampe , dettagli e cose varie , do your magicEditI'll help you enhance your machinery management system with maintenance features. Based on the code you've shared, I'll design a maintenance module that integrates seamlessly with your existing system. Here's a comprehensive solution for tracking maintenance activities, including QR code generation, maintenance scheduling, technician reporting, and approval workflows.
        Let's start with the database structure for maintenance management:
        sqlCopy-- Table for maintenance types
        DROP TABLE IF EXISTS `mac_manutenzioni_tipi`;
        CREATE TABLE `mac_manutenzioni_tipi` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nome` varchar(100) NOT NULL,
          `descrizione` text DEFAULT NULL,
          `colore` varchar(20) DEFAULT '#007bff',
          `is_programmata` tinyint(1) DEFAULT 0,
          `intervallo_giorni` int(11) DEFAULT NULL,
          `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `nome` (`nome`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Table for maintenance records
        DROP TABLE IF EXISTS `mac_manutenzioni`;
        CREATE TABLE `mac_manutenzioni` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mac_id` int(11) NOT NULL,
          `tipo_id` int(11) NOT NULL,
          `data_manutenzione` date NOT NULL,
          `data_completamento` date DEFAULT NULL,
          `operatore` varchar(100) NOT NULL,
          `descrizione` text NOT NULL,
          `lavori_eseguiti` text DEFAULT NULL,
          `ricambi_utilizzati` text DEFAULT NULL,
          `tempo_impiegato` decimal(5,2) DEFAULT NULL,
          `costo` decimal(10,2) DEFAULT NULL,
          `stato` enum('richiesta','in_corso','completata','approvata','rifiutata') DEFAULT 'richiesta',
          `is_programmata` tinyint(1) DEFAULT 0,
          `approvata_da` varchar(50) DEFAULT NULL,
          `note_approvazione` text DEFAULT NULL,
          `data_approvazione` datetime DEFAULT NULL,
          `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
          `data_aggiornamento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `mac_id` (`mac_id`),
          KEY `tipo_id` (`tipo_id`),
          CONSTRAINT `manutenzioni_mac_fk` FOREIGN KEY (`mac_id`) REFERENCES `mac_anag` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Table for maintenance attachments/documents
        DROP TABLE IF EXISTS `mac_manutenzioni_allegati`;
        CREATE TABLE `mac_manutenzioni_allegati` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `manutenzione_id` int(11) NOT NULL,
          `nome_file` varchar(255) NOT NULL,
          `percorso_file` varchar(255) NOT NULL,
          `tipo_file` varchar(50) DEFAULT NULL,
          `dimensione` int(11) DEFAULT NULL,
          `data_caricamento` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `manutenzione_id` (`manutenzione_id`),
          CONSTRAINT `allegati_manutenzione_fk` FOREIGN KEY (`manutenzione_id`) REFERENCES `mac_manutenzioni` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Table for scheduled maintenance
        DROP TABLE IF EXISTS `mac_manutenzioni_programmate`;
        CREATE TABLE `mac_manutenzioni_programmate` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mac_id` int(11) NOT NULL,
          `tipo_id` int(11) NOT NULL,
          `frequenza` enum('giornaliera','settimanale','mensile','trimestrale','semestrale','annuale','personalizzata') NOT NULL,
          `intervallo_giorni` int(11) DEFAULT NULL,
          `ultima_manutenzione` date DEFAULT NULL,
          `prossima_manutenzione` date NOT NULL,
          `descrizione` text NOT NULL,
          `operatore_preferito` varchar(100) DEFAULT NULL,
          `priorita` enum('bassa','media','alta','critica') DEFAULT 'media',
          `stato` enum('attiva','sospesa','completata') DEFAULT 'attiva',
          `notifica_giorni_prima` int(11) DEFAULT 7,
          `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
          `data_aggiornamento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `mac_id` (`mac_id`),
          KEY `tipo_id` (`tipo_id`),
          CONSTRAINT `programmate_mac_fk` FOREIGN KEY (`mac_id`) REFERENCES `mac_anag` (`id`) ON DELETE CASCADE,
          CONSTRAINT `programmate_tipo_fk` FOREIGN KEY (`tipo_id`) REFERENCES `mac_manutenzioni_tipi` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Table for QR code access logs
        DROP TABLE IF EXISTS `mac_qrcode_logs`;
        CREATE TABLE `mac_qrcode_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mac_id` int(11) NOT NULL,
          `ip_address` varchar(45) DEFAULT NULL,
          `user_agent` varchar(255) DEFAULT NULL,
          `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
          `azione` varchar(50) DEFAULT 'scansione',
          PRIMARY KEY (`id`),
          KEY `mac_id` (`mac_id`),
          CONSTRAINT `qrlogs_mac_fk` FOREIGN KEY (`mac_id`) REFERENCES `mac_anag` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        Now, let's create the necessary pages for your maintenance management system. I'll focus on the key pages that you'll need:QR Code Generator Page (qrcode.php)Click to open codeTap to openPublic Maintenance Form Page (manutenzione-form.php)Click to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.ScontinuaEditI'll continue with the maintenance form and then create the other necessary files for the maintenance management system.Public Maintenance Form Page (manutenzione-form.php)EditedClick to open codeTap to openNow, let's create the maintenance management pages that will be used by administrators:Maintenance Types Management (tipi_manutenzione.php)Click to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.ScontinuaEditLet me continue with the maintenance types management page and then create the other necessary pages for your maintenance management system.Maintenance Types Management (tipi_manutenzione.php)EditedClick to open codeTap to openNow, let's create the maintenance management page where administrators can view, approve, and manage maintenance records:Maintenance Records List (manutenzioni.php)Click to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.ScontinuaEditLet me continue with the maintenance management list page:Maintenance Records List (manutenzioni.php)EditedClick to open codeTap to openNow, let's create the maintenance details page:Maintenance Details Page (dettaglio_manutenzione.php)Click to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.Sparla in italiano , continuaEditCertamente, continuerò in italiano completando la pagina di dettaglio manutenzione.Maintenance Details Page (dettaglio_manutenzione.php)EditedClick to open codeTap to openInfine, creiamo una pagina per la gestione delle manutenzioni programmate:Gestione Manutenzioni Programmate (manutenzioni_programmate.php)Click to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.ScontinuaEditContinuerò con la pagina di gestione delle manutenzioni programmate:Gestione Manutenzioni Programmate (manutenzioni_programmate.php)EditedClick to open codeTap to openRetryClaude hit the max length for a message and has paused its response. You can write Continue to keep the chat going.ScontinuaEditContinuerò con il completamento della pagina di manutenzioni programmate:Gestione Manutenzioni Programmate (manutenzioni_programmate.php)EditedClick to open codeTap to openPer completare il sistema di gestione delle manutenzioni dei macchinari, ecco un riassunto di quanto abbiamo sviluppato:
        Database
        Abbiamo creato diverse tabelle per gestire il sistema di manutenzione:
        
        mac_anag (esistente) - Anagrafica macchinari
        mac_manutenzioni_tipi - Tipologie di manutenzione
        mac_manutenzioni - Registrazioni delle manutenzioni effettuate
        mac_manutenzioni_allegati - Allegati relativi alle manutenzioni
        mac_manutenzioni_programmate - Pianificazione delle manutenzioni periodiche
        mac_qrcode_logs - Log delle scansioni dei QR code
        
        Pagine Principali
        
        QR Code Generator (qrcode.php)
        
        Genera QR code per ogni macchinario da stampare e attaccare fisicamente alla macchina
        Consente di scaricare o stampare etichette con il QR code
        
        
        Form di Manutenzione Pubblico (manutenzione-form.php)
        
        Form accessibile tramite scansione QR code
        Permette ai manutentori di registrare interventi direttamente dal campo
        Include caricamento di allegati (foto, documenti, ecc.)
        
        
        Gestione Tipi di Manutenzione (tipi_manutenzione.php)
        
        Configurazione dei diversi tipi di intervento (ordinaria, straordinaria, ecc.)
        Definizione delle manutenzioni programmabili e loro intervalli predefiniti
        
        
        Lista Manutenzioni (manutenzioni.php)
        
        Visualizzazione di tutte le manutenzioni effettuate
        Filtri per stato, tipo, data, ecc.
        Funzionalità di approvazione/rifiuto degli interventi
        
        
        Dettaglio Manutenzione (dettaglio_manutenzione.php)
        
        Visualizzazione dettagliata di un singolo intervento
        Gestione allegati e approvazioni
        Informazioni sul macchinario e sull'intervento
        
        
        Manutenzioni Programmate (manutenzioni_programmate.php)
        
        Pianificazione delle manutenzioni periodiche
        Monitoraggio delle scadenze e notifiche
        Configurazione delle frequenze e priorità
        
        
        
        Funzionalità Principali
        
        Sistema QR Code
        
        Generazione di QR code univoci per ogni macchinario
        Scansione da parte dei manutentori per accedere direttamente al form
        
        
        Workflow di Approvazione
        
        Registrazione degli interventi da parte dei manutentori
        Revisione e approvazione/rifiuto da parte dei responsabili
        Tracciamento dello stato di ogni intervento
        
        
        Manutenzioni Programmate
        
        Pianificazione automatica delle manutenzioni periodiche
        Avvisi per interventi in scadenza
        Diverse frequenze (giornaliera, settimanale, mensile, ecc.)
        
        
        Gestione Allegati
        
        Caricamento di foto, documenti tecnici e certificati
        Visualizzazione diretta nel sistema
        Organizzazione per macchinario e intervento
        
        
        Sistema di Reportistica
        
        Esportazione dati in Excel e PDF
        Filtri avanzati per analisi personalizzate
        Storico completo degli interventi per ogni macchinario
        
        
        
        Questo sistema completo di gestione delle manutenzioni si integra perfettamente con il modulo esistente di gestione dei macchinari, creando un unico ambiente per monitorare l'intero ciclo di vita delle attrezzature aziendali, dalla registrazione iniziale fino alla manutenzione programmata e straordinaria.
        Per completare l'implementazione, sarà necessario:
        
        Installare eventuali librerie PHP come PhpQrCode per la generazione dei codici QR
        Creare le cartelle per gli allegati con i corretti permessi di scrittura
        Impostare eventuali notifiche email per avvisare i responsabili delle manutenzioni in scadenza
        
        Vuoi che ti fornisca qualche altro dettaglio o funzionalità aggiuntiva per completare il sistema?Retry<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        
        // Get database instance
        $pdopastedSscrivimi SOLO IL COMPLETAMENTO di questo codiceEditphpCopy                               </div>
                                       
                                       <div class="form-group mb-2 mr-2">
                                           <label for="data_da" class="sr-only">Data Da</label>
                                           <div class="input-group">
                                               <div class="input-group-prepend">
                                                   <span class="input-group-text">Da</span>
                                               </div>
                                               <input type="date" class="form-control" id="data_da" name="data_da" value="<?= htmlspecialchars($data_da) ?>">
                                           </div>
                                       </div>
                                       
                                       <div class="form-group mb-2 mr-2">
                                           <label for="data_a" class="sr-only">Data A</label>
                                           <div class="input-group">
                                               <div class="input-group-prepend">
                                                   <span class="input-group-text">A</span>
                                               </div>
                                               <input type="date" class="form-control" id="data_a" name="data_a" value="<?= htmlspecialchars($data_a) ?>">
                                           </div>
                                       </div>
                                       
                                       <div class="form-group mb-2 mr-2">
                                           <label for="sort" class="sr-only">Ordinamento</label>
                                           <select class="form-control" id="sort" name="sort">
                                               <option value="data_manutenzione" <?= ($sort == 'data_manutenzione') ? 'selected' : '' ?>>Data Manutenzione</option>
                                               <option value="stato" <?= ($sort == 'stato') ? 'selected' : '' ?>>Stato</option>
                                               <option value="operatore" <?= ($sort == 'operatore') ? 'selected' : '' ?>>Operatore</option>
                                               <option value="data_creazione" <?= ($sort == 'data_creazione') ? 'selected' : '' ?>>Data Registrazione</option>
                                           </select>
                                       </div>
                                       
                                       <div class="form-group mb-2 mr-2">
                                           <label for="order" class="sr-only">Direzione</label>
                                           <select class="form-control" id="order" name="order">
                                               <option value="DESC" <?= ($order == 'DESC') ? 'selected' : '' ?>>Decrescente</option>
                                               <option value="ASC" <?= ($order == 'ASC') ? 'selected' : '' ?>>Crescente</option>
                                           </select>
                                       </div>
                                       
                                       <button type="submit" class="btn btn-primary mb-2">Applica Filtri</button>
                                   </form>
                               </div>
                           </div>Retry<div class="col-md-3 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tipologia
                   pastedSScrivimi il completamento di questo codiceEditphpCopy                                   <div class="col-md-12 form-group">
                                               <label for="edit_descrizione"><strong>Descrizione *</strong></label>
                                               <textarea name="descrizione" id="edit_descrizione" class="form-control" rows="3" required></textarea>
                                           </div>
                                       </div>
                                       
                                       <div class="row">
                                           <div class="col-md-6 form-group">
                                               <label for="edit_operatore_preferito">Operatore Preferito</label>
                                               <input type="text" name="operatore_preferito" id="edit_operatore_preferito" class="form-control">
                                           </div>
                                           
                                           <div class="col-md-6 form-group">
                                               <label for="edit_stato"><strong>Stato *</strong></label>
                                               <select name="stato" id="edit_stato" class="form-control" required>
                                                   <option value="attiva">Attiva</option>
                                                   <option value="sospesa">Sospesa</option>
                                                   <option value="completata">Completata</option>
                                               </select>
                                           </div>
                                       </div>
                                       
                                       <div class="row">
                                           <div class="col-md-6 form-group">
                                               <label for="edit_notifica_giorni_prima">Notifica (giorni prima)</label>
                                               <input type="number" name="notifica_giorni_prima" id="edit_notifica_giorni_prima" class="form-control" min="0" max="60">
                                           </div>
                                       </div>
                                   </div>
                                   <div class="modal-footer">
                                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                       <button type="submit" class="btn btn-primary">Aggiorna</button>
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>RetrySParse error: syntax error, unexpected 'endif' (T_ENDIF), expecting end of file in C:\xampp\htdocs\webgre\functions\machine\manutenzioni_programmate.php on line 28EditQuesto errore indica che c'è un problema di bilanciamento dei tag PHP nel file. L'errore specifico indica che c'è un endif inatteso alla linea 28, il che significa che probabilmente c'è un tag if che non è stato aperto correttamente o un endif di troppo.
        Per risolvere il problema, dovresti:
        
        Controllare tutti i tuoi blocchi if e assicurarti che ogni if abbia il corrispondente endif (quando usi la sintassi alternativa con : ) o la corrispondente chiusura di parentesi graffe } (quando usi la sintassi standard).
        Nel tuo caso specifico, controlla la linea 28 e le precedenti per trovare un endif che potrebbe essere superfluo o un blocco if incompleto.
        Assicurati che tutte le strutture di controllo del flusso come if, for, foreach, while siano correttamente chiuse e bilanciate.
        
        Se lo desideri, posso aiutarti a verificare il codice completo per identificare il problema esatto. In questo caso, dovresti condividere una porzione più ampia del codice intorno alla linea 28.Retry<div class="col-md-3 mb-3">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tipologia
                   pastedSil codice è il tuoEditSembri avere un problema con il tuo file manutenzioni_programmate.php. Analizzando il tuo codice, ho individuato il problema: ci sono frammenti ripetuti e un errore di sintassi.
        Il problema principale è la presenza di un tag di chiusura PHP </body>group"> che appare più volte nel codice. Questa non è una sintassi valida e sta causando l'errore.
        Inoltre, sembra che il file contenga due versioni dello stesso script, con la seconda versione che inizia con un nuovo tag <?php nel mezzo del file.
        Per risolvere il problema:
        
        Elimina tutti i </body>group"> nel file
        Mantieni solo una versione del codice PHP e del markup HTML
        Assicurati che tutti i tag if abbiano i corrispondenti endif quando usi la sintassi alternativa
        
        Ti suggerisco di ricreare il file completamente, o almeno pulire il codice esistente rimuovendo le sezioni duplicate e i tag non validi. Il codice che hai condiviso nei documenti dovrebbe essere corretto se rimosso il tag problematico.
        Il flusso corretto dovrebbe essere:
        
        Una singola dichiarazione PHP all'inizio che include la logica di business
        Una struttura HTML completa con i tag PHP integrati
        Inclusioni dei file di script e footer alla fine
        
        L'errore specifico che hai segnalato unexpected 'endif' (T_ENDIF), expecting end of file indica che c'è un endif che non corrisponde a nessun tag if aperto, probabilmente a causa della duplicazione del codice.Retry<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        require_once '../../vendor/autolpastedSmi da questo erroreFatal error: Uncaught Error: Class 'Endroid\QrCode\ErrorCorrectionLevel' not found in C:\xampp\htdocs\webgre\functions\machine\qrcode.php:61 Stack trace: #0 {main} thrown in C:\xampp\htdocs\webgre\functions\machine\qrcode.php on line 61EditL'errore indica che la classe Endroid\QrCode\ErrorCorrectionLevel non è stata trovata. Questo è un problema con la libreria QR Code che stai utilizzando.
        Il problema si verifica perché:
        
        O la libreria Endroid QR Code non è installata correttamente
        O stai utilizzando una versione diversa della libreria rispetto a quella nel codice
        
        Per risolvere questo problema, ecco cosa dovresti fare:
        Installazione della libreria corretta:
        Esegui questo comando nella directory principale del tuo progetto:
        bashCopycomposer require endroid/qr-code
        Aggiornamento del codice:
        La sintassi nel tuo codice sembra essere per una versione specifica di endroid/qr-code. Se hai installato una versione più recente (v4+), potrebbe essere necessario aggiornare il codice. Ecco come potrebbe apparire il codice aggiornato per le versioni recenti:
        phpCopy<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        require_once '../../vendor/autoload.php';
        
        use Endroid\QrCode\QrCode;
        use Endroid\QrCode\Writer\PngWriter;
        use Endroid\QrCode\Encoding\Encoding;
        use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
        use Endroid\QrCode\Color\Color;
        use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
        use Endroid\QrCode\Writer\Result\PngResult;
        
        // Resto del codice...
        
        if (isset($_GET['action']) && ($_GET['action'] == 'download' || $_GET['action'] == 'print')) {
            // URL for maintenance form that will be encoded in QR code
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            $maintenanceUrl = $baseUrl . BASE_URL . "/manutenzione-form?id=" . $id . "&token=" . md5($macchinario['matricola'] . $id);
            
            // Generate QR code
            $qrCode = QrCode::create($maintenanceUrl)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->setSize(300)
                ->setMargin(10)
                ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            // Resto del codice...
        }
        Soluzione alternativa con libreria più semplice:
        Se hai difficoltà con Endroid, puoi considerare l'utilizzo di una libreria più semplice come chillerlan/php-qrcode:
        
        Installa:
        
        bashCopycomposer require chillerlan/php-qrcode
        
        Modifica il codice:
        
        phpCopy<?php
        session_start();
        require_once '../../config/config.php';
        require_once BASE_PATH . '/components/auth_validate.php';
        require_once '../../utils/log_utils.php';
        require_once '../../vendor/autoload.php';
        
        use chillerlan\QRCode\QRCode;
        use chillerlan\QRCode\QROptions;
        
        // Resto del codice...
        
        if (isset($_GET['action']) && ($_GET['action'] == 'download' || $_GET['action'] == 'print')) {
            // URL for maintenance form that will be encoded in QR code
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            $maintenanceUrl = $baseUrl . BASE_URL . "/manutenzione-form?id=" . $id . "&token=" . md5($macchinario['matricola'] . $id);
            
            // Generate QR code
            $options = new QROptions([
                'version'    => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_H,
                'scale'      => 10,
                'margin'     => 4,
            ]);
            
            $qrcode = new QRCode($options);
            $qrString = $qrcode->render($maintenanceUrl);
            
            // Handle download action
            if ($_GET['action'] == 'download') {
                $filename = 'QRCode_' . $macchinario['matricola'] . '.png';
                header('Content-Type: image/png');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo base64_decode(explode(',', $qrString)[1]);
                exit;
            }
            
            // For print action
            if ($_GET['action'] == 'print') {
                $qrCodePath = '../../uploads/qrcodes/';
                if (!file_exists($qrCodePath)) {
                    mkdir($qrCodePath, 0755, true);
                }
                
                $qrFileName = 'qrcode_' . $id . '_' . time() . '.png';
                $qrFilePath = $qrCodePath . $qrFileName;
                
                file_put_contents($qrFilePath, base64_decode(explode(',', $qrString)[1]));
                $qrCodeImage = BASE_URL . '/uploads/qrcodes/' . $qrFileName;
            }
        
            // Resto del codice...
        }
        Scegli la soluzione più adatta alle tue esigenze. Assicurati di avere installato le dipendenze corrette tramite Composer prima di eseguire il codice.Retry<?php
        session_start();
        require_once '../../config/config.php';
        require_once '../../utils/log_utils.php';
        
        // Get database instance
        $pdo = getDbInstance();
        
        // Check if ID was passed
        if (!issepastedSil tuo manutenzioni-form è molto incompleto 
        sistemaloEditHai ragione, il file manutenzione-form.php è incompleto. Sembra che sia stato troncato alla linea che contiene box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0. Ecco la versione completa del file:Form di Manutenzione Completo (manutenzione-form.php)Click to open codeTap to openPer quanto riguarda il problema con la libreria QR Code, ti consiglio di installare una di queste librerie:
        
        Per Endroid QR Code (mostrato nel tuo codice originale):
        
        bashCopycomposer require endroid/qr-code
        
        Se preferisci una soluzione più semplice, puoi anche utilizzare:
        
        bashCopycomposer require chillerlan/php-qrcode
        Dopo l'installazione della libreria, assicurati che i namespace nelle importazioni corrispondano alla versione della libreria che hai installato. Se hai problemi con i namespace, controlla la documentazione della libreria o verifica quali classi sono disponibili nella versione installata.
        Se continui a riscontrare problemi con il generatore di QR code, fammi sapere e posso fornirti una versione alternativa del file che utilizza una libreria diversa o un servizio QR code online.Retry<?php
        session_start();
        require_once '../../config/config.php';
        require_once '../../utils/log_utils.php';
        
        // Get database instance
        $pdo = getDbInstance();
        
       // Insert maintenance record
        $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni (
            mac_id, tipo_id, data_manutenzione, operatore, descrizione, 
            lavori_eseguiti, ricambi_utilizzati, tempo_impiegato, 
            stato, is_programmata
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");  // Nota: rimosso un parametro ? per il costo
        
        $stmt->execute([
            $id,
            $_POST['tipo_id'],
            $_POST['data_manutenzione'],
            $_POST['operatore'],
            $_POST['descrizione'],
            $_POST['lavori_eseguiti'] ?? null,
            $_POST['ricambi_utilizzati'] ?? null,
            $_POST['tempo_impiegato'] ?? null,
            'richiesta',
            $is_programmata
        ]); 
        
        $manutenzioneId = $pdo->lastInsertId();
        
        // Save attachments if any
        foreach ($uploadedFiles as $file) {
            $stmt = $pdo->prepare("INSERT INTO mac_manutenzioni_allegati (
                manutenzione_id, nome_file, percorso_file, tipo_file, dimensione
            ) VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $manutenzioneId,
                $file['nome_file'],
                $file['percorso_file'],
                $file['tipo_file'],
                $file['dimensione']
            ]);
        }
        
        // If it's a scheduled maintenance, update the schedule
        if ($is_programmata && isset($_POST['scheduled_id'])) {
            $stmt = $pdo->prepare("UPDATE mac_manutenzioni_programmate SET 
                ultima_manutenzione = ?, 
                prossima_manutenzione = DATE_ADD(?, INTERVAL intervallo_giorni DAY)
                WHERE id = ?");
                
            $stmt->execute([
                $_POST['data_manutenzione'],
                $_POST['data_manutenzione'],
                $_POST['scheduled_id']
            ]);
        }
        
        $pdo->commit();
        
        $successMessage = "Manutenzione registrata con successo! Sarà approvata dal responsabile.";
        $formData = []; // Clear form data after success
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = "Errore: " . $e->getMessage();
    }
}

// Simple layout for external access (not using the admin template)
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registrazione Manutenzione - <?= htmlspecialchars($macchinario['matricola']) ?></title>
    
    <!-- Custom fonts -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
        }
        .maintenance-logo {
            max-height: 80px;
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .bg-maintenance {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }
        .maintenance-header {
            text-align: center;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="maintenance-header bg-maintenance rounded-lg mb-4">
            <h2 class="h3 mb-0">
                <i class="fas fa-tools mr-2"></i>
                Registrazione Manutenzione
            </h2>
        </div>
        
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> <?= $successMessage ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $errorMessage ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <!-- Machine Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    Dettagli Macchinario
                </h6>
                <span class="badge badge-primary px-3 py-2">
                    #<?= $macchinario['id'] ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Matricola
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['matricola']) ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tipologia
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['tipologia']) ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Modello
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($macchinario['modello']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Maintenance Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clipboard-list mr-2"></i>Registra Intervento di Manutenzione
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data" id="maintenanceForm">
                    <?php if (isset($_GET['scheduled_id'])): ?>
                        <input type="hidden" name="scheduled_id" value="<?= filter_var($_GET['scheduled_id'], FILTER_VALIDATE_INT) ?>">
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Stai registrando una manutenzione programmata.
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="tipo_id"><strong>Tipo di Intervento *</strong></label>
                            <select name="tipo_id" id="tipo_id" class="form-control" required>
                                <option value="">-- Seleziona tipo --</option>
                                <?php foreach ($tipi_manutenzione as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" <?= (isset($formData['tipo_id']) && $formData['tipo_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (empty($tipi_manutenzione)): ?>
                                    <option value="1">Manutenzione Ordinaria</option>
                                    <option value="2">Manutenzione Straordinaria</option>
                                    <option value="3">Riparazione</option>
                                    <option value="4">Controllo</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label for="data_manutenzione"><strong>Data Intervento *</strong></label>
                            <input type="date" name="data_manutenzione" id="data_manutenzione" class="form-control" value="<?= htmlspecialchars($formData['data_manutenzione'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="operatore"><strong>Nome Operatore/Tecnico *</strong></label>
                            <input type="text" name="operatore" id="operatore" class="form-control" value="<?= htmlspecialchars($formData['operatore'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label for="tempo_impiegato">Tempo Impiegato (ore)</label>
                            <input type="number" name="tempo_impiegato" id="tempo_impiegato" class="form-control" step="0.25" min="0" value="<?= htmlspecialchars($formData['tempo_impiegato'] ?? '') ?>" placeholder="Es. 1.5">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="descrizione"><strong>Descrizione Intervento *</strong></label>
                            <textarea name="descrizione" id="descrizione" class="form-control" rows="3" required placeholder="Descrivi il tipo di intervento e il motivo..."><?= htmlspecialchars($formData['descrizione'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="lavori_eseguiti">Lavori Eseguiti</label>
                            <textarea name="lavori_eseguiti" id="lavori_eseguiti" class="form-control" rows="3" placeholder="Descrivi nel dettaglio i lavori eseguiti..."><?= htmlspecialchars($formData['lavori_eseguiti'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="ricambi_utilizzati">Ricambi Utilizzati</label>
                            <textarea name="ricambi_utilizzati" id="ricambi_utilizzati" class="form-control" rows="2" placeholder="Elenca i ricambi utilizzati..."><?= htmlspecialchars($formData['ricambi_utilizzati'] ?? '') ?></textarea>
                        </div>
                        
                     
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="allegati">Allegati (foto, documenti, ecc.)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="allegati" name="allegati[]" multiple>
                                <label class="custom-file-label" for="allegati">Scegli file...</label>
                            </div>
                            <small class="form-text text-muted">Puoi allegare fino a 5 file (max 10MB ciascuno). Formati supportati: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirmCheck" required>
                            <label class="custom-control-label" for="confirmCheck">
                                Confermo che le informazioni inserite sono corrette.
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-save mr-2"></i>Registra Manutenzione
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <p class="text-muted small">
                Questa manutenzione verrà registrata nel sistema e sarà visibile ai responsabili.
                <br>
                &copy; <?= date('Y') ?> Sistema di Gestione Macchinari - Tutti i diritti riservati
            </p>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Update file input label with selected files
            $('.custom-file-input').on('change', function() {
                var fileCount = $(this)[0].files.length;
                var label = fileCount > 0 ? fileCount + ' file selezionati' : 'Scegli file...';
                $(this).next('.custom-file-label').html(label);
            });
            
            // Set current date as default
            if ($('#data_manutenzione').val() === '') {
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;
                $('#data_manutenzione').val(today);
            }
            
            // Form validation
            $('#maintenanceForm').on('submit', function(e) {
                var isValid = true;
                
                // Check required fields
                $(this).find('[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Compila tutti i campi obbligatori contrassegnati con *');
                }
                
                // Check file size
                var maxFileSize = 10 * 1024 * 1024; // 10MB
                var files = $('#allegati')[0].files;
                
                if (files.length > 5) {
                    isValid = false;
                    alert('Puoi caricare al massimo 5 file.');
                    e.preventDefault();
                    return;
                }
                
                for (var i = 0; i < files.length; i++) {
                    if (files[i].size > maxFileSize) {
                        isValid = false;
                        alert('Il file ' + files[i].name + ' supera la dimensione massima di 10MB.');
                        e.preventDefault();
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html>