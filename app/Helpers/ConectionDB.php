<?php

namespace App\Helpers;

use App\DataBase;
use Artisan;
use Illuminate\Support\Facades\DB;
use PDO;
use Config;

class ConectionDB
{

  public $app = null;
  public $database = null;

  function __construct($aplication) {
    $this->app = $aplication;
    $this->database = DataBase::find(76);
  }

  // Aqui realizo la creacion de la BD al igual que en createDataBase pero ademas acomodo el nombre
  public static function createDataBaseByAppName($aplicationName) {

    //Hago el nombre de la aplicacion legible para una BD
    $DBname = str_replace(" ", "_", $aplicationName);
    $DBname = strtolower($DBname);
    //Lo uno junto al prefijo y sufijo para crear el nuevo nombre de 54 caracteres
    $DBname = 'erd_app_'.$DBname.'_'.uniqid();

    return ConectionDB::createDataBase($DBname);
  }

  // Aqui se realiza la creacion de la base de datos con un comando consola ejecutado con Artisan::call()
  public static function createDataBase($DBname) {

    // Creacion de la base de datos
    Artisan::call('make:database', ['nombrebd' => $DBname, 'tipo' => 'mysql', 'cotejamiento' => 'utf8mb4-unicode']);

    //Retorno el nuevo nombre para usarlo fuera de la funcion
    return $DBname;

  }

  //Eliminando base de datos
  public static function delete_database($DBname) {
    DB::connection('mysql_local')->select('DROP DATABASE IF EXISTS '. $DBname);
  }

  public static function reset_database() {//Miercoleeeeeeeeeeees
    Artisan::call("migrate:reset", ["--force" => true,'--database'  => 'mysql_local']);
  }

  //Ejecutando las tablas y las seeders de una base de datos
  public static function DBMigrationsFolder_database($dirMigrations, $dirSeeders = null) {
    // Limpiando tu base de datos
    ConectionDB::reset_database();

    $settings = [
      '--database'  => 'mysql_local',
      '--path'      => 'database/migrations/'.$dirMigrations,
      '--force'     => true
    ];

    //Los seeders son opcionales
    if ($dirSeeders) {
      $settings['--class']  = $dirSeeders;
      $settings['--seed']   = true;
    }

    //Creando migraciones y seeders de tu base de datos
    Artisan::call('migrateandseed', $settings);
  }

  // Ejecutando una migration especifica
  public static function Migration_database($NameMigration) {
    Artisan::call('migrate', [
      '--database'  => 'mysql_local',
      '--path'      => 'database/migrations/local_migrations/'.$NameMigration,
      '--force'     => true,
    ]);
  }

  // Cambia la Base de datos a una App en especifico
  public static function ChangeDBToApp($App, $reconect = false) {

    $InsConnection = new Self($App);
    // $InsConnection->set_database();
    $InsConnection->set_database($App->database);
    if ($reconect) {
      DB::purge('mysql_local');
      DB::connection('mysql_local')->reconnect();
    }

  }

  // Ejecutando migraciones en especifico
  public static function Migrations_database($names = []) {
    foreach ($names as $name) ConectionDB::Migration_database($name);
  }

  // Get Connections
  public static function getConnections() {
    $connections = DB::getConnections();
    return $connections;
  }

  // Ejecutando una seeder especifica
  public static function Seeder_database($NameSeeder) {
    Artisan::call('db:seed', [
        '--database'  => 'mysql_local',
        '--class'     => $NameSeeder,
        '--force'     => true
    ]);
  }
  // Ejecutando seeders en especifico
  public static function Seeders_database($names = []) {
    foreach ($names as $name) ConectionDB::Seeder_database($name);
  }

  //Modificando archivo de databe para conectarce con otra base de datos
  // public function set_database(){
    public function set_database($database){
    $configDb = array(
      'driver' => 'mysql',
      'url' => env('DATABASE_URL'),
      'host' => env('DB_HOST', '127.0.0.1'),
      'port' => env('DB_PORT', '3306'),
      // 'database' => $this->database->name,
      // 'username' => $this->database->username,
      // 'password' => $this->database->password,
      'database' => $database->name,
      'username' => $database->username,
      'password' => $database->password,
      'unix_socket' => env('DB_SOCKET', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
          PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
    );
    Config::set('database.connections.mysql_local', $configDb);
  }

  //Determino el tamaño de una base de datos
  public static function determineSizeDB($DBname) {//luego hay que cambiarlo
    $tables = DB::connection('mysql')->select('SELECT table_name AS "Table",
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
    FROM information_schema.TABLES
    WHERE table_schema = "'.$DBname.'"
    ORDER BY (data_length + index_length) DESC;');
    $size = 0;
    foreach ($tables as $table) {
      $table = json_decode(json_encode($table),1);
      $size += (float) $table["Size (MB)"];
    }
    return $size;
  }

  //Determino si la base de datos existe
  public static function DBExists($DBname)
  {
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
        $db = DB::select($query, [$DBname]);
        if (empty($db)) {
            return false;
        }
        return true;
   }

   public static function SizeIfExistsDB($DBname) {
     $size = 0;

     if (Self::DBExists($DBname))
      $size = Self::determineSizeDB($DBname);

     return $size;
   }



}
