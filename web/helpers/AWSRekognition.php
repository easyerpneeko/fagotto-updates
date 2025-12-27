<?php
/**
 * Helper para AWS Rekognition
 * Gestiona reconocimiento facial usando AWS SDK
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Aws\Rekognition\RekognitionClient;
use Aws\Exception\AwsException;

class AWSRekognition {
    
    private $client;
    private $collectionId = 'fagotto-employees';
    
    public function __construct() {
        $this->client = new RekognitionClient([
            'version' => 'latest',
            'region' => AWS_REGION,
            'credentials' => [
                'key' => AWS_ACCESS_KEY,
                'secret' => AWS_SECRET_KEY
            ]
        ]);
        
        // Crear collection si no existe
        $this->ensureCollectionExists();
    }
    
    /**
     * Crear collection si no existe
     */
    private function ensureCollectionExists() {
        try {
            $this->client->describeCollection([
                'CollectionId' => $this->collectionId
            ]);
        } catch (AwsException $e) {
            // Collection no existe, crearla
            if ($e->getAwsErrorCode() === 'ResourceNotFoundException') {
                $this->client->createCollection([
                    'CollectionId' => $this->collectionId
                ]);
            }
        }
    }
    
    /**
     * Indexar rostro de empleado en la collection
     * 
     * @param string $employeeId - ID del empleado
     * @param string $fotoBase64 - Foto en base64
     * @return array - Resultado de la indexación
     */
    public function indexarRostro($employeeId, $fotoBase64) {
        try {
            // Convertir base64 a bytes
            $imageBytes = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
            
            // Indexar rostro
            $result = $this->client->indexFaces([
                'CollectionId' => $this->collectionId,
                'Image' => [
                    'Bytes' => $imageBytes
                ],
                'ExternalImageId' => $employeeId,
                'DetectionAttributes' => ['ALL'],
                'MaxFaces' => 1,
                'QualityFilter' => 'AUTO'
            ]);
            
            if (count($result['FaceRecords']) === 0) {
                return [
                    'success' => false,
                    'message' => 'No se detectó ningún rostro en la imagen'
                ];
            }
            
            $faceRecord = $result['FaceRecords'][0];
            
            return [
                'success' => true,
                'faceId' => $faceRecord['Face']['FaceId'],
                'confidence' => $faceRecord['Face']['Confidence'],
                'boundingBox' => $faceRecord['Face']['BoundingBox'],
                'imageId' => $faceRecord['Face']['ImageId']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => 'Error AWS: ' . $e->getAwsErrorMessage(),
                'code' => $e->getAwsErrorCode()
            ];
        }
    }
    
    /**
     * Buscar rostro en la collection
     * 
     * @param string $fotoBase64 - Foto a comparar
     * @param float $similarityThreshold - Umbral de similitud (0-100)
     * @return array - Empleados que coinciden
     */
    public function buscarRostro($fotoBase64, $similarityThreshold = 80) {
        try {
            $imageBytes = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
            
            $result = $this->client->searchFacesByImage([
                'CollectionId' => $this->collectionId,
                'Image' => [
                    'Bytes' => $imageBytes
                ],
                'FaceMatchThreshold' => $similarityThreshold,
                'MaxFaces' => 5
            ]);
            
            if (count($result['FaceMatches']) === 0) {
                return [
                    'success' => false,
                    'message' => 'No se encontró coincidencia. Intenta con mejor iluminación.'
                ];
            }
            
            // Obtener mejor coincidencia
            $bestMatch = $result['FaceMatches'][0];
            
            return [
                'success' => true,
                'employeeId' => $bestMatch['Face']['ExternalImageId'],
                'similarity' => $bestMatch['Similarity'],
                'confidence' => $bestMatch['Face']['Confidence'],
                'faceId' => $bestMatch['Face']['FaceId']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => 'Error AWS: ' . $e->getAwsErrorMessage()
            ];
        }
    }
    
    /**
     * Comparar dos rostros directamente
     * 
     * @param string $foto1Base64 - Primera foto
     * @param string $foto2Base64 - Segunda foto
     * @return array - Resultado de la comparación
     */
    public function compararRostros($foto1Base64, $foto2Base64) {
        try {
            $image1Bytes = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto1Base64));
            $image2Bytes = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto2Base64));
            
            $result = $this->client->compareFaces([
                'SourceImage' => ['Bytes' => $image1Bytes],
                'TargetImage' => ['Bytes' => $image2Bytes],
                'SimilarityThreshold' => 70
            ]);
            
            if (count($result['FaceMatches']) === 0) {
                return [
                    'success' => false,
                    'message' => 'Los rostros no coinciden'
                ];
            }
            
            $match = $result['FaceMatches'][0];
            
            return [
                'success' => true,
                'similarity' => $match['Similarity'],
                'confidence' => $match['Face']['Confidence']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => 'Error AWS: ' . $e->getAwsErrorMessage()
            ];
        }
    }
    
    /**
     * Eliminar rostro de la collection
     * 
     * @param string $faceId - ID del rostro
     * @return array - Resultado
     */
    public function eliminarRostro($faceId) {
        try {
            $this->client->deleteFaces([
                'CollectionId' => $this->collectionId,
                'FaceIds' => [$faceId]
            ]);
            
            return ['success' => true];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => 'Error AWS: ' . $e->getAwsErrorMessage()
            ];
        }
    }
    
    /**
     * Detectar rostro y calidad
     * 
     * @param string $fotoBase64
     * @return array - Información del rostro
     */
    public function detectarRostro($fotoBase64) {
        try {
            $imageBytes = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
            
            $result = $this->client->detectFaces([
                'Image' => ['Bytes' => $imageBytes],
                'Attributes' => ['ALL']
            ]);
            
            if (count($result['FaceDetails']) === 0) {
                return [
                    'success' => false,
                    'message' => 'No se detectó rostro'
                ];
            }
            
            $face = $result['FaceDetails'][0];
            
            return [
                'success' => true,
                'confidence' => $face['Confidence'],
                'quality' => [
                    'brightness' => $face['Quality']['Brightness'],
                    'sharpness' => $face['Quality']['Sharpness']
                ],
                'emotions' => $face['Emotions'],
                'ageRange' => $face['AgeRange'],
                'gender' => $face['Gender']
            ];
            
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => 'Error AWS: ' . $e->getAwsErrorMessage()
            ];
        }
    }
}
