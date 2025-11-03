<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clear {--reset-id : Resetuje autoinkrementację tabeli}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Czyści wszystkie rekordy w tabeli users.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->confirm('Czy na pewno chcesz usunąć wszystkie rekordy z tabeli users?')) {
            DB::table('users')->truncate(); // usuwa wszystkie rekordy i resetuje ID w MySQL

            if ($this->option('reset-id')) {
                DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
                $this->info('Tabela users została wyczyszczona, a ID zresetowane.');
            } else {
                $this->info('Tabela users została wyczyszczona.');
            }

            return Command::SUCCESS;
        }

        $this->info('Operacja anulowana.');
        return Command::FAILURE;
    }
}
