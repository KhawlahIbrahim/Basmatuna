<?php

namespace App\Console\Commands;

use App\Models\Participation;
use Illuminate\Console\Command;

class PurgeDemo extends Command
{
    protected $signature = 'basmah:purge-demo {--force}';

    protected $description = 'حذف جميع المشاركات التجريبية (is_demo) قبل الإطلاق';

    public function handle(): int
    {
        $count = Participation::where('is_demo', true)->count();
        if ($count === 0) {
            $this->info('لا توجد بيانات تجريبية.');

            return self::SUCCESS;
        }
        if (! $this->option('force') && ! $this->confirm("سيتم حذف {$count} مشاركة تجريبية. متابعة؟")) {
            return self::FAILURE;
        }

        Participation::where('is_demo', true)->get()->each->delete();
        $this->info("تم حذف {$count} مشاركة تجريبية.");

        return self::SUCCESS;
    }
}
