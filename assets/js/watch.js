document.addEventListener("DOMContentLoaded", function () {
  console.log("WATCH.JS: Cargado.");

  // --- ELEMENTOS EXISTENTES ---
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const likeBtn = document.getElementById("likeBtn");
  const dislikeBtn = document.getElementById("dislikeBtn");
  const likeCountSpan = document.getElementById("likeCount");

  // --- ELEMENTOS COMPARTIR ---
  const shareBtn = document.getElementById("shareBtn");
  const shareModal = document.getElementById("shareModal");
  const shareUrlInput = document.getElementById("shareUrlInput");
  const copyLinkBtn = document.getElementById("copyLinkBtn");
  const startAtCheckbox = document.getElementById("startAtCheckbox");

  // --- ELEMENTOS GUARDAR EN LISTA (NUEVO) ---
  const saveBtn = document.getElementById("saveBtn");
  const saveModal = document.getElementById("saveModal");
  const playlistsList = document.getElementById("playlistsList");
  const createView = document.getElementById("createPlaylistView");
  const saveFooter = document.getElementById("saveModalFooter");
  const showCreateBtn = document.getElementById("showCreateFormBtn");
  const cancelCreateBtn = document.getElementById("cancelCreateBtn");
  const confirmCreateBtn = document.getElementById("confirmCreateBtn");
  const newPlaylistInput = document.getElementById("newPlaylistName");

  // Redes sociales
  const shareWhatsapp = document.querySelector(
    '.social-icon[title="WhatsApp"]'
  );
  const shareFacebook = document.querySelector(
    '.social-icon[title="Facebook"]'
  );
  const shareTwitter = document.querySelector(
    '.social-icon[title="X / Twitter"]'
  );
  const shareEmail = document.querySelector('.social-icon[title="Email"]');

  const originalUrl = shareUrlInput
    ? shareUrlInput.value
    : window.location.href;

  // --- ELEMENTOS DE COMENTARIOS ---
  const commentInput = document.getElementById("commentInput");
  const submitCommentBtn = document.getElementById("submitCommentBtn");
  const commentsList = document.querySelector(".comments-list");
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
  let modalSaveInstance = null;

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
            updateShareLinks(originalUrl);
          },
        });
      } catch (e) {}
    }
    // Inicializar Modal de Guardar
    if (saveModal) {
      try {
        modalSaveInstance = M.Modal.init(saveModal, { opacity: 0.5 });
      } catch (e) {
        console.error("Error init save modal", e);
      }
    }
  }

  function updateShareLinks(url) {
    const encodedUrl = encodeURIComponent(url);
    const text = encodeURIComponent("Mira este video: ");
    if (shareWhatsapp)
      shareWhatsapp.href = `https://wa.me/?text=${text}${encodedUrl}`;
    if (shareFacebook)
      shareFacebook.href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
    if (shareTwitter)
      shareTwitter.href = `https://twitter.com/intent/tweet?text=${text}&url=${encodedUrl}`;
    if (shareEmail)
      shareEmail.href = `mailto:?subject=${text}&body=${encodedUrl}`;
  }

  // =================================================
  // 1. LÓGICA DE COMENTARIOS
  // =================================================
  let activeReplyForm = null;

  function setupCommentForm(formContainer) {
    const input = formContainer.querySelector("textarea");
    const actionsDiv = formContainer.querySelector(".comment-form-actions");
    if (!input || !actionsDiv) return;

    const submitBtn = actionsDiv.querySelector("button:last-child");
    const cancelBtn = actionsDiv.querySelector("button:first-child");

    if (!formContainer.classList.contains("reply-active")) {
      actionsDiv.style.display = "none";
    } else {
      actionsDiv.style.display = "flex";
    }

    const autoResize = () => {
      input.style.height = "auto";
      input.style.height = input.scrollHeight + "px";
    };
    input.addEventListener("input", autoResize);

    if (!formContainer.classList.contains("reply-active")) {
      input.addEventListener(
        "focus",
        () => (actionsDiv.style.display = "flex")
      );
    }

    input.addEventListener("input", () => {
      if (input.value.trim().length > 0) submitBtn.removeAttribute("disabled");
      else submitBtn.setAttribute("disabled", "true");
    });

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

  // Delegación Comentarios
  if (commentsList) {
    commentsList.addEventListener("click", (e) => {
      const replyBtn = e.target.closest(".btn-reply-text");
      if (replyBtn) {
        openReplyForm(replyBtn.getAttribute("data-parent-id"));
        return;
      }
      const likeBtn = e.target.closest(".like-comment-btn");
      if (likeBtn) {
        handleCommentRate(likeBtn, "like");
        return;
      }

      const dislikeBtn = e.target.closest(".dislike-comment-btn");
      if (dislikeBtn) {
        handleCommentRate(dislikeBtn, "dislike");
        return;
      }

      const menuBtn = e.target.closest(".btn-comment-menu");
      if (menuBtn) {
        e.stopPropagation();
        const wrapper = menuBtn.closest(".comment-menu-wrapper");
        document
          .querySelectorAll(".comment-menu-wrapper.active")
          .forEach((el) => {
            if (el !== wrapper) el.classList.remove("active");
          });
        wrapper.classList.toggle("active");
        return;
      }

      const editBtn = e.target.closest(".btn-edit-comment");
      if (editBtn) {
        const id = editBtn.getAttribute("data-id");
        editBtn.closest(".comment-menu-wrapper").classList.remove("active");
        startEditingComment(id);
        return;
      }

      const deleteBtn = e.target.closest(".btn-delete-comment");
      if (deleteBtn) {
        const id = deleteBtn.getAttribute("data-id");
        deleteBtn.closest(".comment-menu-wrapper").classList.remove("active");
        if (confirm("¿Eliminar comentario?")) deleteComment(id);
        return;
      }
    });
  }

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
    let newMargin = Math.min(currentMargin + 48, 96);
    const html = createCommentHTML(comment, newMargin);
    parentComment.insertAdjacentHTML("afterend", html);
  }

  function createCommentHTML(c, marginLeft = 0) {
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

    fetch("actions/rate_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ comment_id: commentId, type: type }),
    }).catch((err) => console.error(err));
  }

  function deleteComment(id) {
    fetch("actions/manage_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", comment_id: id }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          const item = document.getElementById("comment-" + id);
          if (item) {
            item.style.opacity = "0";
            setTimeout(() => item.remove(), 300);
            showMessage("Comentario eliminado");
          }
        }
      });
  }

  function startEditingComment(id) {
    const contentDiv = document.getElementById("comment-content-" + id);
    if (!contentDiv) return;
    const oldText = contentDiv.innerText;
    const editContainer = document.createElement("div");
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
    textarea.focus();

    editContainer
      .querySelector(".btn-cancel-edit")
      .addEventListener("click", () => {
        editContainer.remove();
        contentDiv.style.display = "block";
      });
    editContainer
      .querySelector(".btn-save-edit")
      .addEventListener("click", () => {
        const newText = textarea.value.trim();
        if (!newText) return;
        fetch("actions/manage_comment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "edit",
            comment_id: id,
            content: newText,
          }),
        })
          .then((r) => r.json())
          .then((d) => {
            if (d.success) {
              contentDiv.innerHTML = d.content;
              editContainer.remove();
              contentDiv.style.display = "block";
              showMessage("Editado");
            }
          });
      });
  }

  // =================================================
  // 3. LÓGICA COMPARTIR
  // =================================================
  if (shareBtn && modalShareInstance) {
    shareBtn.addEventListener("click", function (e) {
      e.preventDefault();
      updateShareLinks(originalUrl);
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

  if (startAtCheckbox && shareUrlInput) {
    startAtCheckbox.addEventListener("change", function () {
      const separator = originalUrl.includes("?") ? "&" : "?";
      shareUrlInput.value = this.checked
        ? originalUrl + separator + "t=0s"
        : originalUrl;
      updateShareLinks(shareUrlInput.value);
    });
  }

  // =================================================
  // 4. LÓGICA SUSCRIPCIÓN Y LIKES VIDEO
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
    const cid = subscribeBtn.getAttribute("data-channel-id");
    fetch("actions/subscribe.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ channel_id: cid }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) updateSubscribeButton(d.status, d.count);
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
      .then((r) => r.json())
      .then((d) => {
        if (d.success && likeCountSpan)
          likeCountSpan.textContent = new Intl.NumberFormat().format(d.likes);
      });
  }

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

  if (likeBtn) likeBtn.addEventListener("click", () => handleRate("like"));
  if (dislikeBtn)
    dislikeBtn.addEventListener("click", () => handleRate("dislike"));
  updateIcons();

  document.addEventListener("click", function (e) {
    if (!e.target.closest(".comment-menu-wrapper")) {
      document
        .querySelectorAll(".comment-menu-wrapper.active")
        .forEach((el) => el.classList.remove("active"));
    }
  });

  // =================================================
  // 5. LÓGICA DE GUARDAR EN LISTA (PLAYLISTS)
  // =================================================

  if (saveBtn && modalSaveInstance) {
    saveBtn.addEventListener("click", function (e) {
      e.preventDefault();
      createView.style.display = "none";
      playlistsList.style.display = "block";
      saveFooter.style.display = "block";

      modalSaveInstance.open();
      loadPlaylists();
    });
  }

  function loadPlaylists() {
    const urlParams = new URLSearchParams(window.location.search);
    const videoId = urlParams.get("id");
    playlistsList.innerHTML =
      '<div class="center-align" style="padding: 20px;"><div class="preloader-wrapper small active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div>';

    fetch("actions/playlist.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "get_playlists", video_id: videoId }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          renderPlaylists(data.playlists);
        } else if (data.error === "auth_required") {
          modalSaveInstance.close();
          showMessage("Inicia sesión para guardar");
          setTimeout(() => (window.location.href = "login.php"), 1500);
        }
      });
  }

  function renderPlaylists(playlists) {
    if (playlists.length === 0) {
      playlistsList.innerHTML =
        '<p style="color:#666; text-align:center; padding:10px;">No tienes listas</p>';
    } else {
      playlistsList.innerHTML = "";
      playlists.forEach((pl) => {
        const div = document.createElement("div");
        div.className = "playlist-item";
        div.innerHTML = `
                    <label>
                        <input type="checkbox" class="filled-in" data-id="${
                          pl.id
                        }" ${pl.contains_video ? "checked" : ""} />
                        <span>${pl.title} ${
          pl.is_private
            ? '<i class="material-icons tiny grey-text">lock</i>'
            : ""
        }</span>
                    </label>
                `;

        const checkbox = div.querySelector("input");
        checkbox.addEventListener("change", function () {
          toggleVideoInPlaylist(this.dataset.id, this.checked);
        });

        playlistsList.appendChild(div);
      });
    }
  }

  function toggleVideoInPlaylist(playlistId, isAdding) {
    const urlParams = new URLSearchParams(window.location.search);
    const videoId = urlParams.get("id");

    fetch("actions/playlist.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "toggle_video",
        playlist_id: playlistId,
        video_id: videoId,
        add: isAdding,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          showMessage(
            isAdding ? "Guardado en la lista" : "Eliminado de la lista"
          );
        } else {
          showMessage("Error al actualizar");
        }
      });
  }

  if (showCreateBtn) {
    showCreateBtn.addEventListener("click", () => {
      playlistsList.style.display = "none";
      saveFooter.style.display = "none";
      createView.style.display = "block";
      newPlaylistInput.focus();
    });
  }

  if (cancelCreateBtn) {
    cancelCreateBtn.addEventListener("click", () => {
      createView.style.display = "none";
      playlistsList.style.display = "block";
      saveFooter.style.display = "block";
      newPlaylistInput.value = "";
    });
  }

  if (newPlaylistInput) {
    newPlaylistInput.addEventListener("input", function () {
      if (this.value.trim().length > 0)
        confirmCreateBtn.removeAttribute("disabled");
      else confirmCreateBtn.setAttribute("disabled", "true");
    });
  }

  if (confirmCreateBtn) {
    confirmCreateBtn.addEventListener("click", () => {
      const title = newPlaylistInput.value.trim();
      const urlParams = new URLSearchParams(window.location.search);
      const videoId = urlParams.get("id");

      if (!title) return;
      confirmCreateBtn.disabled = true;

      fetch("actions/playlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "create",
          title: title,
          video_id: videoId,
        }),
      })
        .then((r) => r.json())
        .then((data) => {
          confirmCreateBtn.disabled = false;
          if (data.success) {
            newPlaylistInput.value = "";
            createView.style.display = "none";
            playlistsList.style.display = "block";
            saveFooter.style.display = "block";
            showMessage("Lista creada y guardada");
            loadPlaylists();
          } else {
            showMessage("Error al crear lista");
          }
        });
    });
  }
});
