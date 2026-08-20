<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * عدد «المواد المجانية» على كارد الصف في لوحة الطالب.
 *
 * كان يُحسب من isEffectivelyFree()، وهي تُرجع true أيضاً عندما يكون السعر الفعّال صفراً أو
 * الوضع hidden — فكان كل صف يظهر وكل مواده مجانية (6 من 6) لأن أسعار المواد المفردة غير
 * مضبوطة. المرجع الآن خيار «مجانية دائماً» في لوحة الأدمن وحده.
 *
 * الجدولان يُبنيان هنا بأقل الأعمدة اللازمة: تشغيل كل الـmigrations غير ممكن على sqlite في
 * هذا المشروع (عدد منها يستعلم information_schema الخاص بـMySQL).
 */
class DeclaredFreeSubjectsCountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // يُولَّد تلقائياً في الموديل عند الإنشاء.
            $table->string('slug')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('name');
            // يُولَّد تلقائياً في الموديل عند الإنشاء.
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free_override')->default(false);
            $table->string('pricing_mode')->default('inherit');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');

        parent::tearDown();
    }

    /**
     * @return list<array{0: bool, 1: string, 2: bool, 3: string}>
     */
    public static function pricingCases(): array
    {
        return [
            // is_free_override, pricing_mode, counts as free, label
            [true, 'inherit', true, 'خيار «مجانية دائماً» مُفعّل'],
            [true, 'paid', true, 'الخيار مُفعّل ويتقدّم على وضع مدفوع'],
            [false, 'free', true, 'الوضع free صراحةً'],
            // الحالات التي كانت تُفخّم العدد:
            [false, 'inherit', false, 'موروث من الصف — سعر صفر لا يعني قرار الأدمن'],
            [false, 'paid', false, 'مدفوعة'],
            [false, 'hidden', false, 'سعر مخفي'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pricingCases')]
    public function test_declared_free_depends_on_the_admin_toggle_only(
        bool $override,
        string $mode,
        bool $expected,
        string $label
    ): void {
        $subject = new Subject(['is_free_override' => $override, 'pricing_mode' => $mode]);

        $this->assertSame($expected, $subject->isDeclaredFree(), $label);
    }

    public function test_the_card_count_only_includes_subjects_the_admin_marked_free(): void
    {
        $class = SchoolClass::create(['name' => 'الصف الأول']);

        $this->subject($class, 'رياضيات', override: true);
        $this->subject($class, 'علوم', override: true);
        // هذه كانت تُحتسب مجانية لأن سعرها الفعّال صفر.
        $this->subject($class, 'عربي', override: false);
        $this->subject($class, 'تاريخ', override: false);

        $this->assertSame(4, $class->subjects()->where('is_active', true)->count());
        $this->assertSame(2, $class->getFreeSubjectsCount());
    }

    public function test_an_inactive_subject_is_never_counted(): void
    {
        $class = SchoolClass::create(['name' => 'الصف الثاني']);

        $this->subject($class, 'مفعّلة', override: true);
        $this->subject($class, 'معطّلة', override: true, active: false);

        $this->assertSame(1, $class->getFreeSubjectsCount());
    }

    public function test_a_class_with_no_marked_subjects_counts_zero(): void
    {
        $class = SchoolClass::create(['name' => 'صف مدفوع']);

        $this->subject($class, 'مادة', override: false);
        $this->subject($class, 'مادة أخرى', override: false);

        // صفر هو الجواب الصحيح: الأدمن لم يُعلن أي مادة مجانية.
        $this->assertSame(0, $class->getFreeSubjectsCount());
    }

    public function test_subjects_of_another_class_do_not_leak_into_the_count(): void
    {
        $first = SchoolClass::create(['name' => 'الأول']);
        $second = SchoolClass::create(['name' => 'الثاني']);

        $this->subject($first, 'مادة الأول', override: true);
        $this->subject($second, 'مادة الثاني', override: true);
        $this->subject($second, 'أخرى', override: true);

        $this->assertSame(1, $first->getFreeSubjectsCount());
        $this->assertSame(2, $second->getFreeSubjectsCount());
    }

    public function test_the_scope_and_the_method_agree(): void
    {
        // الواجهة تستعمل العدد المحسوب مسبقاً (بـ isDeclaredFree في الذاكرة) والدالة تستعمل
        // الـscope في قاعدة البيانات؛ اختلافهما يعني رقمين مختلفين لنفس الصف.
        $class = SchoolClass::create(['name' => 'صف']);
        $this->subject($class, 'أ', override: true);
        $this->subject($class, 'ب', override: false, mode: 'free');
        $this->subject($class, 'ج', override: false, mode: 'hidden');
        $this->subject($class, 'د', override: false);

        $inMemory = $class->subjects()->where('is_active', true)->get()
            ->filter(fn (Subject $subject) => $subject->isDeclaredFree())
            ->count();

        $this->assertSame($inMemory, $class->getFreeSubjectsCount());
        $this->assertSame(2, $inMemory);
    }

    private function subject(
        SchoolClass $class,
        string $name,
        bool $override,
        string $mode = 'inherit',
        bool $active = true
    ): Subject {
        return Subject::create([
            'class_id' => $class->id,
            'name' => $name,
            'is_active' => $active,
            'is_free_override' => $override,
            'pricing_mode' => $mode,
        ]);
    }
}
