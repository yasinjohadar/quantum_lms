<?php

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password form redirects without prior otp session', function () {
    $response = $this->get('/reset-password');

    $response->assertRedirect(route('password.request'));
});

test('legacy email reset link redirects to forgot password flow', function () {
    $response = $this->get('/reset-password/legacy-token-example');

    $response->assertRedirect(route('password.request'));
});
