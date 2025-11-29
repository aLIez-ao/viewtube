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

  // Variables Globales de Comentarios
  let activeReplyForm = null;

  // Función para inicializar comportamiento de formularios (Principal o Clonado)
  function setupCommentForm(formContainer) {
    const input = formContainer.querySelector("textarea");
    const submitBtn = formContainer.querySelector("button:last-child"); // Comentar
    const cancelBtn = formContainer.querySelector("button:first-child"); // Cancelar
    const actionsDiv = formContainer.querySelector(".comment-form-actions");

    // Estado inicial
    if (!formContainer.classList.contains("reply-active")) {
      actionsDiv.style.display = "none";
    } else {
      actionsDiv.style.display = "flex";
    }

    // Auto-resize
    const autoResize = () => {
      input.style.height = "auto";
      input.style.height = input.scrollHeight + "px";
    };
    input.addEventListener("input", autoResize);

    // Foco (Solo para el principal)
    if (!formContainer.classList.contains("reply-active")) {
      input.addEventListener(
        "focus",
        () => (actionsDiv.style.display = "flex")
      );
    }

    // Habilitar botón
    input.addEventListener("input", () => {
      if (input.value.trim().length > 0) submitBtn.removeAttribute("disabled");
      else submitBtn.setAttribute("disabled", "true");
    });

    // Cancelar
    cancelBtn.addEventListener("click", () => {
      input.value = "";
      input.style.height = "auto";
      submitBtn.setAttribute("disabled", "true");

      if (formContainer.classList.contains("reply-active")) {
        formContainer.remove(); // Eliminar form de respuesta
        activeReplyForm = null;
      } else {
        actionsDiv.style.display = "none"; // Ocultar acciones del principal
        input.blur();
      }
    });

    // Enviar
    submitBtn.addEventListener("click", () => {
      const content = input.value.trim();
      const urlParams = new URLSearchParams(window.location.search);
      const videoId = urlParams.get("id");
      // Obtener ID del padre si es una respuesta
      const parentId = formContainer.getAttribute("data-parent-id") || 0;

      if (!content || !videoId) return;

      submitBtn.disabled = true;

      fetch("actions/post_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          video_id: videoId,
          content: content,
          parent_id: parentId,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          submitBtn.disabled = false;
          if (data.success) {
            input.value = "";
            input.style.height = "auto";
            submitBtn.setAttribute("disabled", "true");

            if (parentId > 0) {
              // Insertar respuesta
              insertReply(data.comment, parentId);
              formContainer.remove();
              activeReplyForm = null;
            } else {
              // Insertar comentario nuevo
              prependComment(data.comment);
              actionsDiv.style.display = "none";
              input.blur();
            }
            showMessage("Comentario publicado");
          } else if (data.error === "auth_required") {
            showMessage("Debes iniciar sesión");
          } else {
            showMessage("Error: " + data.error);
          }
        })
        .catch((err) => {
          console.error(err);
          submitBtn.disabled = false;
        });
    });
  }

  // Inicializar el formulario principal
  const mainForm = document.querySelector(
    ".main-comment-form .input-field-comment"
  );
  if (mainForm) setupCommentForm(mainForm);

  // Delegación para Botones "Responder"
  if (commentsList) {
    commentsList.addEventListener("click", (e) => {
      const replyBtn = e.target.closest(".btn-reply-text");
      if (replyBtn) {
        const parentId = replyBtn.getAttribute("data-parent-id");
        openReplyForm(parentId);
      }
    });
  }

  function openReplyForm(parentId) {
    if (activeReplyForm) activeReplyForm.remove(); // Cerrar otros abiertos

    const container = document.getElementById("reply-form-" + parentId);
    if (!container) return;

    const formHTML = `
            <div class="input-field-comment reply-active" data-parent-id="${parentId}" style="margin-top: 16px;">
                <textarea class="materialize-textarea" placeholder="Añade una respuesta..."></textarea>
                <div class="comment-form-actions" style="display: flex;">
                    <button class="btn-flat waves-effect">Cancelar</button>
                    <button class="btn-flat waves-effect" disabled>Responder</button>
                </div>
            </div>
        `;

    container.innerHTML = formHTML;

    const newForm = container.querySelector(".input-field-comment");
    setupCommentForm(newForm);
    activeReplyForm = newForm;
    newForm.querySelector("textarea").focus();
  }

  // Función para insertar RESPUESTA visualmente
  function insertReply(comment, parentId) {
    const parentComment = document.getElementById("comment-" + parentId);
    if (!parentComment) return;

    let currentMargin = parseInt(parentComment.style.marginLeft || 0);
    let newMargin = currentMargin + 48;
    if (newMargin > 48) newMargin = 48; // Limitar sangría visual

    const html = createCommentHTML(comment, newMargin);
    const replyContainer = document.getElementById("reply-form-" + parentId);
    // Insertamos después del contenedor de respuesta del padre (visualmente abajo)
    // Ojo: insertAdjacentHTML en 'afterend' del padre lo pone como hermano siguiente
    parentComment.insertAdjacentHTML("afterend", html);
  }

  // Función reutilizable para HTML de comentarios
  function createCommentHTML(c, marginLeft = 0) {
    return `
            <div class="comment-item" id="comment-${c.id}" style="margin-left: ${marginLeft}px; animation: fadeIn 0.5s;">
                <a href="#!" class="comment-avatar-link"><img src="${c.avatar}" alt="${c.username}"></a>
                <div class="comment-body">
                    <div class="comment-header">
                        <span class="author-name">${c.username}</span>
                        <span class="comment-time">${c.date}</span>
                    </div>
                    <div class="comment-content">${c.content}</div>
                    <div class="comment-actions-toolbar">
                        <button class="btn-icon-comment like-comment-btn" data-comment-id="${c.id}">
                            <i class="material-icons">thumb_up_alt</i><span class="count-text"></span>
                        </button>
                        <button class="btn-icon-comment dislike-comment-btn" data-comment-id="${c.id}">
                            <i class="material-icons">thumb_down_alt</i>
                        </button>
                        <button class="btn-reply-text" data-parent-id="${c.id}">Responder</button>
                    </div>
                    <div class="reply-form-container" id="reply-form-${c.id}"></div>
                </div>
            </div>
        `;
  }

  function prependComment(c) {
    if (!commentsList) return;
    const html = createCommentHTML(c, 0);
    const emptyMsg = commentsList.querySelector("p.center-align");
    if (emptyMsg) emptyMsg.remove();
    commentsList.insertAdjacentHTML("afterbegin", html);
    updateCount();
  }

  function updateCount() {
    const t = document.querySelector(".comments-count-title");
    if (t) {
      const n = parseInt(t.textContent.replace(/[^0-9]/g, "")) || 0;
      t.textContent = new Intl.NumberFormat().format(n + 1) + " comentarios";
    }
  }

  // =================================================
  // 2. LÓGICA DE LIKES EN COMENTARIOS (NUEVO)
  // =================================================

  if (commentsList) {
    commentsList.addEventListener("click", function (e) {
      const likeBtn = e.target.closest(".like-comment-btn");
      const dislikeBtn = e.target.closest(".dislike-comment-btn");

      if (likeBtn) handleCommentRate(likeBtn, "like");
      else if (dislikeBtn) handleCommentRate(dislikeBtn, "dislike");
    });
  }

  function handleCommentRate(btn, type) {
    const commentId = btn.getAttribute("data-comment-id");
    if (!commentId) return;

    const parentToolbar = btn.closest(".comment-actions-toolbar");
    const likeButton = parentToolbar.querySelector(".like-comment-btn");
    const dislikeButton = parentToolbar.querySelector(".dislike-comment-btn");
    const countSpan = likeButton.querySelector(".count-text");
    const likeIcon = likeButton.querySelector("i");
    const dislikeIcon = dislikeButton.querySelector("i");

    // Optimistic UI
    if (type === "like") {
      const wasActive = likeButton.classList.contains("active-comment-rate");
      if (wasActive) {
        likeButton.classList.remove("active-comment-rate");
        likeIcon.textContent = "thumb_up_alt";
        let current = parseInt(countSpan.textContent) || 0;
        countSpan.textContent = current > 1 ? current - 1 : "";
      } else {
        likeButton.classList.add("active-comment-rate");
        likeIcon.textContent = "thumb_up";
        dislikeButton.classList.remove("active-comment-rate");
        dislikeIcon.textContent = "thumb_down_alt";

        let current = parseInt(countSpan.textContent) || 0;
        countSpan.textContent = current + 1;
      }
    } else {
      const wasActive = dislikeButton.classList.contains("active-comment-rate");
      if (wasActive) {
        dislikeButton.classList.remove("active-comment-rate");
        dislikeIcon.textContent = "thumb_down_alt";
      } else {
        dislikeButton.classList.add("active-comment-rate");
        dislikeIcon.textContent = "thumb_down";
        if (likeButton.classList.contains("active-comment-rate")) {
          likeButton.classList.remove("active-comment-rate");
          likeIcon.textContent = "thumb_up_alt";
          let current = parseInt(countSpan.textContent) || 0;
          countSpan.textContent = current > 1 ? current - 1 : "";
        }
      }
    }

    // Petición AJAX
    fetch("actions/rate_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ comment_id: commentId, type: type }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          countSpan.textContent = data.likes > 0 ? data.likes : "";
        } else if (data.error === "auth_required") {
          showMessage("Inicia sesión para valorar");
          // Revertir UI
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
    shareBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modalShareInstance.open();
    });
  } else if (shareBtn) {
    shareBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (navigator.share) {
        navigator
          .share({ title: document.title, url: originalUrl })
          .catch(console.error);
      } else {
        prompt("Copia este enlace:", originalUrl);
      }
    });
  }

  if (copyLinkBtn && shareUrlInput) {
    copyLinkBtn.addEventListener("click", () => {
      shareUrlInput.select();
      navigator.clipboard
        .writeText(shareUrlInput.value)
        .then(() => showMessage("Enlace copiado"));
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
