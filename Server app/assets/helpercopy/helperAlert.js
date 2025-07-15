const ___ALERT___ = []; //memoria de alertas creadas

function notificacion(type,message,time=3000){
    bac = "white" ; col="black"
    switch(type){
        case "success": bac = "lime" ; col="black" ;break; //success
        case "danger": bac = "red" ; col="black" ;break; //danger
        case "warning": bac = "yellow" ; col="black" ;break; //warning
        case "info": bac = "aqua" ; col="black" ;break; //info
        case "primary": bac = "blue" ; col="black" ;break; //primary
        case "light": bac = "white" ; col="black" ;break; //light
        case "dark": bac = "black" ; col="white" ;break; //dark
    }
    const noty = `<div class="alert" style="background-color:${bac};color:${col};">${message}</div>`
    //agregarlo al documento uno debajo del otro
    document.body.insertAdjacentHTML("beforeend",noty)
    //guardamos en la memoria 
    ___ALERT___.push(document.body.lastChild)

    //cambiar posicion de elemento para que quede debajo del ultimo
    if(___ALERT___.length>1){
        ___ALERT___.forEach((el,i)=>{
            el.style.top = (i*50)+"px"
        })
    }

    //darle tiempo para que se muestre
    setTimeout(() => { 
        document.body.removeChild(document.body.lastChild)
        //eliminar d ela memoria
        ___ALERT___.splice(___ALERT___.indexOf(document.body.lastChild),1)
        //cambiar posicion de elemento para que quede debajo del ultimo
        if(___ALERT___.length>1){
            ___ALERT___.forEach((el,i)=>{
                el.style.top = (i*50)+"px"
            })
        }
    } , time);

    //eliminarlo al hacer clik sobre la alerta
    document.body.lastChild.addEventListener("click",()=>{
        document.body.removeChild(document.body.lastChild)
        //eliminar d ela memoria
        ___ALERT___.splice(___ALERT___.indexOf(document.body.lastChild),1)
        //cambiar posicion de elemento para que quede debajo del ultimo
        if(___ALERT___.length>1){
            ___ALERT___.forEach((el,i)=>{
                el.style.top = (i*50)+"px"
            })
        }
    })
}



function alerta(mesage)
{

    var opcion = confirm(mesage);
    return opcion == true;
}