import $ from 'jquery';
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ control de validacion de campos vacios ]
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ focus ] __________________________________________________________________________________________________________________
export const __focused = (id) => { id.focus(); }

//¿(Js) [ control de validacion de campos vacios ] __________________________________________________________________________________________________________________
export const __input_chek_string =(id)=> {
    if (id.value.length > 0) {
        $(id).removeClass("border border-3 border-danger shadow is-invalid");
        //$(id).addClass("is-valid");

    } else {
        $(id).addClass("border border-3 border-danger shadow is-invalid");
        //$(id).removeClass("is-valid");
    }
}

//¿(Js) [ validacion de campos por clases ] __________________________________________________________________________________________________________________
export const input_checked = (classe)=> {
   let metaFocus = false
   let val = true;
   let tinput = $("." + String(classe))
   for (var i = 0; i < tinput.length; i++) {
       let newInput = tinput[i]

       if (newInput.value.length < 1) {
           if (val === true) { metaFocus = newInput }
           val = false;
           __input_chek_string(newInput);
       }
   }
   
   if (metaFocus !== false) { __focused(metaFocus) }
   return val;
}
//¿(Js) [ validacion de campos por jquery ] __________________________________________________________________________________________________________________
export const input_checked_ext =(classe)=> {
    metaFocus = false
    val = true;
    tinput = classe
    for (var i = 0; i < tinput.length; i++) {
        newInput = tinput[i]

        if (newInput.value.length < 1) {
            if (val == true) { metaFocus = newInput }
            val = false;
            __input_chek_string(newInput);
        }
    }
    if (metaFocus != false) { __focused(metaFocus) }
    return val;
}

//¿(Js) [ validacion de campos independientes ] __________________________________________________________________________________________________________________
export const input_cheked_one = (classe)=> { $("." + String(classe)).on("keyup", function() { __input_chek_string(this) }) }


//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ parseador de input numerico ]
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿parsea un input con nueros decimales o nueros enteros
export const parseInputNumber_ext = (id,format={miles:true,decimal:true,count:2})=>
{
    //¿que solo admita numeros punto y comas elimnar cualqueir otro caracter
    let $input = $(id);
    let val = $input.val();

    val = val.replace(/[^0-9.,]/g, '');                 //¿que solo pasen numeros y puntos
    if(val=="" || val==null){val="0"}                   //¿si no hay valor devolver 0
    format.miles ? val = unformatNumber(val) : null;    //¿desformateamos lospuntos de miles

    //¿que solo admita 1 punto y despues de ese punto solo 2 numeros borra todos los nuers despues
    if(format.decimal)
    {
        val = val.replace(/(\..*)\./g, '$1');   //?que solo tenga un punto decimal
        val = val.replace(/(\..{2}).*/g, '$1'); //?que solo tenga 2 decimales
    }

    //¿formateamos los puntos de miles
    format.miles ? val = formatNumber(val) : null;      //¿formateamos los puntos de miles
    $(id).val(val)                                      //?devolvemos el valor formateado

    //¿control de cero pirncipal
    let valChar = $(id).val().charAt(1)
    if($(id).val().length > 1 && $(id).val().charAt(0) == "0" && (valChar != "." || valChar != ",")){
        $(id).val($(id).val().substring(1));
    }

}

//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ centrar un elemento en la pantalla ]
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ centrar elemento ] __________________________________________________________________________________________________________________
export const centerElement = (element)=>{

    //@element = elemento a centrar $(elemento)
    //@spaceTop = espacio superior a dejar libre en px

    var top = element.offset().top;
    var height = element.height();
    var windowHeight = $(window).height();
    var offset = top - ((windowHeight / 2) - (height / 2));
    //con un espacio de 32px en la aprte superior
    offset = offset - 16;
    //animacion rapida
    $('html, body').animate({scrollTop:offset}, 200);
    console.log("centrando");
}







//==========================================================================================================================
//(Js)-->( 'limitadores' )
//==========================================================================================================================
export const input_limit_max_min = (id, max, min)=> {
   $(id).on("keyup", function() {
       if (this.value.length > max) { this.value = this.value.substr(0, max); }
       if (this.value.length < min) { this.value = this.value.substr(0, min); }
   })
}
