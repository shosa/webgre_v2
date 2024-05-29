<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Controllo dei permessi dell'utente

// Recupera il progressivo dalla richiesta GET
$progressivo = $_GET['progressivo'];

// Recupera l'istanza del database
$db = getDbInstance();

// Aggiorna il campo first_boot a 0 nel documento corrente
$db->where('id', $progressivo)->update('exp_documenti', ['first_boot' => 0]);

// Redirect alla pagina di dettaglio del documento
header("Location: view_ddt.php?progressivo=$progressivo");
