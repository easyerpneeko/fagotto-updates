<?php

namespace App\Helpers;

class PaymentMethodHelper
{
    const PAYMENT_METHODS = [
        'contado' => 'Contado',
        'credito' => 'Crédito',
        'efectivo' => 'Efectivo',
        'debito' => 'Débito',
        'transferencia' => 'Transferencia',
        'cheque' => 'Cheque',
        'banco' => 'Banco',
        'amipass' => 'Amipass',
        'multicaja' => 'Multicaja',
        'edenred' => 'Edenred',
        'convenio_empresa' => 'Convenio Empresa',
        'sodexo' => 'Sodexo',
        'rappi' => 'Rappi',
        'junaeb' => 'Junaeb',
        'uber' => 'Uber',
        'pedidos_ya' => 'Pedidos Ya',
        'pluxee' => 'Pluxee',
        'banco_chile_20' => 'Banco Chile 20%',
        'halloween_20' => 'Halloween 20%',
    ];

    const INVOICE_TYPES = [
        'ticket' => 'Ticket',
        'boleta' => 'Boleta'
    ];

    /**
     * Obtiene todos los métodos de pago disponibles
     */
    public static function getPaymentMethods()
    {
        return self::PAYMENT_METHODS;
    }

    /**
     * Obtiene todos los tipos de comprobante disponibles
     */
    public static function getInvoiceTypes()
    {
        return self::INVOICE_TYPES;
    }

    /**
     * Verifica si un método de pago es válido
     */
    public static function isValidPaymentMethod($method)
    {
        return array_key_exists($method, self::PAYMENT_METHODS);
    }

    /**
     * Verifica si un tipo de comprobante es válido
     */
    public static function isValidInvoiceType($type)
    {
        return array_key_exists($type, self::INVOICE_TYPES);
    }

    /**
     * Obtiene la descripción de un método de pago
     */
    public static function getPaymentMethodDescription($method)
    {
        return self::PAYMENT_METHODS[$method] ?? 'Método desconocido';
    }

    /**
     * Obtiene la descripción de un tipo de comprobante
     */
    public static function getInvoiceTypeDescription($type)
    {
        return self::INVOICE_TYPES[$type] ?? 'Tipo desconocido';
    }

    /**
     * Obtiene los métodos de pago electrónicos (que requieren procesamiento especial)
     */
    public static function getElectronicPaymentMethods()
    {
        return [
            'debito',
            'transferencia',
            'banco',
            'amipass',
            'multicaja',
            'edenred',
            'sodexo',
            'rappi',
            'uber',
            'pedidos_ya',
            'pluxee',
            'banco_chile_20',
            'halloween_20'
        ];
    }

    /**
     * Verifica si un método de pago es electrónico
     */
    public static function isElectronicPaymentMethod($method)
    {
        return in_array($method, self::getElectronicPaymentMethods());
    }

    /**
     * Obtiene la configuración de forma de pago para el XML del SII
     */
    public static function getXmlPaymentForm($method)
    {
        switch ($method) {
            case 'contado':
            case 'efectivo':
                return 1; // Contado
            case 'credito':
                return 2; // Crédito
            case 'debito':
                return 3; // Débito
            case 'transferencia':
            case 'banco':
                return 4; // Transferencia
            case 'cheque':
                return 5; // Cheque
            case 'amipass':
            case 'multicaja':
            case 'edenred':
            case 'sodexo':
            case 'pluxee':
            case 'banco_chile_20':
            case 'halloween_20':
                return 6; // Tarjeta alimentación/servicio
            case 'rappi':
            case 'uber':
            case 'pedidos_ya':
                return 7; // Plataforma digital
            default:
                return 1; // Por defecto contado
        }
    }

    /**
     * Obtiene los métodos de pago configurados y activos en la aplicación
     */
    public static function getActivePaymentMethods()
    {
        $activePaymentMethods = [];
        
        // Mapeo de métodos de pago a sus configuraciones
        $paymentConfigs = [
            'factura' => 'modulos.ventas.submodulos.sii.ajustes.factura',
            'efectivo' => 'modulos.ventas.submodulos.sii.ajustes.boleta_local',
            'boleta' => 'modulos.ventas.submodulos.sii.ajustes.boleta',
            'debito' => 'modulos.ventas.submodulos.sii.ajustes.debito',
            'nota_de_credito' => 'modulos.ventas.submodulos.sii.ajustes.nota_de_credito',
            'credito' => 'modulos.ventas.submodulos.sii.ajustes.credito',
            'transferencia' => 'modulos.ventas.submodulos.sii.ajustes.transferencia',
            'cheque' => 'modulos.ventas.submodulos.sii.ajustes.cheque',
            'banco' => 'modulos.ventas.submodulos.sii.ajustes.banco',
            'amipass' => 'modulos.ventas.submodulos.sii.ajustes.amipass',
            'multicaja' => 'modulos.ventas.submodulos.sii.ajustes.multicaja',
            'edenred' => 'modulos.ventas.submodulos.sii.ajustes.edenred',
            'convenio_empresa' => 'modulos.ventas.submodulos.sii.ajustes.convenio_empresa',
            'sodexo' => 'modulos.ventas.submodulos.sii.ajustes.sodexo',
            'rappi' => 'modulos.ventas.submodulos.sii.ajustes.rappi',
            'junaeb' => 'modulos.ventas.submodulos.sii.ajustes.junaeb',
            'uber' => 'modulos.ventas.submodulos.sii.ajustes.uber',
            'pedidos_ya' => 'modulos.ventas.submodulos.sii.ajustes.pedidos_ya',
            'pluxee' => 'modulos.ventas.submodulos.sii.ajustes.pluxee',
            'guia_despacho' => 'modulos.ventas.submodulos.sii.ajustes.guia_despacho',
            'banco_chile_20' => 'modulos.ventas.submodulos.sii.ajustes.banco_chile_20',
            'halloween_20' => 'modulos.ventas.submodulos.sii.ajustes.halloween_20'
        ];
        
        // Verificar qué métodos están configurados
        foreach ($paymentConfigs as $method => $config) {
            if (\App\Helpers\CurrentApp::ConfStr($config)) {
                $activePaymentMethods[] = $method;
            }
        }
        
        // Agregar métodos especiales
        if (\App\Helpers\CurrentApp::ConfStr('modulos.ventas.submodulos.sell_fast')) {
            $activePaymentMethods[] = 'fastSells';
        }
        
        return $activePaymentMethods;
    }
}
