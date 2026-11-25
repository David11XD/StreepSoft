// Funcion para ocultar mensajes
let backClickCount = 0;
  window.history.pushState(null, null, window.location.href);
  window.addEventListener('popstate', function() {
    backClickCount++;
    if (backClickCount >= 2) {
      alert('No puedes retroceder más de 2 veces');
      window.history.pushState(null, null, window.location.href);
    }
});


const autoHide = (id) => {
  const el = document.getElementById(id);
    if (el) {
      setTimeout(() => {
        el.style.transition = "opacity 0.5s ease";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
      }, 3000);
    }
};

autoHide("mensajeError");


document.addEventListener("DOMContentLoaded",()=>{

    const wrapper=document.querySelector(".page-wrapper");

    wrapper.classList.add("entrar-login");

    requestAnimationFrame(()=>{

        wrapper.classList.remove("entrar-login");

    });

    const enlace=document.querySelector(".alogin");

    enlace.addEventListener("click",(e)=>{

        e.preventDefault();

        wrapper.classList.add("salir-login");

        setTimeout(()=>{

            window.location.href=enlace.href;

        },500);

    });

});