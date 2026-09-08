<?php

namespace App\Console\Commands;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserBadge;
use App\Services\AchievementService;
use App\Services\BadgeService;
use App\Services\Gamification\CriteriaProgressCalculator;
use Illuminate\Console\Command;

/**
 * إعادة حساب تقدّم الإنجازات ومنح/سحب الشارات التلقائية لكل الطلاب،
 * بعد إصلاح عدم تطابق مفاتيح criteria بين AchievementService/BadgeService
 * والبيانات الفعلية المزروعة (AchievementsSeeder/BadgesSeeder).
 *
 * قبل الإصلاح: تقدّم الإنجازات كان يُحسب دائماً كصفر (لا يكتمل أي إنجاز تلقائياً)،
 * بينما شرط الشارات التلقائية كان يتحقق دائماً (تُمنح كل الشارات فوراً بلا تحقق حقيقي).
 * هذا الأمر يصحّح الحالة الحالية: يحدّث تقدم الإنجازات (ويفتح ما يستحق الفتح الآن)،
 * ويسحب الشارات الممنوحة خطأً (لم تعد تحقق شرطها الصحيح) ويمنح الشارات المستحقة فعلاً.
 */
class RecalculateGamificationProgress extends Command
{
    protected $signature = 'gamification:recalculate {--user= : إعادة الحساب لمستخدم واحد فقط (معرّفه)}';

    protected $description = 'إعادة حساب تقدّم الإنجازات وتصحيح الشارات التلقائية لكل الطلاب وفق الشرط الصحيح';

    public function handle(
        CriteriaProgressCalculator $calculator,
        AchievementService $achievementService,
        BadgeService $badgeService
    ): int {
        $users = $this->option('user')
            ? User::where('id', $this->option('user'))->get()
            : User::students()->get();

        if ($users->isEmpty()) {
            $this->warn('لا يوجد مستخدمون لإعادة الحساب.');

            return self::SUCCESS;
        }

        $achievements = Achievement::active()->get();
        $badges = Badge::active()->automatic()->get();

        $achievementsUpdated = 0;
        $achievementsUnlocked = 0;
        $badgesAwarded = 0;
        $badgesRevoked = 0;

        $this->output->progressStart($users->count());

        foreach ($users as $user) {
            foreach ($achievements as $achievement) {
                $userAchievement = UserAchievement::firstOrNew([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                ]);

                // لا نلمس إنجازاً مكتملاً فعلاً — لا يوجد مسار يمنح إكمالاً خاطئاً هنا أصلاً
                // (كانت مشكلة الإنجازات في الاتجاه المعاكس: التقدم يبقى صفراً دائماً).
                if ($userAchievement->completed_at !== null) {
                    continue;
                }

                $progress = $calculator->progress($user, $achievement->criteria ?? []);
                $userAchievement->progress = $progress;
                $userAchievement->save();
                $achievementsUpdated++;

                if ($progress >= 100) {
                    $achievementService->unlockAchievement($user, $achievement, $userAchievement);
                    $achievementsUnlocked++;
                }
            }

            $ownedBadgeIds = $user->badges()->pluck('badges.id')->all();
            foreach ($badges as $badge) {
                $isMet = $calculator->isMet($user, $badge->criteria ?? []);
                $owns = in_array($badge->id, $ownedBadgeIds, true);

                if ($isMet && ! $owns) {
                    $badgeService->awardBadge($user, $badge);
                    $badgesAwarded++;
                } elseif (! $isMet && $owns) {
                    UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->delete();
                    $badgesRevoked++;
                }
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine();
        $this->info("الإنجازات: تحديث {$achievementsUpdated} سجل تقدم، فتح {$achievementsUnlocked} إنجاز جديد مستحق.");
        $this->info("الشارات: منح {$badgesAwarded} شارة مستحقة، وسحب {$badgesRevoked} شارة كانت ممنوحة خطأً (لا تحقق شرطها الحقيقي).");

        return self::SUCCESS;
    }
}
