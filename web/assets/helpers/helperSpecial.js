
//¿control de validacion de estructuras
    function is_undefined(a) { return (typeof a == 'undefined') } //verifica si es undefined
    function is_null(a) { return a == null }                    //detecta si uan variable es nula
//VERIFICAR SI ES UNA FUNCION
    function is_function(a) { return (typeof a == 'function') } //verifica si es una funcion
    function is_array(a) { return (Array.isArray(a)) }          //verifica si es array
    function is_object(a) { return (typeof a === 'object') }    //verifica si es un objeto
    function is_string(a) { return (typeof a === 'string') }    //verifica si es una cadena
    function is_number(a) { return (typeof a === 'number') }    //verifica si es numero
    function is_file(a) { return (typeof a === 'file') }        //verifica si es un archivo
    function is_keyObject(obj, key) { return key in obj; }      //-si la key esta en el objeto
    function is_valueObject(obj, value) { return value in obj; }//-si el valor esta en el objeto

//¿control de estructuras?
//control de tamaño de estructuras
    function array_size(a) { return a.length }                  //determina el tamaño de una array
    function object_size(a) { return Object.keys(a).length }    //determina el tamaño de un objeto

//control de verificacion si una estructura esta vacia
    function is_array_empty(a) { return (a.length == 0) }               //verifica si un array esta vacio
    function is_object_empty(a) { return (Object.keys(a).length == 0) } //verifica si un objeto esta vacio

//control de busqueda de estructuras
    function array_search(a, b) { return a.indexOf(b) } //busca un elemento en un array
    function object_search(a, b) { return a[b] }        //busca un elemento en un objeto

//control de conversion de estructuras
    function array_to_object(a) { return Object.assign({}, a); }    //convierte un array en un objeto
    function object_to_array(a) { return Object.values(a); }        //convierte un objeto en un array

//control de ordenamiento de estructuras
    function array_sort(a) { return a.sort() } //ordena un array
    function object_sort(a) { return Object.keys(a).sort().reduce((r, k) => (r[k] = a[k], r), {}) } //ordena un objeto

//control de union de estructuras
    function array_merge(array1, array2) {  return array1.concat(array2); } //une dos arrays
    function object_merge(obj1, obj2) {Object.assign (obj1, obj2); return obj1; } //une dos objetos

//obtenemos las keys de las estructuras
    function array_keys(a) { return Object.keys(a) } //obtenemos las keys de un array
    function object_keys(a) { return Object.keys(a) } //obtenemos las keys de un objeto

const _console = {
        textColor : "black;",
        background: "aqua",
        color:function(col,bac="aqua"){
            this.textColor=col;
            this.background=bac;
            return this;
        },
        log:function(a,b){
            console.log(`%c ${a}`,`color:${this.textColor};background:${this.background};padding:5px;border-radius:10px;`,b);
        }
    }
    