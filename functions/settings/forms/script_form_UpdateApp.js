document
  .getElementById("updateAppBtn")
  .addEventListener("click", function (event) {
    event.preventDefault();

    // Mostra il log e la barra di progresso
    document.getElementById("updateLog").style.display = "block";
    document.getElementById("progressBar").value = 0;
    document.getElementById("progressText").textContent =
      "Aggiornamento in corso...";

    // Avvia l'aggiornamento
    fetch("forms/processing_UpdateApp.php", {
      method: "POST",
    })
      .then((response) => response.text())
      .then((text) => {
        // Mostra il log di aggiornamento
        document.getElementById("updateLog").textContent = text;
        document.getElementById("progressText").textContent =
          "Aggiornamento completato.";
      })
      .catch((error) => {
        console.error("Errore:", error);
        document.getElementById("progressText").textContent =
          "Errore durante l'aggiornamento.";
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
