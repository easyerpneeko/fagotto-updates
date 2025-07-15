<?php

use Illuminate\Database\Seeder;

class SettingsModuleAppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /*//Setting "Permitir codigo de barras" en modulo "Productos" en App #1
      DB::table("settings_modules_apps")->insert([
          "modules_apps_id" => 1,
          'settings_module_id' => 1,'active' => false
      ]);
      //Setting "Permitir codigo de barras" en modulo "Productos" en App #2
      DB::table("settings_modules_apps")->insert([
          "modules_apps_id" => 4,
          'settings_module_id' => 1,'active' => false
      ]);*/
    }
}
