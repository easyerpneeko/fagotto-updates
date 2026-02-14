<?php
/**
 * CONFIGURACIÓN DE MÁQUINAS/LOCALES
 * 
 * Mapea el ID de cada máquina/local del sistema ERP
 * al Device ID correspondiente de MercadoPago Point
 */

return [
    // app_id => Configuración de MercadoPago
    
    // APP_ID 58 - Máquina 4 
    58 => [
        'nombre' => 'Máquina 4 / Local Cuenta 4',
        'device_id' => 'NEWLAND_N950__N950NCC804178629',
        'access_token' => 'APP_USR-2728862682527643-020612-70b77f3b86cd7ad85e2e00c4bfa4ba99-479074878',
        'store_id' => '77440880',
        'pos_id' => '123313735',
        'descripcion' => 'Terminal NEWLAND N950 - Cuenta 4'
    ],
    
    // APP_ID 116 - Fagotto Las Condes (Cuenta 1)
    116 => [
        'nombre' => 'Fagotto Las Condes',
        'device_id' => 'NEWLAND_N950__N950NCC302980808',
        'access_token' => 'APP_USR-2728862682527643-020612-70b77f3b86cd7ad85e2e00c4bfa4ba99-479074878',
        'store_id' => '76216860',
        'pos_id' => '119836588',
        'descripcion' => 'Terminal NEWLAND N950 - Cuenta 1'
    ],
    
    // Ejemplos de otros app_ids si los necesitas:
    
    // 2 => [
    //     'nombre' => 'Máquina 2 / Terminal NEWLAND',
    //     'device_id' => 'NEWLAND_N950__N950NCC302980807',
    //     'access_token' => 'APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034',
    //     'store_id' => '75998370',
    //     'pos_id' => '120882246',
    //     'descripcion' => 'Terminal NEWLAND N950 - Cuenta 2'
    // ],
    
    // 3 => [
    //     'nombre' => 'Máquina 3 / PVD Ahumada',
    //     'device_id' => 'PAX_A910__SMARTPOS1495485450',
    //     'access_token' => 'APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034',
    //     'store_id' => '73565262',
    //     'pos_id' => '116722925',
    //     'descripcion' => 'Terminal PAX A910 - Cuenta 3'
    // ],
];
