<?php

namespace Database\Seeders;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleNormalizer;
use App\AiHtmlQuiz\Support\AiHtmlQuizQuestionTypes;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiHtmlQuizComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@admin.com')->first()
            ?? User::query()->orderBy('id')->first();

        $title = 'مغامرة الحيوانات الشاملة — كل أنواع الأسئلة';

        AiHtmlQuiz::query()->where('title', $title)->delete();

        $normalizer = app(AiHtmlQuizBundleNormalizer::class);
        $bundle = $normalizer->normalize([
            'title' => $title,
            'html' => $this->html(),
            'css' => $this->css(),
            'js' => $this->js(),
            'summary' => 'اختبار تفاعلي شامل يغطي الأنواع العشرة مع أصوات وتغذية راجعة بصرية.',
            'answer_key' => [
                ['id' => 'q1', 'type' => 'single_choice', 'correct' => 'cheetah'],
                ['id' => 'q2', 'type' => 'true_false', 'correct' => false],
                ['id' => 'q3', 'type' => 'multiple_select', 'correct' => ['dolphin', 'whale', 'fish']],
                ['id' => 'q4', 'type' => 'fill_blank', 'correct' => 'مياو'],
                ['id' => 'q5', 'type' => 'matching', 'correct' => ['lion' => 'roar', 'bee' => 'buzz', 'dog' => 'bark']],
                ['id' => 'q6', 'type' => 'ordering', 'correct' => ['egg', 'caterpillar', 'cocoon', 'butterfly']],
                ['id' => 'q7', 'type' => 'drag_drop', 'correct' => ['camel' => 'desert', 'penguin' => 'ice', 'monkey' => 'jungle']],
                ['id' => 'q8', 'type' => 'click_hotspot', 'correct' => 'lion'],
                ['id' => 'q9', 'type' => 'memory', 'correct' => '3 pairs'],
                ['id' => 'q10', 'type' => 'short_answer', 'correct' => '8'],
            ],
        ]);

        $types = AiHtmlQuizQuestionTypes::keys();

        $quiz = AiHtmlQuiz::create([
            'title' => $bundle['title'],
            'description' => 'اختبار تجريبي تفاعلي جداً يمرّ على كل أنواع الأسئلة: اختيار، صح/خطأ، متعدد، فراغ، مطابقة، ترتيب، سحب، نقطة ساخنة، ذاكرة، وإجابة قصيرة.',
            'status' => AiHtmlQuiz::STATUS_PUBLISHED,
            'schema_version' => AiHtmlQuiz::SCHEMA_VERSION,
            'created_by' => $admin?->id,
            'prompt_meta' => [
                'topic' => 'عالم الحيوانات للأطفال — أصواتها وبيئاتها ودورة حياتها',
                'objectives' => 'تجربة كل أنماط التفاعل في صفحة واحدة مع تغذية راجعة صوتية وبصرية',
                'question_count' => 10,
                'difficulty' => 'easy',
                'question_types' => $types,
                'interaction_hints' => 'seed شامل — تفاعل عالي، حركات، أصوات، شريط تقدّم',
                'seed' => 'AiHtmlQuizComprehensiveSeeder',
            ],
            'bundle_html' => $bundle['html'],
            'bundle_css' => $bundle['css'],
            'bundle_js' => $bundle['js'],
            'answer_key_json' => $bundle['answer_key'],
        ]);

        $this->command?->info('AI HTML quiz created: id='.$quiz->id);
        $this->command?->info('Play: /ai-html-quizzes/'.$quiz->id);
        $this->command?->info('Admin: /admin/ai-html-quizzes/'.$quiz->id.'/edit');
    }

    protected function html(): string
    {
        return <<<'HTML'
<div id="app" class="ahq">
  <div class="ahq-bg" aria-hidden="true">
    <span class="orb orb-a"></span>
    <span class="orb orb-b"></span>
    <span class="orb orb-c"></span>
    <span class="leaf leaf-1">🌿</span>
    <span class="leaf leaf-2">🍃</span>
  </div>

  <header class="ahq-top">
    <div class="ahq-brand">
      <span class="brand-mark">🐾</span>
      <div>
        <strong>مغامرة الحيوانات</strong>
        <small>استكشف · العب · تعلّم</small>
      </div>
    </div>
    <div class="ahq-meta">
      <span id="scorePill" class="pill pill-score">النقاط: 0</span>
      <span id="stepPill" class="pill pill-step">1 / 10</span>
    </div>
  </header>

  <div class="progress-wrap">
    <div class="progress"><i id="progressBar"></i></div>
  </div>

  <section id="screen-intro" class="screen active">
    <div class="hero-card pop-in">
      <div class="badge-ribbon">رحلة استكشاف</div>
      <div class="mascot-wrap"><div class="mascot bounce">🦊</div></div>
      <h1>هل أنت مستكشف حيوانات؟</h1>
      <p class="lead">10 تحديات مختلفة: اختيار، صح وخطأ، سحب، مطابقة، ذاكرة والمزيد — بتصميم حيوي ولعب ممتع!</p>
      <ul class="feat">
        <li><span>🔊</span><div><b>أصوات تفاعلية</b><small>نجاح وخطأ وتشجيع</small></div></li>
        <li><span>✨</span><div><b>حركة وبصريات</b><small>تأثيرات وانتقالات سلسة</small></div></li>
        <li><span>🏆</span><div><b>نتيجة محفوظة</b><small>تظهر في نهاية المغامرة</small></div></li>
      </ul>
      <button type="button" class="btn primary pulse" id="btnStart">ابدأ المغامرة</button>
    </div>
  </section>

  <section id="screen-quiz" class="screen">
    <div id="quizMount" class="quiz-mount"></div>
  </section>

  <section id="screen-result" class="screen">
    <div class="hero-card pop-in">
      <div class="mascot-wrap"><div class="mascot bounce" id="resultEmoji">🏆</div></div>
      <h1 id="resultTitle">أحسنت!</h1>
      <p class="lead" id="resultText"></p>
      <div class="stat-row">
        <div class="stat"><b id="statScore">0</b><span>صح</span></div>
        <div class="stat"><b id="statTotal">10</b><span>الكل</span></div>
        <div class="stat"><b id="statPct">0%</b><span>النسبة</span></div>
      </div>
      <button type="button" class="btn primary" id="btnRestart">إعادة اللعب</button>
    </div>
  </section>

  <audio id="sndOk" preload="auto" src="/sounds/ai-html-quiz/success-01.mp3"></audio>
  <audio id="sndBad" preload="auto" src="/sounds/ai-html-quiz/wrong-01.mp3"></audio>
  <audio id="sndPass" preload="auto" src="/sounds/ai-html-quiz/pass-01.mp3"></audio>
  <audio id="sndGo" preload="auto" src="/sounds/ai-html-quiz/continue-01.mp3"></audio>
  <div id="toast" class="toast" hidden></div>
  <div id="fx" class="fx" aria-hidden="true"></div>
</div>
HTML;
    }

    protected function css(): string
    {
        return <<<'CSS'
:root {
  --font: "Alexandria", "Segoe UI", Tahoma, sans-serif;
  --ink: #073b36;
  --muted: #5b6b73;
  --teal: #0f766e;
  --teal-bright: #14b8a6;
  --sky: #0284c7;
  --sun: #f59e0b;
  --ok: #15803d;
  --bad: #dc2626;
  --card: rgba(255,255,255,.86);
  --radius: 26px;
  --shadow: 0 24px 60px rgba(7, 59, 54, 0.16);
}
* { box-sizing: border-box; }
html, body { margin:0; min-height:100%; }
body {
  font-family: var(--font);
  color: var(--ink);
  background: #dff7f1;
  -webkit-font-smoothing: antialiased;
}
.ahq {
  position: relative;
  min-height: 100vh;
  padding: 1rem 1.1rem 2.4rem;
  overflow-x: hidden;
}
.ahq-bg {
  position: fixed; inset: 0; z-index: -1; overflow: hidden;
  background:
    radial-gradient(980px 520px at 8% -8%, rgba(45,212,191,.55), transparent 58%),
    radial-gradient(860px 480px at 100% 0%, rgba(56,189,248,.42), transparent 52%),
    radial-gradient(700px 420px at 50% 110%, rgba(250,204,21,.28), transparent 55%),
    linear-gradient(165deg, #ecfdf5 0%, #e0f2fe 42%, #fef3c7 100%);
}
.orb {
  position: absolute; border-radius: 50%; filter: blur(2px);
  opacity: .55; animation: floaty 9s ease-in-out infinite;
}
.orb-a { width: 180px; height: 180px; background: rgba(20,184,166,.35); top: 12%; left: -40px; }
.orb-b { width: 140px; height: 140px; background: rgba(56,189,248,.3); top: 55%; right: -30px; animation-delay: -2s; }
.orb-c { width: 90px; height: 90px; background: rgba(245,158,11,.28); bottom: 8%; left: 30%; animation-delay: -4s; }
.leaf { position:absolute; font-size:1.6rem; opacity:.35; animation: floaty 7s ease-in-out infinite; }
.leaf-1 { top: 18%; right: 8%; }
.leaf-2 { bottom: 16%; left: 6%; animation-delay: -3s; }
@keyframes floaty {
  0%,100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-14px) rotate(4deg); }
}

.ahq-top {
  display: flex; justify-content: space-between; align-items: center;
  gap: .85rem; margin-bottom: .85rem;
}
.ahq-brand {
  display: flex; align-items: center; gap: .7rem;
}
.brand-mark {
  width: 48px; height: 48px; border-radius: 16px;
  display: grid; place-items: center; font-size: 1.35rem;
  background: linear-gradient(145deg, #fff, #ccfbf1);
  border: 1px solid rgba(15,118,110,.18);
  box-shadow: 0 10px 24px rgba(15,118,110,.14);
}
.ahq-brand strong {
  display: block; font-size: 1.05rem; font-weight: 800; letter-spacing: -.02em;
  background: linear-gradient(120deg, #0f766e, #0369a1 70%);
  -webkit-background-clip: text; background-clip: text; color: transparent;
}
.ahq-brand small { color: var(--muted); font-size: .72rem; font-weight: 600; }
.ahq-meta { display:flex; gap: .45rem; flex-wrap: wrap; }
.pill {
  font-family: var(--font);
  border-radius: 999px; padding: .38rem .85rem; font-size: .78rem; font-weight: 800;
  border: 1px solid transparent; box-shadow: 0 8px 18px rgba(7,59,54,.08);
}
.pill-score { background: linear-gradient(135deg,#ecfdf5,#ccfbf1); color:#0f766e; border-color:#99f6e4; }
.pill-step { background: linear-gradient(135deg,#eff6ff,#dbeafe); color:#1d4ed8; border-color:#bfdbfe; }

.progress-wrap { margin-bottom: 1.1rem; }
.progress {
  height: 12px; border-radius: 999px; overflow: hidden;
  background: rgba(255,255,255,.72);
  border: 1px solid rgba(15,118,110,.14);
  box-shadow: inset 0 1px 2px rgba(7,59,54,.06);
}
.progress i {
  display:block; height:100%; width:0%;
  background: linear-gradient(90deg, #14b8a6 0%, #0ea5e9 55%, #f59e0b 100%);
  transition: width .4s cubic-bezier(.2,.8,.2,1);
  border-radius: inherit;
  box-shadow: 0 0 16px rgba(20,184,166,.45);
}

.screen { display:none; }
.screen.active { display:block; animation: fadeUp .42s cubic-bezier(.2,.8,.2,1); }
@keyframes fadeUp { from { opacity:0; transform: translateY(16px) scale(.985); } to { opacity:1; transform:none; } }
@keyframes bounce { 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(-10px);} }
@keyframes pulse { 0%,100%{ transform: scale(1);} 50%{ transform: scale(1.03);} }
@keyframes shake { 0%,100%{ transform: translateX(0);} 25%{ transform: translateX(-7px);} 75%{ transform: translateX(7px);} }
@keyframes pop { from { opacity:0; transform: scale(.9) translateY(12px);} to { opacity:1; transform:none;} }
.bounce { animation: bounce 1.5s ease-in-out infinite; }
.pulse { animation: pulse 1.7s ease-in-out infinite; }
.pop-in { animation: pop .45s cubic-bezier(.2,.8,.2,1); }

.hero-card, .q-card {
  position: relative;
  max-width: 740px; margin: 0 auto;
  background: var(--card);
  backdrop-filter: blur(16px);
  border-radius: var(--radius);
  padding: 1.45rem 1.45rem 1.65rem;
  box-shadow: var(--shadow);
  border: 1px solid rgba(255,255,255,.7);
  outline: 1px solid rgba(15,118,110,.1);
}
.hero-card::before, .q-card::before {
  content: "";
  position: absolute; inset: 0 0 auto 0; height: 5px; border-radius: var(--radius) var(--radius) 0 0;
  background: linear-gradient(90deg, #14b8a6, #0ea5e9, #f59e0b);
}
.badge-ribbon {
  display: inline-block; margin: 0 auto .85rem; width: fit-content;
  background: linear-gradient(135deg, #042f2e, #0f766e);
  color: #ecfdf5; font-size: .72rem; font-weight: 800; letter-spacing: .04em;
  padding: .35rem .8rem; border-radius: 999px;
}
.mascot-wrap {
  width: 92px; height: 92px; margin: 0 auto .7rem;
  border-radius: 28px;
  display: grid; place-items: center;
  background: linear-gradient(160deg, #fff 10%, #ccfbf1 100%);
  border: 1px solid rgba(15,118,110,.16);
  box-shadow: 0 14px 30px rgba(15,118,110,.14);
}
.mascot { font-size: 3.1rem; line-height: 1; }
h1 {
  margin: .15rem 0 .55rem; font-size: clamp(1.35rem, 3.5vw, 1.85rem);
  text-align: center; font-weight: 800; letter-spacing: -.03em; line-height: 1.35;
}
.lead, .q-stem {
  text-align: center; color: var(--muted); margin: 0 0 1.15rem;
  line-height: 1.85; font-size: .98rem; font-weight: 500;
}
.q-stem { font-size: 1.12rem; font-weight: 700; color: var(--ink); }

.feat { list-style: none; padding: 0; margin: 0 0 1.25rem; display: grid; gap: .55rem; }
.feat li {
  display: flex; align-items: center; gap: .75rem;
  background: linear-gradient(135deg, rgba(240,253,250,.95), rgba(224,242,254,.9));
  border: 1px solid rgba(15,118,110,.12);
  border-radius: 16px; padding: .7rem .85rem;
}
.feat li > span {
  width: 42px; height: 42px; border-radius: 14px; display: grid; place-items: center;
  background: #fff; box-shadow: 0 6px 14px rgba(7,59,54,.08); font-size: 1.15rem;
}
.feat b { display:block; font-size: .92rem; font-weight: 800; }
.feat small { color: var(--muted); font-size: .75rem; font-weight: 500; }

.btn {
  appearance: none; border: 0; border-radius: 16px;
  padding: .9rem 1.25rem; font-family: var(--font); font-weight: 800; font-size: 1.02rem;
  cursor: pointer; transition: transform .15s, box-shadow .15s, filter .15s;
}
.btn:active { transform: scale(.97); }
.btn.primary {
  width: 100%; color: #fff;
  background: linear-gradient(135deg, #0f766e 0%, #0d9488 40%, #0284c7 100%);
  box-shadow: 0 14px 32px rgba(15,118,110,.38), inset 0 1px 0 rgba(255,255,255,.25);
}
.btn.primary:hover { filter: brightness(1.06); }
.btn.wide { width: 100%; margin-top: .85rem; }

.q-type {
  margin: 0 auto .65rem; width: fit-content;
  font-size: .72rem; font-weight: 800; letter-spacing: .06em;
  color: #0f766e; background: #ecfdf5; border: 1px solid #99f6e4;
  padding: .28rem .7rem; border-radius: 999px; text-align: center;
}

.options { display: grid; gap: .65rem; }
.opt, .chip, .tf-btn, .zone, .hot, .mem, .order-item, .match-item {
  font-family: var(--font);
  border-radius: 16px; border: 2px solid rgba(15,118,110,.14);
  background: linear-gradient(180deg, #fff, #f8fffd);
  padding: .95rem 1.05rem; cursor: pointer; font-weight: 700;
  transition: transform .14s, border-color .14s, background .14s, box-shadow .14s;
  user-select: none; box-shadow: 0 8px 18px rgba(7,59,54,.06);
}
.opt:hover, .chip:hover, .tf-btn:hover, .hot:hover, .order-item:hover, .match-item:hover {
  transform: translateY(-3px);
  border-color: #2dd4bf;
  box-shadow: 0 14px 28px rgba(15,118,110,.14);
}
.opt.selected, .chip.selected, .tf-btn.selected, .match-item.selected {
  border-color: var(--teal-bright); background: linear-gradient(180deg, #ccfbf1, #e0f2fe);
}
.opt.correct, .chip.correct, .tf-btn.correct, .zone.correct, .hot.correct, .mem.correct {
  border-color: var(--ok); background: linear-gradient(180deg, #dcfce7, #bbf7d0);
}
.opt.wrong, .chip.wrong, .tf-btn.wrong, .zone.wrong, .hot.wrong {
  border-color: var(--bad); background: linear-gradient(180deg, #fee2e2, #fecaca);
  animation: shake .38s ease;
}

.tf-row { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem; }
.chips { display: flex; flex-wrap: wrap; gap: .55rem; justify-content: center; }
.chip { min-width: 44%; text-align: center; }
.blank-row {
  display: flex; flex-wrap: wrap; gap: .55rem; align-items: center; justify-content: center;
  font-size: 1.12rem; font-weight: 800;
}
.blank-row input, .short-input {
  font-family: var(--font); font-weight: 700;
  border: 2px dashed var(--teal-bright); border-radius: 14px;
  padding: .65rem .8rem; font-size: 1.08rem; text-align: center;
  min-width: 130px; outline: none; background: #f0fdfa;
  box-shadow: inset 0 2px 6px rgba(15,118,110,.06);
}
.blank-row input:focus, .short-input:focus {
  border-style: solid; border-color: var(--sky);
  box-shadow: 0 0 0 4px rgba(14,165,233,.18);
}

.match-grid, .drag-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
@media (max-width: 560px) {
  .match-grid, .drag-grid { grid-template-columns: 1fr; }
  .ahq-brand small { display: none; }
}
.match-col { display: grid; gap: .5rem; }
.order-list { display: grid; gap: .5rem; }
.order-item { display: flex; align-items: center; gap: .65rem; }
.order-item .n {
  width: 30px; height: 30px; border-radius: 50%;
  background: linear-gradient(145deg, #ccfbf1, #bae6fd);
  display: grid; place-items: center; font-size: .85rem; font-weight: 800;
}
.order-actions { display: flex; gap: .35rem; margin-inline-start: auto; }
.order-actions button {
  border: 0; background: #ecfdf5; border-radius: 10px;
  width: 34px; height: 34px; cursor: pointer; font-weight: 800; color: var(--teal);
}

.zone {
  min-height: 84px; border-style: dashed;
  display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; justify-content: center;
}
.zone.over { background: #ecfeff; border-color: #22d3ee; }
.token {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .5rem .85rem; border-radius: 999px; font-family: var(--font); font-weight: 800;
  background: linear-gradient(135deg, #99f6e4, #bae6fd);
  cursor: grab; border: 1px solid #5eead4; box-shadow: 0 8px 16px rgba(14,165,233,.16);
}
.token.dragging { opacity: .45; }

.scene {
  position: relative; height: 230px; border-radius: 20px; overflow: hidden;
  background:
    radial-gradient(circle at 80% 18%, rgba(255,255,255,.55), transparent 28%),
    linear-gradient(#7dd3fc 0 40%, #86efac 40% 68%, #a16207 68% 100%);
  border: 2px solid rgba(15,118,110,.2);
  margin-bottom: .85rem;
  box-shadow: inset 0 -20px 40px rgba(0,0,0,.08);
}
.hot {
  position: absolute; width: 68px; height: 68px; border-radius: 50%;
  display: grid; place-items: center; font-size: 1.85rem; padding: 0; border-width: 3px;
  background: rgba(255,255,255,.62); backdrop-filter: blur(3px);
}
.hot[data-id="tree"] { left: 8%; top: 38%; }
.hot[data-id="lion"] { left: 42%; top: 48%; }
.hot[data-id="bird"] { left: 72%; top: 18%; }

.mem-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; }
.mem {
  aspect-ratio: 1; display: grid; place-items: center; font-size: 1.8rem; border: 0;
  background: linear-gradient(145deg, #0f766e, #0284c7);
  color: #fff; box-shadow: 0 12px 24px rgba(2,132,199,.28);
}
.mem.flipped, .mem.matched {
  background: #fff; color: var(--ink); border: 2px solid #99f6e4;
  box-shadow: 0 10px 20px rgba(15,118,110,.12);
}
.mem.matched { background: #dcfce7; border-color: var(--ok); pointer-events: none; }

.feedback {
  text-align: center; min-height: 1.5em; margin: .75rem 0 .15rem;
  font-weight: 800; font-size: .95rem;
}
.feedback.ok { color: var(--ok); }
.feedback.bad { color: var(--bad); }

.stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: .55rem; margin: 1.1rem 0; }
.stat {
  text-align: center;
  background: linear-gradient(160deg, #f0fdfa, #e0f2fe);
  border: 1px solid rgba(15,118,110,.12);
  border-radius: 18px; padding: .85rem .5rem;
}
.stat b { display: block; font-size: 1.45rem; font-weight: 800; color: var(--teal); letter-spacing: -.03em; }
.stat span { font-size: .72rem; color: var(--muted); font-weight: 600; }

.toast {
  position: fixed; left: 50%; bottom: 1.35rem; transform: translateX(-50%);
  background: linear-gradient(135deg, #042f2e, #0f766e); color: #fff;
  padding: .7rem 1.15rem; border-radius: 999px; font-weight: 800; z-index: 20;
  box-shadow: 0 16px 36px rgba(7,59,54,.35); font-family: var(--font);
}
.fx { pointer-events: none; position: fixed; inset: 0; overflow: hidden; z-index: 15; }
.fx span {
  position: absolute; top: -12px; font-size: 1.25rem;
  animation: fall 1.7s linear forwards;
}
@keyframes fall { to { transform: translateY(110vh) rotate(420deg); opacity: 0; } }
CSS;
    }

    protected function js(): string
    {
        return <<<'JS'
(function () {
  var TOTAL = 10;
  var score = 0;
  var index = 0;
  var answers = [];
  var startedAt = Date.now();
  var locked = false;

  var screens = {
    intro: document.getElementById('screen-intro'),
    quiz: document.getElementById('screen-quiz'),
    result: document.getElementById('screen-result')
  };
  var mount = document.getElementById('quizMount');
  var progressBar = document.getElementById('progressBar');
  var scorePill = document.getElementById('scorePill');
  var stepPill = document.getElementById('stepPill');
  var toast = document.getElementById('toast');

  function play(id) {
    var el = document.getElementById(id);
    if (!el) return;
    try { el.currentTime = 0; el.play().catch(function () {}); } catch (e) {}
  }

  function showScreen(name) {
    Object.keys(screens).forEach(function (k) { screens[k].classList.toggle('active', k === name); });
  }

  function updateHud() {
    scorePill.textContent = 'النقاط: ' + score;
    stepPill.textContent = Math.min(index + 1, TOTAL) + ' / ' + TOTAL;
    progressBar.style.width = Math.round((index / TOTAL) * 100) + '%';
  }

  function showToast(msg) {
    toast.hidden = false;
    toast.textContent = msg;
    clearTimeout(showToast._t);
    showToast._t = setTimeout(function () { toast.hidden = true; }, 1400);
  }

  function burst() {
    var fx = document.getElementById('fx');
    var emojis = ['⭐', '🎉', '✨', '🐾', '💚'];
    for (var i = 0; i < 18; i++) {
      var s = document.createElement('span');
      s.textContent = emojis[i % emojis.length];
      s.style.left = Math.random() * 100 + '%';
      s.style.animationDelay = (Math.random() * 0.4) + 's';
      fx.appendChild(s);
      (function (node) { setTimeout(function () { node.remove(); }, 1800); })(s);
    }
  }

  function mark(result, type, detail) {
    answers.push({ id: 'q' + (index + 1), type: type, correct: !!result.ok, detail: detail || null });
    if (result.ok) {
      score += 1;
      play('sndOk');
      burst();
      showToast('أحسنت! 🌟');
    } else {
      play('sndBad');
      showToast('حاول مرة أخرى في السؤال التالي');
    }
    updateHud();
    locked = true;
    setTimeout(function () {
      locked = false;
      index += 1;
      if (index >= TOTAL) finish();
      else renderQuestion();
    }, 900);
  }

  function card(typeLabel, stem, bodyHtml) {
    return '' +
      '<div class="q-card pop-in">' +
        '<div class="q-type">' + typeLabel + '</div>' +
        '<div class="mascot">' + (['🦁','🐘','🦋','🐠','🐶'][index % 5]) + '</div>' +
        '<p class="q-stem">' + stem + '</p>' +
        bodyHtml +
        '<div class="feedback" id="fb"></div>' +
      '</div>';
  }

  function setFb(ok, text) {
    var fb = document.getElementById('fb');
    if (!fb) return;
    fb.className = 'feedback ' + (ok ? 'ok' : 'bad');
    fb.textContent = text;
  }

  var questions = [
    function singleChoice() {
      mount.innerHTML = card('اختيار من متعدد', 'ما أسرع حيوان بري؟',
        '<div class="options" id="opts">' +
          opt('turtle', '🐢 سلحفاة') +
          opt('cheetah', '🐆 فهد') +
          opt('elephant', '🐘 فيل') +
          opt('snail', '🐌 حلزون') +
        '</div>'
      );
      function opt(id, label) {
        return '<button type="button" class="opt" data-id="' + id + '">' + label + '</button>';
      }
      mount.querySelectorAll('.opt').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (locked) return;
          var ok = btn.getAttribute('data-id') === 'cheetah';
          mount.querySelectorAll('.opt').forEach(function (b) {
            if (b.getAttribute('data-id') === 'cheetah') b.classList.add('correct');
          });
          btn.classList.add(ok ? 'correct' : 'wrong');
          setFb(ok, ok ? 'نعم! الفهد سريع جداً' : 'الإجابة: الفهد');
          mark({ ok: ok }, 'single_choice', btn.getAttribute('data-id'));
        });
      });
    },

    function trueFalse() {
      mount.innerHTML = card('صح أو خطأ', 'الفيل يستطيع الطيران مثل الطيور.',
        '<div class="tf-row">' +
          '<button type="button" class="tf-btn" data-v="true">✅ صح</button>' +
          '<button type="button" class="tf-btn" data-v="false">❌ خطأ</button>' +
        '</div>'
      );
      mount.querySelectorAll('.tf-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (locked) return;
          var ok = btn.getAttribute('data-v') === 'false';
          btn.classList.add(ok ? 'correct' : 'wrong');
          mount.querySelector('[data-v="false"]').classList.add('correct');
          setFb(ok, ok ? 'صحيح! الفيل لا يطير' : 'العبارة خاطئة');
          mark({ ok: ok }, 'true_false', btn.getAttribute('data-v'));
        });
      });
    },

    function multipleSelect() {
      mount.innerHTML = card('اختيار متعدد', 'اختر كل الحيوانات التي تعيش في الماء (يمكن أكثر من واحد):',
        '<div class="chips" id="chips">' +
          chip('dolphin', '🐬 دولفين') +
          chip('cat', '🐱 قطة') +
          chip('whale', '🐋 حوت') +
          chip('fish', '🐟 سمكة') +
          chip('camel', '🐪 جمل') +
        '</div>' +
        '<button type="button" class="btn primary wide" id="btnMulti">تأكيد الاختيارات</button>'
      );
      function chip(id, label) {
        return '<button type="button" class="chip" data-id="' + id + '">' + label + '</button>';
      }
      var selected = {};
      mount.querySelectorAll('.chip').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (locked) return;
          var id = btn.getAttribute('data-id');
          selected[id] = !selected[id];
          btn.classList.toggle('selected', !!selected[id]);
        });
      });
      document.getElementById('btnMulti').addEventListener('click', function () {
        if (locked) return;
        var need = { dolphin: 1, whale: 1, fish: 1 };
        var ok = true;
        Object.keys(need).forEach(function (k) { if (!selected[k]) ok = false; });
        Object.keys(selected).forEach(function (k) {
          if (selected[k] && !need[k]) ok = false;
        });
        ['dolphin', 'whale', 'fish'].forEach(function (k) {
          var el = mount.querySelector('[data-id="' + k + '"]');
          if (el) el.classList.add('correct');
        });
        Object.keys(selected).forEach(function (k) {
          if (selected[k] && !need[k]) {
            var bad = mount.querySelector('[data-id="' + k + '"]');
            if (bad) bad.classList.add('wrong');
          }
        });
        setFb(ok, ok ? 'ممتاز! كلها بحرية' : 'الصحيح: دولفين، حوت، سمكة');
        mark({ ok: ok }, 'multiple_select', Object.keys(selected).filter(function (k) { return selected[k]; }));
      });
    },

    function fillBlank() {
      mount.innerHTML = card('أكمل الفراغ', 'ماذا تقول القطة؟',
        '<div class="blank-row"><span>القطة تقول</span><input id="blank" maxlength="10" placeholder="..."><span>!</span></div>' +
        '<button type="button" class="btn primary wide" id="btnBlank">تحقق</button>'
      );
      document.getElementById('btnBlank').addEventListener('click', function () {
        if (locked) return;
        var val = (document.getElementById('blank').value || '').trim();
        var norm = val.replace(/\s+/g, '');
        var accepted = ['مياو', 'مواء', 'ميو', 'meow', 'Meow', 'MEOW'];
        var ok = accepted.indexOf(norm) !== -1 || accepted.indexOf(norm.toLowerCase()) !== -1;
        if (!ok && (norm.indexOf('ميا') === 0 || norm.indexOf('موا') === 0)) ok = true;
        setFb(ok, ok ? 'نعم! مياو 🐱' : 'الإجابة الشائعة: مياو');
        mark({ ok: ok }, 'fill_blank', val);
      });
    },

    function matching() {
      var left = [
        { id: 'lion', label: '🦁 أسد' },
        { id: 'bee', label: '🐝 نحلة' },
        { id: 'dog', label: '🐶 كلب' }
      ];
      var right = [
        { id: 'buzz', label: 'طنين' },
        { id: 'bark', label: 'نباح' },
        { id: 'roar', label: 'زئير' }
      ];
      var map = { lion: 'roar', bee: 'buzz', dog: 'bark' };
      var pickL = null;
      var links = {};
      mount.innerHTML = card('مطابقة', 'اربط كل حيوان بالصوت المناسب (اضغط يسار ثم يمين):',
        '<div class="match-grid">' +
          '<div class="match-col" id="leftCol"></div>' +
          '<div class="match-col" id="rightCol"></div>' +
        '</div>' +
        '<button type="button" class="btn primary wide" id="btnMatch">تأكيد المطابقة</button>'
      );
      var leftCol = document.getElementById('leftCol');
      var rightCol = document.getElementById('rightCol');
      left.forEach(function (item) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'match-item'; b.dataset.id = item.id; b.textContent = item.label;
        b.addEventListener('click', function () {
          if (locked) return;
          pickL = item.id;
          leftCol.querySelectorAll('.match-item').forEach(function (x) { x.classList.remove('selected'); });
          b.classList.add('selected');
        });
        leftCol.appendChild(b);
      });
      right.forEach(function (item) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'match-item'; b.dataset.id = item.id; b.textContent = item.label;
        b.addEventListener('click', function () {
          if (locked || !pickL) return;
          links[pickL] = item.id;
          var lb = leftCol.querySelector('[data-id="' + pickL + '"]');
          if (lb) lb.textContent = lb.textContent.split('→')[0].trim() + ' → ' + item.label;
          pickL = null;
          leftCol.querySelectorAll('.match-item').forEach(function (x) { x.classList.remove('selected'); });
        });
        rightCol.appendChild(b);
      });
      document.getElementById('btnMatch').addEventListener('click', function () {
        if (locked) return;
        var ok = Object.keys(map).every(function (k) { return links[k] === map[k]; });
        setFb(ok, ok ? 'مطابقة رائعة!' : 'راجع أصوات الحيوانات');
        mark({ ok: ok }, 'matching', links);
      });
    },

    function ordering() {
      var items = [
        { id: 'butterfly', label: '🦋 فراشة' },
        { id: 'egg', label: '🥚 بيضة' },
        { id: 'cocoon', label: '🕸️ شرنقة' },
        { id: 'caterpillar', label: '🐛 يرقة' }
      ];
      var correct = ['egg', 'caterpillar', 'cocoon', 'butterfly'];
      mount.innerHTML = card('ترتيب', 'رتّب مراحل دورة حياة الفراشة من البداية للنهاية:',
        '<div class="order-list" id="orderList"></div>' +
        '<button type="button" class="btn primary wide" id="btnOrder">تأكيد الترتيب</button>'
      );
      var list = document.getElementById('orderList');
      function render() {
        list.innerHTML = '';
        items.forEach(function (item, i) {
          var row = document.createElement('div');
          row.className = 'order-item';
          row.innerHTML = '<span class="n">' + (i + 1) + '</span><span>' + item.label + '</span>' +
            '<div class="order-actions">' +
              '<button type="button" data-dir="-1" data-i="' + i + '">↑</button>' +
              '<button type="button" data-dir="1" data-i="' + i + '">↓</button>' +
            '</div>';
          list.appendChild(row);
        });
        list.querySelectorAll('button').forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (locked) return;
            var i = Number(btn.getAttribute('data-i'));
            var dir = Number(btn.getAttribute('data-dir'));
            var j = i + dir;
            if (j < 0 || j >= items.length) return;
            var tmp = items[i]; items[i] = items[j]; items[j] = tmp;
            render();
          });
        });
      }
      render();
      document.getElementById('btnOrder').addEventListener('click', function () {
        if (locked) return;
        var ids = items.map(function (x) { return x.id; });
        var ok = ids.join() === correct.join();
        setFb(ok, ok ? 'ترتيب ممتاز!' : 'الترتيب: بيضة ← يرقة ← شرنقة ← فراشة');
        mark({ ok: ok }, 'ordering', ids);
      });
    },

    function dragDrop() {
      var tokens = [
        { id: 'camel', label: '🐪 جمل' },
        { id: 'penguin', label: '🐧 بطريق' },
        { id: 'monkey', label: '🐒 قرد' }
      ];
      var zones = [
        { id: 'desert', label: '🏜️ صحراء' },
        { id: 'ice', label: '🧊 قطبية' },
        { id: 'jungle', label: '🌴 غابة' }
      ];
      var map = { camel: 'desert', penguin: 'ice', monkey: 'jungle' };
      var placed = {};
      mount.innerHTML = card('سحب وإفلات', 'اسحب كل حيوان إلى بيئته المناسبة:',
        '<div class="chips" id="tokenBank"></div>' +
        '<div class="drag-grid" id="zones"></div>' +
        '<button type="button" class="btn primary wide" id="btnDrag">تأكيد</button>'
      );
      var bank = document.getElementById('tokenBank');
      var zonesEl = document.getElementById('zones');
      tokens.forEach(function (t) {
        var el = document.createElement('div');
        el.className = 'token'; el.draggable = true; el.dataset.id = t.id; el.textContent = t.label;
        el.addEventListener('dragstart', function (e) {
          e.dataTransfer.setData('text/plain', t.id);
          el.classList.add('dragging');
        });
        el.addEventListener('dragend', function () { el.classList.remove('dragging'); });
        bank.appendChild(el);
      });
      zones.forEach(function (z) {
        var box = document.createElement('div');
        box.className = 'zone'; box.dataset.id = z.id;
        box.innerHTML = '<strong>' + z.label + '</strong>';
        box.addEventListener('dragover', function (e) { e.preventDefault(); box.classList.add('over'); });
        box.addEventListener('dragleave', function () { box.classList.remove('over'); });
        box.addEventListener('drop', function (e) {
          e.preventDefault();
          box.classList.remove('over');
          if (locked) return;
          var id = e.dataTransfer.getData('text/plain');
          var token = bank.querySelector('[data-id="' + id + '"]') || document.querySelector('.token[data-id="' + id + '"]');
          if (!token) return;
          Object.keys(placed).forEach(function (k) { if (placed[k] === z.id) delete placed[k]; });
          placed[id] = z.id;
          box.appendChild(token);
        });
        zonesEl.appendChild(box);
      });
      document.getElementById('btnDrag').addEventListener('click', function () {
        if (locked) return;
        var ok = Object.keys(map).every(function (k) { return placed[k] === map[k]; });
        zonesEl.querySelectorAll('.zone').forEach(function (z) {
          z.classList.add(ok ? 'correct' : 'wrong');
        });
        setFb(ok, ok ? 'بيئات صحيحة!' : 'الجمل→صحراء، البطريق→قطبية، القرد→غابة');
        mark({ ok: ok }, 'drag_drop', placed);
      });
    },

    function hotspot() {
      mount.innerHTML = card('انقر على المنطقة', 'أين الأسد في المشهد؟ اضغط عليه!',
        '<div class="scene">' +
          '<button type="button" class="hot" data-id="tree" title="شجرة">🌳</button>' +
          '<button type="button" class="hot" data-id="lion" title="أسد">🦁</button>' +
          '<button type="button" class="hot" data-id="bird" title="طائر">🐦</button>' +
        '</div>'
      );
      mount.querySelectorAll('.hot').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (locked) return;
          var ok = btn.getAttribute('data-id') === 'lion';
          btn.classList.add(ok ? 'correct' : 'wrong');
          if (!ok) mount.querySelector('[data-id="lion"]').classList.add('correct');
          setFb(ok, ok ? 'وجدته! 🦁' : 'الأسد في الوسط');
          mark({ ok: ok }, 'click_hotspot', btn.getAttribute('data-id'));
        });
      });
    },

    function memory() {
      var faces = ['🦊', '🐸', '🐼', '🦊', '🐸', '🐼'];
      for (var i = faces.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = faces[i]; faces[i] = faces[j]; faces[j] = tmp;
      }
      var opened = [];
      var matched = 0;
      var busy = false;
      mount.innerHTML = card('بطاقات ذاكرة', 'اقلب البطاقات وأوجد 3 أزواج متطابقة:',
        '<div class="mem-grid" id="memGrid"></div>'
      );
      var grid = document.getElementById('memGrid');
      faces.forEach(function (face, idx) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'mem';
        btn.dataset.face = face;
        btn.dataset.idx = String(idx);
        btn.textContent = '?';
        btn.addEventListener('click', function () {
          if (locked || busy || btn.classList.contains('flipped') || btn.classList.contains('matched')) return;
          btn.classList.add('flipped');
          btn.textContent = face;
          opened.push(btn);
          if (opened.length < 2) return;
          busy = true;
          var a = opened[0], b = opened[1];
          opened = [];
          if (a.dataset.face === b.dataset.face) {
            a.classList.add('matched', 'correct');
            b.classList.add('matched', 'correct');
            matched += 1;
            play('sndOk');
            busy = false;
            if (matched >= 3) {
              setFb(true, 'ذاكرة ذهبية!');
              mark({ ok: true }, 'memory', { pairs: 3 });
            }
          } else {
            setTimeout(function () {
              a.classList.remove('flipped'); a.textContent = '?';
              b.classList.remove('flipped'); b.textContent = '?';
              busy = false;
            }, 650);
          }
        });
        grid.appendChild(btn);
      });
    },

    function shortAnswer() {
      mount.innerHTML = card('إجابة قصيرة', 'كم عدد أرجل العنكبوت؟ اكتب الرقم فقط',
        '<div style="text-align:center"><input class="short-input" id="short" inputmode="numeric" placeholder="؟"></div>' +
        '<button type="button" class="btn primary wide" id="btnShort">تحقق</button>'
      );
      document.getElementById('btnShort').addEventListener('click', function () {
        if (locked) return;
        var val = (document.getElementById('short').value || '').trim();
        var ok = val === '8' || val === '٨';
        setFb(ok, ok ? 'صحيح! للعنكبوت 8 أرجل' : 'الإجابة: 8');
        mark({ ok: ok }, 'short_answer', val);
      });
    }
  ];

  function renderQuestion() {
    updateHud();
    progressBar.style.width = Math.round((index / TOTAL) * 100) + '%';
    play('sndGo');
    questions[index]();
  }

  function finish() {
    progressBar.style.width = '100%';
    var pct = Math.round((score / TOTAL) * 100);
    document.getElementById('statScore').textContent = String(score);
    document.getElementById('statTotal').textContent = String(TOTAL);
    document.getElementById('statPct').textContent = pct + '%';
    document.getElementById('resultTitle').textContent = pct >= 70 ? 'مستكشف ممتاز!' : 'بداية رائعة!';
    document.getElementById('resultText').textContent = 'أحرزت ' + score + ' من ' + TOTAL + ' — جرب مرة أخرى لتحطيم الرقم.';
    document.getElementById('resultEmoji').textContent = pct >= 70 ? '🏆' : '🌱';
    showScreen('result');
    play('sndPass');
    burst();
    var durationSeconds = Math.max(1, Math.round((Date.now() - startedAt) / 1000));
    window.parent.postMessage({
      type: 'ile-html-quiz-result',
      payload: {
        score: score,
        total: TOTAL,
        percentage: pct,
        answers: answers,
        durationSeconds: durationSeconds
      }
    }, '*');
  }

  document.getElementById('btnStart').addEventListener('click', function () {
    startedAt = Date.now();
    score = 0; index = 0; answers = [];
    showScreen('quiz');
    renderQuestion();
  });

  document.getElementById('btnRestart').addEventListener('click', function () {
    startedAt = Date.now();
    score = 0; index = 0; answers = []; locked = false;
    showScreen('quiz');
    renderQuestion();
  });

  updateHud();
})();
JS;
    }
}
