<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(ClientsTableSeeder::class);
        $this->call(DataBasesTableSeeder::class);
        $this->call(AplicationsTableSeeder::class);
        $this->call(SubmodulesTableSeeder::class);
        $this->call(ModulessAppsTableSeeder::class);
        $this->call(SubModulesAppsTableSeeder::class);


        $this->call(SettingsTableSeeder::class);
        $this->call(SettingsSubModuleTableSeeder::class);

        $this->call(SettingsModuleAppsTableSeeder::class);
        $this->call(SettingsSubModuleAppsTableSeeder::class);

    }
}
