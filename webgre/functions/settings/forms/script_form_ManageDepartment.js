document.querySelectorAll('input[data-field="Nome"]').forEach((input) => {
    input.addEventListener("change", function () {
      const id = this.dataset.id;
      const value = this.value;
      const field = this.dataset.field;
  
      const formData = new FormData();
      formData.append("action", "update");
      formData.append("id", id);
      formData.append("field", field);
      formData.append("value", value);
  
      fetch("forms/processing_ManageDepartment.php", {
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
            text:
              "Si è verificato un errore durante l'aggiornamento del reparto.",
          });
        });
    });
  });
  
  document.querySelectorAll(".btn-delete-department").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
  
      Swal.fire({
        title: "Sei sicuro?",
        text: "Questa azione non può essere annullata!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sì, elimina!",
        cancelButtonText: "Annulla",
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("action", "delete");
          formData.append("id", id);
  
          fetch("forms/processing_ManageDepartment.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                Swal.fire("Eliminato!", data.message, "success");
                reloadForm();
              } else {
                Swal.fire("Errore!", data.message, "error");
              }
            })
            .catch((error) => {
              Swal.fire(
                "Errore!",
                "Si è verificato un errore durante l'eliminazione del reparto.",
                "error"
              );
            });
        }
      });
    });
  });
  
  document.getElementById("addDepartmentBtn").addEventListener("click", function () {
    Swal.fire({
      title: "Aggiungi Reparto",
      html: '<input id="nome" class="swal2-input" placeholder="Nome">',
      showCancelButton: true,
      confirmButtonText: "Aggiungi",
      cancelButtonText: "Annulla",
      preConfirm: () => {
        const nome = Swal.getPopup().querySelector("#nome").value;
  
        if (!nome) {
          Swal.showValidationMessage(
            "Per favore, inserisci il nome del reparto."
          );
        }
        return { nome: nome };
      },
    }).then((result) => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append("action", "add");
        formData.append("nome", result.value.nome);
  
        fetch("forms/processing_ManageDepartment.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              Swal.fire("Aggiunto!", data.message, "success");
              reloadForm();
            } else {
              Swal.fire("Errore!", data.message, "error");
            }
          })
          .catch((error) => {
            Swal.fire(
              "Errore!",
              "Si è verificato un errore durante l'aggiunta del reparto.",
              "error"
            );
          });
      }
    });
  });
  
  function reloadForm() {
    fetch("forms/form_ManageDepartment.php")
      .then((response) => response.text())
      .then((html) => {
        document.getElementById("formManageDepartment").innerHTML = html;
        initializeEventListeners();
      })
      .catch((error) => {
        console.error("Errore durante il ricaricamento del modulo:", error);
      });
  }
  
  function initializeEventListeners() {
    document.querySelectorAll('input[data-field="Nome"]').forEach((input) => {
      input.addEventListener("change", function () {
        const id = this.dataset.id;
        const value = this.value;
        const field = this.dataset.field;
  
        const formData = new FormData();
        formData.append("action", "update");
        formData.append("id", id);
        formData.append("field", field);
        formData.append("value", value);
  
        fetch("forms/processing_ManageDepartment.php", {
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
              text:
                "Si è verificato un errore durante l'aggiornamento del reparto.",
            });
          });
      });
    });
  
    document.querySelectorAll(".btn-delete-department").forEach((button) => {
      button.addEventListener("click", function () {
        const id = this.dataset.id;
  
        Swal.fire({
          title: "Sei sicuro?",
          text: "Questa azione non può essere annullata!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sì, elimina!",
          cancelButtonText: "Annulla",
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("action", "delete");
            formData.append("id", id);
  
            fetch("forms/processing_ManageDepartment.php", {
              method: "POST",
              body: formData,
            })
              .then((response) => response.json())
              .then((data) => {
                if (data.success) {
                  Swal.fire("Eliminato!", data.message, "success");
                  reloadForm();
                } else {
                  Swal.fire("Errore!", data.message, "error");
                }
              })
              .catch((error) => {
                Swal.fire(
                  "Errore!",
                  "Si è verificato un errore durante l'eliminazione del reparto.",
                  "error"
                );
              });
          }
        });
      });
    });
  
    document.getElementById("addDepartmentBtn").addEventListener("click", function () {
      Swal.fire({
        title: "Aggiungi Reparto",
        html: '<input id="nome" class="swal2-input" placeholder="Nome">',
        showCancelButton: true,
        confirmButtonText: "Aggiungi",
        cancelButtonText: "Annulla",
        preConfirm: () => {
          const nome = Swal.getPopup().querySelector("#nome").value;
  
          if (!nome) {
            Swal.showValidationMessage(
              "Per favore, inserisci il nome del reparto."
            );
          }
          return { nome: nome };
        },
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("action", "add");
          formData.append("nome", result.value.nome);
  
          fetch("forms/processing_ManageDepartment.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                Swal.fire("Aggiunto!", data.message, "success");
                reloadForm();
              } else {
                Swal.fire("Errore!", data.message, "error");
              }
            })
            .catch((error) => {
              Swal.fire(
                "Errore!",
                "Si è verificato un errore durante l'aggiunta del reparto.",
                "error"
              );
            });
        }
      });
    });
  }
  
  initializeEventListeners();
  