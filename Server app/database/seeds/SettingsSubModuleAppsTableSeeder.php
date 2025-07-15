<?php

use Illuminate\Database\Seeder;

class SettingsSubModuleAppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      //Setting "Permitir factura electronica" en submodulo "Facturas" del modulo "Ventas" en App #1
      /*DB::table("setting_submodules_modules_apps")->insert([
          "submodule_module_apps_id" => 2,'setting_submodule_id' => 1,'active' => true
      ]);*/

    }
}
