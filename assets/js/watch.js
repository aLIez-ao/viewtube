document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Cargado e inicializado.");

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

  // Contenedor de acciones
  const commentFormWrapper = document.querySelector(".main-comment-form");
  const commentFormActions = commentFormWrapper
    ? commentFormWrapper.querySelector(".comment-form-actions")
    : null;
  const cancelCommentBtn = commentFormActions
    ? commentFormActions.querySelector("button:first-child")
    : null;

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
      } catch (e) {}
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
      } catch (e) {}
    }
  }

  // =================================================
  // 1. LÓGICA DE COMENTARIOS (CREAR Y RESPONDER)
  // =================================================

  let activeReplyForm = null;

  function setupCommentForm(formContainer) {
    const input = formContainer.querySelector("textarea");
    const actionsDiv = formContainer.querySelector(".comment-form-actions");

    if (!input || !actionsDiv) return;

    const submitBtn = actionsDiv.querySelector("button:last-child");
    const cancelBtn = actionsDiv.querySelector("button:first-child");

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

    // Foco
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
    if (cancelBtn) {
      cancelBtn.addEventListener("click", (e) => {
        e.preventDefault();
        input.value = "";
        input.style.height = "auto";
        submitBtn.setAttribute("disabled", "true");

        if (formContainer.classList.contains("reply-active")) {
          formContainer.remove();
          activeReplyForm = null;
        } else {
          actionsDiv.style.display = "none";
          input.blur();
        }
      });
    }

    // Enviar
    if (submitBtn) {
      submitBtn.addEventListener("click", (e) => {
        e.preventDefault();
        const content = input.value.trim();
        const urlParams = new URLSearchParams(window.location.search);
        const videoId = urlParams.get("id");
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
                insertReply(data.comment, parentId);
                formContainer.remove();
                activeReplyForm = null;
              } else {
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
  }

  const mainFormContainer = document.querySelector(
    ".main-comment-form .input-field-comment"
  );
  if (mainFormContainer) setupCommentForm(mainFormContainer);

  // =================================================
  // DELEGACIÓN CENTRALIZADA DE EVENTOS (CRUCIAL)
  // =================================================
  if (commentsList) {
    commentsList.addEventListener("click", (e) => {
      // A. RESPONDER
      const replyBtn = e.target.closest(".btn-reply-text");
      if (replyBtn) {
        openReplyForm(replyBtn.getAttribute("data-parent-id"));
        return;
      }

      // B. LIKES/DISLIKES
      const likeBtn = e.target.closest(".like-comment-btn");
      const dislikeBtn = e.target.closest(".dislike-comment-btn");
      if (likeBtn) {
        handleCommentRate(likeBtn, "like");
        return;
      }
      if (dislikeBtn) {
        handleCommentRate(dislikeBtn, "dislike");
        return;
      }

      // C. MENÚ DE OPCIONES (3 PUNTOS) - ¡ESTO FALTABA!
      const menuBtn = e.target.closest(".btn-comment-menu");
      if (menuBtn) {
        e.stopPropagation();
        const wrapper = menuBtn.closest(".comment-menu-wrapper");
        // Cerrar otros menús abiertos
        document
          .querySelectorAll(".comment-menu-wrapper.active")
          .forEach((el) => {
            if (el !== wrapper) el.classList.remove("active");
          });
        wrapper.classList.toggle("active");
        return;
      }

      // D. OPCIÓN EDITAR
      const editBtn = e.target.closest(".btn-edit-comment");
      if (editBtn) {
        const id = editBtn.getAttribute("data-id");
        // Cerrar menú
        editBtn.closest(".comment-menu-wrapper").classList.remove("active");
        startEditingComment(id);
        return;
      }

      // E. OPCIÓN ELIMINAR
      const deleteBtn = e.target.closest(".btn-delete-comment");
      if (deleteBtn) {
        const id = deleteBtn.getAttribute("data-id");
        // Cerrar menú
        deleteBtn.closest(".comment-menu-wrapper").classList.remove("active");
        if (confirm("¿Eliminar comentario permanentemente?")) {
          deleteComment(id);
        }
        return;
      }
    });
  }

  // CERRAR MENÚS AL CLIC FUERA
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".comment-menu-wrapper")) {
      document
        .querySelectorAll(".comment-menu-wrapper.active")
        .forEach((el) => {
          el.classList.remove("active");
        });
    }
  });

  // =================================================
  // FUNCIONES AUXILIARES (RESPUESTA, LIKES, EDICIÓN)
  // =================================================

  function openReplyForm(parentId) {
    if (activeReplyForm) activeReplyForm.remove();
    const container = document.getElementById("reply-form-" + parentId);
    if (!container) return;

    container.innerHTML = `
            <div class="input-field-comment reply-active" data-parent-id="${parentId}" style="margin-top: 16px;">
                <textarea class="materialize-textarea" placeholder="Añade una respuesta..."></textarea>
                <div class="comment-form-actions" style="display: flex;">
                    <button class="btn-flat waves-effect">Cancelar</button>
                    <button class="btn-flat waves-effect" disabled>Responder</button>
                </div>
            </div>`;

    const newForm = container.querySelector(".input-field-comment");
    setupCommentForm(newForm);
    activeReplyForm = newForm;
    newForm.querySelector("textarea").focus();
  }

  function insertReply(comment, parentId) {
    const parentComment = document.getElementById("comment-" + parentId);
    if (!parentComment) return;

    let currentMargin = parseInt(parentComment.style.marginLeft || 0);
    let newMargin = Math.min(currentMargin + 48, 48); // Max sangría 48px

    const html = createCommentHTML(comment, newMargin);
    parentComment.insertAdjacentHTML("afterend", html);
  }

  function createCommentHTML(c, marginLeft = 0) {
    // Nota: Agregamos botones de menú aquí para que los nuevos comentarios también tengan la opción
    // (Aunque para que funcionen 100% en nuevos, el backend debe decir si 'isOwner' es true.
    //  Por simplificación en JS asumimos que si acabas de comentar, eres el dueño).
    return `
            <div class="comment-item" id="comment-${c.id}" style="margin-left: ${marginLeft}px; animation: fadeIn 0.5s;">
                <a href="#!" class="comment-avatar-link"><img src="${c.avatar}" alt="${c.username}"></a>
                <div class="comment-body">
                    <div class="comment-header-row">
                        <div class="comment-meta">
                            <span class="author-name">${c.username}</span>
                            <span class="comment-time">${c.date}</span>
                        </div>
                        <div class="comment-menu-wrapper">
                            <button class="btn-icon-comment btn-comment-menu"><i class="material-icons">more_vert</i></button>
                            <div class="comment-dropdown-menu">
                                <div class="menu-option btn-edit-comment" data-id="${c.id}"><i class="material-icons">edit</i> Editar</div>
                                <div class="menu-option btn-delete-comment" data-id="${c.id}"><i class="material-icons">delete</i> Eliminar</div>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content" id="comment-content-${c.id}">${c.content}</div>
                    <div class="comment-actions-toolbar">
                        <button class="btn-icon-comment like-comment-btn" data-comment-id="${c.id}"><i class="material-icons">thumb_up_alt</i><span class="count-text"></span></button>
                        <button class="btn-icon-comment dislike-comment-btn" data-comment-id="${c.id}"><i class="material-icons">thumb_down_alt</i></button>
                        <button class="btn-reply-text" data-parent-id="${c.id}">Responder</button>
                    </div>
                    <div class="reply-form-container" id="reply-form-${c.id}"></div>
                </div>
            </div>`;
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

  function handleCommentRate(btn, type) {
    const commentId = btn.getAttribute("data-comment-id");
    if (!commentId) return;

    const parentToolbar = btn.closest(".comment-actions-toolbar");
    const likeButton = parentToolbar.querySelector(".like-comment-btn");
    const dislikeButton = parentToolbar.querySelector(".dislike-comment-btn");
    const countSpan = likeButton.querySelector(".count-text");
    const likeIcon = likeButton.querySelector("i");
    const dislikeIcon = dislikeButton.querySelector("i");

    if (type === "like") {
      if (likeButton.classList.contains("active-comment-rate")) {
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
      if (dislikeButton.classList.contains("active-comment-rate")) {
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

    fetch("actions/rate_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ comment_id: commentId, type: type }),
    }).catch((err) => console.error(err));
  }

  // --- FUNCIÓN BORRAR ---
  function deleteComment(id) {
    fetch("actions/manage_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", comment_id: id }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const item = document.getElementById("comment-" + id);
          if (item) item.remove();
          showMessage("Comentario eliminado");
        } else {
          showMessage("Error: " + data.error);
        }
      });
  }

  // --- FUNCIÓN EDITAR ---
  function startEditingComment(id) {
    const contentDiv = document.getElementById("comment-content-" + id);
    if (!contentDiv) return;
    const oldText = contentDiv.innerText; // innerText respeta saltos de línea

    // Crear form
    const editContainer = document.createElement("div");
    editContainer.className = "edit-form-container";
    editContainer.innerHTML = `
            <div class="input-field-comment" style="margin-top: 8px;">
                <textarea class="materialize-textarea" style="min-height: 60px;">${oldText}</textarea>
                <div class="comment-form-actions" style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;">
                    <button class="btn-flat waves-effect btn-cancel-edit">Cancelar</button>
                    <button class="btn-flat waves-effect btn-save-edit" style="background-color: #065fd4; color: white;">Guardar</button>
                </div>
            </div>`;

    contentDiv.style.display = "none";
    contentDiv.parentNode.insertBefore(editContainer, contentDiv.nextSibling);

    const textarea = editContainer.querySelector("textarea");
    // Auto-resize para el editor
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";
    textarea.focus();

    const cancelBtn = editContainer.querySelector(".btn-cancel-edit");
    const saveBtn = editContainer.querySelector(".btn-save-edit");

    cancelBtn.addEventListener("click", () => {
      editContainer.remove();
      contentDiv.style.display = "block";
    });

    saveBtn.addEventListener("click", () => {
      const newText = textarea.value.trim();
      if (!newText) return;

      saveBtn.disabled = true;
      fetch("actions/manage_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "edit",
          comment_id: id,
          content: newText,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            contentDiv.innerHTML = data.content; // data.content viene escapado del server
            editContainer.remove();
            contentDiv.style.display = "block";
            showMessage("Comentario editado");
          } else {
            showMessage("Error al editar");
            saveBtn.disabled = false;
          }
        });
    });
  }

  // =================================================
  // LOGICA SHARE, SUSCRIBE, LIKE VIDEO (MANTENER)
  // =================================================
  // ... [Copia aquí los bloques de Share, Suscribe y Like de Video Principal del archivo anterior] ...
  // Para no hacer el archivo gigante en la respuesta, asumo que mantienes esas secciones.
  // Si necesitas el archivo 100% completo avísame, pero con lo de arriba ya arreglas el menú.

  // (Incluir aquí el código de shareBtn, copyLinkBtn, startAtCheckbox, subscribeBtn y likeBtn del video principal)
  // Te dejo el cierre del DOMContentLoaded

  // --- LÓGICA COMPARTIR ---
  if (shareBtn && modalShareInstance) {
    shareBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modalShareInstance.open();
    });
  }
  if (copyLinkBtn && shareUrlInput) {
    copyLinkBtn.addEventListener("click", () => {
      shareUrlInput.select();
      navigator.clipboard
        .writeText(shareUrlInput.value)
        .then(() => showMessage("Copiado"));
    });
  }
  // --- LÓGICA SUSCRIBIR ---
  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (subscribeBtn.classList.contains("subscribed")) {
        if (modalUnsubInstance) modalUnsubInstance.open();
      } else performSubscriptionAction();
    });
  }
  if (confirmUnsubBtn)
    confirmUnsubBtn.addEventListener("click", (e) => {
      e.preventDefault();
      performSubscriptionAction();
      if (modalUnsubInstance) modalUnsubInstance.close();
    });
  function performSubscriptionAction() {
    const cid = subscribeBtn.getAttribute("data-channel-id");
    fetch("actions/subscribe.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ channel_id: cid }),
    })
      .then((r) => r.text())
      .then((t) => {
        try {
          const d = JSON.parse(t);
          if (d.success) updateSubscribeButton(d.status, d.count);
        } catch (e) {}
      });
  }
  function updateSubscribeButton(s, c) {
    if (s === "subscribed") {
      subscribeBtn.classList.add("subscribed");
      subscribeBtn.textContent = "Suscrito";
    } else {
      subscribeBtn.classList.remove("subscribed");
      subscribeBtn.textContent = "Suscribirse";
    }
    if (document.getElementById("subscribersCount"))
      document.getElementById("subscribersCount").textContent =
        c + " suscriptores";
  }
  // --- LÓGICA LIKE VIDEO ---
  function updateIcons() {
    if (!likeBtn) return;
    likeBtn.querySelector("i").textContent = likeBtn.classList.contains(
      "active"
    )
      ? "thumb_up"
      : "thumb_up_alt";
    dislikeBtn.querySelector("i").textContent = dislikeBtn.classList.contains(
      "active"
    )
      ? "thumb_down"
      : "thumb_down_alt";
  }
  function handleRate(t) {
    if (t === "like") {
      if (likeBtn.classList.contains("active"))
        likeBtn.classList.remove("active");
      else {
        likeBtn.classList.add("active");
        dislikeBtn.classList.remove("active");
      }
    } else {
      if (dislikeBtn.classList.contains("active"))
        dislikeBtn.classList.remove("active");
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
      body: JSON.stringify({ video_id: vid, type: t }),
    });
  }
  if (likeBtn) likeBtn.addEventListener("click", () => handleRate("like"));
  if (dislikeBtn)
    dislikeBtn.addEventListener("click", () => handleRate("dislike"));

  updateIcons();
});
