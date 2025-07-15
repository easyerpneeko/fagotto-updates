    //-==========================================================================================================================
    //?(Js) [ STORE]
    //-==========================================================================================================================
    function storeWeb_set(name, info){localStorage.setItem(String(name), String(info));}
    function storeWeb_get(name){return String(window.localStorage.getItem(name))}
    function storeWeb_delete(name){localStorage.setItem(String(name), "null");}
    
    const _store = () => {
            const st = {
                clear:function(){localStorage.clear();},
                //?(Js) [ serial ] __________________________________________________________________________________________________________________
                serial:{
                    key:"912338BA",
                    set: function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                    get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                    delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                    isset: function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                },
                
                //?(Js) [ appkey ] __________________________________________________________________________________________________________________
                waiter:{
                    key:"74JGARJS",
                    set: function($param){storeWeb_set(this.key, JSON.stringify($param));return $param},     //?guarda el serial en el store web
                    get: function($param=""){
                        if($param===""){return JSON.parse(storeWeb_get(this.key))}
                        return JSON.parse(storeWeb_get(this.key))[$param]
                    },//?obtiene el serial del store web
                    delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                    isset: function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                },
                boards:{
                    key:"74J4H5JS",
                    set: function($param){storeWeb_set(this.key,JSON.stringify($param));return $param},     //?guarda el serial en el store web
                    get: function($param=""){
                        if($param===""){return JSON.parse(storeWeb_get(this.key))}
                        return JSON.parse(storeWeb_get(this.key))[$param]
                    },//?obtiene el serial del store web
                    delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                    isset :function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                },
                app:{
                    //¿(Js) [ serial ] __________________________________________________________________________________________________________________
                    serial: function(){
                        return {
                            key:"912338BA",
                            set: function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                            get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                            delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                            isset: function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                        }
                    },
                    //¿(Js) [ key ] __________________________________________________________________________________________________________________
                    key:function(){
                        return {
                            key:"74JS93JS",
                            set: function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                            get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                            delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                            isset: function(){return storeWeb_get(this.key)!=="null"},    
                        }
                    },
                    //¿(Js) [ name ] __________________________________________________________________________________________________________________
                    name:function(){
                        return {
                            key:"74J4H5JS",
                            set: function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                            get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                            delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                            isset :function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                        }                                                                                           //?verifica si el serial existe en el store web
                    }
                },
                
                session:{
                    key:"HAS72J29S",
                    set: function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                    get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                    delete: function(){
                        storeWeb_delete(this.key)
                        storeWeb_delete("HAS72J2TS")
                        storeWeb_delete("HAS72J2TSAA")
                        storeWeb_delete("HAS72J2TSAB")
                        storeWeb_delete("HASYEU739")
                    },                                          //?elimina el serial del store web
                    isset :function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                    user:function(){
                        return {
                            key:"HASYEU739",
                            set:function($param){storeWeb_set(this.key,String($param));return String($param)},     //?guarda el serial en el store web
                            get: function($param=""){
                                if($param===""){return JSON.parse(storeWeb_get(this.key))}
                                return JSON.parse(storeWeb_get(this.key))[$param]
                            },//?obtiene el serial del store web
                            delete: function(){storeWeb_delete(this.key)},     
                            isset :function(){return storeWeb_get(this.key)!=="null"},             
                        }    
                    },
                    time:function(){
                        return {
                            key:"HAS72J2TS",
                            keyleft:"HAS72J2TSAA",
                            keystart:"HAS72J2TSAB",
                            set: function($param){
                                 //control de tiepos
                                let time = $param
                                let timeastart = new Date().getTime()//tiempo de inicio
                                let timeLef = time + timeastart //tiempo max activo
                                
                                storeWeb_set(this.keystart,String(timeastart))
                                storeWeb_set(this.keyleft,String(timeLef))
                                storeWeb_set(this.key,String($param));
                                return timeLef
                            },     //?guarda el serial en el store web
                            get: function(){return storeWeb_get(this.key)},                                         //?obtiene el serial del store web
                            delete: function(){storeWeb_delete(this.key)},                                          //?elimina el serial del store web
                            isset :function(){return storeWeb_get(this.key)!=="null"},                              //?verifica si el serial existe en el store web
                           
                            lef:function(){return storeWeb_get(this.keyleft)},              //¿obtenemos el tiempo restante 
                            start:function(){return storeWeb_get(this.keyleft)},            //¿obtenemos el tiempo en que incio la app
                            isExpired:function(){                                           //¿obtenemos el tiempo restante
                                let timeLef = storeWeb_get(this.keyleft)
                                let timeastart = new Date().getTime()//tiempo de inicio
                                let time = timeLef - timeastart
                                //transformarlo a segundos
                                let seconds = time / 1000
                                if(seconds <= 0){
                                    return true
                                }
                                return false
                            }
                        }
                    }
                }
            }
            return st;

        }

        function waiter(){return JSON.parse(storeWeb_get(this.key))} //obtenemos la mesera
        function credentials(){
            return {
                "app-key":storeWeb_get("912338BA"),
                'authorization': `Bearer ${storeWeb_get("HAS72J29S")}`
            }
        }
