document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Cargado.");

  // --- ELEMENTOS EXISTENTES ---
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const likeBtn = document.getElementById("likeBtn");
  const dislikeBtn = document.getElementById("dislikeBtn");
  const likeCountSpan = document.getElementById("likeCount");
  const shareBtn = document.getElementById("shareBtn");
  const shareModal = document.getElementById("shareModal");
  const shareUrlInput = document.getElementById("shareUrlInput");
  const copyLinkBtn = document.getElementById("copyLinkBtn");
  const startAtCheckbox = document.getElementById("startAtCheckbox");

  const originalUrl = shareUrlInput
    ? shareUrlInput.value
    : window.location.href;

  // --- ELEMENTOS DE COMENTARIOS ---
  const commentInput = document.getElementById("commentInput");
  const submitCommentBtn = document.getElementById("submitCommentBtn");
  const commentsList = document.querySelector(".comments-list");
  const commentFormActions = document.querySelector(".comment-form-actions"); // Contenedor de botones
  const cancelCommentBtn = document.querySelector(
    ".comment-form-actions button:first-child"
  );

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
    if (unsubscribeModal) {
      try {
        modalUnsubInstance = M.Modal.init(unsubscribeModal, { opacity: 0.5 });
      } catch (e) {
        console.error("Error init unsub modal", e);
      }
    }
    if (shareModal) {
      try {
        modalShareInstance = M.Modal.init(shareModal, {
          opacity: 0.5,
          onOpenStart: () => {
            if (startAtCheckbox) startAtCheckbox.checked = false;
            if (shareUrlInput) shareUrlInput.value = originalUrl;
          },
        });
      } catch (e) {
        console.error("Error init share modal", e);
      }
    }
  }

  // =================================================
  // 1. LÓGICA DE COMENTARIOS (ACTUALIZADA)
  // =================================================

  if (commentInput && submitCommentBtn && commentFormActions) {
    // 0. Estado Inicial: Ocultar botones de acción
    commentFormActions.style.display = "none";

    // 1. Mostrar botones al hacer foco (click en input)
    commentInput.addEventListener("focus", function () {
      commentFormActions.style.display = "flex";
    });

    // 2. Habilitar/Deshabilitar botón según contenido
    commentInput.addEventListener("input", function () {
      const text = this.value.trim();
      if (text.length > 0) {
        submitCommentBtn.removeAttribute("disabled");
      } else {
        submitCommentBtn.setAttribute("disabled", "true");
      }
    });

    // 3. Botón Cancelar
    if (cancelCommentBtn) {
      cancelCommentBtn.addEventListener("click", function () {
        // A. Limpiar texto
        commentInput.value = "";
        // B. Deshabilitar botón enviar
        submitCommentBtn.setAttribute("disabled", "true");
        // C. Ocultar botones
        commentFormActions.style.display = "none";
        // D. Quitar foco del input (blur)
        commentInput.blur();
      });
    }

    // 4. Enviar Comentario
    submitCommentBtn.addEventListener("click", function () {
      const content = commentInput.value.trim();
      const urlParams = new URLSearchParams(window.location.search);
      const videoId = urlParams.get("id");

      if (!content || !videoId) return;

      submitCommentBtn.disabled = true;

      fetch("actions/post_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          video_id: videoId,
          content: content,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          submitCommentBtn.disabled = false;

          if (data.success) {
            // Resetear formulario completamente al enviar
            commentInput.value = "";
            submitCommentBtn.setAttribute("disabled", "true");
            commentFormActions.style.display = "none"; // Ocultar botones
            commentInput.blur(); // Quitar foco

            prependComment(data.comment);
            showMessage("Comentario publicado");
          } else if (data.error === "auth_required") {
            showMessage("Debes iniciar sesión para comentar");
            setTimeout(() => (window.location.href = "login.php"), 1500);
          } else {
            showMessage("Error al publicar: " + data.error);
          }
        })
        .catch((err) => {
          console.error(err);
          submitCommentBtn.disabled = false;
          showMessage("Error de conexión");
        });
    });
  }

  // Función para inyectar comentario
  function prependComment(commentData) {
    if (!commentsList) return;

    const commentHTML = `
            <div class="comment-item" style="animation: fadeIn 0.5s;">
                <a href="#!" class="comment-avatar-link">
                    <img src="${commentData.avatar}" alt="${commentData.username}">
                </a>
                <div class="comment-body">
                    <div class="comment-header">
                        <span class="author-name">${commentData.username}</span>
                        <span class="comment-time">${commentData.date}</span>
                    </div>
                    <div class="comment-content">
                        ${commentData.content}
                    </div>
                    <div class="comment-actions-toolbar">
                        <button class="btn-icon-comment like-comment">
                            <i class="material-icons">thumb_up_alt</i>
                            <span class="count-text"></span>
                        </button>
                        <button class="btn-icon-comment dislike-comment">
                            <i class="material-icons">thumb_down_alt</i>
                        </button>
                        <button class="btn-reply-text">Responder</button>
                    </div>
                </div>
            </div>
        `;

    const emptyMsg = commentsList.querySelector("p.center-align");
    if (emptyMsg && emptyMsg.textContent.includes("primero")) {
      emptyMsg.remove();
    }

    commentsList.insertAdjacentHTML("afterbegin", commentHTML);

    const countTitle = document.querySelector(".comments-count-title");
    if (countTitle) {
      const currentText = countTitle.textContent;
      const currentNum = parseInt(currentText.replace(/[^0-9]/g, "")) || 0;
      countTitle.textContent =
        new Intl.NumberFormat().format(currentNum + 1) + " comentarios";
    }
  }

  // =================================================
  // 2. LÓGICA COMPARTIR
  // =================================================

  if (shareBtn && modalShareInstance) {
    shareBtn.addEventListener("click", function (e) {
      e.preventDefault();
      modalShareInstance.open();
    });
  } else if (shareBtn) {
    shareBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (navigator.share) {
        navigator
          .share({
            title: document.title,
            url: originalUrl,
          })
          .catch(console.error);
      } else {
        prompt("Copia este enlace:", originalUrl);
      }
    });
  }

  if (copyLinkBtn && shareUrlInput) {
    copyLinkBtn.addEventListener("click", function () {
      shareUrlInput.select();
      shareUrlInput.setSelectionRange(0, 99999);

      if (navigator.clipboard) {
        navigator.clipboard
          .writeText(shareUrlInput.value)
          .then(() => showMessage("Enlace copiado"))
          .catch(() => showMessage("Error al copiar"));
      } else {
        document.execCommand("copy");
        showMessage("Enlace copiado");
      }
    });
  }

  if (startAtCheckbox && shareUrlInput) {
    startAtCheckbox.addEventListener("change", function () {
      if (this.checked) {
        const separator = originalUrl.includes("?") ? "&" : "?";
        shareUrlInput.value = originalUrl + separator + "t=0s";
      } else {
        shareUrlInput.value = originalUrl;
      }
    });
  }

  // =================================================
  // 3. LÓGICA DE SUSCRIPCIÓN
  // =================================================

  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const isSubscribed = subscribeBtn.classList.contains("subscribed");

      if (isSubscribed) {
        if (modalUnsubInstance) {
          modalUnsubInstance.open();
        } else if (confirm("¿Quieres anular tu suscripción?")) {
          performSubscriptionAction();
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
          if (data.success) {
            updateSubscribeButton(data.status, data.count);
          } else if (data.error === "auth_required") {
            showAuthError();
          } else {
            console.error("Error suscripción:", data.error);
          }
        } catch (e) {
          console.error("Error JSON Suscripción:", text);
        }
      })
      .catch((err) => {
        subscribeBtn.disabled = false;
        console.error(err);
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

    if (countSpan && count) {
      countSpan.textContent = count + " suscriptores";
    }
  }

  // =================================================
  // 4. LÓGICA DE LIKES
  // =================================================

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
    if (type === "like") {
      const wasActive = likeBtn.classList.contains("active");
      if (wasActive) likeBtn.classList.remove("active");
      else {
        likeBtn.classList.add("active");
        dislikeBtn.classList.remove("active");
      }
    } else {
      const wasActive = dislikeBtn.classList.contains("active");
      if (wasActive) dislikeBtn.classList.remove("active");
      else {
        dislikeBtn.classList.add("active");
        likeBtn.classList.remove("active");
      }
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
          if (data.success) {
            updateRateUI(data.likes, data.dislikes, data.action, type);
          } else if (data.error === "auth_required") {
            showAuthError();
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
