<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * لا يُنشئ أي بيانات افتراضيًا في الإنتاج.
     * لبيانات التطوير: php artisan db:seed --class=DemoParticipationSeeder
     * لإنشاء مسؤول: php artisan basmah:admin you@taibahu.edu.sa
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            $this->call(DemoParticipationSeeder::class);
        }
    }
}
