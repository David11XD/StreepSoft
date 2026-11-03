document.addEventListener("DOMContentLoaded",()=>{
    const wrapper=document.querySelector(".page-wrapper");
    wrapper.classList.add("entrar-recover");

    requestAnimationFrame(()=>{
        wrapper.classList.remove("entrar-recover");

    });
    const volver=document.querySelector(".link a");

    volver.addEventListener("click",(e)=>{
        e.preventDefault();
        wrapper.classList.add("salir-recover");

        setTimeout(()=>{
            window.location.href=volver.href;
        },500);
    });
});