"use strict";

const la = true; // Sorteo activo

if ( !la ) {
  document.querySelector("#title").innerHTML = "Sorteo no disponible";
  document.querySelector("#headline").innerHTML = "En el momento no hay sorteo activo, pero puedes seguirnos en nuestras redes sociales para más contenido y conocer los nuevos sorteos.";

  document.querySelector("#form").remove();
  document.querySelector("#disclaimer").remove();

  const followLink = document.createElement('div')
  followLink.classList.add("mt-4");
  followLink.innerHTML = `<a href="https://followlink.co/@hablemosdecodigo" class="btn btn-dark" target="_blank" rel="noopenner">Síguenos en redes</a>`;
  document.querySelector(".col-form").append(followLink);

  document.querySelector("#img").setAttribute("src", "img/lot-unavailable.jpeg");
}

const button = document.querySelector("#register");

button.addEventListener("click", function() {
  const userEmail = document.querySelector("#email");
  const topBar = document.querySelector(".top-bar");
  const data = {
      lot: 1,
      email: userEmail.value
  };

  if ( userEmail.value !== '' && userEmail.value.indexOf('@') > -1 ) {
    button.innerHTML = 'Inscribiendo...';
    userEmail.classList.remove("error");

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
          userEmail.setAttribute('disabled', true);
          button.setAttribute('disabled', true);
          button.innerHTML = 'Inscrito';
          break;

        case 'error':
          document.querySelector("#msg").innerHTML = "Ha ocurrido un error con tu inscripción, por favor inténtalo nuevamente.";
          topBar.classList.add("error", "show");
          button.innerHTML = 'Inscribirme';
          break;

        case 'registered':
          document.querySelector("#msg").innerHTML = "Ya te has registrado anteriormente. Gracias por participar.";
          topBar.classList.add("error", "show");
          userEmail.setAttribute('disabled', true);
          button.setAttribute('disabled', true);
          button.innerHTML = 'Inscrito';
          break;

        case 'invalid':
          document.querySelector("#msg").innerHTML = "Revisa que el correo ingresado sea válido e intentalo de nuevo.";
          topBar.classList.add("warning", "show");
          button.innerHTML = 'Inscribirme';
          break;
      
        default:
          break;
      }
    })
    .catch(() => {
      document.querySelector("#msg").innerHTML = "Ha ocurrido un error registrando tu inscripción, por favor intentalo nuevamente.";
      topBar.classList.add("error", "show");
      button.innerHTML = 'Inscribirme';
    });
  } else {
    userEmail.classList.add("error");
  }
})