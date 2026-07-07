<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\Currency;
use App\Models\Enrollment;
use App\Models\Price;
use App\Models\Purchase;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use App\Services\Pricing\AccessResolver;
use App\Services\PurchaseService;
use App\Support\AdminClassSubscriptionInput;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ClassSubscriptionEndDateTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $admin;

    protected Stage $stage;

    protected Currency $currency;

    protected function setUp(): void
    {
        if (env('DB_CONNECTION') === 'sqlite') {
            $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
        }

        parent::setUp();

        $this->student = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->stage = Stage::create(['name' => 'Subscription Stage', 'order' => 1]);
        $this->currency = Currency::create([
            'code' => 'SAR',
            'name' => 'ريال',
            'symbol' => 'ر.س',
            'is_active' => true,
            'is_default' => true,
            'order' => 1,
        ]);
    }

    protected function createClass(array $attributes = []): SchoolClass
    {
        return SchoolClass::create(array_merge([
            'name' => 'Subscription Class',
            'slug' => 'subscription-class-'.uniqid(),
            'stage_id' => $this->stage->id,
            'is_active' => true,
            'is_free' => false,
            'price' => 100,
            'default_currency_id' => $this->currency->id,
        ], $attributes));
    }

    protected function createSubject(SchoolClass $class, array $attributes = []): Subject
    {
        return Subject::create(array_merge([
            'name' => 'Bundle Subject',
            'slug' => 'bundle-subject-'.uniqid(),
            'class_id' => $class->id,
            'is_active' => true,
            'pricing_mode' => 'inherit',
            'default_currency_id' => $this->currency->id,
        ], $attributes));
    }

    public function test_admin_subscription_input_stores_end_of_day_and_clears_revocation_on_extension(): void
    {
        $class = $this->createClass([
            'subscription_ends_at' => now()->subDay()->endOfDay(),
            'subscription_revoked_at' => now()->subDay(),
        ]);

        $request = Request::create('/', 'PUT', [
            'subscription_ends_at' => now()->addMonth()->format('Y-m-d'),
        ]);

        $data = AdminClassSubscriptionInput::merge([], $request, $class);

        $this->assertTrue($data['subscription_ends_at']->isEndOfDay());
        $this->assertNull($data['subscription_revoked_at']);
    }

    public function test_class_has_subscription_ended_helper(): void
    {
        $activeClass = $this->createClass([
            'subscription_ends_at' => now()->addWeek()->endOfDay(),
        ]);
        $expiredClass = $this->createClass([
            'subscription_ends_at' => now()->subMinute(),
        ]);

        $this->assertFalse($activeClass->hasSubscriptionEnded());
        $this->assertTrue($expiredClass->hasSubscriptionEnded());
    }

    public function test_expire_subscriptions_command_revokes_class_and_bundle_enrollments(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $class = $this->createClass([
            'subscription_ends_at' => now()->subHour(),
        ]);
        $bundledSubject = $this->createSubject($class);

        ClassEnrollment::create([
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'enrolled_by' => $this->admin->id,
            'enrolled_at' => now(),
        ]);

        Enrollment::create([
            'user_id' => $this->student->id,
            'subject_id' => $bundledSubject->id,
            'status' => 'active',
            'enrolled_by' => $this->admin->id,
            'enrolled_at' => now(),
        ]);

        Purchase::create([
            'user_id' => $this->student->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        Artisan::call('classes:expire-subscriptions');

        $this->assertDatabaseHas('class_enrollments', [
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'subject_id' => $bundledSubject->id,
            'status' => 'suspended',
        ]);

        $this->assertNotNull($class->fresh()->subscription_revoked_at);

        Carbon::setTestNow();
    }

    public function test_access_resolver_blocks_access_after_class_subscription_ends(): void
    {
        $class = $this->createClass([
            'subscription_ends_at' => now()->subMinute(),
        ]);
        $subject = $this->createSubject($class);

        ClassEnrollment::create([
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'enrolled_by' => $this->admin->id,
            'enrolled_at' => now(),
        ]);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->student, $class));
        $this->assertFalse($resolver->hasSubjectAccess($this->student, $subject));
    }

    public function test_approve_pending_purchase_caps_expiry_to_class_subscription_end(): void
    {
        $classEnd = now()->addDays(10)->endOfDay();
        $class = $this->createClass([
            'subscription_ends_at' => $classEnd,
        ]);
        $this->createSubject($class);

        $purchase = Purchase::create([
            'user_id' => $this->student->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'pending',
        ]);

        app(PurchaseService::class)->approvePendingDirectPurchase(
            $purchase,
            $this->admin->id,
            'موافقة',
            now()->addMonths(3)
        );

        $purchase->refresh();

        $this->assertTrue($purchase->expires_at->equalTo($class->fresh()->subscription_ends_at));
    }

    public function test_extending_subscription_date_resets_revoked_flag_without_re_enrolling_students(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $class = $this->createClass([
            'subscription_ends_at' => now()->subDay()->endOfDay(),
            'subscription_revoked_at' => now()->subDay(),
        ]);

        ClassEnrollment::create([
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'status' => 'rejected',
            'enrolled_by' => $this->admin->id,
            'enrolled_at' => now()->subWeek(),
        ]);

        $request = Request::create('/', 'PUT', [
            'subscription_ends_at' => now()->addMonth()->format('Y-m-d'),
        ]);

        $data = AdminClassSubscriptionInput::merge([
            'name' => $class->name,
        ], $request, $class);

        $class->update($data);

        $class->refresh();

        $this->assertNull($class->subscription_revoked_at);
        $this->assertTrue($class->subscription_ends_at->isFuture());
        $this->assertDatabaseHas('class_enrollments', [
            'user_id' => $this->student->id,
            'class_id' => $class->id,
            'status' => 'rejected',
        ]);

        Carbon::setTestNow();
    }
}
