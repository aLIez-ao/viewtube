// assets/js/app.js - VERSIÓN DE DIAGNÓSTICO
document.addEventListener("DOMContentLoaded", function () {
  console.log("1. App.js cargado correctamente.");

  // --- LÓGICA DEL SIDEBAR ---
  const sidebar = document.querySelector(".sidenav");
  // ... (tu lógica del sidebar anterior se mantiene igual, la omito para resumir) ...

  // --- LÓGICA DEL MENÚ DE USUARIO (DEBUG) ---
  const userBtn = document.getElementById("userMenuBtn");
  const userDropdown = document.getElementById("userDropdown");

  console.log("2. Buscando elementos...", {
    boton: userBtn,
    menu: userDropdown,
  });

  if (userBtn && userDropdown) {
    console.log("3. Elementos encontrados. Agregando evento click.");

    userBtn.addEventListener("click", function (e) {
      console.log("4. ¡CLIC DETECTADO en el botón!");
      e.stopPropagation();
      userDropdown.classList.toggle("active");
      console.log(
        "5. Clase 'active' alternada. Estado actual:",
        userDropdown.classList
      );
    });

    document.addEventListener("click", function (e) {
      if (!userDropdown.contains(e.target) && e.target !== userBtn) {
        if (userDropdown.classList.contains("active")) {
          console.log("6. Clic fuera -> Cerrando menú.");
          userDropdown.classList.remove("active");
        }
      }
    });
  } else {
    console.error(
      "ERROR: No se encontró el botón (userMenuBtn) o el menú (userDropdown) en el HTML. Revisa los IDs en header.php"
    );
  }
});
