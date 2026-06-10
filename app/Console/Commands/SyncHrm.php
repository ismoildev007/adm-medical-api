<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HrmSyncService;

class SyncHrm extends Command
{
    protected $signature = 'sync:hrm {--fresh : Barcha ma\'lumotlarni yangilash}';
    protected $description = 'HRM tizimidan ma\'lumotlarni sync qilish';

    public function handle()
    {
        $this->info('HRM sync boshlandi...');

        $fresh = $this->option('fresh');
        (new HrmSyncService())->syncAll($fresh);

        $this->info('HRM sync tugadi!');
    }
}
