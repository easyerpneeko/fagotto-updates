<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Setting "Permitir descargar reporte de Productos"
        DB::table("settings_modules")->insert([
          "name"=> "Descargar excel de inventario",
          "keyname"=> "donwload_inventory",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        //Setting "Permitir codigo de barras" en el modulo "Productos"
        DB::table("settings_modules")->insert([
          "name"=> "Permitir codigo de barras",
          "keyname"=> "permitir_barcode",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        //Setting "Permitir Stock" en el modulo "Productos"
        DB::table("settings_modules")->insert([
          "name"=> "Permitir stock",
          "keyname"=> "permitir_stock",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir cecina",
          "keyname"=> "permitir_cecina",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Productos tablas",
          "keyname"=> "productos_tabla",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir precio variante",
          "keyname"=> "permitir_precio_variante",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir cantidad minima",
          "keyname"=> "permitir_cantidad_minima",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Mostrar usuario en ventas",
          "keyname"=> "mostrar_usuario_venta",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir ganancia",
          "keyname"=> "permitir_ganancia",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir precio unitario",
          "keyname"=> "permitir_precio_unitario",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir imprimir venta",
          "keyname"=> "permitir_imprimir_venta",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Permitir crear producto en venta",
          "keyname"=> "permitir_crear_producto_en_venta",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);

        DB::table("settings_modules")->insert([
          "name"=> "No mostrar nueva venta en menu",
          "keyname"=> "no_new_sell",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Mesas activas solo en cafetería",
          "keyname"=> "layout_only_cafeteria",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "No mostrar mesas activas",
          "keyname"=> "not_cafeteria_layout",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Procesar venta con ticket",
          "keyname"=> "ticket_sell",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        //
        DB::table("settings_modules")->insert([
          "name"=> "Reporte perforance/mesero/mesa",
          "keyname"=> "perforance_mesero_mesa_report",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        //
        DB::table("settings_modules")->insert([
          "name"=> "Ocultar lista de ventas",
          "keyname"=> "hide_sells_menu",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        DB::table("settings_modules")->insert([
          "name"=> "Ocultar lista de productos",
          "keyname"=> "hide_products_menu",
          "module_id" => DB::table("modules")->where('keyname','productos')->first()->id
        ]);
        // UPDATE 3/4/22
        DB::table("settings_modules")->insert([
          "name"=> "Cambiar color de mesas",
          "keyname"=> "change_board_color",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        // UPDATE 14/5/22
        // PUSHER EN CAFETERIA
        DB::table("settings_modules")->insert([
          "name"=> "Pusher en dashboard",
          "keyname"=> "pusher_caffeteria",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);

        // Procesar y cerrar venta TK
        DB::table("settings_modules")->insert([
          "name"=> "Procesar y cerrar venta TK",
          "keyname"=> "ticket_sell_proccess_close",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        // Procesar ticket con cliente
        DB::table("settings_modules")->insert([
          "name"=> "Venta",
          "keyname"=> "ticket_sell_client",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
        // Procesar venta con modal
        DB::table("settings_modules")->insert([
          "name"=> "Venta",
          "keyname"=> "Permitir producto con modal",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        // Procesar venta al mayor
        DB::table("settings_modules")->insert([
          "name"=> "Permitir venta al mayor",
          "keyname"=> "permitir_ventas_mayor",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        // Procesar venta al mayor
        DB::table("settings_modules")->insert([
          "name"=> "Permitir precio de compra",
          "keyname"=> "permitir_compra",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        // Permitir vender sin stock
        DB::table("settings_modules")->insert([
          "name"=> "Permitir vender sin stock",
          "keyname"=> "permitir_venta_sin_stock",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        // Permitir descuentos
        DB::table("settings_modules")->insert([
          "name"=> "Permitir descuentos",
          "keyname"=> "permitir_descuento",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        // Despacho gratis en pedidos
        DB::table("settings_modules")->insert([
          "name"=> "Despacho Gratis",
          "keyname"=> "despacho_gratis",
          "module_id" => DB::table("modules")->where('keyname','pedidos')->first()->id
        ]);
        // Presiona enter para buscar en ventas
        DB::table("settings_modules")->insert([
          "name"=> "Presiona enter para buscar",
          "keyname"=> "enter_para_buscar",
          "module_id" => DB::table("modules")->where('keyname','ventas')->first()->id
        ]);
        /*
          INSERT INTO `settings_modules`
          (`id`, `name`, `keyname`, `module_id`, `env_vars`, `created_at`, `updated_at`)
          VALUES (NULL, 'Pusher en dashboard', 'pusher_caffeteria', '3', '', NULL, NULL)
        */
    }
}
