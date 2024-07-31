document
  .getElementById("updateAppBtn")
  .addEventListener("click", function (event) {
    event.preventDefault();

    // Mostra il log, la barra di progresso e la rotella di caricamento
    document.getElementById("updateLog").style.display = "block";
    document.getElementById("progressBar").value = 0;
    document.getElementById("progressText").textContent =
      "Aggiornamento in corso...";
    document.getElementById("spinner").style.display = "inline-block";

    // Avvia l'aggiornamento
    fetch("forms/processing_UpdateApp.php", {
      method: "POST",
    })
      .then((response) => response.text())
      .then((text) => {
        // Mostra il log di aggiornamento, trasformando HTML
        document.getElementById("updateLog").innerHTML = text;
        document.getElementById("progressText").textContent =
          "Aggiornamento completato.";
        document.getElementById("spinner").style.display = "none";
      })
      .catch((error) => {
        console.error("Errore:", error);
        document.getElementById("progressText").textContent =
          "Errore durante l'aggiornamento.";
        document.getElementById("spinner").style.display = "none";
      });

    // Simula l'aggiornamento della progress bar
    let progress = 0;
    const interval = setInterval(() => {
      progress += 10;
      document.getElementById("progressBar").value = progress;
      if (progress >= 100) {
        clearInterval(interval);
      }
    }, 1000); // Aggiorna ogni secondo
  });
