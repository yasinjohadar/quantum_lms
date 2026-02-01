<?php

namespace Database\Seeders;

use App\Models\ClassEnrollment;
use App\Models\PlatformReview;
use Illuminate\Database\Seeder;

class PlatformReviewSeeder extends Seeder
{
    const REVIEWS_COUNT = 15;

    /**
     * إنشاء 15 رأياً معتمداً مرتبطين بطلاب لديهم انضمام معتمد.
     * إن كان عدد الطلاب أقل من 15 يُعاد استخدامهم لبلوغ 15 رأياً.
     */
    public function run(): void
    {
        $enrollments = ClassEnrollment::where('status', 'approved')
            ->with(['user', 'schoolClass'])
            ->orderBy('user_id')
            ->orderBy('id')
            ->get();

        // تجميع حسب user_id وأخذ أول انضمام لكل مستخدم (لجلب class_id)
        $usersWithClass = [];
        foreach ($enrollments as $e) {
            if (!isset($usersWithClass[$e->user_id]) && $e->user) {
                $usersWithClass[$e->user_id] = [
                    'user_id' => $e->user_id,
                    'class_id' => $e->class_id,
                ];
            }
        }
        $usersWithClass = array_values($usersWithClass);

        if (empty($usersWithClass)) {
            $this->command->warn('لا يوجد طلاب لديهم انضمام معتمد. تم تخطي إنشاء آراء الطلاب.');
            return;
        }

        $comments = [
            'منصة ممتازة ومحتوى منظم، استفدت كثيراً من الدروس.',
            'تجربة تعليمية رائعة، المعلمون متعاونون والمحتوى واضح.',
            'أنصح أي شخص يريد تطوير مهاراته بهذه المنصة.',
            'المنصة ساعدتني في تحقيق تقدم حقيقي في مساري الدراسي.',
            'محتوى ذو جودة عالية وطريقة عرض ممتازة.',
            'سهولة الاستخدام والتنظيم جعلت التعلم ممتعاً.',
            'دعم فني سريع واهتمام بالطالب، شكراً للفريق.',
            'دورات متنوعة ومناسبة لمختلف المستويات.',
            'المنصة وفرت لي الوقت والجهد في التعلم.',
            'تجربة إيجابية جداً، سأستمر في الاستفادة من المحتوى.',
            'واجهة بسيطة ومحتوى غني، أنصح بها بشدة.',
            'تعلمت مهارات جديدة بفضل المنهج المتبع هنا.',
            'الالتزام بمواعيد الدروس والجودة كانا مميزين.',
            'منصة موثوقة ومحتوى يلبي التوقعات.',
            'شكراً للمنصة على ما تقدمه من قيمة تعليمية حقيقية.',
        ];

        // إنشاء 15 رأياً: إعادة استخدام قائمة الطلاب إن كان عددهم أقل من 15
        for ($i = 0; $i < self::REVIEWS_COUNT; $i++) {
            $item = $usersWithClass[$i % count($usersWithClass)];
            $comment = $comments[$i % count($comments)];
            $stars = ($i % 5) + 1; // 1 إلى 5

            PlatformReview::create([
                'user_id' => $item['user_id'],
                'class_id' => $item['class_id'],
                'stars' => $stars,
                'comment' => $comment,
                'status' => 'approved',
                'order' => $i + 1,
                'approved_at' => now(),
            ]);
        }

        $this->command->info('تم إنشاء ' . self::REVIEWS_COUNT . ' رأياً معتمداً لآراء الطلاب.');
    }
}
