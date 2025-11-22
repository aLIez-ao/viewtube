document.addEventListener("DOMContentLoaded", function () {
  console.log("APP.JS: Iniciando...");

  // =================================================
  // GESTOR DEL SIDEBAR
  // =================================================
  const SidebarManager = {
    body: document.body,
    toggleBtn: document.getElementById("toggleSidebarBtn"),
    overlay: document.getElementById("sidebarOverlay"),

    init: function () {
      if (!this.toggleBtn) {
        console.error("APP.JS: No se encontró el botón 'toggleSidebarBtn'");
        return;
      }
      console.log("APP.JS: Botón sidebar encontrado.");

      // Determinar layout inicial
      const isWatchPage = this.body.classList.contains("layout-watch");
      const isLargeScreen = window.matchMedia("(min-width: 1201px)").matches;

      // Restaurar estado guardado (Solo PC + Guide)
      if (!isWatchPage && isLargeScreen) {
        const savedState = localStorage.getItem("viewtube_collapsed");
        if (savedState === "true") {
          this.body.classList.add("is-collapsed");
        }
      }

      // Click Listener
      this.toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        console.log("APP.JS: Click en sidebar toggle");
        this.handleToggle();
      });

      if (this.overlay) {
        this.overlay.addEventListener("click", () => {
          this.closeOverlay();
        });
      }
    },

    handleToggle: function () {
      const isWatchPage = this.body.classList.contains("layout-watch");
      const isLargeScreen = window.matchMedia("(min-width: 1201px)").matches;

      // CASO A: Layout Guide en PC (Mini Sidebar)
      if (!isWatchPage && isLargeScreen) {
        console.log("APP.JS: Alternando modo colapsado (PC)");
        this.body.classList.toggle("is-collapsed");
        localStorage.setItem(
          "viewtube_collapsed",
          this.body.classList.contains("is-collapsed")
        );
      }
      // CASO B: Móvil o Watch (Overlay)
      else {
        console.log("APP.JS: Alternando modo overlay (Móvil/Watch)");
        this.toggleOverlayMode();
      }
    },

    toggleOverlayMode: function () {
      const isOpen = this.body.classList.contains("sidebar-open");
      if (isOpen) {
        this.closeOverlay();
      } else {
        this.openOverlay();
      }
    },

    openOverlay: function () {
      this.body.classList.add("sidebar-open");
      if (this.overlay) this.overlay.classList.add("active");
    },

    closeOverlay: function () {
      this.body.classList.remove("sidebar-open");
      if (this.overlay) this.overlay.classList.remove("active");
    },
  };

  SidebarManager.init();

  // =================================================
  // MENÚ USUARIO
  // =================================================
  const userBtn = document.getElementById("userMenuBtn");
  const userDropdown = document.getElementById("userDropdown");

  if (userBtn && userDropdown) {
    userBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      userDropdown.classList.toggle("active");
    });

    document.addEventListener("click", function (e) {
      if (!userDropdown.contains(e.target) && e.target !== userBtn) {
        userDropdown.classList.remove("active");
      }
    });
  }
});
