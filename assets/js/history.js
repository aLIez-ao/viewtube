document.addEventListener("DOMContentLoaded", function () {
  console.log("HISTORY.JS: Cargado");

  const btnClear = document.getElementById("btnClearHistory");
  const btnPause = document.getElementById("btnPauseHistory");
  const btnPauseText = btnPause ? btnPause.querySelector("span") : null;
  const btnPauseIcon = btnPause ? btnPause.querySelector("i") : null;
  const historyListContainer = document.querySelector(".history-list"); // Selector seguro por clase

  // --- 1. OBTENER ESTADO INICIAL ---
  if (btnPause) {
    fetch("actions/manage_history.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "get_status" }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          updatePauseButton(data.paused);
        }
      });
  }

  // --- 2. ACCIONES GLOBALES ---
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

  if (btnPause) {
    btnPause.addEventListener("click", function () {
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
            updatePauseButton(data.paused);
            M.toast({ html: data.message });
          }
        })
        .catch((err) => (btnPause.disabled = false));
    });
  }

  // --- 3. MENÚ DE ELEMENTOS INDIVIDUALES (DELEGACIÓN) ---
  if (historyListContainer) {
    console.log("HISTORY.JS: Lista encontrada, activando delegación.");

    historyListContainer.addEventListener("click", function (e) {
      // A. BOTÓN DE MENÚ (3 Puntos)
      const menuBtn = e.target.closest(".btn-history-menu");
      if (menuBtn) {
        e.preventDefault(); // Evitar navegar al video
        e.stopPropagation();

        const wrapper = menuBtn.closest(".history-menu-wrapper");

        // Cerrar otros menús
        document
          .querySelectorAll(".history-menu-wrapper.active")
          .forEach((el) => {
            if (el !== wrapper) el.classList.remove("active");
          });

        wrapper.classList.toggle("active");
        return;
      }

      // B. BOTÓN ELIMINAR ITEM (Dentro del menú)
      const removeBtn = e.target.closest(".btn-remove-item");
      if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();

        const videoId = removeBtn.getAttribute("data-video-id");
        const menuWrapper = removeBtn.closest(".history-menu-wrapper");
        menuWrapper.classList.remove("active"); // Cerrar menú

        removeItem(videoId);
        return; // Importante para no cerrar inmediatamente por clic fuera
      }

      // C. OTROS BOTONES (Placeholders)
      if (
        e.target.closest(".menu-option") &&
        !e.target.closest(".btn-remove-item")
      ) {
        // Cerrar menú al hacer clic en cualquier opción que no sea eliminar
        const menuWrapper = e.target.closest(".history-menu-wrapper");
        if (menuWrapper) menuWrapper.classList.remove("active");
      }
    });
  }

  // CERRAR MENÚS AL CLIC FUERA
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".history-menu-wrapper")) {
      document
        .querySelectorAll(".history-menu-wrapper.active")
        .forEach((el) => {
          el.classList.remove("active");
        });
    }
  });

  // Función para borrar un item
  function removeItem(videoId) {
    fetch("actions/manage_history.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "remove_item", video_id: videoId }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const item = document.getElementById("history-item-" + videoId);
          if (item) {
            // Animación de salida
            item.style.transition = "opacity 0.3s, transform 0.3s";
            item.style.opacity = "0";
            item.style.transform = "translateX(20px)";

            setTimeout(() => {
              item.remove();
              // Comprobar si quedó vacía la lista
              if (!document.querySelector(".history-item")) {
                location.reload(); // O mostrar mensaje de vacío dinámicamente
              }
            }, 300);

            M.toast({ html: "Se ha quitado del historial" });
          }
        } else {
          M.toast({ html: "Error al quitar elemento" });
        }
      })
      .catch((err) => console.error(err));
  }

  function updatePauseButton(isPaused) {
    if (!btnPauseText || !btnPauseIcon) return;
    if (isPaused) {
      btnPauseText.textContent = "Activar el historial de reproducciones";
      btnPauseIcon.textContent = "play_circle_outline";
    } else {
      btnPauseText.textContent = "Pausar el historial de reproducciones";
      btnPauseIcon.textContent = "pause_circle_outline";
    }
  }
});
