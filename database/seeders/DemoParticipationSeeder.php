<?php

namespace Database\Seeders;

use App\Enums\ParticipationStatus;
use App\Models\Participation;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية للتطوير فقط. الأسماء واضحة أنها تجريبية حتى لا تُحسب مشاركات حقيقية.
 * الحذف: php artisan basmah:purge-demo
 */
class DemoParticipationSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('تم تجاهل البيانات التجريبية في بيئة الإنتاج.');

            return;
        }

        $messages = [
            'السعودية بالنسبة لي بيتٌ كبير نتقاسم فيه الحلم.',
            'وطن يعلّمنا أن الطموح لا سقف له.',
            'أمانٌ نعيشه كل يوم دون أن ننتبه لقيمته.',
            'هنا تعلمت أن العمل المخلص يصنع الفرق.',
            'مستقبل نبنيه معًا، خطوة بعد خطوة.',
            'فخرٌ نحمله أينما ذهبنا.',
            'هويتنا جذورٌ ثابتة وأغصانٌ تمتد نحو الغد.',
            'عطاء لا ينقطع من أرضٍ كريمة وأهلها.',
        ];
        $departments = config('basmah.departments');
        $words = config('basmah.words');
        $statuses = [ParticipationStatus::Approved, ParticipationStatus::Approved, ParticipationStatus::Approved, ParticipationStatus::Pending, ParticipationStatus::Hidden];

        for ($i = 1; $i <= 30; $i++) {
            // توزيع غير متساوٍ للكلمات لاختبار حجم السحابة
            $word = $words[min(count($words) - 1, (int) floor(($i * $i) % 37 / 4))];

            Participation::create([
                'full_name' => 'مشارك تجريبي '.$i,
                'department' => $departments[$i % count($departments)],
                'selected_word' => $word,
                'message' => $messages[$i % count($messages)],
                'status' => $statuses[$i % count($statuses)],
                'consent' => true,
                'consented_at' => now(),
                'is_demo' => true,
            ]);
        }
    }
}
