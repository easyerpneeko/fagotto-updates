<?php
/**
 * CONFIGURACIÓN DE MÁQUINAS/LOCALES
 * 
 * Mapea el ID de cada máquina/local del sistema ERP
 * al Device ID correspondiente de MercadoPago Point
 */

return [
    // app_id => Configuración de MercadoPago
    
    // APP_ID 58 - Fagotto Agustinas (Cuenta 4 - PRODUCCIÓN)
    // Terminal: NEWLAND_N950__N950NCC804178629
    // S/N: N950NCC804178629
    58 => [
        'nombre' => 'Fagotto Agustinas',
        'device_id' => 'NEWLAND_N950__N950NCC804178629',
        'access_token' => 'APP_USR-2109560573074144-021321-184c12b654af486efc6d9e4ccc8887ce-2674218898',
        'store_id' => '77440880',
        'pos_id' => '123313735',
        'client_id' => '2109560573074144',
        'client_secret' => '7sDnWoq4LQLGqDlFzw410Qm6SYxtKUmG',
        'descripcion' => 'Terminal NEWLAND N950 629 - Cuenta 4 PRODUCCIÓN Agustinas'
    ],
    
    // APP_ID 116 - Fagotto Las Condes (Cuenta 2 - PRODUCCIÓN)
    // Terminal 1: NEWLAND_N950__N950NCC302980807 (Store: 75998370, POS: 120882246)
    // Terminal 2: PAX_A910__SMARTPOS1495485450 (Store: 73565262, POS: 120882245)
    116 => [
        'nombre' => 'Fagotto Las Condes',
        'device_id' => 'NEWLAND_N950__N950NCC302980807',
        'access_token' => 'APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034',
        'store_id' => '75998370',
        'pos_id' => '120882246',
        'client_id' => '8228397783120956',
        'client_secret' => 'nxDp3sCINQCR716fCShUmJpZlAspyI4d',
        'descripcion' => 'Terminal NEWLAND N950 807 - Cuenta 2 PRODUCCIÓN Las Condes'
    ],
    
    // APP_ID 86 - Fagotto Ahumada (Cuenta 3 - PRODUCCIÓN)
    // Terminal: NEWLAND_N950__N950NCC804178691
    // S/N: N950NCC804178691
    86 => [
        'nombre' => 'Fagotto Ahumada',
        'device_id' => 'NEWLAND_N950__N950NCC804178691',
        'access_token' => 'APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034',
        'store_id' => '73565262',
        'pos_id' => '120882245',
        'client_id' => '8228397783120956',
        'client_secret' => 'nxDp3sCINQCR716fCShUmJpZlAspyI4d',
        'descripcion' => 'Terminal NEWLAND N950 691 - Cuenta 3 PRODUCCIÓN Ahumada'
    ],
    
    // ========== CUENTA 1 - FAGOTTO PRUEBAS (NO USAR EN PRODUCCIÓN) ==========
    // Terminal: NEWLAND_N950__N950NCC302980808
    // Store ID: 76216860
    // POS ID: 121142594
    // Token: APP_USR-6283355973944660-111310-a9bb1118923d4beb4d7574d3f057abd9-67282805
    // Device ID: NEWLAND_N950__N950NCC302980808
    // Client ID: 6283355973944660
    // Client Secret: f4AEk80wBgiUUcwvv4EJTgofXVR0Ls3e
    // Environment: PRUEBAS - NO USAR EN PRODUCCIÓN
    // ========================================================================
];
