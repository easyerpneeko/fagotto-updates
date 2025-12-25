// ========== CHISTES PARA DASHBOARD - 100 CHISTES DE NEGOCIOS ==========
const chistes = [
    "¿Por qué los ingenieros comerciales nunca juegan al escondite? Porque siempre están en ventas visibles 📊",
    "Un Excel entra a un bar y el bartender dice: '¿Qué vas a tomar?' Excel responde: '=#REF!' 🍺",
    "¿Cuál es el colmo de un vendedor? Que le compren el cuento 😂",
    "¿Por qué los contadores son buenos en matemáticas? Porque saben que 2+2=5 si el jefe lo necesita 🤑",
    "Mi jefe me dijo: 'Trabaja más inteligente, no más duro'. Ahora trabajo desde casa en pijama 🏠",
    "¿Cómo llamas a un ingeniero comercial sin Excel? Desempleado 💼",
    "El ROI positivo más grande que he tenido es el café gratis de la oficina ☕",
    "¿Por qué los gerentes odian la tabla periódica? Porque tiene demasiados elementos sin KPIs 📈",
    "Mi CV dice que soy 'orientado a resultados'. En realidad, estoy orientado al viernes 🎉",
    "¿Cuántos ingenieros comerciales se necesitan para cambiar una ampolleta? Ninguno, contratan un outsourcing 💡",
    
    "Error 404: Motivación laboral no encontrada. Por favor, deposite café ☕",
    "¿Por qué el PowerPoint cruzó la calle? Para llegar a la siguiente diapositiva 🖥️",
    "Mi sueldo es como mi celular: siempre con batería baja 🔋",
    "¿Qué es un ingeniero comercial vegano? Alguien que solo come números verdes 🥗",
    "El lunes es como una fórmula de Excel: nunca funciona a la primera 📊",
    "¿Por qué los vendedores son buenos mentirosos? Años de experiencia diciendo 'el cheque está en el correo' 📨",
    "Mi vida laboral es como WiFi: siempre conectado pero sin productividad 📡",
    "¿Cuál es el animal favorito de un contador? El cash-toro 🐂",
    "Hice una reunión de 2 horas que pudo ser un email. Ahora soy gerente 👔",
    "¿Por qué los economistas llevan paraguas? Por si llueven variables 🌧️",
    
    "Mi plan de negocios: Fase 1) Idea brillante, Fase 2) ????, Fase 3) Millonario 💰",
    "¿Cuánto es 1+1? Depende del presupuesto disponible 🤔",
    "Renuncié a mi trabajo de vendedor. Ya no podía venderme más 😅",
    "¿Por qué los marketeros no juegan póker? Porque siempre muestran todas sus cartas en Instagram 🃏",
    "Mi LinkedIn dice 'Dinámico y proactivo'. Mi cama dice 'Sí, claro' 🛏️",
    "¿Cómo duerme un auditor? Bien, porque siempre cuadra todo 💤",
    "El único KPI que me importa es el 'Kilometraje de Piscolas' el viernes 🍹",
    "¿Por qué el gerente fue al psicólogo? Sufría de Excel-encia laboral 🏥",
    "Mi jefe me pidió 110% de dedicación. Le di 100% y una factura del 10% restante 💵",
    "¿Cuál es el deporte favorito de los contadores? El balance extremo ⚖️",
    
    "Trabajo en equipo significa: yo hago todo y todos firman 📝",
    "¿Por qué los ingenieros comerciales aman el café? Porque Java también es un lenguaje de negocios ☕",
    "Mi estrategia de ventas: Si no funciona, haz otro PowerPoint 📽️",
    "¿Cuántos MBA se necesitan para cambiar una ampolleta? Solo uno, pero cobra $500 por la consultoría 💡",
    "El salario emocional no paga el arriendo, pero al menos me hace sentir pobre con dignidad 🏚️",
    "¿Por qué los vendedores nunca mienten? Simplemente ajustan la realidad al forecast 📊",
    "Mi título universitario y $2 me compran un café. Solo el café, porque el título no vale nada ☕",
    "¿Cuál es el lema de un startup? 'Fake it till you make it... or break it' 🚀",
    "Hice networking toda la noche. Traducción: bebí cerveza y repartí tarjetas 🍺",
    "¿Por qué los economistas son pesimistas? Porque siempre esperan la próxima crisis 📉",
    
    "Mi presentación tiene 80 slides. Los primeros 79 son la intro 🎭",
    "¿Cuál es la diferencia entre un optimista y un pesimista? El Excel que usan 📈",
    "Trabajo bajo presión. Traducción: entrego todo atrasado 📅",
    "¿Por qué el contador fue al gimnasio? Para hacer cuadrar sus abdominales 💪",
    "Mi email dice 'Enviado desde mi iPhone'. En realidad, lo envié desde el baño 🚽",
    "¿Cuántos consultores se necesitan para cambiar una ampolleta? No importa, ya facturaron 20 horas 💰",
    "El secreto del éxito: Ctrl+C, Ctrl+V y mucha confianza ⌨️",
    "¿Por qué los gerentes aman las metáforas deportivas? Porque nunca han hecho deporte 🏈",
    "Mi jefe me dijo 'Piensa fuera de la caja'. Ahora trabajo desde un container ⬜",
    "¿Cuál es el colmo de un vendedor de seguros? Que nadie le asegure el éxito 🔒",
    
    "Reunión productiva: aquella donde todos fingen escuchar 👂",
    "¿Por qué los marketeros van al cielo? Porque ya vivieron el infierno de las campañas 😇",
    "Mi plan de pensión: ganar la lotería o esperar que el Bitcoin suba 🎰",
    "¿Cuál es el miedo de un ingeniero comercial? Que le pidan hablar sin PowerPoint 😱",
    "Habilidades blandas: saber fingir que te importan las reuniones de 8 AM 😴",
    "¿Por qué los contadores son buenos en fiestas? Porque siempre traen balance 🎊",
    "Mi Excel tiene más colores que un arcoíris y menos sentido 🌈",
    "¿Cuántos vendedores se necesitan para atornillar algo? Ninguno, lo subcontratan 🔧",
    "El único leverage que conozco es mi tarjeta de crédito 💳",
    "¿Por qué el MBA fue al psicólogo? Sufría de análisis parálisis 🧠",
    
    "Mi comfort zone es una cama. Mi stretch goal es levantarme 🛌",
    "¿Cuál es el deporte de los contadores? El cash-flow extremo 💸",
    "Innovación disruptiva: copiar lo que hace la competencia pero con otro color 🎨",
    "¿Por qué los gerentes de proyecto siempre llegan tarde? Están ajustando el cronograma ⏰",
    "Mi estrategia de crecimiento: crecer verticalmente en la cama 📏",
    "¿Cuántos economistas predicen una crisis? Todos. ¿Cuántos aciertan? Ninguno 🎯",
    "El único synergy que necesito es café + WiFi ☕📶",
    "¿Por qué los vendedores aman las metas? Porque nunca las alcanzan y así siempre tienen trabajo 🎯",
    "Mi dashboard tiene más rojo que semáforo de barrio peligroso 🚦",
    "¿Cuál es el colmo de un analista financiero? Que no le den los números 🔢",
    
    "Emprendimiento exitoso: tener más seguidores en Instagram que clientes 📱",
    "¿Por qué el ingeniero comercial cruzó la calle? Para venderle algo al del otro lado 🛣️",
    "Mi valor agregado es que sé usar Google 🔍",
    "¿Cuántos millennials se necesitan para cambiar una ampolleta? Ninguno, piden delivery de luz 💡",
    "El único KPI que importa en viernes: Kilómetros hasta el bar más cercano 🍻",
    "¿Por qué los contadores son buenos en yoga? Saben hacer el balance perfecto 🧘",
    "Mi jefe pidió 'out of the box thinking'. Le entregué un PowerPoint con WordArt 🎨",
    "¿Cuál es el hobby de un auditor? Buscar errores en los memes de WhatsApp 🕵️",
    "Trabajo remoto: estar en pijama y fingir profesionalismo en Zoom 👔",
    "¿Por qué los vendedores son buenos en matemáticas? Porque siempre suman comisiones imaginarias 🧮",
    
    "Mi plan de carrera: esperar que mi jefe renuncie 📈",
    "¿Cuántos gerentes se necesitan para aprobar algo? Todos menos el que decidió 👥",
    "El éxito es 1% inspiración y 99% evitar reuniones innecesarias 💡",
    "¿Por qué el economista no tiene amigos? Porque todo lo ve como costo-beneficio 🤝",
    "Mi elevator pitch dura 3 pisos porque el ascensor es lento 🛗",
    "¿Cuál es el animal favorito de un vendedor? El cierre de ventas 🦎",
    "Pensamiento estratégico: googlear antes de la reunión 🧠",
    "¿Por qué los ingenieros comerciales aman Excel? Porque ahí pueden ser Dioses 👑",
    "Mi networking: seguir a gente en LinkedIn y nunca hablarles 🔗",
    "¿Cuántos consultores se necesitan para definir cuántos consultores se necesitan? Depende del presupuesto 💰",
    
    "Liderazgo transformacional: cambiar el fondo del PowerPoint a oscuro 🌑",
    "¿Por qué el contador fue a terapia? Porque no le cuadraban las emociones 😢",
    "Mi core business es procrastinar con estilo 😎",
    "¿Cuál es el sueño de todo vendedor? Que el cliente se venda solo 💭",
    "Gestión del cambio: aceptar que nunca llegaré temprano los lunes 🕐",
    "¿Por qué los marketeros son buenos actores? Porque venden humo profesionalmente 🎭",
    "Mi ventaja competitiva es que nadie más quiere este trabajo 🏆",
    "¿Cuántos MBA se necesitan para cambiar el mundo? Ninguno, subcontratan el cambio 🌍",
    "El balance perfecto: trabajo 40 horas, finjo otras 40 ⚖️"
];

// Función para obtener un chiste aleatorio sin repetir
let chistesUsados = [];

function obtenerChiste() {
    // Si ya usamos todos, reiniciamos
    if (chistesUsados.length >= chistes.length) {
        chistesUsados = [];
    }
    
    // Buscar un chiste no usado
    let chisteAleatorio;
    do {
        chisteAleatorio = chistes[Math.floor(Math.random() * chistes.length)];
    } while (chistesUsados.includes(chisteAleatorio));
    
    chistesUsados.push(chisteAleatorio);
    return chisteAleatorio;
}

// Función para actualizar el chiste en el loader
function actualizarChiste() {
    const chisteElement = document.getElementById('loader-chiste');
    if (chisteElement) {
        const nuevoChiste = obtenerChiste();
        chisteElement.style.opacity = '0';
        
        setTimeout(() => {
            chisteElement.textContent = nuevoChiste;
            chisteElement.style.opacity = '1';
        }, 300);
    }
}

// Exportar funciones
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { obtenerChiste, actualizarChiste };
}
