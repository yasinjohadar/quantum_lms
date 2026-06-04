<?php

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Storage;

test('tinymce_public_image_url prefers same-origin storage when file exists locally', function () {
    Storage::fake('public');
    Storage::disk('public')->put('questions/images/sample.png', 'binary');

    $url = tinymce_public_image_url('questions/images/sample.png');

    expect($url)->toContain('/storage/questions/images/sample.png');
});

test('absoluteImageUrlForDisplay resolves bare storage paths via tinymce helper', function () {
    Storage::fake('public');
    Storage::disk('public')->put('questions/images/q1.png', 'binary');

    $url = \App\Models\Question::absoluteImageUrlForDisplay('questions/images/q1.png');

    expect($url)->toContain('/storage/questions/images/q1.png');
});
