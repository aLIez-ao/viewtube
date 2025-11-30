document.addEventListener("DOMContentLoaded", function () {
  const createForm = document.getElementById("createChannelForm");

  if (createForm) {
    createForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const nameInput = document.getElementById("channelName");
      const btn = document.getElementById("btnCreateChannel");
      const name = nameInput.value.trim();

      if (!name) return;

      btn.disabled = true;
      btn.textContent = "Creando...";

      fetch("actions/studio_actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "create_channel",
          name: name,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            // Recargar para mostrar el dashboard
            window.location.reload();
          } else {
            alert("Error: " + data.error);
            btn.disabled = false;
            btn.textContent = "Crear canal";
          }
        })
        .catch((err) => {
          console.error(err);
          alert("Error de conexión");
          btn.disabled = false;
          btn.textContent = "Crear canal";
        });
    });
  }
});
