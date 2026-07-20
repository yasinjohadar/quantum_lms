<?php

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function backfillAdmin(): User
{
    Permission::firstOrCreate(
        ['name' => 'question-import', 'guard_name' => 'web'],
        ['description' => 'استيراد الأسئلة']
    );

    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->givePermissionTo('question-import');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

test('status endpoint returns question and option totals', function () {
    $admin = backfillAdmin();

    $question = Question::create([
        'type' => 'single_choice',
        'title' => 'سؤال عادي',
        'content' => 'سؤال عادي',
        'difficulty' => 'easy',
        'default_points' => 1,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);
    QuestionOption::create(['question_id' => $question->id, 'content' => 'خيار', 'is_correct' => true, 'order' => 1]);

    $response = $this->actingAs($admin)->getJson(route('admin.questions.math-backfill.status'));

    $response->assertOk();
    expect($response->json('questions'))->toBeGreaterThanOrEqual(1);
    expect($response->json('options'))->toBeGreaterThanOrEqual(1);
});

test('process batch endpoint repairs a legacy question with broken math delimiters', function () {
    $admin = backfillAdmin();

    // نفس النمط الذي ظهر على السيرفر: f(x) مُغلَّف منعزلاً، وعبارة عربية مُغلَّفة
    // خطأً بالكامل، بينما باقي التعبير غير مُغلَّف أبداً (لاتكس خام ظاهر للطالب).
    $broken = Question::create([
        'type' => 'single_choice',
        'title' => 'نهاية التابع $f(x)$ = x - \sqrt(x^{2}+x) $عندما x$ \to+\infty هي:',
        'content' => 'نهاية التابع $f(x)$ = x - \sqrt(x^{2}+x) $عندما x$ \to+\infty هي:',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->postJson(route('admin.questions.math-backfill.process-batch'), [
        'entity' => 'questions',
        'after_id' => 0,
        'limit' => 500,
    ]);

    $response->assertOk();
    expect($response->json('updated'))->toBeGreaterThanOrEqual(1);
    expect($response->json('done'))->toBeTrue();

    $broken->refresh();
    expect($broken->title)
        ->toContain('$f(x) = x - \\sqrt(x^{2}+x)$')
        ->toContain('$x \\to+\\infty$')
        ->not->toContain('عندما x$');
});

test('process batch endpoint leaves already-correct questions untouched', function () {
    $admin = backfillAdmin();

    $good = Question::create([
        'type' => 'single_choice',
        'title' => 'نهاية التابع $f(x) = x - \sqrt{x^{2}+x}$ عندما $x \to +\infty$ هي:',
        'content' => 'نهاية التابع $f(x) = x - \sqrt{x^{2}+x}$ عندما $x \to +\infty$ هي:',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);
    $originalTitle = $good->title;

    $this->actingAs($admin)->postJson(route('admin.questions.math-backfill.process-batch'), [
        'entity' => 'questions',
        'after_id' => 0,
        'limit' => 500,
    ])->assertOk();

    $good->refresh();
    expect($good->title)->toBe($originalTitle);
});

test('process batch endpoint requires the question-import permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('admin.questions.math-backfill.process-batch'), [
        'entity' => 'questions',
    ])->assertForbidden();
});
