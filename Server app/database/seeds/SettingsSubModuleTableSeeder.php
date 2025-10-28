<?php

use Illuminate\Database\Seeder;

class SettingsSubModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /*//Setting "Permitir factura electronica" en el submodulo "Factura" del modulo "Ventas"
      DB::table("settings_submodules")->insert([
        "name"=> "Permitir factura electronica",
        "keyname"=> "permitir_factura_electronica",
        "submodule_id" => 2
      ]);*/
      DB::table("settings_submodules")->insert([
        "name"=> "Cliente obligatorio",
        "keyname"=> "cliente_obligatorio",
        "submodule_id" => DB::table("submodules")->where('keyname','clientes')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Campo telefonico",
        "keyname"=> "cliente_telefono",
        "submodule_id" => DB::table("submodules")->where('keyname','clientes')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Multi Caja",
        "keyname"=> "multicaja",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Sodexo",
        "keyname"=> "sodexo",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Credito",
        "keyname"=> "credito",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Amipass",
        "keyname"=> "amipass",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Rappi",
        "keyname"=> "rappi",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Junaeb",
        "keyname"=> "junaeb",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Edenred",
        "keyname"=> "edenred",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Uber",
        "keyname"=> "uber",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Efectivo",
        "keyname"=> "efectivo",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Convenio Empresa",
        "keyname"=> "convenio_empresa",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Facturas",
        "keyname"=> "factura",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Boletas",
        "keyname"=> "boleta",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Nota de credito",
        "keyname"=> "nota_de_credito",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Boleta local",
        "keyname"=> "boleta_local",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      // NEW TYPES SELLS
      DB::table("settings_submodules")->insert([
        "name"=> "Debito",
        "keyname"=> "debito",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Transferencia",
        "keyname"=> "transferencia",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Cheque restaurante",
        "keyname"=> "cheque",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Banco Estado",
        "keyname"=> "banco",
        "submodule_id" => DB::table("submodules")->where('keyname','sii')->first()->id
      ]);
      // NEW TYPES SELLS
      DB::table("settings_submodules")->insert([
        "name"=> "Mostrar datos opcionales",
        "keyname"=> "datos_opcionales",
        "submodule_id" => DB::table("submodules")->where('keyname','reporte')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Previsualización de la orden",
        "keyname"=> "ver_ticket",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);

      DB::table("settings_submodules")->insert([
        "name"=> "Ocultar codigo de barra",
        "keyname"=> "no_code_bar",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Ocultar total en tickect",
        "keyname"=> "view_total",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Ocultar subtotal en tickect",
        "keyname"=> "view_subtotal",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Letra pequeña en tickect",
        "keyname"=> "lower_case",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Desactivar mesas de la cafeteria",
        "keyname"=> "desactive_boards",
        "submodule_id" => DB::table("submodules")->where('keyname','garzon_mode')->first()->id
      ]);
      // UPDATE 3/4/22
      DB::table("settings_submodules")->insert([
        "name"=> "Ver description en ordenes",
        "keyname"=> "ticket_description",
        "submodule_id" => DB::table("submodules")->where('keyname','ticket')->first()->id
      ]);
      // UPDATE 1/5/22
      DB::table("settings_submodules")->insert([
        "name"=> "Ordenamiento LIFO",
        "keyname"=> "lifo_ordenament",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Antiguedad de tarjetas",
        "keyname"=> "antiguedad_tarjetas",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Cocina sin Kanban",
        "keyname"=> "no_kanban_cocina",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Cocina en blanco y negro",
        "keyname"=> "blanco_y_negro",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Mostrar nombre garzon",
        "keyname"=> "coc_nombre_garzon",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Mostrar nombre mesa",
        "keyname"=> "coc_nombre_mesa",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Mostrar orden descripcion",
        "keyname"=> "kit_o_descripcion",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "TODO cocina blanco y negro",
        "keyname"=> "all_blanco_y_negro",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      DB::table("settings_submodules")->insert([
        "name"=> "Cocina Cantidad",
        "keyname"=> "x_cocina_cantidad",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
      // UPDATE 14/5/22
      // PUSHER EN COCINA
      DB::table("settings_submodules")->insert([
        "name"=> "Pusher en tablero",
        "keyname"=> "pusher_cocina",
        "submodule_id" => DB::table("submodules")->where('keyname','modo_cocina')->first()->id
      ]);
    }
}
