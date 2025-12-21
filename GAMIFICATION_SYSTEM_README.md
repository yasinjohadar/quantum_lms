# نظام التحفيز والإحصائيات والتقارير (Gamification System)

## نظرة عامة

تم بناء نظام تحفيز متكامل (Gamification) مع إحصائيات شاملة وتقارير مصورة. النظام ديناميكي وقابل للتعديل بالكامل من قبل الأدمن.

## المكونات الرئيسية

### 1. نظام النقاط (Points System)
- تتبع النقاط لكل نشاط (حضور، إكمال درس، اختبار، سؤال، إنجاز)
- قواعد قابلة للتعديل من قبل الأدمن
- تاريخ كامل لجميع المعاملات

### 2. نظام الشارات (Badges System)
- شارات قابلة للإضافة والتعديل
- منح تلقائي أو يدوي
- معايير مخصصة للحصول على الشارات

### 3. نظام الإنجازات (Achievements System)
- إنجازات متنوعة (حضور، اختبارات، كورسات، خاصة)
- تتبع التقدم نحو الإنجازات
- مكافآت نقاط عند فتح الإنجاز

### 4. نظام المستويات (Levels System)
- مستويات قابلة للتخصيص
- تتبع تقدم المستوى
- إشعارات عند ترقية المستوى

### 5. التحديات (Challenges)
- تحديات أسبوعية/شهرية/مخصصة
- تتبع التقدم
- مكافآت عند الإكمال

### 6. المكافآت (Rewards)
- متجر مكافآت
- استبدال النقاط بمكافآت
- إدارة الكميات المتاحة

### 7. الشهادات (Certificates)
- توليد شهادات PDF
- قوالب قابلة للتخصيص
- أرقام تسلسلية فريدة

### 8. لوحة المتصدرين (Leaderboards)
- لوحات متعددة (عامة، كورس، أسبوعية، شهرية)
- تحديث تلقائي
- ترتيب المستخدمين

### 9. الإشعارات (Notifications)
- إشعارات داخل النظام
- إشعارات بريد إلكتروني (اختياري)
- تتبع الإشعارات المقروءة

## الملفات الرئيسية

### Models
- `PointTransaction` - معاملات النقاط
- `Badge` - الشارات
- `UserBadge` - شارات المستخدمين
- `Achievement` - الإنجازات
- `UserAchievement` - إنجازات المستخدمين
- `Level` - المستويات
- `UserLevel` - مستويات المستخدمين
- `Challenge` - التحديات
- `UserChallenge` - تحديات المستخدمين
- `Reward` - المكافآت
- `UserReward` - مكافآت المستخدمين
- `Certificate` - الشهادات
- `CertificateTemplate` - قوالب الشهادات
- `Leaderboard` - لوحة المتصدرين
- `LeaderboardEntry` - إدخالات اللوحة
- `GamificationNotification` - إشعارات التحفيز

### Services
- `GamificationService` - الخدمة الرئيسية
- `PointService` - إدارة النقاط
- `BadgeService` - إدارة الشارات
- `AchievementService` - إدارة الإنجازات
- `LevelService` - إدارة المستويات
- `ChallengeService` - إدارة التحديات
- `RewardService` - إدارة المكافآت
- `CertificateService` - إدارة الشهادات
- `LeaderboardService` - إدارة لوحة المتصدرين
- `GamificationNotificationService` - إدارة الإشعارات

## الربط مع الأحداث

النظام مربوط تلقائياً مع:
- `LessonCompletion` - عند الحضور أو الإكمال
- `QuizAttempt` - عند إكمال الاختبار
- `QuestionAttempt` - عند إكمال السؤال

## Routes

### Admin Routes
- `/admin/gamification` - لوحة التحكم
- `/admin/badges` - إدارة الشارات
- `/admin/achievements` - إدارة الإنجازات
- `/admin/levels` - إدارة المستويات
- `/admin/challenges` - إدارة التحديات
- `/admin/rewards` - إدارة المكافآت
- `/admin/certificates` - إدارة الشهادات
- `/admin/leaderboards` - إدارة لوحة المتصدرين

### Student Routes
- `/student/gamification/dashboard` - لوحة التحفيز
- `/student/gamification/badges` - الشارات
- `/student/gamification/achievements` - الإنجازات
- `/student/gamification/leaderboard` - لوحة المتصدرين
- `/student/gamification/challenges` - التحديات
- `/student/gamification/rewards` - المكافآت
- `/student/gamification/certificates` - الشهادات
- `/student/gamification/stats` - الإحصائيات
- `/student/notifications` - الإشعارات

## الخطوات التالية

1. تشغيل Migrations:
```bash
php artisan migrate
```

2. إنشاء Seeders للبيانات التجريبية (اختياري)

3. إعداد قوالب الشهادات

4. إعداد قواعد النقاط من لوحة الأدمن

5. إنشاء الشارات والإنجازات والمستويات

## ملاحظات

- النظام يستخدم Caching لتحسين الأداء
- جميع القواعد قابلة للتعديل من قبل الأدمن
- النظام قابل للتوسع والإضافة

