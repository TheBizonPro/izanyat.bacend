<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDatabaseDumpCommand extends Command
{
    protected $signature = 'database:dump';

    protected $description = 'Makes dump of db';

    public function handle()
    {
        $databaseName = config('database.connections.mysql.database');
        $databaseUser = config('database.connections.mysql.username');
        $databasePassword = config('database.connections.mysql.password');

        $timestamp = date('d-m-Y_h:i');
        $dumpDir = storage_path('dump/database/');

        try {
            mkdir($dumpDir, 0777, true);
        } catch (\Exception $e){}

        $fileName = $dumpDir . "{$databaseName}_$timestamp.sql.gz";

        exec("mysqldump -u$databaseUser -p$databasePassword $databaseName | gzip -c > $fileName");
    }
}
