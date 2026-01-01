<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AIModel;
use App\Services\AI\AIProviderFactory;
use App\Services\AI\AIModelService;
use Illuminate\Support\Facades\Log;

echo "\n[36m═══════════════════════════════════════════════════════════════[0m\n";
echo "[36m  🔍 اختبار موديل OpenAI[0m\n";
echo "[36m═══════════════════════════════════════════════════════════════[0m\n\n";

// الحصول على API Key من المعاملات أو البحث عن موديل OpenAI
$apiKey = $argv[1] ?? null;
$modelKey = $argv[2] ?? 'gpt-3.5-turbo';

if (!$apiKey) {
    // البحث عن موديل OpenAI في قاعدة البيانات
    $openAIModel = AIModel::where('provider', 'openai')
        ->where('is_active', true)
        ->first();
    
    if ($openAIModel) {
        echo "[33m📋 استخدام موديل من قاعدة البيانات:[0m\n";
        echo "  - الاسم: " . $openAIModel->name . "\n";
        echo "  - Model Key: " . $openAIModel->model_key . "\n";
        $apiKey = $openAIModel->getDecryptedApiKey();
        $modelKey = $openAIModel->model_key;
        
        if (empty($apiKey)) {
            echo "[31m✗ API Key غير موجود في قاعدة البيانات![0m\n\n";
            echo "[34m💡 للحصول على API Key:[0m\n";
            echo "[34m  1. اذهب إلى: https://platform.openai.com/api-keys[0m\n";
            echo "[34m  2. أنشئ حساب أو سجل دخول[0m\n";
            echo "[34m  3. اضغط على 'Create new secret key'[0m\n";
            echo "[34m  4. انسخ API Key (يبدأ بـ sk-)[0m\n\n";
            echo "[34m  الاستخدام: php test_openai.php sk-xxxxxxxxxx [model_key][0m\n";
            echo "[34m  مثال: php test_openai.php sk-xxxxxxxxxx gpt-4[0m\n\n";
            exit(1);
        }
    } else {
        echo "[33m⚠️  لم يتم توفير API Key كمعامل ولم يتم العثور على موديل OpenAI في قاعدة البيانات.[0m\n";
        echo "[34m  ℹ️  للحصول على API Key:[0m\n";
        echo "[34m  1. اذهب إلى: https://platform.openai.com/api-keys[0m\n";
        echo "[34m  2. أنشئ حساب أو سجل دخول[0m\n";
        echo "[34m  3. اضغط على 'Create new secret key'[0m\n";
        echo "[34m  4. انسخ API Key (يبدأ بـ sk-)[0m\n\n";
        echo "[34m  الاستخدام: php test_openai.php sk-xxxxxxxxxx [model_key][0m\n";
        echo "[34m  مثال: php test_openai.php sk-xxxxxxxxxx gpt-4[0m\n\n";
        exit(1);
    }
} else {
    echo "[33m📋 API Key المقدم: " . substr($apiKey, 0, 10) . "...[0m\n";
    echo "[33m📋 Model Key: {$modelKey}[0m\n\n";
}

// إنشاء موديل وهمي للاختبار
$model = new AIModel([
    'name' => 'OpenAI Test Model',
    'provider' => 'openai',
    'model_key' => $modelKey,
    'api_key' => $apiKey,
    'base_url' => 'https://api.openai.com/v1',
    'api_endpoint' => '/chat/completions',
    'max_tokens' => 100,
    'temperature' => 0.7,
    'is_active' => true,
    'is_default' => false,
    'priority' => 0,
    'cost_per_1k_tokens' => 0,
    'capabilities' => ['chat'],
    'settings' => [],
]);

echo "[36m═══════════════════════════════════════════════════════════════[0m\n";
echo "[36m  🧪 الاختبارات[0m\n";
echo "[36m═══════════════════════════════════════════════════════════════[0m\n\n";

try {
    $provider = AIProviderFactory::create($model);
    
    // اختبار 1: اختبار الاتصال الأساسي
    echo "[33m📋 اختبار 1: اختبار الاتصال الأساسي[0m\n";
    $startTime = microtime(true);
    $testResult = $provider->testConnection();
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($testResult) {
        echo "[32m  ✅ نجح الاتصال![0m\n";
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
    } else {
        echo "[31m  ❌ فشل الاتصال![0m\n";
        $lastError = $provider->getLastError();
        if ($lastError) {
            echo "[31m  - رسالة الخطأ: {$lastError}[0m\n";
        }
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
        exit(1);
    }
    
    // اختبار 2: محادثة بسيطة
    echo "[33m📋 اختبار 2: محادثة بسيطة[0m\n";
    $startTime = microtime(true);
    $chatResult = $provider->chat([
        ['role' => 'user', 'content' => 'مرحبا، كيف حالك؟ اجب بجملة واحدة فقط.']
    ], ['max_tokens' => 20]);
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($chatResult['success']) {
        echo "[32m  ✅ نجح![0m\n";
        echo "[34m  - الرد: " . substr($chatResult['content'], 0, 100) . "...[0m\n";
        echo "[34m  - Tokens المستخدمة: " . ($chatResult['tokens_used'] ?? 0) . "[0m\n";
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
    } else {
        echo "[31m  ❌ فشل![0m\n";
        echo "[31m  - رسالة الخطأ: " . ($chatResult['error'] ?? 'خطأ غير معروف') . "[0m\n";
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
    }
    
    // اختبار 3: توليد نص طويل
    echo "[33m📋 اختبار 3: توليد نص طويل[0m\n";
    $startTime = microtime(true);
    $longResult = $provider->generateText(
        'اكتب 3 جمل عن الذكاء الاصطناعي بالعربية.',
        ['max_tokens' => 100]
    );
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if (!empty($longResult)) {
        echo "[32m  ✅ نجح![0m\n";
        echo "[34m  - النص المولد: " . substr($longResult, 0, 150) . "...[0m\n";
        echo "[34m  - طول النص: " . strlen($longResult) . " حرف[0m\n";
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
    } else {
        echo "[31m  ❌ فشل![0m\n";
        $lastError = $provider->getLastError();
        if ($lastError) {
            echo "[31m  - رسالة الخطأ: {$lastError}[0m\n";
        }
        echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
    }
    
    // اختبار 4: اختبار Model Key مختلف
    if ($modelKey !== 'gpt-4') {
        echo "[33m📋 اختبار 4: اختبار GPT-4[0m\n";
        $model->model_key = 'gpt-4';
        $provider = AIProviderFactory::create($model);
        
        $startTime = microtime(true);
        $gpt4Result = $provider->chat([
            ['role' => 'user', 'content' => 'Hello']
        ], ['max_tokens' => 5]);
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        
        if ($gpt4Result['success']) {
            echo "[32m  ✅ GPT-4 يعمل![0m\n";
            echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
        } else {
            echo "[31m  ❌ GPT-4 غير متاح![0m\n";
            echo "[31m  - رسالة الخطأ: " . ($gpt4Result['error'] ?? 'خطأ غير معروف') . "[0m\n";
            echo "[34m  - وقت الاستجابة: {$responseTime} مللي ثانية[0m\n\n";
        }
    }
    
    echo "[36m═══════════════════════════════════════════════════════════════[0m\n";
    echo "[32m  ✅ جميع الاختبارات اكتملت![0m\n";
    echo "[36m═══════════════════════════════════════════════════════════════[0m\n";
    
} catch (\Exception $e) {
    echo "[31m  ❌ حدث خطأ غير متوقع: " . $e->getMessage() . "[0m\n";
    Log::error('OpenAI Test Script Exception: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
    ]);
    exit(1);
}

echo "\n";


