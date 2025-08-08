const ___ALERT___ = []; // memoria de alertas creadas

function notificacion(type, message, time = 3000) {
//   const type = "info"; // Tipo de alerta fijo
  let bac = "white";
  let col = "black";

  switch (type) {
    case "success":
      bac = "#d1e7dd";
      col = "black";
      break; //success
    case "danger":
      bac = "#f8d7da";
      col = "black";
      break; //danger
    case "warning":
      bac = "#fff3cd";
      col = "black";
      break; //warning
    case "info":
      bac = "#9ec5fe";
      col = "black";
      break; //info
    case "primary":
      bac = "#cfe2ff";
      col = "black";
      break; //primary
    case "light":
      bac = "#fcfcfd";
      col = "black";
      break; //light
    case "dark":
      bac = "#ced4da";
      col = "white";
      break; //dark
  }

  const noty = `<div class="alert alert-info" role="alert" style="background-color:${bac};color:${col};">${message}</div>`;

  const alertDiv = document.querySelector('.alert');
  if (alertDiv) {
    alertDiv.innerHTML = message;
    alertDiv.style.backgroundColor = bac;
    alertDiv.style.color = col;
  } else {
    document.body.insertAdjacentHTML("beforeend", noty);
  }

  // guardamos en la memoria
  ___ALERT___.push(document.body.lastChild);

  // cambiar posición de elemento para que quede debajo del último
//   if (___ALERT___.length > 1) {
//     ___ALERT___.forEach((el, i) => {
//       el.style.top = i * 50 + "px";
//     });
//   }

  // darle tiempo para que se muestre
  setTimeout(() => {
    const lastAlert = document.body.lastChild;
    document.body.removeChild(lastAlert);

    // eliminar de la memoria
    const index = ___ALERT___.indexOf(lastAlert);
    if (index > -1) {
      ___ALERT___.splice(index, 1);
    }

    // cambiar posición de elemento para que quede debajo del último
    if (___ALERT___.length > 1) {
      ___ALERT___.forEach((el, i) => {
        el.style.top = i * 50 + "px";
      });
    }
  }, time);

  // eliminar al hacer clic sobre la alerta
  document.body.lastChild.addEventListener("click", () => {
    const lastAlert = document.body.lastChild;
    document.body.removeChild(lastAlert);

    // eliminar de la memoria
    const index = ___ALERT___.indexOf(lastAlert);
    if (index > -1) {
      ___ALERT___.splice(index, 1);
    }

    // cambiar posición de elemento para que quede debajo del último
    if (___ALERT___.length > 1) {
      ___ALERT___.forEach((el, i) => {
        el.style.top = i * 50 + "px";
      });
    }
  });
}

function alerta(mesage)
{

    var opcion = confirm(mesage);
    return opcion == true;
}