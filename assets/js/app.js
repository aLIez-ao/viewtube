document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;
  const menuBtns = document.querySelectorAll(".menu-btn");
  const sidebar = document.querySelector(".sidenav");

  if (!sidebar) return;

  // --- LÓGICA DE MEMORIA (Index) ---
  // Solo recuperamos el estado colapsado si el sidebar es FIJO
  if (sidebar.classList.contains("sidenav-fixed")) {
    const isCollapsed =
      localStorage.getItem("viewtube_sidebar_collapsed") === "true";
    if (isCollapsed) {
      body.classList.add("sidebar-collapsed");
    }
  }

  // --- LÓGICA DE BOTONES ---
  menuBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // Estamos en Index (Sidebar Fijo)
      if (
        sidebar.classList.contains("sidenav-fixed") &&
        window.innerWidth > 992
      ) {
        e.preventDefault(); // Evitamos que Materialize abra el overlay
        body.classList.toggle("sidebar-collapsed");

        // Guardar estado
        const isNowCollapsed = body.classList.contains("sidebar-collapsed");
        localStorage.setItem("viewtube_sidebar_collapsed", isNowCollapsed);
      }

      // Estamos en Watch (Sidebar Flotante)
      else {
        // No hacemos nada en JS.
        // El atributo HTML data-target="slide-out" del botón
        // hará que Materialize abra el menú con el fondo oscuro automáticamente.
      }
    });
  });
});
