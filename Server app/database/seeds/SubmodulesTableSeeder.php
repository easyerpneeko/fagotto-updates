<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmodulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*DB::table("submodules")->insert([
            "name" => "Inventario",
            "keyname" => "inventario",
            'module_id' => 1,
            "migrations" => "[]",
            "dependencies" => "[]",
            'version' => '0.1',
            'description' => 'Permite poder gestionar un inventario y llevar stock de los productos.',
            'env_vars' => null
        ]);*/
        DB::table("submodules")->insert([
            "name" => "SII",
            "keyname" => "sii",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode(['sii_module/2020_11_04_174409_xml_cargados_table.php','sii_module/2020_11_05_190328_folios_table.php','sii_module/2020_11_09_151224_history_sii_table.php']),
            "dependencies" => "['clientes']",
            'version' => '0.1',
            'description' => 'Permite gestionar el SII.',
            "permissions" => json_encode([]),
            'env_vars' => json_encode(
              [
                'sii_rut'             => ['label' => 'Rut SII',                  'type' => 'string', 'max' => 12],
                'sii_password'        => ['label' => 'Password SII',             'type' => 'string', 'max' => 12],
                'sii_emisor_rut'      => ['label' => 'Rut Emisor SII',           'type' => 'string', 'max' => 12],
                'sii_rnz_soc'         => ['label' => 'Razon Social SII',         'type' => 'string', 'max' => 100],
                'sii_giro_emisor'     => ['label' => 'Giro Emisor SII',          'type' => 'string', 'max' => 80],
                'sii_arteco'          => ['label' => 'Arteco SII',               'type' => 'string', 'max' => 6],
                'sii_dirorigen'       => ['label' => 'Direccion de Origen SII',  'type' => 'string', 'max' => 60],
                'sii_comuna_origen'   => ['label' => 'Comuna de Origen SII',     'type' => 'string', 'max' => 20],
                'sii_ciudad_origen'   => ['label' => 'Ciudad de Origen SII',     'type' => 'string', 'max' => 20],
                'sii_iva_amount'      => ['label' => 'Cantidad de IVA SII',      'type' => 'string', 'max' => 2],
                'sii_ambiente'        => ['label' => 'SII modo produccion',      'type' => 'check', 'default' => false],
                'sii_no_enviar_datos' => ['label' => 'No enviar datos a moises', 'type' => 'check', 'default' => false],
                'sii_logo'            => ['label' => 'Logo del negocio',         'type' => 'image',  'default' => null],
              ]
            )
        ]);
        DB::table("submodules")->insert([
            "name" => "Categorias",
            "keyname" => "categorias",
            'module_id' => DB::table("modules")->where('keyname','productos')->first()->id,
            "migrations" => json_encode(['products_module/categories_submodule/2020_02_03_222857_categories.php']),
            "dependencies" => "[]",
            "permissions" => json_encode([
              'productos_categorias_gestion' => 'Gestionar categorias de productos',
              'productos_categorias_obtener' => 'Obtener categorias de productos'
            ]),
            'version' => '0.1',
            'description' => 'Permite poder gestionar las categorias de los productos.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
          "name" => "Historial de productos",
          "keyname" => "history_products",
          'module_id' => DB::table("modules")->where('keyname','productos')->first()->id,
          "migrations" => json_encode(['products_module/2023_09_05_220701_history_products.php']),
          "dependencies" => "[]",
          "permissions" => "[]",
          'version' => '0.1',
          'description' => 'Permite gestionar el historial de cambios de productos',
          'env_vars' => null
      ]);
        DB::table("submodules")->insert([
            "name" => "Clientes",
            "keyname" => "clientes",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode(['sells_module/clients_submodule/2020_02_03_222655_clients.php']),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Permite poder gestionar el uso de clientes en una venta.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
            "name" => "Reportes",
            "keyname" => "reporte",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode([]),
            "dependencies" => "[]",
            "permissions" => json_encode([
              'monto_inicial' => 'Gestionar precio inicial',
            ]),
            'version' => '0.1',
            'description' => 'Reportes de ventas y estadisticas.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
            "name" => "Venta rapida",
            "keyname" => "sell_fast",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode([]),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Modulo de ventas rapidas.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
            "name" => "Ticket",
            "keyname" => "ticket",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode(['sells_module/ticket_submodule/2020_11_25_113700_orders.php']),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Submodulo de tickets.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
            "name" => "Gastos del día",
            "keyname" => "expenses_day",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode(['sells_module/expenses_day_submodule/2021_02_17_154913_expenses_day_table.php']),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Submodulo de gastos del día.',
            'env_vars' => null
        ]);
        DB::table("submodules")->insert([
            "name" => "Adiciones de meseras",
            "keyname" => "additions_waiter",
            'module_id' => DB::table("modules")->where('keyname','cafeteria')->first()->id,
            "migrations" => json_encode(['cafeteria_module/waiters/2021_02_21_150311_additions_waiters_table.php']),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Submodulo de trabajos adicionales de las meseras.',
            'env_vars' => json_encode(
              [
                'default_value_addtion' => ['label' => 'Precio por adicion','type' => 'number', 'default' => '1500', 'value' => '1500'],
              ]
            ),
        ]);
        // Vuelto apra nueva venta
        DB::table("submodules")->insert([
            "name" => "Vuelto en ventas",
            "keyname" => "turned_ventas",
            'module_id' => DB::table("modules")->where('keyname','ventas')->first()->id,
            "migrations" => json_encode([]),
            "dependencies" => "[]",
            "permissions" => json_encode([]),
            'version' => '0.1',
            'description' => 'Submodulo de vuelto para nueva venta.',
            'env_vars' => json_encode([]),
        ]);
        // Vuelto para cafeteria
        DB::table("submodules")->insert([
            "name"          => "Vuelto en cafeteria",
            "keyname"       => "turned_cafeteria",
            'module_id'     => DB::table("modules")->where('keyname','cafeteria')->first()->id,
            "migrations"    => json_encode([]),
            "dependencies"  => "[]",
            "permissions"   => json_encode([]),
            'version'       => '0.1',
            'description'   => 'Submodulo de vuelto para cafeteria.',
            'env_vars'      => json_encode([]),
        ]);
        // Submodulo de modo garzon
        DB::table("submodules")->insert([
            "name"            => "Modo garzon para cafeteria",
            "keyname"         => "garzon_mode",
            'module_id'       => DB::table("modules")->where('keyname','cafeteria')->first()->id,
            "migrations"      => json_encode([]),
            "dependencies"    => "[]",
            "permissions"     => json_encode([]),
            'version'         => '0.1',
            'description'     => 'Submodulo de Cafeteria que activa la modalidad Garzon.',
            'env_vars'        => json_encode([]),
        ]);
        // Submodulo de modo client_orders_mobile_devices
        DB::table("submodules")->insert([
            "name"            => "Ordenes de dispositivos moviles",
            "keyname"         => "co_mobile_device",
            'module_id'       => DB::table("modules")->where('keyname','client_orders')->first()->id,
            "migrations"      => json_encode(['client_orders_module/2021_09_22_001328_create_phone_repair_orders_table.php']),
            "dependencies"    => "[]",
            "permissions"     => json_encode([]),
            'version'         => '0.1',
            'description'     => 'Submodulo de dispositivos moviles para el modulo de ordenes de clientes.',
            'env_vars'        => json_encode([
              'client_orders_device_models' => [
                'label' => 'Modelos de dispositivos',
                'type' => 'string',
                'default' => json_encode([
                  'samsung' => 'Samsung',
                ])
              ],
              'client_orders_device_conditions' => [
                'label' => 'Condiciones del equipo',
                'type' => 'string',
                'default' => json_encode([
                  'camara_trasera' => 'Camara trasera',
                ])
              ],
            ]),
        ]);
        // Submodulo de modo garzon 12/04/22
        DB::table("submodules")->insert([
            "name"            => "SubModulo de cocina para cafeteria",
            "keyname"         => "modo_cocina",
            'module_id'       => DB::table("modules")->where('keyname','cafeteria')->first()->id,
            "migrations"      => json_encode([
              'cafeteria_module/2022_04_12_213832_sub_module_cocina_migration.php',
              'cafeteria_module/2022_04_12_214859_create_order_kitchens_table.php'
            ]),
            "dependencies"    => "[]",
            "permissions"     => json_encode([]),
            'version'         => '0.1',
            'description'     => 'Submodulo de Cafeteria que activa la pagina de cocina.',
            'env_vars'        => json_encode([
              'size_letra_cocina_kanban' => [
                'label' => 'Tamaño de letra en cocina',
                'type' => 'number',
                'default' => 0
              ],
            ]),/*
              {"size_letra_cocina_kanban":{"label":"Tamaño de letra en cocina","type":"number","default":0}}
            */
        ]);
        /*
          INSERT INTO `submodules` 
          (`id`, `name`, `keyname`, `description`, `module_id`, `migrations`, `dependencies`, `permissions`, `env_vars`, `version`, `created_at`, `updated_at`)
          VALUES (
            NULL,
            'SubModulo de cocina para cafeteria',
            'cafeteria',
            'Submodulo de Cafeteria que activa la pagina de cocina',
            '3' //<---- caffeteria module,
            '[\"cafeteria_module/2022_04_12_213832_sub_module_cocina_migration.php\",\"cafeteria_module/2022_04_12_214859_create_order_kitchens_table.php\"]',
            '[]',
            '[]',
            '{}',
            '0.1',
            NULL,
            NULL
          )
        */
        // Submodulo de cantidades con decimales
        DB::table("submodules")->insert([
          "name"            => "Cantidad con decimales",
          "keyname"         => "cantidades_float",
          'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
          "migrations"      => json_encode(['sells_module/2022_05_08_163734_added_decimales_module.php']),
          "dependencies"    => "[]",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Submodulo de de cantidad con decimales para ventas.',
          'env_vars'        => json_encode([]),
        ]);
        // DB::table("submodules")->insert([
        //   "name"            => "Usuario que elimina ventas",
        //   "keyname"         => "user_trash",
        //   'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
        //   "migrations"      => json_encode(['sells_module/2023_07_19_140111_add_user_delete_to_sells_table.php']),
        //   "dependencies"    => "[]",
        //   "permissions"     => json_encode([]),
        //   'version'         => '0.1',
        //   'description'     => 'Añade para usuario eliminar ventas',
        //   'env_vars'        => json_encode([]),
        // ]);
        DB::table("submodules")->insert([
          "name"            => "Ordenes con tickets de cliente",
          "keyname"         => "client_ticket",
          'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
          "migrations"      => json_encode(['sells_module/2023_08_06_140111_add_client_ticket_to_orders_table.php']),
          "dependencies"    => "[]",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Ordenes con tickets de cliente',
          'env_vars'        => json_encode([]),
        ]);
        DB::table("submodules")->insert([
          "name"            => "Turnos de trabajo",
          "keyname"         => "workshift",
          'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
          "migrations"      => json_encode(['sells_module/2023_12_08_152903_create_workshift_table.php']),
          "dependencies"    => "[]",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Turnos de trabajo para las cajas',
          'env_vars'        => json_encode([]),
        ]);
        DB::table("submodules")->insert([
          "name"            => "Devoluciones",
          "keyname"         => "devolutions",
          'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
          "migrations"      => json_encode(['sells_module\/2024_31_05_142903_create_devolutions_table.php']),
          "dependencies"    => "['sells']",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Permite hacer devoluciones en ventas',
          'env_vars'        => json_encode([]),
        ]);

        DB::table("submodules")->insert([
          "name"            => "Precios Promo",
          "keyname"         => "precio_promo",
          'module_id'       => DB::table("modules")->where('keyname','ventas')->first()->id,
          "migrations"      => json_encode(['products_module\/promos_submodule\/2024_07_22_174920_added_promos_submodule.php']),
          "dependencies"    => "['products']",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Agregar precio promocial a un producto',
          'env_vars'        => json_encode([]),
        ]);

        /*
          INSERT INTO `submodules` (`id`, `name`, `keyname`, `description`, `module_id`, `migrations`, `dependencies`, `permissions`, `env_vars`, `version`, `created_at`, `updated_at`) VALUES (NULL, 'Cantidad con decimales', 'cantidades_float', 'Submodulo de de cantidad con decimales para ventas', '2', '[\"sells_module/2022_05_08_163734_added_decimales_module.php\"]', '[]', '[]', '{}', '0.1', NULL, NULL)
        */

        /*DB::table("submodules")->insert([
          "name"            => "Costos productos",
          "keyname"         => "costos_productos",
          'module_id'       => DB::table("modules")->where('keyname','productos')->first()->id,
          "migrations"      => json_encode(['sells_module/2022_05_08_163734_added_decimales_module.php']),
          "dependencies"    => "[]",
          "permissions"     => json_encode([]),
          'version'         => '0.1',
          'description'     => 'Submodulo de de cantidad con decimales para ventas.',
          'env_vars'        => json_encode([]),
        ]);*/
        /*

        */
        // PUSHER EN CAFETERIA
        DB::table("settings_modules")->insert([
          "name"=> "Pusher en dashboard",
          "keyname"=> "pusher_caffeteria",
          "module_id" => DB::table("modules")->where('keyname','cafeteria')->first()->id
        ]);
    }
}
