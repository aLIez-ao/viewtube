document.addEventListener("DOMContentLoaded", function () {
  const subscribeBtn = document.getElementById("subscribeBtn");
  const unsubscribeModal = document.getElementById("unsubscribeModal");
  const confirmUnsubBtn = document.getElementById("confirmUnsubscribe");
  const subscribersCountSpan = document.getElementById("subscribersCount");

  // Inicializar Modal
  let modalInstance = null;
  if (typeof M !== "undefined" && unsubscribeModal) {
    modalInstance = M.Modal.init(unsubscribeModal, { opacity: 0.5 });
  }

  // Clic en Botón Suscribirse
  if (subscribeBtn) {
    subscribeBtn.addEventListener("click", function () {
      const isSubscribed = subscribeBtn.classList.contains("subscribed");

      if (isSubscribed) {
        // Si ya está suscrito, abrir modal
        if (modalInstance) modalInstance.open();
        else if (confirm("¿Anular suscripción?")) performAction();
      } else {
        // Si no, suscribirse
        performAction();
      }
    });
  }

  // Confirmar Desuscripción
  if (confirmUnsubBtn) {
    confirmUnsubBtn.addEventListener("click", function () {
      performAction();
      if (modalInstance) modalInstance.close();
    });
  }

  function performAction() {
    const channelId = subscribeBtn.getAttribute("data-channel-id");
    subscribeBtn.disabled = true;

    fetch("actions/subscribe.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ channel_id: channelId }),
    })
      .then((res) => res.json())
      .then((data) => {
        subscribeBtn.disabled = false;
        if (data.success) {
          updateUI(data.status, data.count);
        } else if (data.error === "auth_required") {
          window.location.href = "login.php";
        } else {
          M.toast({ html: "Error: " + data.error });
        }
      })
      .catch((err) => {
        console.error(err);
        subscribeBtn.disabled = false;
      });
  }

  function updateUI(status, count) {
    if (status === "subscribed") {
      subscribeBtn.classList.add("subscribed");
      subscribeBtn.textContent = "Suscrito";
      M.toast({ html: "Suscripción añadida" });
    } else {
      subscribeBtn.classList.remove("subscribed");
      subscribeBtn.textContent = "Suscribirse";
      M.toast({ html: "Suscripción eliminada" });
    }

    if (subscribersCountSpan) {
      subscribersCountSpan.textContent =
        new Intl.NumberFormat().format(count) + " suscriptores";
    }
  }
});
