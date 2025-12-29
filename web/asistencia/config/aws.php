<?php
/**
 * Configuración AWS Rekognition
 * 
 * INSTRUCCIONES:
 * 1. Ve a https://console.aws.amazon.com/iam/
 * 2. Crea un nuevo usuario: fagotto-rekognition
 * 3. Adjunta política: AmazonRekognitionFullAccess
 * 4. Genera Access Key → Security credentials → Create access key
 * 5. Copia el Access Key ID y Secret Access Key aquí
 */

// ✅ CREDENCIALES AWS CONFIGURADAS
define('AWS_ACCESS_KEY_ID', 'AKIATG6MGTGRHLKBQSZB');
define('AWS_SECRET_ACCESS_KEY', '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');
define('AWS_REGION', 'us-east-1'); // Región de Virginia (USA)

// Nombre de la colección de rostros en AWS Rekognition
define('AWS_REKOGNITION_COLLECTION', 'fagotto-employees');

// Threshold de similitud (75-95 recomendado)
define('AWS_REKOGNITION_THRESHOLD', 75);

/**
 * REGIONES AWS DISPONIBLES:
 * 
 * us-east-1      → Virginia (USA) - Recomendado
 * us-west-2      → Oregón (USA)
 * eu-west-1      → Irlanda (Europa)
 * ap-southeast-2 → Sydney (Australia)
 * sa-east-1      → São Paulo (Brasil) - Más cercano a Chile
 * 
 * NOTA: Rekognition NO está disponible en todas las regiones
 * Verifica: https://aws.amazon.com/about-aws/global-infrastructure/regional-product-services/
 */

/**
 * EJEMPLO DE CREDENCIALES VÁLIDAS:
 * 
 * define('AWS_ACCESS_KEY_ID', 'AKIAIOSFODNN7EXAMPLE');
 * define('AWS_SECRET_ACCESS_KEY', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
 * define('AWS_REGION', 'us-east-1');
 */

// Verificar que las credenciales están configuradas
if (AWS_ACCESS_KEY_ID === 'TU_ACCESS_KEY_AQUI') {
    die('❌ ERROR: Configura tus credenciales AWS en config/aws.php');
}
