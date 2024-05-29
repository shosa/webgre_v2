<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/includes/header.php';

// Recupera tutte le date disponibili nel database
$db = getDbInstance();
$db->groupBy('data');
$db->orderBy('data', 'desc');
$dates = $db->get('cq_records', null, 'data');

?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header page-action-links text-left">Elenco dei giorni con dati disponibili</h2>
        </div>
    </div>
    <HR>
    <div class="row">
        <div class="col-lg-12">
            <ul>
                <?php foreach ($dates as $date): ?>
                    <li><a href="detail?date=<?php echo $date['data']; ?>"><?php echo $date['data']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/includes/footer.php'; ?>