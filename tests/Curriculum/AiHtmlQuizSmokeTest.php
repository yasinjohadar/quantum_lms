<?php

use App\AiHtmlQuiz\Models\AiHtmlQuiz;
use App\AiHtmlQuiz\Models\AiHtmlQuizAttempt;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleAssembler;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleNormalizer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

test('published ai html quiz play page and bundle are reachable', function () {
    $user = User::factory()->create(['is_active' => true]);

    $quiz = AiHtmlQuiz::create([
        'title' => 'اختبار دخان',
        'status' => AiHtmlQuiz::STATUS_PUBLISHED,
        'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
        'created_by' => $user->id,
        'bundle_html' => '<button id="done">إنهاء</button>',
        'bundle_css' => 'button{font-size:24px}',
        'bundle_js' => 'document.getElementById("done").onclick=function(){window.parent.postMessage({type:"ile-html-quiz-result",payload:{score:2,total:3,percentage:66.67,answers:[{id:"q1",correct:true}],durationSeconds:12}},"*");};',
    ]);

    $this->actingAs($user)
        ->get(route('ai-html-quizzes.show', $quiz))
        ->assertOk()
        ->assertSee('ile-html-quiz-result', false)
        ->assertSee('ahq-frame', false);

    $this->actingAs($user)
        ->get(route('ai-html-quizzes.bundle', $quiz))
        ->assertOk()
        ->assertSee('Content-Security-Policy', false)
        ->assertSee('ile-html-quiz-result', false)
        ->assertSee('إنهاء');
});

test('attempt api stores result from host postMessage contract', function () {
    $user = User::factory()->create(['is_active' => true]);

    $quiz = AiHtmlQuiz::create([
        'title' => 'اختبار نتيجة',
        'status' => AiHtmlQuiz::STATUS_PUBLISHED,
        'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
        'created_by' => $user->id,
        'bundle_html' => '<div>ok</div>',
        'bundle_css' => '',
        'bundle_js' => 'window.parent.postMessage({type:"ile-html-quiz-result",payload:{score:1,total:1,percentage:100,answers:[],durationSeconds:5}},"*");',
    ]);

    $response = $this->actingAs($user)->postJson(route('ai-html-quizzes.attempts.store', $quiz), [
        'score' => 2,
        'total' => 3,
        'percentage' => 66.67,
        'durationSeconds' => 42,
        'answers' => [
            ['id' => 'q1', 'correct' => true],
            ['id' => 'q2', 'correct' => false],
        ],
    ]);

    $response->assertOk()->assertJson(['ok' => true]);

    $attempt = AiHtmlQuizAttempt::query()->where('ai_html_quiz_id', $quiz->id)->first();
    expect($attempt)->not->toBeNull()
        ->and($attempt->score)->toBe(2)
        ->and($attempt->total)->toBe(3)
        ->and((float) $attempt->percentage)->toBe(66.67)
        ->and($attempt->duration)->toBe(42)
        ->and($attempt->answers_json)->toHaveCount(2);
});

test('bundle apply path normalizes and persists for review', function () {
    $user = User::factory()->create(['is_active' => true]);

    $quiz = AiHtmlQuiz::create([
        'title' => 'مسودة',
        'status' => AiHtmlQuiz::STATUS_DRAFT,
        'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
        'created_by' => $user->id,
    ]);

    $normalizer = app(AiHtmlQuizBundleNormalizer::class);
    $normalized = $normalizer->normalize([
        'title' => 'بعد الاعتماد',
        'html' => '<div>مرحبا<audio src="/sounds/ai-html-quiz/success-01.mp3"></audio></div>',
        'css' => 'div{color:teal}',
        'js' => 'console.log(1);',
        'summary' => 'ملخص',
    ]);

    $quiz->update([
        'title' => $normalized['title'],
        'bundle_html' => $normalized['html'],
        'bundle_css' => $normalized['css'],
        'bundle_js' => $normalized['js'],
        'status' => AiHtmlQuiz::STATUS_REVIEW,
    ]);

    $doc = app(AiHtmlQuizBundleAssembler::class)->assembleDocument($quiz->fresh());

    expect($quiz->fresh()->status)->toBe(AiHtmlQuiz::STATUS_REVIEW)
        ->and($normalized['js'])->toContain('ile-html-quiz-result')
        ->and($doc)->toContain('/sounds/ai-html-quiz/success-01.mp3')
        ->and($doc)->toContain("default-src 'none'");
});
