<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ejecutateGlobalMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicializando migraciones de una aplicación';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      Artisan::call('migrate', [
          '--database'  => 'mysql_local',
          '--path'      => 'database/migrations/App_local',
          '--force'     => true,
      ]);
      $this->call('db:seed', [
          '--database'  => 'mysql_local',
          '--class'     => 'database/seeds/App_local',
      ]);
    }
}
