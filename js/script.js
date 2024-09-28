function confirmDelete(userId) {
  Swal.fire({
    title: "Sind Sie sicher?",
    text: "Kann nicht rückgängig gemacht werden!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#009999",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ja, Benutzer löschen!",
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "admin_dashboard.php";

      const hiddenField = document.createElement("input");
      hiddenField.type = "hidden";
      hiddenField.name = "deleteUser";
      hiddenField.value = userId;

      form.appendChild(hiddenField);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

function startRoulette() {
  Swal.fire({
    title: "Bist du sicher?",
    text: "Möchtest du das Roulette wirklich starten?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#009999",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ja, starten!",
    cancelButtonText: "Abbrechen",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "../functional_files/generate_pairings.php", // Correct path
        method: "POST",
        dataType: "json", // Expecting a JSON response
        success: function (response) {
          if (response.status === "success") {
            Swal.fire("Gestartet!", response.message, "success");
          } else {
            Swal.fire("Fehler!", response.message, "error");
          }
        },
        error: function (xhr, status, error) {
          Swal.fire(
            "Fehler!",
            "Es ist ein Fehler aufgetreten: " + error,
            "error"
          );
        },
      });
    }
  });
}
