<?php

namespace App\Helpers;

class PaymentMethodHelper
{
    const PAYMENT_METHODS = [
        'contado' => 'Contado',
        'credito' => 'Crédito',
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
        return [];
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
                return 1; // Contado
            case 'credito':
                return 2; // Crédito
            default:
                return 1; // Por defecto contado
        }
    }
}
