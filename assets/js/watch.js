document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Listo.");

  // --- ELEMENTOS ---
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const likeBtn = document.getElementById("likeBtn");
  const dislikeBtn = document.getElementById("dislikeBtn");
  const likeCountSpan = document.getElementById("likeCount");

  // --- UTILS ---
  const M_AVAILABLE = typeof M !== "undefined";
  function showMessage(msg) {
    if (M_AVAILABLE) M.toast({ html: msg });
    else console.log("Toast:", msg);
  }

  // --- MODAL ---
  let modalInstance = null;
  if (M_AVAILABLE && unsubscribeModal) {
    try {
      modalInstance = M.Modal.init(unsubscribeModal, { opacity: 0.5 });
    } catch (e) {}
  }

  // --- SUSCRIPCIÓN ---
  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const isSubscribed = subscribeBtn.classList.contains("subscribed");
      if (isSubscribed) {
        if (modalInstance) modalInstance.open();
        else if (confirm("¿Deseas anular la suscripción?"))
          performSubscriptionAction();
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
      .then((response) => response.text())
      .then((text) => {
        subscribeBtn.disabled = false;
        try {
          const data = JSON.parse(text);
          if (data.success) {
            updateSubscribeButton(data.status, data.count);
          } else if (data.error === "auth_required") {
            showMessage("Debes iniciar sesión");
            setTimeout(() => (window.location.href = "login.php"), 1000);
          }
        } catch (e) {
          console.error("Error JSON Suscripción:", text);
        }
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

  // --- LIKES (Lógica Mejorada) ---

  // Función auxiliar para cambiar iconos (Relleno vs Contorno)
  function updateIcons() {
    if (!likeBtn || !dislikeBtn) return;

    const likeIcon = likeBtn.querySelector("i");
    const dislikeIcon = dislikeBtn.querySelector("i");

    // Si está activo, ponemos el icono relleno (thumb_up). Si no, el contorno (thumb_up_alt)
    if (likeBtn.classList.contains("active")) {
      likeIcon.textContent = "thumb_up";
    } else {
      likeIcon.textContent = "thumb_up_alt";
    }

    if (dislikeBtn.classList.contains("active")) {
      dislikeIcon.textContent = "thumb_down";
    } else {
      dislikeIcon.textContent = "thumb_down_alt";
    }
  }

  function handleRate(type) {
    // 1. Efecto visual inmediato (Optimistic UI)
    if (type === "like") {
      // Si ya estaba activo, lo quitamos. Si no, lo ponemos y quitamos dislike.
      if (likeBtn.classList.contains("active")) {
        likeBtn.classList.remove("active"); // Toggle OFF
      } else {
        likeBtn.classList.add("active"); // Toggle ON
        dislikeBtn.classList.remove("active");
      }
    } else {
      if (dislikeBtn.classList.contains("active")) {
        dislikeBtn.classList.remove("active"); // Toggle OFF
      } else {
        dislikeBtn.classList.add("active"); // Toggle ON
        likeBtn.classList.remove("active");
      }
    }

    // Actualizamos iconos inmediatamente para feedback visual
    updateIcons();

    // 2. Petición al servidor
    const videoId = likeBtn.getAttribute("data-video-id");

    fetch("actions/rate_video.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ video_id: videoId, type: type }),
    })
      .then((response) => response.text())
      .then((text) => {
        try {
          const data = JSON.parse(text);
          if (data.success) {
            updateRateUI(data.likes, data.dislikes, data.action, type);
          } else if (data.error === "auth_required") {
            showMessage("Inicia sesión para valorar");
            // Revertir UI si falló
            likeBtn.classList.remove("active");
            dislikeBtn.classList.remove("active");
            updateIcons();
          }
        } catch (e) {
          console.error("Error JSON Likes:", text);
        }
      });
  }

  if (likeBtn) likeBtn.addEventListener("click", () => handleRate("like"));
  if (dislikeBtn)
    dislikeBtn.addEventListener("click", () => handleRate("dislike"));

  function updateRateUI(likes, dislikes, action, typeTriggered) {
    if (likeCountSpan) {
      likeCountSpan.textContent = new Intl.NumberFormat().format(likes);
    }

    // Sincronizar clases finales con la verdad del servidor
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

    // Asegurar que los iconos coincidan con el estado final
    updateIcons();
  }

  // Ejecutar al inicio para asegurar estado correcto de iconos
  updateIcons();
});
