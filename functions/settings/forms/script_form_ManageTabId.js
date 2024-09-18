document
  .querySelector('input[data-field="ID"]')
  .addEventListener("change", function () {
    const value = this.value;
    const formData = new FormData();
    formData.append("value", value);
    fetch("forms/processing_ManageTabId.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        Swal.fire({
          icon: data.success ? "success" : "error",
          title: data.success ? "Successo" : "Errore",
          text: data.message,
        });
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Errore",
          text: "Si è verificato un errore durante l'aggiornamento dell'ID.",
        });
      });
  });
