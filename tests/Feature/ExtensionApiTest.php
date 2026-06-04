<?php

use App\Models\Question;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function extensionApiUser(): User
{
    $permission = Permission::firstOrCreate(
        ['name' => 'question-import', 'guard_name' => 'web'],
        ['description' => 'استيراد الأسئلة']
    );

    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->givePermissionTo($permission);

    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);
    $user->assignRole($adminRole);

    return $user;
}

function extensionApiSubject(): Subject
{
    $suffix = uniqid();

    $stage = Stage::create([
        'name' => 'Stage '.$suffix,
        'slug' => 'stage-'.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class '.$suffix,
        'slug' => 'class-'.$suffix,
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    return Subject::create([
        'name' => 'Subject '.$suffix,
        'slug' => 'subject-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);
}

test('extension login returns token for authorized user', function () {
    $user = extensionApiUser();

    $response = $this->postJson('/api/v1/extension/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-extension',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email', 'roles']]);
});

test('extension login rejects user without question-import permission', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/extension/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertForbidden();
});

test('extension import saves questions into subject bank', function () {
    $user = extensionApiUser();
    $subject = extensionApiSubject();

    $login = $this->postJson('/api/v1/extension/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $login->json('token');

    $payload = [
        'subject_id' => $subject->id,
        'questions' => [
            [
                'title' => 'ما عاصمة السعودية؟',
                'type' => 'single_choice',
                'options' => [
                    ['text' => 'الرياض', 'is_correct' => true],
                    ['text' => 'جدة', 'is_correct' => false],
                ],
            ],
            [
                'title' => 'الأرض كروية',
                'type' => 'true_false',
                'options' => [
                    ['text' => 'صح', 'is_correct' => true],
                    ['text' => 'خطأ', 'is_correct' => false],
                ],
            ],
        ],
    ];

    $response = $this->withToken($token)
        ->postJson('/api/v1/extension/questions/import', $payload);

    $response->assertOk()
        ->assertJsonPath('imported', 2)
        ->assertJsonPath('skipped', 0);

    expect(Question::where('subject_id', $subject->id)->count())->toBe(2);
});

test('extension curriculum subjects endpoint returns data', function () {
    $user = extensionApiUser();
    extensionApiSubject();

    $login = $this->postJson('/api/v1/extension/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->withToken($login->json('token'))
        ->getJson('/api/v1/extension/curriculum/subjects')
        ->assertOk()
        ->assertJsonStructure(['data']);
});
