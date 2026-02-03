# نظام التحفيز (Gamification) — ملخص طريقة العمل

## 1. نظرة عامة

نظام التحفيز في منصة Quantum LMS يعتمد على **النقاط**، **الشارات**، **الإنجازات**، **المستويات**، **المهام اليومية والأسبوعية**، **التحديات**، و**المكافآت**. يُمنح الطالب نقاطاً عند قيامه بأفعال معينة (حضور درس، إكمال اختبار، إلخ)، وتُفحص تلقائياً الشارات والإنجازات وترقية المستوى وتقدم المهام.

---

## 2. المكونات والخدمات

| المكون | الوصف |
|--------|--------|
| **GamificationService** | نقطة الدخول لكل حدث تحفيزي. يستقبل الأحداث من المتحكمات/الخدمات ويستدعي: منح النقاط، فحص المهام، فحص الشارات، فحص الإنجازات، ترقية المستوى. |
| **PointService** | حساب النقاط (من `system_settings` أو قيم افتراضية) ومنحها للمستخدم، وتحديث إجمالي النقاط في `user_levels` و Cache. |
| **BadgeService** | فحص شروط الشارات ومنحها تلقائياً عند الاستيفاء. |
| **AchievementService** | فحص شروط الإنجازات وفتحها ومنح نقاط المكافأة. |
| **LevelService** | فحص إجمالي النقاط وترقية مستوى المستخدم عند الوصول إلى عتبة المستوى التالي. |
| **TaskService** | فحص المهام اليومية والأسبوعية وتحديث تقدم المستخدم ومنح نقاط عند إكمال المهمة. |

**الملفات الرئيسية:**

- `app/Services/GamificationService.php` — processEvent, processLessonAttendance, processLessonCompletion, processQuizCompletion, processQuestionCompletion
- `app/Services/PointService.php` — calculatePoints, awardPoints, getUserTotalPoints
- `app/Services/BadgeService.php` — checkAndAwardBadges
- `app/Services/AchievementService.php` — checkAndUnlockAchievements
- `app/Services/LevelService.php` — checkLevelUp
- `app/Services/TaskService.php` — checkTaskCompletion, getDailyTasks, getWeeklyTasks

---

## 3. تدفق الأحداث

```
فعل الطالب (حضور درس / إكمال اختبار / إجابة سؤال / مشاهدة مكتبة / تحميل)
    ↓
Controller أو Service (مثلاً StudentLessonController, StudentQuizController, QuestionAttemptService, StudentLibraryController)
    ↓
GamificationService (processLessonAttendance / processLessonCompletion / processQuizCompletion / processEvent)
    ↓
1) PointService: حساب النقاط → منح النقاط → تحديث user_levels و Cache
2) TaskService: فحص المهام اليومية/الأسبوعية وتحديث التقدم
3) BadgeService: فحص الشارات ومنحها
4) AchievementService: فحص الإنجازات وفتحها
5) LevelService: فحص ترقية المستوى
```

---

## 4. متى يعمل تلقائياً

| الحدث | الملف | الاستدعاء |
|-------|------|-----------|
| تحديد حضور درس | StudentLessonController (markLessonStatus) | processLessonAttendance |
| تحديد إكمال درس | StudentLessonController (markLessonStatus) | processLessonCompletion |
| إنهاء اختبار | StudentQuizController (submitQuiz) | processQuizCompletion |
| إكمال إجابة سؤال | QuestionAttemptService | processEvent('question_answered') |
| مشاهدة عنصر مكتبة | StudentLibraryController | processEvent('library_item_viewed') |
| تحميل عنصر مكتبة | StudentLibraryController | processEvent('library_item_downloaded') |

لا يلزم تشغيل يدوي؛ النظام يعمل تلقائياً عند تنفيذ الطالب لهذه الأفعال.

---

## 5. الجداول الرئيسية

| الجدول | الغرض |
|--------|--------|
| `point_transactions` | سجل كل منحة نقاط (نوع الحدث، النقاط، المستخدم، المصدر). |
| `user_levels` | ربط المستخدم بالمستوى الحالي وإجمالي النقاط. |
| `levels` | تعريف المستويات (level_number, points_required, benefits). |
| `badges` | تعريف الشارات (معايير، نقاط مطلوبة، أيقونة، لون). |
| `user_badges` | الشارات التي حصل عليها كل مستخدم. |
| `achievements` | تعريف الإنجازات (معايير، نقاط مكافأة، ربط بشارة). |
| `user_achievements` | الإنجازات التي فتحها كل مستخدم. |
| `daily_tasks` | المهام اليومية (نوع، معايير، نقاط مكافأة). |
| `weekly_tasks` | المهام الأسبوعية (نوع، معايير، أيام). |
| `user_tasks` | تقدم المستخدم في المهام (taskable_type, taskable_id, progress, status). |
| `challenges` | التحديات (أسبوعية/شهرية، معايير، مكافآت). |
| `user_challenges` | تقدم المستخدم في التحديات. |
| `rewards` | المكافآت القابلة للاستبدال بنقاط. |
| `user_rewards` | المكافآت التي استبدلها المستخدم. |
| `system_settings` | إعدادات التحفيز (مفاتيح `gamification_*`: قواعد النقاط، الإشعارات، إلخ). |

---

## 6. تشغيل الـ Seed

لتهيئة نظام التحفيز بالكامل (إعدادات، شارات، إنجازات، مستويات، تحديات، مكافآت، مهام يومية وأسبوعية):

```bash
# تشغيل إعدادات التحفيز فقط
php artisan db:seed --class=GamificationSettingsSeeder

# تشغيل كل بيانات التحفيز (بدون الإعدادات)
php artisan db:seed --class=GamificationSeeder

# تشغيل كل الـ seed (يشمل التحفيز إذا كان مضافاً في DatabaseSeeder)
php artisan db:seed
```

**ملاحظة:** في `DatabaseSeeder` يتم استدعاء `GamificationSettingsSeeder` ثم `GamificationSeeder` تلقائياً. جميع seeders التحفيز (Badges, Achievements, Levels, Challenges, Rewards, Tasks) تستخدم `updateOrCreate`، لذا يمكن إعادة تشغيل الـ seed دون تكرار السجلات.

**ترتيب الاستدعاء داخل GamificationSeeder:**

1. BadgesSeeder  
2. AchievementsSeeder (يعتمد على معرفات الشارات)  
3. LevelsSeeder  
4. ChallengesSeeder  
5. RewardsSeeder  
6. TasksSeeder (مهام يومية وأسبوعية)

---

## 7. التحقق من أن النظام يعمل

1. تشغيل الـ seed كما في القسم 6.
2. تسجيل الدخول كطالب.
3. تنفيذ أحد الأفعال (مثلاً: فتح درس وتحديد "حضر" أو "أكمل" من واجهة الطالب).
4. التحقق من:
   - ظهور النقاط في الهيدر أو صفحة التحفيز.
   - وجود سجل جديد في جدول `point_transactions`.
   - (إن وُجدت مهام) ظهور المهام اليومية/الأسبوعية وتحديث التقدم عند تنفيذ أفعال مطابقة.

---

## 8. إعدادات النقاط (system_settings)

تُخزَن قواعد النقاط في `system_settings` بالمفتاح `gamification_points_{نوع_الحدث}`. أمثلة:

- `gamification_points_lesson_attended` — نقاط حضور الدرس  
- `gamification_points_lesson_completed` — نقاط إكمال الدرس  
- `gamification_points_quiz_completed` — نقاط إكمال الاختبار  
- `gamification_points_question_answered` — نقاط الإجابة على السؤال  
- `gamification_points_quiz_perfect_score` — نقاط إضافية لدرجة 100% في الاختبار  
- `gamification_points_library_item_viewed` — نقاط مشاهدة عنصر مكتبة  
- `gamification_points_library_item_downloaded` — نقاط تحميل عنصر مكتبة  

في حال عدم وجود مفتاح لنوع حدث معين، يستخدم `PointService` قيماً افتراضية من الكود.
