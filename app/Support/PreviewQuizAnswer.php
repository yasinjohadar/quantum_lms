<?php

namespace App\Support;

use App\Models\QuizAnswer;

/**
 * إجابة معاينة في الذاكرة فقط — لا تُحفظ في قاعدة البيانات.
 */
class PreviewQuizAnswer extends QuizAnswer
{
    public $exists = false;

    public function save(array $options = [])
    {
        return true;
    }

    public function delete()
    {
        return true;
    }
}
