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
  // Botones de acción (Cancelar/Comentar)
  const commentFormActions = document.querySelector(".comment-form-actions");
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
  // 1. LÓGICA DE COMENTARIOS (COMPLETA)
  // =================================================

  if (commentInput && submitCommentBtn && commentFormActions) {
    // 0. Estado Inicial: Ocultar botones de acción
    commentFormActions.style.display = "none";

    // 1. Auto-resize del Textarea
    const autoResize = () => {
      commentInput.style.height = "auto";
      commentInput.style.height = commentInput.scrollHeight + "px";
    };
    commentInput.addEventListener("input", autoResize);

    // 2. Mostrar botones al hacer foco (Click en el input)
    commentInput.addEventListener("focus", function () {
      commentFormActions.style.display = "flex";
    });

    // 3. Habilitar botón enviar si hay texto
    commentInput.addEventListener("input", function () {
      const text = this.value.trim();
      if (text.length > 0) {
        submitCommentBtn.removeAttribute("disabled");
      } else {
        submitCommentBtn.setAttribute("disabled", "true");
      }
    });

    // 4. Botón Cancelar (Limpiar y Ocultar)
    if (cancelCommentBtn) {
      cancelCommentBtn.addEventListener("click", function () {
        // A. Limpiar texto
        commentInput.value = "";
        // B. Resetear altura del textarea
        commentInput.style.height = "auto";
        // C. Deshabilitar botón enviar
        submitCommentBtn.setAttribute("disabled", "true");
        // D. Ocultar botones de acción
        commentFormActions.style.display = "none";
        // E. Quitar foco del input (blur)
        commentInput.blur();
      });
    }

    // 5. Enviar Comentario
    submitCommentBtn.addEventListener("click", function () {
      const content = commentInput.value.trim();
      const urlParams = new URLSearchParams(window.location.search);
      const videoId = urlParams.get("id");

      if (!content || !videoId) return;

      // Deshabilitar para evitar doble envío
      submitCommentBtn.disabled = true;

      fetch("actions/post_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ video_id: videoId, content: content }),
      })
        .then((response) => response.json())
        .then((data) => {
          submitCommentBtn.disabled = false;

          if (data.success) {
            // Resetear formulario completamente al enviar exitosamente
            commentInput.value = "";
            commentInput.style.height = "auto";
            submitCommentBtn.setAttribute("disabled", "true");
            commentFormActions.style.display = "none"; // Ocultar botones
            commentInput.blur(); // Quitar foco

            prependComment(data.comment);
            showMessage("Comentario publicado");
          } else if (data.error === "auth_required") {
            showMessage("Debes iniciar sesión");
            setTimeout(() => (window.location.href = "login.php"), 1500);
          } else {
            showMessage("Error: " + data.error);
          }
        })
        .catch((err) => {
          console.error(err);
          submitCommentBtn.disabled = false;
          showMessage("Error de conexión");
        });
    });
  }

  // Función para insertar comentario nuevo al principio
  function prependComment(commentData) {
    if (!commentsList) return;

    // CORRECCIÓN DE ESPACIOS: HTML comprimido para evitar sangrías no deseadas
    // Además añadimos los botones con sus clases para que el like funcione en los nuevos
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
                    <div class="comment-content">${commentData.content}</div>
                    <div class="comment-actions-toolbar">
                        <button class="btn-icon-comment like-comment-btn" data-comment-id="${commentData.id}">
                            <i class="material-icons">thumb_up_alt</i>
                            <span class="count-text"></span>
                        </button>
                        <button class="btn-icon-comment dislike-comment-btn" data-comment-id="${commentData.id}">
                            <i class="material-icons">thumb_down_alt</i>
                        </button>
                        <button class="btn-reply-text">Responder</button>
                    </div>
                </div>
            </div>
        `;

    const emptyMsg = commentsList.querySelector("p.center-align");
    if (emptyMsg) emptyMsg.remove();

    commentsList.insertAdjacentHTML("afterbegin", commentHTML);

    // Actualizar contador visualmente (Opcional)
    const countTitle = document.querySelector(".comments-count-title");
    if (countTitle) {
      const currentText = countTitle.textContent;
      const currentNum = parseInt(currentText.replace(/[^0-9]/g, "")) || 0;
      countTitle.textContent =
        new Intl.NumberFormat().format(currentNum + 1) + " comentarios";
    }
  }

  // =================================================
  // 2. LÓGICA DE LIKES EN COMENTARIOS (NUEVO)
  // =================================================

  // Delegación de eventos para manejar clics en comentarios existentes Y nuevos
  if (commentsList) {
    commentsList.addEventListener("click", function (e) {
      // Buscamos si el clic fue en un botón o dentro de él (icono)
      const likeBtn = e.target.closest(".like-comment-btn");
      const dislikeBtn = e.target.closest(".dislike-comment-btn");

      if (likeBtn) {
        handleCommentRate(likeBtn, "like");
      } else if (dislikeBtn) {
        handleCommentRate(dislikeBtn, "dislike");
      }
    });
  }

  function handleCommentRate(btn, type) {
    const commentId = btn.getAttribute("data-comment-id");
    // Si es un comentario nuevo recién insertado, asegúrate de que el backend devuelva el ID real
    if (!commentId) return;

    // Referencias a los elementos de este comentario específico
    const parentToolbar = btn.closest(".comment-actions-toolbar");
    const likeButton = parentToolbar.querySelector(".like-comment-btn");
    const dislikeButton = parentToolbar.querySelector(".dislike-comment-btn");
    const countSpan = likeButton.querySelector(".count-text");
    const likeIcon = likeButton.querySelector("i");
    const dislikeIcon = dislikeButton.querySelector("i");

    // Optimistic UI (Cambio visual inmediato)
    if (type === "like") {
      const wasActive = likeButton.classList.contains("active-comment-rate");
      if (wasActive) {
        // Quitar Like
        likeButton.classList.remove("active-comment-rate");
        likeIcon.textContent = "thumb_up_alt";
        // Restar 1 visualmente
        let current = parseInt(countSpan.textContent) || 0;
        countSpan.textContent = current > 1 ? current - 1 : "";
      } else {
        // Poner Like
        likeButton.classList.add("active-comment-rate");
        likeIcon.textContent = "thumb_up"; // Relleno

        // Si tenía dislike, quitarlo
        if (dislikeButton.classList.contains("active-comment-rate")) {
          dislikeButton.classList.remove("active-comment-rate");
          dislikeIcon.textContent = "thumb_down_alt";
        }

        // Sumar 1 visualmente
        let current = parseInt(countSpan.textContent) || 0;
        countSpan.textContent = current + 1;
      }
    } else {
      // Dislike
      const wasActive = dislikeButton.classList.contains("active-comment-rate");
      if (wasActive) {
        // Quitar Dislike
        dislikeButton.classList.remove("active-comment-rate");
        dislikeIcon.textContent = "thumb_down_alt";
      } else {
        // Poner Dislike
        dislikeButton.classList.add("active-comment-rate");
        dislikeIcon.textContent = "thumb_down"; // Relleno

        // Si había like, quitarlo y restar
        if (likeButton.classList.contains("active-comment-rate")) {
          likeButton.classList.remove("active-comment-rate");
          likeIcon.textContent = "thumb_up_alt";
          let current = parseInt(countSpan.textContent) || 0;
          countSpan.textContent = current > 1 ? current - 1 : "";
        }
      }
    }

    // Petición AJAX al servidor
    fetch("actions/rate_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ comment_id: commentId, type: type }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Confirmar contador real del servidor para asegurar consistencia
          countSpan.textContent = data.likes > 0 ? data.likes : "";
        } else if (data.error === "auth_required") {
          showMessage("Inicia sesión para valorar");
          // Revertir UI (simplificado: recargar o quitar clases manualmente)
          likeButton.classList.remove("active-comment-rate");
          dislikeButton.classList.remove("active-comment-rate");
          likeIcon.textContent = "thumb_up_alt";
          dislikeIcon.textContent = "thumb_down_alt";
        }
      })
      .catch((err) => console.error(err));
  }

  // =================================================
  // 3. LÓGICA COMPARTIR
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
  // 4. LÓGICA DE SUSCRIPCIÓN Y LIKES DE VIDEO
  // =================================================

  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const isSubscribed = subscribeBtn.classList.contains("subscribed");
      if (isSubscribed) {
        if (modalUnsubInstance) modalUnsubInstance.open();
        else if (confirm("¿Anular?")) performSubscriptionAction();
      } else performSubscriptionAction();
    });
  }
  if (confirmUnsubBtn) {
    confirmUnsubBtn.addEventListener("click", (e) => {
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
      .then((r) => r.text())
      .then((text) => {
        subscribeBtn.disabled = false;
        try {
          const data = JSON.parse(text);
          if (data.success) updateSubscribeButton(data.status, data.count);
        } catch (e) {}
      });
  }
  function updateSubscribeButton(s, c) {
    const sp = document.getElementById("subscribersCount");
    if (s === "subscribed") {
      subscribeBtn.classList.add("subscribed");
      subscribeBtn.textContent = "Suscrito";
    } else {
      subscribeBtn.classList.remove("subscribed");
      subscribeBtn.textContent = "Suscribirse";
    }
    if (sp) sp.textContent = c + " suscriptores";
  }

  // LIKES VIDEO PRINCIPAL
  function updateIcons() {
    if (!likeBtn) return;
    const i1 = likeBtn.querySelector("i"),
      i2 = dislikeBtn.querySelector("i");
    i1.textContent = likeBtn.classList.contains("active")
      ? "thumb_up"
      : "thumb_up_alt";
    i2.textContent = dislikeBtn.classList.contains("active")
      ? "thumb_down"
      : "thumb_down_alt";
  }
  function handleRate(type) {
    if (type === "like") {
      const active = likeBtn.classList.contains("active");
      if (active) likeBtn.classList.remove("active");
      else {
        likeBtn.classList.add("active");
        dislikeBtn.classList.remove("active");
      }
    } else {
      const active = dislikeBtn.classList.contains("active");
      if (active) dislikeBtn.classList.remove("active");
      else {
        dislikeBtn.classList.add("active");
        likeBtn.classList.remove("active");
      }
    }
    updateIcons();
    const vid = likeBtn.getAttribute("data-video-id");
    fetch("actions/rate_video.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ video_id: vid, type: type }),
    })
      .then((r) => r.text())
      .then((t) => {
        try {
          const d = JSON.parse(t);
          if (d.success) updateRateUI(d.likes, d.dislikes, d.action, type);
        } catch (e) {}
      });
  }
  if (likeBtn) likeBtn.addEventListener("click", () => handleRate("like"));
  if (dislikeBtn)
    dislikeBtn.addEventListener("click", () => handleRate("dislike"));
  function updateRateUI(l, d, a, t) {
    if (likeCountSpan)
      likeCountSpan.textContent = new Intl.NumberFormat().format(l);
    if (a === "removed") {
      likeBtn.classList.remove("active");
      dislikeBtn.classList.remove("active");
    } else if (t === "like") {
      likeBtn.classList.add("active");
      dislikeBtn.classList.remove("active");
    } else {
      dislikeBtn.classList.add("active");
      likeBtn.classList.remove("active");
    }
    updateIcons();
  }
  updateIcons();
});
