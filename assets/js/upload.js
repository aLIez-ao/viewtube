document.addEventListener("DOMContentLoaded", function () {
  // Referencias a elementos del DOM
  // Nota: En upload.php definimos id="videoUrl" para el input del enlace
  const urlInput = document.getElementById("videoUrl");
  const titleInput = document.getElementById("videoTitle");

  const previewContainer = document.getElementById("previewContainer");
  const previewLink = document.getElementById("previewLink");
  const btnPublish = document.getElementById("btnPublish");
  const uploadForm = document.getElementById("uploadForm");

  // LÓGICA DE PREVISUALIZACIÓN E ID
  if (urlInput) {
    urlInput.addEventListener("input", function () {
      const url = this.value.trim();
      const videoId = extractYouTubeID(url);

      if (videoId) {
        updatePreview(videoId);
      } else {
        resetPreview();
      }
      // Validar formulario cada vez que cambia la URL
      checkFormValidity();
    });
  }

  // Función para extraer ID de YouTube de varios formatos de URL
  function extractYouTubeID(url) {
    const regExp =
      /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);

    if (match && match[2].length === 11) {
      return match[2];
    }

    if (url.length === 11) {
      return url;
    }
    return null;
  }

  function updatePreview(id) {
    // Crear iframe de YouTube
    const iframe = document.createElement("iframe");
    iframe.width = "100%";
    iframe.height = "100%";
    iframe.src = `https://www.youtube.com/embed/${id}`;
    iframe.frameBorder = "0";
    iframe.allow =
      "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
    iframe.allowFullscreen = true;

    // Reemplazar contenido del contenedor de preview
    previewContainer.innerHTML = "";
    previewContainer.appendChild(iframe);
    previewContainer.style.background = "#000";

    // Actualizar el texto del enlace
    previewLink.textContent = `youtu.be/${id}`;
    previewLink.href = `https://youtu.be/${id}`;
    previewLink.target = "_blank";
  }

  function resetPreview() {
    previewContainer.innerHTML = `
            <div class="placeholder-preview" style="color: #aaa; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
                <i class="material-icons" style="font-size: 48px; margin-bottom: 10px;">play_circle_outline</i>
                <p style="margin: 0; font-size: 14px;">Pega un enlace válido</p>
            </div>
        `;
    previewContainer.style.background = "#f0f0f0";
    previewLink.textContent = "youtu.be/...";
    previewLink.removeAttribute("href");
  }

  // VALIDACIÓN Y ENVÍO
  const checkFormValidity = () => {
    const hasTitle = titleInput.value.trim().length > 0;
    // Validamos que tengamos un ID extraíble válido
    const hasValidUrl = extractYouTubeID(urlInput.value.trim()) !== null;

    if (hasTitle && hasValidUrl) {
      btnPublish.removeAttribute("disabled");
      btnPublish.style.opacity = "1";
      btnPublish.style.cursor = "pointer";
    } else {
      btnPublish.setAttribute("disabled", "true");
      btnPublish.style.opacity = "0.5";
      btnPublish.style.cursor = "not-allowed";
    }
  };

  if (uploadForm) {
    // Escuchar cambios en el título también
    titleInput.addEventListener("input", checkFormValidity);

    // Interceptar envío
    uploadForm.addEventListener("submit", function (e) {
      if (btnPublish.disabled) {
        e.preventDefault();
        return;
      }

      // UI de carga
      btnPublish.disabled = true;
      btnPublish.textContent = "PUBLICANDO...";
    });

    // Comprobar al inicio por si el navegador autocompletó
    checkFormValidity();
  }
});
