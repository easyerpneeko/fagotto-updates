<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Artisan;

class MigrationsSeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrateandseed {--database=} {--path=} {--force} {--seed} {--class=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            '--database'  => $this->option('database'),
            '--path'      => $this->option('path'),
            '--force'     => $this->option('force'),
        ]);
        $this->call('db:seed', [
            '--database'  => $this->option('database'),
            '--class'     => $this->option('class'),
        ]);
    }
}
