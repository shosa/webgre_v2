document.getElementById("updateAppBtn").addEventListener("click", function () {
  var logElement = document.getElementById("updateLog");
  logElement.style.display = "block";

  // Pulisce il log esistente
  logElement.textContent = "";

  // Funzione per recuperare e aggiornare il log
  function updateLog() {
    fetch("forms/processing_UpdateApp.php")
      .then((response) => response.text())
      .then((text) => {
        logElement.textContent = text;
        logElement.scrollTop = logElement.scrollHeight; // Scrolla verso il basso
      })
      .catch((error) => console.error("Errore:", error));
  }

  // Esegue l'aggiornamento del log ogni secondo
  var intervalId = setInterval(updateLog, 1000);

  // Termina l'aggiornamento del log quando il processo è completato (metodo semplificato)
  // Dovresti considerare un metodo migliore per determinare quando il processo è completato
  setTimeout(function () {
    clearInterval(intervalId);
  }, 60000); // Cambia questo valore in base alla durata del processo
});
