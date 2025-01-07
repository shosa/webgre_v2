<script src="<?php echo BASE_URL; ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="<?php echo BASE_URL; ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="<?php echo BASE_URL; ?>/js/sb-admin-2.min.js"></script>
<script src="<?php echo BASE_URL; ?>/vendor/jquery/jquery.min.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo BASE_URL; ?>/service-worker.js')
                .then((registration) => {
                    console.log('Software creato da Stefano Solidoro');
                    console.log('Service Worker running on:', registration.scope);
                })
                .catch((error) => {
                    console.error('Service Worker error:', error);
                });
        });
    }
</script>