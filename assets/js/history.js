document.addEventListener("DOMContentLoaded", function () {
  console.log("HISTORY.JS: Cargado");

  // Elementos
  const btnClear = document.getElementById("btnClearHistory");
  const btnPause = document.getElementById("btnPauseHistory");

  // Elementos internos del botón pausar
  const btnPauseText = btnPause ? btnPause.querySelector("span") : null;
  const btnPauseIcon = btnPause ? btnPause.querySelector("i") : null;

  // --- 1. OBTENER ESTADO INICIAL (Al cargar la página) ---
  if (btnPause) {
    fetch("actions/manage_history.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "get_status" }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          console.log(
            "Estado inicial historial:",
            data.paused ? "Pausado" : "Activo"
          );
          updatePauseButton(data.paused);
        }
      })
      .catch((err) => console.error("Error obteniendo estado:", err));
  }

  // --- 2. ACCIÓN: BORRAR TODO ---
  if (btnClear) {
    btnClear.addEventListener("click", function () {
      if (confirm("¿Estás seguro de que quieres borrar todo tu historial?")) {
        fetch("actions/manage_history.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "clear_all" }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              M.toast({ html: "Historial borrado" });
              setTimeout(() => window.location.reload(), 500);
            } else {
              M.toast({ html: "Error al borrar" });
            }
          });
      }
    });
  }

  // --- 3. ACCIÓN: PAUSAR / REANUDAR ---
  if (btnPause) {
    btnPause.addEventListener("click", function () {
      // Deshabilitar temporalmente para evitar doble clic
      btnPause.disabled = true;

      fetch("actions/manage_history.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "toggle_pause" }),
      })
        .then((res) => res.json())
        .then((data) => {
          btnPause.disabled = false;

          if (data.success) {
            console.log("Nuevo estado:", data.paused);
            updatePauseButton(data.paused);
            M.toast({ html: data.message });
          } else {
            M.toast({ html: "Error al cambiar estado" });
            console.error(data.error);
          }
        })
        .catch((err) => {
          btnPause.disabled = false;
          console.error("Error AJAX:", err);
        });
    });
  }

  // Función auxiliar visual
  function updatePauseButton(isPaused) {
    if (!btnPauseText || !btnPauseIcon) return;

    if (isPaused) {
      // Si está pausado, el botón debe ofrecer "Activar"
      btnPauseText.textContent = "Activar el historial de reproducciones";
      btnPauseIcon.textContent = "play_circle_outline";
    } else {
      // Si está activo, el botón debe ofrecer "Pausar"
      btnPauseText.textContent = "Pausar el historial de reproducciones";
      btnPauseIcon.textContent = "pause_circle_outline";
    }
  }
});
