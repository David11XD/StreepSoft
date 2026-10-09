$(".menu > ul > li").click(function (e) {

    $(this).siblings().removeClass("active");

    $(this).toggleClass("active");

    //Activa los desplegables multilevel
    $(this).find("ul").slideToggle();

    //Desactiva un desplegable multilevel cuando se selecciona otro desplegable con esta misma funcion
     $(this).siblings().find("ul").slideUp();

    //Desactiva la funcion multilevel cuando se le da click a otra opcion de la funcion multilevel
    $(this).siblings().find("ul").find("li").removeClass("active");

});


$(".menu-btn").click(function () {
    $(".sidebar").toggleClass("active");
})