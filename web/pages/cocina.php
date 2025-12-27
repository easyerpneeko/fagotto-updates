<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>FagottoERP - COCINA</title>
    <link rel="icon" type="image/x-icon" href="../../assets/css/negro.png">
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/cocina.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/dacd53634d.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!--helpers-->
    <script src="../../assets/helpers/helperURL.js"></script>
    <script src="../../assets/helpers/helperAlert.js"></script>
    <script src="../../assets/helpers/helperSpecial.js"></script>
    <script src="../../assets/helpers/helperRoutes.js"></script>
    <script src="../../assets/helpers/helperStore.js"></script>
    <script src="../../assets/helpers/helperRequest.js"></script>
</head>

<body>
    <div class="bodys-header">
        <div class="header-content">
            <img src="../../assets/css/negro.png" alt="Fagotto" class="header-logo">
            <div class="header-titles">
                <div class="monitor-title"></div>
                <div class="server-date" id="serverDate">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span id="currentDate"></span>
                </div>
            </div>
        </div>
        
        <div class="toggle-switches">
            <!-- <div class="time-info" style="font-size: 0.875rem; color: var(--text-gray); margin-right: 1rem;">
                Actualización en <span id="updateTime" style="color: var(--bg-orange); font-weight: 600;">0</span>s
            </div>
            <div class="switch-group">
                <label><i class="fa-solid fa-volume-high"></i> Sonido</label>
                <div class="toggle-switch active" id="sound-toggle"></div>
            </div>
            <div class="switch-group">
                <label><i class="fa-solid fa-arrows-rotate"></i> Actualización automática</label>
                <div class="toggle-switch active" id="auto-update-toggle"></div>
            </div> -->
            <button id="reload" class="status-btn kitchen">
                <i class="fa-solid fa-rotate-right"></i> Refrescar
            </button>
            <button id="btn-volver" class="status-btn secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </button>
            <button id="btn-cerrar-sesion" class="status-btn danger">
                <i class="fa-solid fa-sign-out-alt"></i> Cerrar Sesión
            </button>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row" id="card-render">
            <!-- Las tarjetas se renderizan aquí -->
        </div>
    </div>


    <script>


        const RenderCard = $("#card-render") //contenedor de cocinas
        const update = $("#updateTime")
        const reload = $("#reload")

        const _timeUpdate = 15; //SEGUNDOS  DE ESPERA ANTES DE RECARGAR
        let _ORDERS = [];       //ARRAY DE ORDENES EN ESPERA
        let _ORDENMemory = []   //array auxiar de tickes 
        let _ORDERHidenMemory = []; //array de oprdenes que cierra el cocinero
        let time = 0;           //TIEMPO DE ESPERA EN REVERSA

        reload.click(function () {
            requestLoadCocinaActive() //recargamos cocina
        })

        // Botón Volver
        $("#btn-volver").click(function() {
            // Volver al dashboard o página anterior
            window.history.back();
        });

        // Botón Cerrar Sesión
        $("#btn-cerrar-sesion").click(function() {
            if (confirm("¿Está seguro que desea cerrar sesión?")) {
                _store().serial.delete();           //borramos el serial
                _store().session.delete()           //borramos la session
                _store().session.user().delete()    //borramos el usuario
                //redirigimos a index
                window.location.href = "../index.html"
            }
        });

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
            return `<li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <b>${prp.quantity}</b><span>${prp.name}</span>
                    </div>                            
                 </li>`
        }

        function cardListOrdenNew(prp) {
            return `<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-4 mb-3 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-gradient-to-r from-orange-400 to-orange-500 text-white font-bold w-10 h-10 rounded-full flex items-center justify-center text-sm shadow-sm">
                                    ${prp.quantity}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-800 text-base">${prp.name}</span>
                                    <span class="text-sm text-gray-500">Cantidad: ${prp.quantity}</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-xs text-gray-400 font-medium">NUEVO</span>
                            </div>
                        </div>
                    </div>`
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

        function requestLoadCocinaActive() {
            /**
            * REQUEST COCINA ALL GET
            * @description esta funcion llama todas las mesas del negocio
            * @param {null}
            * @return {null}
            */

            __conection({
                url: URL("/app/local/cafeteria/modo_cocina/orders_kitchens"),
                header: credentials(),
            }, {}, function (request) {
                time = _timeUpdate
                _ORDERS = []
                _ORDENMemory = []
                console.log(request);

                if (is_array(request)) {
                    RenderCard.find("div").remove()

                    request.reverse().forEach(item => {
                        //console.log("order",is_undefined(_ORDENMemory[item.order.id]))
                        if (is_undefined(_ORDERHidenMemory[item.order.id])) {
                            // Generar tarjeta con diseño moderno personalizado
                            htmlCard = `<div class="order-card fade-in" onClick="handlehiddenOrden(this,${item.order.id})"></div>`
                            htmlCard = $(htmlCard) //contenedor

                            // Header moderno personalizado
                            let descripcionPedido = '';
                            if (item.order && item.order.description && item.order.description.trim() && item.order.description !== '.') {
                                descripcionPedido = item.order.description.trim();
                            }
                            
                            htmlHeader = `
                            <div class="card-header">
                                <div style="text-align: center; padding: 1rem 0;">
                                    <div class="card-logo-info">
                                        <img src="../../assets/css/negro.png" alt="Fagotto" class="card-fagotto-logo">
                                    </div>
                                    <div style="font-size: 3rem; font-weight: 700; color: var(--bg-orange); margin-bottom: 0.5rem;">#${item.order_id}</div>
                                    ${descripcionPedido ? `<div style="font-size: 1.5rem; font-weight: 600; margin-top: 0.5rem; padding: 0 1rem;"><span style="color: #1e293b;">Nombre:</span> <span class="customer-name">${descripcionPedido}</span></div>` : ''}
                                    <!-- <div class="location-info">
                                        <div class="location-name">
                                            <i class="fa-solid fa-user-tie"></i>
                                            ${is_undefined(item.order.waiter_assigned) ? "No asignado" : String(item.order.waiter_assigned.name)}
                                        </div>
                                        <div class="table-info">${is_undefined(item.order.board) ? "Sin Mesa" : item.order.board.name}</div>
                                    </div>
                                    <div class="people-count">
                                        <i class="fa-solid fa-users"></i>
                                        <span>${String(item.order.state)}</span>
                                    </div> -->
                                </div>
                            </div>`;

                            htmlHeader = $(htmlHeader) //header
                            htmlHeaderTime = $(htmlHeader).find(".time-elapsed");
                            
                            // Body con lista de productos personalizada
                            htmlBody = `
                                <div class="card-body">
                                    <ul class="order-items">
                                        ${item.productos.map((prp) => { return prp.quantity > 0 ? cardListOrdenNew(prp) : "" }).join("")}    
                                    </ul>
                                </div>`
                            htmlBody = $(htmlBody)

                            // Footer con botón personalizado
                            htmlFooter = `
                                <div class="card-footer">
                                    <button class="complete-btn">
                                        <i class="fa-solid fa-check"></i>
                                        COMPLETAR ORDEN
                                    </button>
                                </div>`;
                            htmlFooter = $(htmlFooter)

                            htmlCard.append(htmlHeader)
                            htmlCard.append(htmlBody)
                            htmlCard.append(htmlFooter)

                            _ORDERS.push({ data: item, dateInt: item.created_at, dateEnd: new Date, timeRender: htmlHeaderTime })
                            _ORDENMemory[item.order.id] = true;
                            RenderCard.append(htmlCard)
                        }
                    });

                }
            })
        }


        function handlehiddenOrden(doc, id) {
            if (alerta("DESEA OCULTAR ESTA ORDEN?") === true) {
                _ORDERHidenMemory[id] = true; //detecta si la id se a cerre 
                $(doc).hide(200)
            }
            changeStatus(doc,id)
        }

        function changeStatus(element,order_id) {
            element.classList.remove('status-preparing');
            element.classList.add('status-ready');
            element.textContent = 'LISTO';
            element.onclick = null; // Deshabilitar clic después de cambiar estado

            setTimeout(() => {
                // Eliminar la fila del DOM después de 15 segundos
                const row = element.closest('tr');
                if (row) {
                    row.remove();
                }
            }, 2000); // 2000 milisegundos = 2 segundos

            __conection({
                url: URL("/local/ticket/orderkitchen/"+order_id),
                header: credentials(),
                method: 'PUT'
            }, {}, function (request) {
                
            });
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

        // Función para mostrar la fecha actual del servidor
        function updateServerDate() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            };
            const dateString = now.toLocaleDateString('es-ES', options);
            document.querySelector('#currentDate').textContent = dateString;
        }

        // Cargar la fecha cuando se carga la página
        $(document).ready(function() {
            updateServerDate();
            // Actualizar la fecha cada minuto
            setInterval(updateServerDate, 60000);
        });


    </script>
</body>

</html>