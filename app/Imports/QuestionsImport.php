<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class QuestionsImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $unitIds = [];
    protected $columnMapping = [];

    public function __construct(array $columnMapping = [])
    {
        $this->columnMapping = $columnMapping;
    }

    /**
     * معالجة البيانات المستوردة
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $rowNumber = $index + 2; // +2 لأن الصف الأول هو headers و index يبدأ من 0
                
                // تنظيف البيانات
                $data = $this->cleanRowData($row);
                
                // التحقق من صحة البيانات
                $validator = $this->validateRow($data, $rowNumber);
                
                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all(),
                        'data' => $data
                    ];
                    $this->errorCount++;
                    continue;
                }

                // إنشاء السؤال
                DB::beginTransaction();
                
                $question = $this->createQuestion($data);
                
                // ربط الوحدات
                if (!empty($data['units'])) {
                    $this->attachUnits($question, $data['units']);
                }
                
                // إنشاء الخيارات
                if ($question->has_options && !empty($data['options'])) {
                    $this->createOptions($question, $data['options']);
                }
                
                DB::commit();
                $this->successCount++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = [
                    'row' => $rowNumber ?? ($index + 2),
                    'errors' => [$e->getMessage()],
                    'data' => $data ?? []
                ];
                $this->errorCount++;
                Log::error('Error importing question at row ' . ($index + 2) . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * الحصول على قيمة من الصف باستخدام column mapping
     */
    protected function getRowValue($row, $fieldKey, $default = null)
    {
        // إذا كان هناك column mapping، استخدمه
        if (!empty($this->columnMapping) && isset($this->columnMapping[$fieldKey])) {
            $columnName = $this->columnMapping[$fieldKey];
            return $row[$columnName] ?? $default;
        }
        
        // محاولة البحث بأسماء مختلفة
        $possibleKeys = [
            $fieldKey,
            str_replace('_', ' ', $fieldKey),
            $this->getArabicFieldName($fieldKey),
        ];
        
        foreach ($possibleKeys as $key) {
            if (isset($row[$key])) {
                return $row[$key];
            }
        }
        
        return $default;
    }

    /**
     * الحصول على الاسم العربي للحقل
     */
    protected function getArabicFieldName($fieldKey): string
    {
        $arabicNames = [
            'type' => 'نوع_السؤال',
            'title' => 'عنوان_السؤال',
            'content' => 'محتوى_السؤال',
            'explanation' => 'شرح',
            'difficulty' => 'صعوبة',
            'points' => 'درجة',
            'category' => 'تصنيف',
            'units' => 'وحدات',
        ];
        
        return $arabicNames[$fieldKey] ?? $fieldKey;
    }

    /**
     * تنظيف بيانات الصف
     */
    protected function cleanRowData($row): array
    {
        $data = [];
        
        // البيانات الأساسية - استخدام column mapping
        $data['type'] = $this->normalizeType($this->getRowValue($row, 'type', ''));
        $data['title'] = trim($this->getRowValue($row, 'title', ''));
        $data['content'] = trim($this->getRowValue($row, 'content', ''));
        $data['explanation'] = trim($this->getRowValue($row, 'explanation', ''));
        $data['difficulty'] = $this->normalizeDifficulty($this->getRowValue($row, 'difficulty', 'medium'));
        $data['default_points'] = floatval($this->getRowValue($row, 'points', 1));
        $data['category'] = trim($this->getRowValue($row, 'category', ''));
        $data['case_sensitive'] = $this->normalizeBoolean($this->getRowValue($row, 'case_sensitive', false));
        $data['tolerance'] = $this->getRowValue($row, 'tolerance') ? floatval($this->getRowValue($row, 'tolerance')) : null;
        $data['is_active'] = $this->normalizeBoolean($this->getRowValue($row, 'is_active', true));
        
        // الوحدات
        $data['units'] = $this->parseUnits($this->getRowValue($row, 'units', ''));
        
        // الخيارات
        $data['options'] = $this->parseOptions($row, $data['type']);
        
        // للأسئلة الرقمية
        if ($data['type'] === 'numerical') {
            $data['correct_answer'] = $this->getRowValue($row, 'correct_answer') ? floatval($this->getRowValue($row, 'correct_answer')) : null;
        }
        
        // لملء الفراغات
        if ($data['type'] === 'fill_blanks') {
            $data['blank_answers'] = $this->parseBlankAnswers($this->getRowValue($row, 'blank_answers', ''));
        }
        
        return $data;
    }

    /**
     * التحقق من صحة بيانات الصف
     */
    protected function validateRow(array $data, int $rowNumber): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(Question::TYPES))],
            'title' => ['required', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'default_points' => ['required', 'numeric', 'min:0', 'max:1000'],
        ];

        // التحقق من الخيارات للأسئلة التي تحتاجها
        if (in_array($data['type'], ['single_choice', 'multiple_choice', 'true_false', 'matching', 'ordering'])) {
            $rules['options'] = ['required', 'array', 'min:2'];
        }

        // التحقق من الإجابة الصحيحة للأسئلة الرقمية
        if ($data['type'] === 'numerical') {
            $rules['correct_answer'] = ['required', 'numeric'];
        }

        return Validator::make($data, $rules, [
            'type.required' => 'نوع السؤال مطلوب',
            'type.in' => 'نوع السؤال غير صحيح',
            'title.required' => 'عنوان السؤال مطلوب',
            'options.required' => 'الخيارات مطلوبة لهذا النوع من الأسئلة',
            'options.min' => 'يجب أن يكون هناك على الأقل خياران',
        ]);
    }

    /**
     * إنشاء السؤال
     */
    protected function createQuestion(array $data): Question
    {
        $questionData = [
            'type' => $data['type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'explanation' => $data['explanation'],
            'difficulty' => $data['difficulty'],
            'default_points' => $data['default_points'],
            'category' => $data['category'] ?: null,
            'case_sensitive' => $data['case_sensitive'],
            'tolerance' => $data['tolerance'],
            'is_active' => $data['is_active'],
            'created_by' => auth()->id(),
        ];

        // لملء الفراغات
        if ($data['type'] === 'fill_blanks' && !empty($data['blank_answers'])) {
            $questionData['blank_answers'] = $data['blank_answers'];
        }

        return Question::create($questionData);
    }

    /**
     * ربط الوحدات
     */
    protected function attachUnits(Question $question, array $unitIds): void
    {
        if (!empty($unitIds)) {
            $question->units()->sync($unitIds);
        }
    }

    /**
     * إنشاء الخيارات
     */
    protected function createOptions(Question $question, array $options): void
    {
        foreach ($options as $index => $optionData) {
            QuestionOption::create([
                'question_id' => $question->id,
                'content' => $optionData['content'],
                'is_correct' => $optionData['is_correct'] ?? false,
                'match_target' => $optionData['match_target'] ?? null,
                'correct_order' => $optionData['correct_order'] ?? null,
                'feedback' => $optionData['feedback'] ?? null,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * تحليل الوحدات
     */
    protected function parseUnits($unitsInput): array
    {
        if (empty($unitsInput)) {
            return [];
        }

        $unitIds = [];
        $units = is_array($unitsInput) ? $unitsInput : explode(',', $unitsInput);
        
        foreach ($units as $unit) {
            $unit = trim($unit);
            if (empty($unit)) continue;
            
            // إذا كان رقم
            if (is_numeric($unit)) {
                $unitIds[] = (int) $unit;
            } else {
                // البحث بالاسم
                $unitModel = Unit::where('title', 'like', "%{$unit}%")->first();
                if ($unitModel) {
                    $unitIds[] = $unitModel->id;
                }
            }
        }
        
        return array_unique($unitIds);
    }

    /**
     * تحليل الخيارات
     */
    protected function parseOptions($row, string $type): array
    {
        $options = [];
        
        // استخدام column mapping للخيارات
        $optionIndex = 1;
        while (true) {
            $optionContentKey = "option{$optionIndex}";
            $optionCorrectKey = "option{$optionIndex}_correct";
            
            // البحث في column mapping أولاً
            $contentColumn = $this->columnMapping[$optionContentKey] ?? null;
            $correctColumn = $this->columnMapping[$optionCorrectKey] ?? null;
            
            // إذا لم يكن في mapping، جرب الأسماء الافتراضية
            if (!$contentColumn) {
                $contentColumn = $row[$optionContentKey] ?? $row["خيار{$optionIndex}"] ?? null;
            } else {
                $contentColumn = $row[$contentColumn] ?? null;
            }
            
            if (!$contentColumn || trim($contentColumn) === '') {
                break;
            }
            
            $content = trim($contentColumn);
            
            // الحصول على قيمة is_correct
            $isCorrect = false;
            if ($correctColumn) {
                $isCorrect = $this->normalizeBoolean($row[$correctColumn] ?? false);
            } else {
                $isCorrect = $this->normalizeBoolean(
                    $row[$optionCorrectKey] ?? 
                    $row["خيار{$optionIndex}_صحيح"] ?? 
                    false
                );
            }
            
            $options[] = [
                'content' => $content,
                'is_correct' => $isCorrect,
                'match_target' => $row["option{$optionIndex}_match"] ?? null,
                'correct_order' => isset($row["option{$optionIndex}_order"]) ? (int) $row["option{$optionIndex}_order"] : null,
                'feedback' => $row["option{$optionIndex}_feedback"] ?? null,
            ];
            
            $optionIndex++;
            
            // حد أقصى 10 خيارات
            if ($optionIndex > 10) break;
        }
        
        // إذا لم توجد خيارات في أعمدة منفصلة، حاول قراءة من عمود واحد
        if (empty($options)) {
            $optionsColumn = $this->columnMapping['options'] ?? 'options';
            $optionsString = $row[$optionsColumn] ?? null;
            
            if ($optionsString) {
                $optionsArray = explode('|', $optionsString);
                
                foreach ($optionsArray as $index => $optionString) {
                    // تنسيق: "النص|صحيح" أو "النص"
                    $parts = explode('|', $optionString);
                    $content = trim($parts[0]);
                    $isCorrect = isset($parts[1]) && $this->normalizeBoolean(trim($parts[1]));
                    
                    if (!empty($content)) {
                        $options[] = [
                            'content' => $content,
                            'is_correct' => $isCorrect,
                        ];
                    }
                }
            }
        }
        
        return $options;
    }

    /**
     * تحليل إجابات الفراغات
     */
    protected function parseBlankAnswers($blankAnswersInput): array
    {
        if (empty($blankAnswersInput)) {
            return [];
        }

        if (is_array($blankAnswersInput)) {
            return $blankAnswersInput;
        }

        // فصل بفواصل أو |
        $answers = preg_split('/[,|]/', $blankAnswersInput);
        return array_map('trim', array_filter($answers));
    }

    /**
     * تطبيع نوع السؤال
     */
    protected function normalizeType($type): string
    {
        $type = strtolower(trim($type));
        
        $typeMap = [
            'single_choice' => 'single_choice',
            'اختيار واحد' => 'single_choice',
            'multiple_choice' => 'multiple_choice',
            'اختيار متعدد' => 'multiple_choice',
            'true_false' => 'true_false',
            'صح خطأ' => 'true_false',
            'true/false' => 'true_false',
            'short_answer' => 'short_answer',
            'إجابة قصيرة' => 'short_answer',
            'essay' => 'essay',
            'مقالي' => 'essay',
            'matching' => 'matching',
            'مطابقة' => 'matching',
            'ordering' => 'ordering',
            'ترتيب' => 'ordering',
            'fill_blanks' => 'fill_blanks',
            'ملء الفراغات' => 'fill_blanks',
            'numerical' => 'numerical',
            'رقمي' => 'numerical',
            'drag_drop' => 'drag_drop',
            'سحب وإفلات' => 'drag_drop',
        ];
        
        return $typeMap[$type] ?? 'single_choice';
    }

    /**
     * تطبيع مستوى الصعوبة
     */
    protected function normalizeDifficulty($difficulty): string
    {
        $difficulty = strtolower(trim($difficulty));
        
        $difficultyMap = [
            'easy' => 'easy',
            'سهل' => 'easy',
            'medium' => 'medium',
            'متوسط' => 'medium',
            'hard' => 'hard',
            'صعب' => 'hard',
        ];
        
        return $difficultyMap[$difficulty] ?? 'medium';
    }

    /**
     * تطبيع القيم المنطقية
     */
    protected function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim((string) $value));
        
        return in_array($value, ['1', 'true', 'yes', 'نعم', 'صحيح', 'نشط', '1', 'y']);
    }

    /**
     * الحصول على الأخطاء
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * الحصول على عدد النجاحات
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * الحصول على عدد الأخطاء
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }
}
