//VERCION 0.0.1
//DEPENDENCIA JSSpecialControl.js
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ parseo de numeros ]
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ

//¿foratear puntos de miles
function formatNumber(num) 
{
    num = String(num);
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

//¿desformatear puntos de miles
function unformatNumber(num)
    {
        num = String(num);
        return num.replace(/,/g, '');
    }

//¿parseaos deciales
function formatDecimal (value)
{
    value = String(value);
    value = value.replace(/(\..*)\./g, '$1');       //?que solo tenga un punto decimal en todo el numero
    value = value.replace(/(\..{2}).*/g, '$1');     //?que solo tenga 2 decimales despues del punto
    value = value.replace(/(\.[0-9]*?)0+$/g, '$1'); //?que no tenga 2 ceros despues del punto
    return value;
}

//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//¿(Js) [ parseo de xml ]
//-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ

