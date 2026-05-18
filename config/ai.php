<?php

return [

  /*
    |--------------------------------------------------------------------------
    | إعدادات توليد الأسئلة من ملفات PDF
    |--------------------------------------------------------------------------
    */
    'question_generation_pdf' => [
        'max_size_kb' => (int) env('AI_PDF_MAX_SIZE_KB', 15360),
        'image_max_size_kb' => (int) env('AI_IMAGE_MAX_SIZE_KB', 8192),
        'max_pages_vision' => (int) env('AI_PDF_MAX_PAGES_VISION', 10),
        'min_extracted_chars' => (int) env('AI_PDF_MIN_EXTRACTED_CHARS', 80),
        'min_chars_per_page' => (int) env('AI_PDF_MIN_CHARS_PER_PAGE', 25),
        'max_text_chars_for_prompt' => (int) env('AI_PDF_MAX_TEXT_CHARS', 100000),
        'page_render_resolution' => (int) env('AI_PDF_PAGE_DPI', 150),
    ],

];
