<?php

uses(Tests\TestCase::class);

use App\Models\Question;
use Illuminate\Support\Facades\Storage;

test('listPreviewText shows image label when title is only an image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('questions/images/q.png', 'x');

    $question = new Question([
        'title' => '<p><img src="/storage/questions/images/q.png" alt=""></p>',
    ]);

    expect($question->listPreviewText(80))->toBe('سؤال بصورة');
    expect($question->embeddedImageUrlsForList())->not->toBeEmpty();
});

test('parseImgSrcFromHtml supports single-quoted src', function () {
    $html = "<img src='/storage/questions/images/a.jpg' />";

    expect(Question::parseImgSrcFromHtml($html))->toBe(['/storage/questions/images/a.jpg']);
});
