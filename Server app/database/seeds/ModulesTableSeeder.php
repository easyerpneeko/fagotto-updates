boards/<?php

use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      DB::table("modules")->insert([
        "name"=> "Producto",
        "icon"=> "fa-boxes",
        "keyname" => "productos",
        "migrations" => json_encode(['products_module/2020_02_03_223938_products.php']),
        "dependencies" => "[]",
        "version" => "0.1",
        'description' => 'Modulo de gestion de productos para poder gestionar todo el catalogo de productos de un negocio.',
        'env_vars' => '[]',
        "permissions" => json_encode([
          'productos_gestion' => 'Gestionar productos',
          'productos_obtener' => 'Obtener productos',
          'productos_modificar_stock' => 'Modificar el stock de productos',
          'crear_productos_nueva_venta' => 'Crear productos desde nueva venta'
          /*
            Muchos administradores querran que ninguno de sus empleados
            pueda modificar el stock por que se robaran los productos
          */
        ]),
        'typeUsers' => json_encode([
          ["name" => "Gestor de Inventario","keyname" => "gestor_de_inventario","permisos" => ["productos_gestion","productos_obtener"]],
        ]),
      ]);
      /**/
      DB::table("modules")->insert([
        "name"=> "Ventas",
        "icon"=> "fa-shopping-cart",
        "keyname" => "ventas",
        "migrations" => json_encode([
          'sells_module/2020_02_03_222657_sells.php',
          'sells_module/2020_02_03_224657_products_sells.php',
        ]),
        "dependencies" => "['productos']",
        "version" => "0.1",
        'description' => 'Modulo de gestion de ventas que permitira crear ventas y enlistar las ventas ya creadas en base a los productos.',
        'env_vars' => json_encode(
          [
            'stgg_header' => ['label' => 'Cabecera de recibo','type' => 'string', 'default' => 'COMERCIALIZADORA ARRIAGADA&ARRIAGADA SPA'],
            'stgg_casa_matriz' => ['label' => 'Casa Matriz','type' => 'string'],
            'stgg_contacto' => ['label' => 'Contacto','type' => 'string'],
            'stgg_horario' => ['label' => 'Horario','type' => 'string'],
            'stgg_url' => ['label' => 'URL de web','type' => 'string'],
            'stgg_mensaje_gracias' => ['label' => 'Mensaje de agradecimiento en recibo','type' => 'string', 'default' => 'GRACIAS POR SU PREFERENCIA * TE ESPERAMOS NUEVAMENTE *'],
            'settings_printer' => ['label' => 'Configuracion de la impresora', 'type' => 'string',
            'default' => json_encode([
                'win32' => ['-print-settings noscale']
              ])
            ],
            'settings_printer_method' => ['label' => 'Metodo de impresion', 'type' => 'string','default' => 'default']
          ]
        ),
        "permissions" => json_encode([
          'crear_venta' => 'Crear ventas',
          'gestionar_ventas' => 'Gestionar ventas',
          'gestionar_reportes' => 'Gestionar reportes',
          'obtener_venta' => 'Obtener ultima venta',
          'eliminar_venta' => 'Eliminar una venta',
          'getionar_tickets' => 'Gestionar tickets',
          'precio_unitario' => 'Modificar precio unitario',
          'gestionar_gastos' => 'Crear y eliminar gastos',
        ]),
        'typeUsers' => json_encode([
          ["name" => "Cajero", "keyname" => "cajero","permisos" => ["obtener_mesas","obtener_meseros","crear_venta","getionar_tickets","gestionar_ventas","productos_obtener"]],
          ["name" => "Analista de Ventas", "keyname" => "analista_de_ventas","permisos" => ["gestionar_ventas", "gestionar_reportes"]],
          ["name" => "Tickero", "keyname" => "tickero","permisos" => ["getionar_tickets","productos_obtener"]],
        ]),
      ]);//Ventas depende del modulo "1" (Productos)


      DB::table("modules")->insert([
        "name"=> "Cafetería",
        "icon"=> "fa-store-alt",
        "keyname" => "cafeteria",
        "migrations" => json_encode([
          'cafeteria_module/waiters/2021_01_07_113754_waiters_table.php',
          'cafeteria_module/boards/2021_01_07_113907_boards_table.php',
        ]),
        "dependencies" => "['productos', 'ventas']",
        "version" => "0.1",
        'description' => 'Modulo de cafeteria, contiene un control de mesas y meseros pudiendo de esta formar emular un restaurante virtual.',
        'env_vars' => json_encode(
          [
            'stgg_password_remove_product_ticket' => ['label' => 'Contraseña para eliminar producto de un ticket', 'type' => 'string', 'default' => 'admin123456'],
            'settings_printer' => ['label' => 'Configuracion de la impresora', 'type' => 'string',
            'default' => json_encode([
                'win32' => ['-print-settings noscale']
              ])
            ],
            'settings_printer_method' => ['label' => 'Metodo de impresion', 'type' => 'string','default' => 'default'],
            'name_panel_tickets' => ['label' => 'Nombre del panel de cafeteria', 'type' => 'string','default' => 'Cafeteria']
          ]
        ),
        "permissions" => json_encode([
          'gestionar_meseros' => 'Gestion de meseros',
          'gestionar_mesas' => 'Gestion de mesas',
          'obtener_meseros' => 'Obtener meseros',
          'obtener_mesas' => 'Obtener mesas',
        ]),
        'typeUsers' => json_encode([
          // ["name" => "Cajero", "keyname" => "cajero", "permisos" => ["obtener_mesas","obtener_meseros","crear_venta","getionar_tickets","gestionar_ventas","productos_obtener"]],
          // ["name" => "Mesero", "keyname" => "mesero","permisos" => ["obtener_mesas","obtener_meseros","getionar_tickets","productos_obtener"],
        ]),
      ]);//Cafeteria depende del modulo "1 y 2" (Productos y ventas)

      /*DB::table("modules")->insert([//Solo para fines de ejemplo, este sera un submodulo de ventas llamado "sacar la x"
        "name"=> "Reportes",
        "icon"=> "fa-file-alt",
        "keyname" => "reportes",
        "migrations" => "[]",
        "dependencies" => "['ventas']",
        "version" => "0.1",
        'description' => 'Modulo de reportes, importante si se quiere tener reportes detallados acerca de las ventas. este modulo solo existe para fines de ejemplo, sera un submodulo del modulo ventas llamado "sacar la x"',
        'env_vars' => null
      ]);//Reportes depende del modulo "2" (Ventas)*/

      DB::table("modules")->insert([
        "name"=> "Ordenes de clientes",
        "icon"=> "fa-store-alt",
        "keyname" => "client_orders",
        "migrations" => json_encode([
          'client_orders_module/2021_09_22_000156_create_client_orders_table.php',
        ]),
        "dependencies" => "['productos', 'ventas']",
        "version" => "0.1",
        'description' => 'Modulo de ordenes de clientes, (requiere tambien el modulo de clientes!).',
        'env_vars' => json_encode([
          'name_shop_public_orders' => ['label' => 'Nombre publico de app en orden', 'type' => 'string','default' => 'Nombre de tienda']
        ]),
        "permissions" => json_encode([
          'create_client_order' => 'Crear orden de cliente',
          'update_client_order' => 'Editar orden de cliente',
          'retrieve_client_orders' => 'Obtener ordenes de clientes',
        ]),
        'typeUsers' => json_encode([
          ["name" => "Atencion al cliente", "keyname" => "client_attentioner", "permisos" => [
            "create_client_order","update_client_order","retrieve_client_orders"
          ]],
        ]),
      ]);

      DB::table("modules")->insert([
        "name"=> "Historial de movimientos",
        "icon"=> "fa-store-alt",
        "keyname" => "historial_movimi",
        "migrations" => json_encode([
          'historical_module/2021_10_04_000615_create_history_movements_table.php',
        ]),
        "dependencies" => "[]",
        "version" => "0.1",
        'description' => 'Modulo de historial de movimientos.',
        'env_vars' => json_encode([]),
        "permissions" => json_encode([
          'can_see_historial' => 'Permitir ver historial de movimientos',
        ]),
        'typeUsers' => json_encode([]),
      ]);

      DB::table("modules")->insert([
        "name"=> "Pedidos",
        "icon"=> "fa-truck-loading",
        "keyname" => "pedidos",
        "migrations" => json_encode([
          // 'requests_module/2024_01_23_173431_create_requests_table.php',
          'requests_module/2024_01_23_173431_create_payments_table.php',
        ]),
        "dependencies" => "['productos']",
        "version" => "0.1",
        'description' => 'Modulo para la gestion de pedidos (requiere tambien el modulo de productos!).',
        'env_vars' => json_encode([]),
        "permissions" => json_encode([
          'create_requests' => 'Crear pedido',
          'update_requests' => 'Editar pedido',
          'retrieve_requests' => 'Obtener pedidos',
          'delete_requests' => 'Eliminar pedidos'
        ]),
        'typeUsers' => json_encode([
          ["name" => "Admin", "keyname" => "Admin", "permisos" => [
            "create_requests","update_requests","retrieve_requests","delete_requests"
          ]],
        ]),
      ]);

      DB::table("modules")->insert([
        "name"=> "Ingredientes",
        "icon"=> "fas fa-wine-bottle",
        "keyname" => "ingredientes",
        "migrations" => json_encode([
          "operations_module\/2025_05_19_211125_create_ingredients_table.php",
          "operations_module\/2025_05_19_211254_create_products_ingredients_table.php"
        ]),
        "dependencies" => "[]",
        "version" => "0.1",
        'description' => 'Modulo para la gestion de Ingredientes.',
        'env_vars' => json_encode([]),
        "permissions" => json_encode([]),
        'typeUsers' => json_encode([]),
      ]);

    }
}
