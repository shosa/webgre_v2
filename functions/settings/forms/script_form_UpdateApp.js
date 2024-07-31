document.getElementById('updateAppBtn').addEventListener('click', function () {
    fetch('forms/processing_UpdateApp.php')
        .then(response => response.text())
        .then(log => {
            document.getElementById('updateLog').style.display = 'block';
            document.getElementById('updateLog').textContent = log;
        });
});