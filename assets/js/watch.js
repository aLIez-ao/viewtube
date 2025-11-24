document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Cargado.");

  // --- ELEMENTOS ---
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const likeBtn = document.getElementById("likeBtn");
  const dislikeBtn = document.getElementById("dislikeBtn");
  const likeCountSpan = document.getElementById("likeCount");

  // --- ELEMENTOS DE COMPARTIR (NUEVO) ---
  const shareBtn = document.getElementById("shareBtn");
  const shareModal = document.getElementById("shareModal");
  const shareUrlInput = document.getElementById("shareUrlInput");
  const copyLinkBtn = document.getElementById("copyLinkBtn");
  const startAtCheckbox = document.getElementById("startAtCheckbox");

  // Guardamos la URL original para restaurarla si desmarcan el checkbox
  const originalUrl = shareUrlInput
    ? shareUrlInput.value
    : window.location.href;

  // --- UTILIDADES ---
  const M_AVAILABLE = typeof M !== "undefined";
  function showMessage(msg) {
    if (M_AVAILABLE) M.toast({ html: msg });
    else console.log("Toast:", msg);
  }

  // --- INICIALIZAR MODALES ---
  let modalUnsubInstance = null;
  let modalShareInstance = null;

  if (M_AVAILABLE) {
    // 1. Modal Desuscripción
    if (unsubscribeModal) {
      modalUnsubInstance = M.Modal.init(unsubscribeModal, { opacity: 0.5 });
    }
    // 2. Modal Compartir (NUEVO)
    if (shareModal) {
      modalShareInstance = M.Modal.init(shareModal, {
        opacity: 0.5,
        onOpenStart: () => {
          // Resetear checkbox al abrir
          if (startAtCheckbox) startAtCheckbox.checked = false;
          if (shareUrlInput) shareUrlInput.value = originalUrl;
        },
      });
    }
  }

  // --- LÓGICA COMPARTIR (NUEVO) ---
  if (shareBtn && modalShareInstance) {
    shareBtn.addEventListener("click", function (e) {
      e.preventDefault();
      modalShareInstance.open();
    });
  } else if (shareBtn) {
    // Fallback si Materialize falla
    shareBtn.addEventListener("click", () =>
      prompt("Copia este enlace:", originalUrl)
    );
  }

  // Copiar al portapapeles
  if (copyLinkBtn && shareUrlInput) {
    copyLinkBtn.addEventListener("click", function () {
      shareUrlInput.select();
      shareUrlInput.setSelectionRange(0, 99999); // Para móviles

      navigator.clipboard
        .writeText(shareUrlInput.value)
        .then(() => showMessage("Enlace copiado al portapapeles"))
        .catch(() => showMessage("Error al copiar"));
    });
  }

  // --- LÓGICA DE SUSCRIPCIÓN (EXISTENTE) ---
  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const isSubscribed = subscribeBtn.classList.contains("subscribed");
      if (isSubscribed) {
        if (modalUnsubInstance) modalUnsubInstance.open();
        else if (confirm("¿Anular suscripción?")) performSubscriptionAction();
      } else {
        performSubscriptionAction();
      }
    });
  }

  if (confirmUnsubBtn) {
    confirmUnsubBtn.addEventListener("click", function (e) {
      e.preventDefault();
      performSubscriptionAction();
      if (modalUnsubInstance) modalUnsubInstance.close();
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
          if (data.success) updateSubscribeButton(data.status, data.count);
          else if (data.error === "auth_required") showAuthError();
        } catch (e) {
          console.error("Error JSON:", text);
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

  // --- LÓGICA DE LIKES (EXISTENTE) ---
  function updateIcons() {
    if (!likeBtn || !dislikeBtn) return;
    const likeIcon = likeBtn.querySelector("i");
    const dislikeIcon = dislikeBtn.querySelector("i");

    likeIcon.textContent = likeBtn.classList.contains("active")
      ? "thumb_up"
      : "thumb_up_alt";
    dislikeIcon.textContent = dislikeBtn.classList.contains("active")
      ? "thumb_down"
      : "thumb_down_alt";
  }

  function handleRate(type) {
    // Optimistic UI
    if (type === "like") {
      const wasActive = likeBtn.classList.contains("active");
      likeBtn.classList.toggle("active");
      if (!wasActive) dislikeBtn.classList.remove("active");
    } else {
      const wasActive = dislikeBtn.classList.contains("active");
      dislikeBtn.classList.toggle("active");
      if (!wasActive) likeBtn.classList.remove("active");
    }
    updateIcons();

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
          if (data.success)
            updateRateUI(data.likes, data.dislikes, data.action, type);
          else if (data.error === "auth_required") {
            showAuthError();
            // Revertir UI
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
    if (likeCountSpan)
      likeCountSpan.textContent = new Intl.NumberFormat().format(likes);

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
    updateIcons();
  }

  function showAuthError() {
    showMessage("Debes iniciar sesión");
    setTimeout(() => (window.location.href = "login.php"), 1500);
  }

  updateIcons();
});
