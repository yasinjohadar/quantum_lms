<?php

use App\Models\ClassEnrollment;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Price;
use App\Models\Purchase;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }

    $this->user = User::factory()->create();
    $this->stage = Stage::create(['name' => 'Gating Stage', 'order' => 1]);
    $this->currency = Currency::create([
        'code' => 'SAR',
        'name' => 'ريال',
        'symbol' => 'ر.س',
        'is_active' => true,
        'order' => 1,
    ]);
});

function gatingCreateClass(array $attributes = []): SchoolClass
{
    return SchoolClass::create(array_merge([
        'name' => 'Gating Class',
        'slug' => 'gating-class-'.uniqid(),
        'stage_id' => test()->stage->id,
        'is_active' => true,
        'is_free' => false,
        'price' => 0,
        'default_currency_id' => test()->currency->id,
        'free_join_auto_approve' => false,
    ], $attributes));
}

function gatingAttachClassPrice(SchoolClass $class, float $amount): void
{
    Price::create([
        'pricable_type' => SchoolClass::class,
        'pricable_id' => $class->id,
        'currency_id' => test()->currency->id,
        'price' => $amount,
        'is_active' => true,
    ]);
}

function gatingCreateSubject(SchoolClass $class): Subject
{
    return Subject::create([
        'name' => 'Gating Subject',
        'slug' => 'gating-subject-'.uniqid(),
        'class_id' => $class->id,
        'is_active' => true,
        'pricing_mode' => 'inherit',
    ]);
}

test('class with price only in prices table creates direct pending review request without payment record', function () {
    $class = gatingCreateClass(['price' => 0]);
    gatingAttachClassPrice($class, 599999.97);
    gatingCreateSubject($class);

    expect($class->fresh()->classJoinRequiresPayment())->toBeTrue();

    $response = $this->actingAs($this->user)
        ->postJson(route('student.enrollments.request-class', $class->id));

    $response->assertOk();
    $response->assertJsonFragment([
        'under_review' => true,
        'class_id' => $class->id,
        'requires_whatsapp_followup' => true,
    ]);

    expect(ClassEnrollment::where('user_id', $this->user->id)->where('class_id', $class->id)->exists())->toBeFalse();
    expect(Purchase::where('user_id', $this->user->id)->where('purchasable_id', $class->id)->where('status', 'pending')->exists())->toBeTrue();
    expect(Payment::whereHas('purchase', fn ($q) => $q->where('user_id', $this->user->id)->where('purchasable_id', $class->id))->exists())->toBeFalse();
});

test('rejected class enrollment on paid class routes to direct pending purchase request', function () {
    $class = gatingCreateClass(['price' => 100]);
    gatingCreateSubject($class);

    ClassEnrollment::create([
        'user_id' => $this->user->id,
        'class_id' => $class->id,
        'status' => 'rejected',
        'notes' => 'مرفوض سابقاً',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('student.enrollments.request-class', $class->id));

    $response->assertOk();
    $response->assertJsonFragment([
        'under_review' => true,
        'requires_whatsapp_followup' => true,
    ]);

    expect(ClassEnrollment::where('user_id', $this->user->id)->where('class_id', $class->id)->value('status'))->toBe('rejected');
    expect(Purchase::where('user_id', $this->user->id)->where('purchasable_id', $class->id)->where('status', 'pending')->exists())->toBeTrue();
});

test('iban payment submission does not create class enrollment until admin approval', function () {
    Storage::fake('public');

    $class = gatingCreateClass(['price' => 150]);
    gatingCreateSubject($class);

    $purchase = app(PurchaseService::class)->createPurchase($this->user, $class, 'class');

    $receipt = UploadedFile::fake()->image('receipt.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('student.purchases.process-payment', $purchase->id), [
            'payment_method' => 'iban',
            'receipt_file' => $receipt,
        ]);

    $response->assertOk();
    $response->assertJsonFragment(['pending_review' => true]);

    expect(Payment::where('purchase_id', $purchase->id)->where('status', 'pending')->exists())->toBeTrue();
    expect(ClassEnrollment::where('user_id', $this->user->id)->where('class_id', $class->id)->exists())->toBeFalse();
});

test('approving payment creates approved class enrollment', function () {
    $class = gatingCreateClass(['price' => 200]);
    gatingCreateSubject($class);

    $purchase = Purchase::create([
        'user_id' => $this->user->id,
        'purchasable_type' => SchoolClass::class,
        'purchasable_id' => $class->id,
        'purchase_type' => 'class',
        'price' => 200,
        'status' => 'pending',
    ]);

    $payment = Payment::create([
        'purchase_id' => $purchase->id,
        'payment_method' => 'iban',
        'amount' => 200,
        'currency' => 'SAR',
        'status' => 'pending',
    ]);

    $admin = User::factory()->create();

    app(PaymentService::class)->reviewIBANPayment($payment, true, 'موافقة', $admin->id);

    expect(ClassEnrollment::where('user_id', $this->user->id)
        ->where('class_id', $class->id)
        ->where('status', 'approved')
        ->exists())->toBeTrue();
});

test('prepare class payment fragment creates pending purchase not awaiting admin review', function () {
    $class = gatingCreateClass(['price' => 0]);
    gatingAttachClassPrice($class, 300);
    gatingCreateSubject($class);

    $response = $this->actingAs($this->user)
        ->get(route('student.purchases.prepare-class.fragment', [
            'class' => $class->id,
            'return' => 'enrollments',
        ]));

    $response->assertOk();

    $purchase = Purchase::where('user_id', $this->user->id)
        ->where('purchasable_id', $class->id)
        ->where('status', 'pending')
        ->first();

    expect($purchase)->not->toBeNull();
    expect($purchase->isAwaitingAdminReview())->toBeFalse();
    expect(Purchase::where('user_id', $this->user->id)->awaitingAdminReview()->count())->toBe(0);
});

test('awaiting admin review scope includes purchase only after receipt submitted', function () {
    Storage::fake('public');

    $class = gatingCreateClass(['price' => 120]);
    gatingCreateSubject($class);

    $purchase = app(PurchaseService::class)->createPurchase($this->user, $class, 'class');

    expect(Purchase::where('user_id', $this->user->id)->awaitingAdminReview()->count())->toBe(0);

    $this->actingAs($this->user)
        ->postJson(route('student.purchases.process-payment', $purchase->id), [
            'payment_method' => 'iban',
            'receipt_file' => UploadedFile::fake()->image('receipt.jpg'),
        ])
        ->assertOk();

    expect(Purchase::where('user_id', $this->user->id)->awaitingAdminReview()->count())->toBe(1);
});
