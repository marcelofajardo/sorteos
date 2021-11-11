"use strict";

const button = document.querySelector("#register");

button.addEventListener("click", function() {
  const userEmail = document.querySelector("#email");
  const topBar = document.querySelector(".top-bar");
  const data = {
      lot: 1,
      email: userEmail.value
  };

  if ( userEmail.value !== '' ) {
    if ( topBar.classList.contains("success") ) {
      topBar.classList.remove("success", "show");
    } else if ( topBar.classList.contains("error") ) {
      topBar.classList.remove("error", "show");
    } else if ( topBar.classList.contains("warning") ) {
      topBar.classList.remove("warning", "show");
    }

    fetch('https://sorteos.hablemosdecodigo.com/api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(data => {
      switch (data.status) {
        case 'success':
          document.querySelector("#msg").innerHTML = "Te has registrado satisfactoriamente. Gracias por participar.";
          topBar.classList.add("success", "show");
          break;

        case 'error':
          document.querySelector("#msg").innerHTML = "Ha ocurrido un error registrando tu inscripción, por favor intentalo nuevamente.";
          topBar.classList.add("error", "show");
          break;

        case 'registered':
          document.querySelector("#msg").innerHTML = "Ya te has registrado anteriormente. Gracias por participar.";
          topBar.classList.add("error", "show");
          break;

        case 'invalid':
          document.querySelector("#msg").innerHTML = "Revisa si has ingresado un correo válido e intentalo de nuevo.";
          topBar.classList.add("warning", "show");
          break;
      
        default:
          break;
      }
    })
    .catch(() => {
      document.querySelector("#msg").innerHTML = "Ha ocurrido un error registrando tu inscripción, por favor intentalo nuevamente.";
      topBar.classList.add("error", "show");
    });
  }
})