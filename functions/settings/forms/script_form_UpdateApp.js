document.addEventListener("DOMContentLoaded", function () {
  const githubToken = document.getElementById("githubToken").value;
  const latestCommitElement = document.getElementById("latestCommit");

  function fetchLatestCommit() {
      const repoOwner = 'shosa';
      const repoName = 'webgre_v2';
      const url = `https://api.github.com/repos/${repoOwner}/${repoName}/commits/main`;

      fetch(url, {
          headers: {
              'Authorization': `token ${githubToken}`,
              'User-Agent': 'Mozilla/5.0'
          }
      })
      .then(response => response.json())
      .then(data => {
          if (data && data.sha) {
              latestCommitElement.textContent = data.sha.substring(0, 7);
          } else {
              latestCommitElement.textContent = "Impossibile recuperare il commit";
          }
      })
      .catch(error => {
          console.error("Errore:", error);
          latestCommitElement.textContent = "Errore durante il caricamento";
      });
  }

  fetchLatestCommit();

  document.getElementById("updateAppBtn").addEventListener("click", function (event) {
      event.preventDefault();

      // Mostra il log e la barra di progresso
      document.getElementById("updateLog").style.display = "block";
      document.getElementById("progressBar").value = 0;
      document.getElementById("progressText").textContent = "Aggiornamento in corso...";
      document.getElementById("spinner").style.display = "inline-block";

      // Avvia l'aggiornamento
      fetch("forms/processing_UpdateApp.php", {
          method: "POST",
      })
      .then(response => {
          if (!response.ok) {
              throw new Error('Errore nella risposta: ' + response.statusText);
          }
          return response.text();
      })
      .then(text => {
          // Mostra il log di aggiornamento
          document.getElementById("updateLog").textContent = text;
          document.getElementById("progressText").textContent = "Aggiornamento completato.";
          document.getElementById("spinner").style.display = "none";
      })
      .catch(error => {
          console.error("Errore:", error);
          document.getElementById("progressText").textContent = "Errore durante l'aggiornamento.";
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
});
