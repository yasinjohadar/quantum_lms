<?php

namespace App\Services\AI;

use App\Models\AIModel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AIModelService
{
    /**
     * إنشاء موديل جديد
     */
    public function createModel(array $data, ?User $user = null): AIModel
    {
        if ($user) {
            $data['created_by'] = $user->id;
        }

        // إذا كان هذا الموديل هو الافتراضي، إلغاء الافتراضي من الموديلات الأخرى
        if (isset($data['is_default']) && $data['is_default']) {
            AIModel::where('is_default', true)->update(['is_default' => false]);
        }

        return AIModel::create($data);
    }

    /**
     * تحديث موديل
     */
    public function updateModel(AIModel $model, array $data): AIModel
    {
        // إذا كان هذا الموديل هو الافتراضي، إلغاء الافتراضي من الموديلات الأخرى
        if (isset($data['is_default']) && $data['is_default'] && !$model->is_default) {
            AIModel::where('is_default', true)->where('id', '!=', $model->id)->update(['is_default' => false]);
        }

        $model->update($data);
        return $model->fresh();
    }

    /**
     * حذف موديل
     */
    public function deleteModel(AIModel $model): bool
    {
        // إذا كان الموديل الافتراضي، تعيين موديل آخر كافتراضي
        if ($model->is_default) {
            $newDefault = AIModel::where('id', '!=', $model->id)
                                ->where('is_active', true)
                                ->orderBy('priority', 'desc')
                                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return $model->delete();
    }

    /**
     * اختبار الموديل
     */
    public function testModel(AIModel $model): array
    {
        try {
            $provider = AIProviderFactory::create($model);
            $success = $provider->testConnection();

            return [
                'success' => $success,
                'message' => $success ? 'الاتصال ناجح' : 'فشل الاتصال',
            ];
        } catch (\Exception $e) {
            Log::error('Error testing AI model: ' . $e->getMessage(), ['model_id' => $model->id]);
            return [
                'success' => false,
                'message' => 'خطأ في الاختبار: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * الحصول على الموديل الافتراضي
     */
    public function getDefaultModel(): ?AIModel
    {
        return AIModel::default()->active()->first() 
            ?? AIModel::active()->orderBy('priority', 'desc')->first();
    }

    /**
     * الحصول على أفضل موديل لقدرة معينة
     */
    public function getBestModelFor(string $capability): ?AIModel
    {
        // أولاً: الموديل الافتراضي إذا كان يدعم القدرة
        $default = $this->getDefaultModel();
        if ($default && $default->canHandle($capability)) {
            return $default;
        }

        // ثانياً: البحث عن موديل نشط يدعم القدرة حسب الأولوية
        return AIModel::active()
                     ->byCapability($capability)
                     ->orderBy('priority', 'desc')
                     ->first();
    }

    /**
     * التبديل بين الموديلات
     */
    public function switchModel(AIModel $model): bool
    {
        if (!$model->is_active) {
            return false;
        }

        // تعيين هذا الموديل كافتراضي
        AIModel::where('is_default', true)->update(['is_default' => false]);
        return $model->update(['is_default' => true]);
    }

    /**
     * الحصول على الموديلات المتاحة
     */
    public function getAvailableModels(string $capability = 'all'): Collection
    {
        $query = AIModel::active();

        if ($capability !== 'all') {
            $query->byCapability($capability);
        }

        return $query->orderBy('priority', 'desc')->orderBy('name')->get();
    }
}

