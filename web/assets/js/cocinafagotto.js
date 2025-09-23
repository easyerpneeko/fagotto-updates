
const RenderCard = $("#card-render") //contenedor de cocinas
const update = $("#updateTime")
const reload = $("#reload")

const _timeUpdate = 90; //SEGUNDOS  DE ESPERA ANTES DE RECARGAR
let _ORDERS = [];       //ARRAY DE ORDENES EN ESPERA
let _ORDENMemory = []   //array auxiar de tickes 
let _ORDERHidenMemory = []; //array de oprdenes que cierra el cocinero
let time = 0;           //TIEMPO DE ESPERA EN REVERSA

reload.click(function () {
    requestLoadCocinaActive() //recargamos cocina
})

$(document).ready(function () {


    requestLoadCocinaActive() //recargamos cocina

    setInterval(() => {
        requestLoadCocinaActive() //recargamos cocina
        update.text(0)
    }, (1000 * _timeUpdate));

    setInterval(() => {
        update.text(time)
        time--
        if (time <= 0) { time = 0 }

        //_ORDERS.push({data:item,date:item.created_at,timeRender : htmlHeaderTime})
        _ORDERS.forEach(item => {
            let render = item.timeRender;
            let _date = item.dateInt
            let _date_ = item.dateEnd

            let _date1 = new Date()
            let _date2 = new Date(_date)

            var dif = _date1.getTime() - _date2.getTime()
            var min = (dif / (1000 * 60) + 30);
            var minutes = min;
            if (minutes <= 0) {
                minutes = 0
                $(render).addClass("text-danger")
            }
            $(render).text(`${String((parseInt(minutes)))}m`)
            //obtener el tiempo actual
        })
    }, 1000);

})

function calcDateTime(date) {
    /* Conseguimos los datos del momento actual (Ahora) */
    var now = new Date(); // Conseguimos los datos de Ahora
    var nowday = now.getDay();  // Que dia estamos

    var nowhour = now.getHours();  // Hora actual
    var nowminute = now.getMinutes(); // Minuto actual
    var nowsecond = now.getSeconds();  // Segundo actual

    date = date.split(" ")
    _date1 = date[0].split("-") //fecha 
    _hour1 = date[1].split(":") //hora
    /*
    Hay que crear tiempo1 y tiempo2 a partir de la función Date al que le pasamos:
    Año, mes, dia, horas, minutos, segundos
    */
    var tiempo1 = new Date(2035, 11, 30, 23, 59, 59, 0);
    var tiempo2 = new Date(2015, 11, 26, nowhour, nowminute, nowsecond, 0);
    var dif = tiempo1.getTime() - tiempo2.getTime()

    var Segundos_de_T1_a_T2 = dif / 1000;
    var Segundos_entre_fechas = Math.abs(Segundos_de_T1_a_T2);
}

function cardListOrden(prp) {

    //Salsas
    if (prp.category == 2) {
        return `<li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>${prp.name}</span><b>${prp.quantity + 'kg'}(${prp.name == "PESTO" ? (prp.vasos / prp.min_quantity) : (prp.vasos / prp.min_quantity) * 2}Bolsas)</b>
                </div>                            
            </li>`
    }
    //De kilos
    else if (prp.category == 4) {
        return `<li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>${prp.name}</span>   <b>${prp.name == "Queso" ? (prp.quantity * 1000) / 459 + ' Bolsas' : prp.quantity + 'kg'}</b>
                </div>                            
            </li>`
    } else {
        return `<li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>${prp.name}</span>   <b>${prp.quantity}</b>
                </div>                            
            </li>`
    }

}

function tiempo_de_orden(time) {
    // time = "2023-01-11 00:46:24"
    // cuantos minutos tiene la orden activa desde que se creo

    let _date1 = new Date()
    let _date2 = new Date(time)

    var dif = _date1.getTime() - _date2.getTime()
    var min = (dif / (1000 * 60) + 30);
    var minutes = min;
    if (minutes <= 0) {
        minutes = 0
    }
    return `${String((parseInt(minutes)))}m`
}

function handlehiddenOrden(pedido, app_id, pedido_id) {

    if (alerta("DESEA OCULTAR ESTA ORDEN?") === true) {
        _ORDERHidenMemory[pedido_id] = true; //detecta si la id se a cerre 
        ocultarPedido(app_id, pedido_id);
        $(pedido).hide(200)
    }

}

function obtenerHora(fecha) {
    var fechaCompleta = new Date(fecha);
    var hora = fechaCompleta.getHours();
    var minutos = fechaCompleta.getMinutes();
    var segundos = fechaCompleta.getSeconds();

    // Agregar ceros iniciales si es necesario
    if (hora < 10) {
        hora = '0' + hora;
    }
    if (minutos < 10) {
        minutos = '0' + minutos;
    }
    if (segundos < 10) {
        segundos = '0' + segundos;
    }

    var horaFormateada = hora + ':' + minutos;
    return horaFormateada;
}

function ocultarPedido(app_id, pedido_id) {

    __conection({
        url: generarURLApi(`/local/request/hide/${app_id}/${pedido_id}`),
        header: credentials(),
        dev: true,
        method: 'PUT'

    }, {}, function (request) {

        console.log(request);

        requestLoadCocinaActive();

    });
}

function requestLoadCocinaActive() {
    console.log("🔄 Iniciando petición a /getApproved...");
    
    __conection({
        url: generarURLApi("/getApproved"),
        header: credentials(),
        method: 'GET',
    }, {}, function (request) {
        time = _timeUpdate;
        _ORDERS = [];
        _ORDENMemory = [];

        console.log("📋 RESPUESTA COMPLETA del servidor:", request);
        console.log("📊 Tipo de respuesta:", typeof request);
        console.log("📊 Es array?:", Array.isArray(request));
        console.log("📊 Cantidad de propiedades:", Object.keys(request || {}).length);

        if (request) {
            RenderCard.find("div").remove();
            RenderCard.addClass("container");

            let carousel = $('<div class="slick-carousel"></div>');
            RenderCard.append(carousel);

            let totalPedidos = 0;
            
            // 🔍 CONTAR TOTAL DE PEDIDOS ANTES DE RENDERIZAR
            for (const app in request) {
                let pedidos = request[app].original || [];
                totalPedidos += pedidos.length;
            }
            
            console.log(`📊 TOTAL PEDIDOS ENCONTRADOS EN TODAS LAS APPS: ${totalPedidos}`);

            for (const app in request) {
                let pedidos = request[app].original;
                console.log(`🏪 App: ${app} - Total pedidos encontrados: ${pedidos.length}`);
                
                if (pedidos.length > 0) {
                    // 🔄 RENDERIZAR CADA PEDIDO INDIVIDUALMENTE (NO SOLO EL PRIMERO)
                    pedidos.forEach((pedido, index) => {
                        console.log(`📦 Procesando pedido ${index + 1}/${pedidos.length} - ID: ${pedido.id} - Emergency: ${pedido.emergency || 'No'}`);
                        
                        // Verificar si este pedido específico está oculto
                        if (is_undefined(_ORDERHidenMemory[pedido.id])) {
                            let cardHeaderClass = "card-header";

                            // ⚡ VERIFICAR SI ES EMERGENCIA
                            if (pedido.emergency && parseFloat(pedido.emergency) > 0) {
                                cardHeaderClass = "card-header card-header-emergency";
                                console.log(`🚨 EMERGENCIA detectada en pedido ${pedido.id}`);
                            } else {
                                console.log(`📝 Pedido NORMAL: ${pedido.id}`);
                            }

                            let appCard = `
                        <div class="col-auto">
                            <div class="card h-100" onClick="handlehiddenOrden(this,'${app}',${pedido.id})">
                                <div class="${cardHeaderClass}">
                                    <h5 class="card-title fw-bold text-center">${app}</h5>
                                    <div class="text-center" style="margin-bottom: 10px;">
                                        <b class="col-auto rounded-pill text-bg-light text-center p-1">${pedido.created_at}</b>
                                    </div>
                                    <div class="row d-flex justify-content-start">
                                        <span class="col-auto p-0 fw-bold"><i class="fa-solid fa-user-tie"></i>
                                            <div class="ml-3 d-inline">${pedido.contact_name}</div>
                                        </span>
                                    </div>
                                    <div class="row d-flex justify-content-between" style="margin-block: 5px;">
                                        <span class="col-auto p-0"># ${pedido.id}</span>
                                        <span class="col-auto">ABCD1234</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        ${JSON.parse(pedido.products).map((prp) => {
                                return prp.quantity > 0 ? cardListOrden(prp) : "";
                            }).join("")}
                                    </ul>
                                </div>
                            </div>
                        </div>`;

                            carousel.append(appCard);
                            console.log(`✅ Tarjeta renderizada para pedido ${pedido.id}`);
                        } else {
                            console.log(`❌ Pedido ${pedido.id} está oculto, no se renderiza`);
                        }
                    });
                }
            }

            carousel.slick({
                infinite: true,
                slidesToShow: 4, // Número de tarjetas visibles por defecto
                slidesToScroll: 4,
                autoplay: true,
                autoplaySpeed: 10000,
                variableWidth: false,
                responsive: [
                    {
                        breakpoint: 768, // Breakpoint para resoluciones menores a 768px
                        settings: {
                            slidesToShow: 2, // Número de tarjetas visibles en resoluciones pequeñas
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 480, // Breakpoint para resoluciones menores a 480px
                        settings: {
                            slidesToShow: 1, // Número de tarjetas visibles en resoluciones muy pequeñas
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
    });
}