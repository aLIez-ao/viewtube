document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Iniciando script...");

  // --- ELEMENTOS ---
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const likeBtn = document.getElementById("likeBtn");
  const dislikeBtn = document.getElementById("dislikeBtn");
  const likeCountSpan = document.getElementById("likeCount");

  // --- VERIFICACIÓN DE MATERIALIZE (M) ---
  const M_AVAILABLE = typeof M !== "undefined";

  if (!M_AVAILABLE) {
    console.warn(
      "ADVERTENCIA: Materialize JS no se cargó. Usando modo compatibilidad (Alerts)."
    );
  }

  // --- INICIALIZAR MODAL ---
  let modalInstance = null;
  if (M_AVAILABLE && unsubscribeModal) {
    try {
      modalInstance = M.Modal.init(unsubscribeModal, {
        opacity: 0.5,
        inDuration: 250,
        outDuration: 250,
      });
    } catch (e) {
      console.error("Error iniciando modal:", e);
    }
  }

  // --- HELPER PARA MENSAJES ---
  function showMessage(msg) {
    if (M_AVAILABLE) M.toast({ html: msg });
    else console.log("Toast:", msg); // Fallback silencioso o alert(msg) si prefieres
  }

  // --- LÓGICA DE SUSCRIPCIÓN ---
  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const isSubscribed = subscribeBtn.classList.contains("subscribed");

      if (isSubscribed) {
        // Intentar abrir modal, si no hay librería, desuscribir directo con confirm nativo
        if (modalInstance) {
          modalInstance.open();
        } else {
          if (confirm("¿Quieres anular tu suscripción?")) {
            performSubscriptionAction();
          }
        }
      } else {
        performSubscriptionAction();
      }
    });
  }

  if (confirmUnsubBtn) {
    confirmUnsubBtn.addEventListener("click", function (e) {
      e.preventDefault();
      performSubscriptionAction();
      if (modalInstance) modalInstance.close();
    });
  }

  function performSubscriptionAction() {
    const channelId = subscribeBtn.getAttribute("data-channel-id");
    subscribeBtn.disabled = true;

    fetch("actions/subscribe.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ channel_id: channelId }),
    })
      .then((response) => response.json())
      .then((data) => {
        subscribeBtn.disabled = false;
        if (data.success) {
          updateSubscribeButton(data.status, data.count);
        } else if (data.error === "auth_required") {
          showMessage("Debes iniciar sesión");
          setTimeout(() => (window.location.href = "login.php"), 1000);
        } else {
          showMessage("Error: " + data.error);
        }
      })
      .catch((err) => {
        console.error(err);
        subscribeBtn.disabled = false;
      });
  }

  function updateSubscribeButton(status, count) {
    const countSpan = document.getElementById("subscribersCount");
    if (status === "subscribed") {
      subscribeBtn.classList.add("subscribed");
      subscribeBtn.textContent = "Suscrito";
      showMessage("Suscripción añadida");
    } else {
      subscribeBtn.classList.remove("subscribed");
      subscribeBtn.textContent = "Suscribirse";
      showMessage("Suscripción eliminada");
    }
    if (countSpan)
      countSpan.textContent =
        new Intl.NumberFormat().format(count) + " suscriptores";
  }

  // --- LÓGICA DE LIKES ---
  function handleRate(type) {
    // Efecto visual inmediato
    if (type === "like") {
      likeBtn.classList.toggle("active");
      dislikeBtn.classList.remove("active");
    } else {
      dislikeBtn.classList.toggle("active");
      likeBtn.classList.remove("active");
    }

    const videoId = likeBtn.getAttribute("data-video-id");

    fetch("actions/rate_video.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ video_id: videoId, type: type }),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("SERVER RESPONSE LIKES:", data);

        if (data.success) {
          updateRateUI(data.likes, data.dislikes, data.action, type);
        } else if (data.error === "auth_required") {
          showMessage("Inicia sesión para valorar");
          likeBtn.classList.remove("active");
          dislikeBtn.classList.remove("active");
        }
      })
      .catch((err) => console.error("Fetch Error:", err));
  }

  if (likeBtn) likeBtn.addEventListener("click", () => handleRate("like"));
  if (dislikeBtn)
    dislikeBtn.addEventListener("click", () => handleRate("dislike"));

  function updateRateUI(likes, dislikes, action, typeTriggered) {
    if (likeCountSpan) {
      likeCountSpan.textContent = new Intl.NumberFormat().format(likes);
    }

    if (action === "removed") {
      likeBtn.classList.remove("active");
      dislikeBtn.classList.remove("active");
    } else if (typeTriggered === "like") {
      likeBtn.classList.add("active");
      dislikeBtn.classList.remove("active");
    } else {
      dislikeBtn.classList.add("active");
      likeBtn.classList.remove("active");
    }
  }
});
