<?php

use Illuminate\Database\Seeder;

define('enverioment_default',[
  'enverioments_version' => [
     'value' => '0.1',
     'label' => 'Version de las variables de ambito',
     'disabled' => true,
     'hidden' => true,
     'type' => 'version',
  ],
  'xml_code_of_folio' => [
     'value' => null,
     'label' => 'Texto XML de Folio',
     'disabled' => true,
     'hidden' => true,
     'type' => 'xml',
  ],
  'xml_code_of_folio_boleta' => [
     'value' => null,
     'label' => 'Texto XML de boleta',
     'disabled' => true,
     'hidden' => true,
     'type' => 'xml',
  ],
  'xml_code_of_folio_factura' => [
     'value' => null,
     'label' => 'Texto XML de factura',
     'disabled' => true,
     'hidden' => true,
     'type' => 'xml',
  ]
]);

class AplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /*DB::table("aplications")->insert(
      [
        "id" => "1",
        "name" => "Aplicacion de prueba",
        "serial" => "AFW4-Q4FW-12RF-GRT3-QWET",
        "database_app" => 1,
        "client" => 1,
        "expiration" => Date('Y-m-d',strtotime('+30 days',strtotime(Date('Y-m-d')))),
        'environment_vars' => json_encode(enverioment_default),
      ]);
      DB::table("aplications")->insert(
      [
        "id" => "2",
        "name" => "Aplicacion de Hotel",
        "serial" => "KF3I-OAK0-6D68-15DE-3CSA",
        "database_app" => 2,
        "client" => 1,
        'environment_vars' => json_encode(enverioment_default),
      ]);
      DB::table("aplications")->insert(
      [
        "id" => "3",
        "name" => "Aplicacion de Restaurante",
        "serial" => "AD3L-GOO4-2AD6-D8E2-F2T5",
        "database_app" => 3,
        "client" => 2,
        'environment_vars' => json_encode(enverioment_default),
      ]);
      DB::table("aplications")->insert(
      [
        "id" => "4",
        "name" => "Aplicacion de Bodega",
        "serial" => "4R8F-RSD3-629R-FF82-E1D8",
        "database_app" => 4,
        "client" => 2,
        "expiration" => Date('Y-m-d',strtotime('+30 days',strtotime(Date('Y-m-d')))),
        'environment_vars' => json_encode(enverioment_default),
      ]);
      DB::table("aplications")->insert(
      [
        "id" => "5",
        "name" => "Aplicacion de Tecnologia",
        "serial" => "S1E5-D3E8-DV8T-FRE3-T374",
        "database_app" => 5,
        "client" => 3,
        'environment_vars' => json_encode(enverioment_default),
      ]);*/
    }
}
