<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      //Masteer
      DB::table("users")->insert([
        "username" => "master",
        "password" => Hash::make('secret'),
      ]);
    }
}
