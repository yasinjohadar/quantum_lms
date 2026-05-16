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
use App\Services\AdminStudentEnrollmentService;
use App\Services\Pricing\AccessResolver;
use App\Services\Pricing\PricingResolver;
use App\Services\Pricing\SubjectPricingResolver;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassBundleEnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Stage $stage;

    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->stage = Stage::create(['name' => 'Flow Stage', 'order' => 1]);
        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
            'order' => 1,
        ]);
    }

    protected function createClass(array $attributes = []): SchoolClass
    {
        return SchoolClass::create(array_merge([
            'name' => 'Flow Class',
            'slug' => 'flow-class-'.uniqid(),
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
            'name' => 'Flow Subject',
            'slug' => 'flow-subject-'.uniqid(),
            'class_id' => $class->id,
            'is_active' => true,
            'is_free' => false,
            'price' => 50,
            'default_currency_id' => $this->currency->id,
            'pricing_mode' => 'inherit',
        ], $attributes));
    }

    protected function attachPrice(object $pricable, float $amount): void
    {
        Price::create([
            'pricable_type' => $pricable::class,
            'pricable_id' => $pricable->id,
            'currency_id' => $this->currency->id,
            'price' => $amount,
            'is_active' => true,
        ]);
    }

    public function test_paid_subject_with_positive_price_is_not_in_class_bundle(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);
        $this->attachPrice($subject, 50.0);

        $resolver = app(SubjectPricingResolver::class);

        $this->assertFalse($resolver->isIncludedInClassBundle($subject));
    }

    public function test_complete_class_purchase_enrolls_only_bundle_included_subjects(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $bundled = $this->createSubject($class, [
            'name' => 'Bundled',
            'slug' => 'bundled-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $separate = $this->createSubject($class, [
            'name' => 'Separate Paid',
            'slug' => 'separate-'.uniqid(),
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);
        $this->attachPrice($separate, 50.0);

        $purchase = Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        app(PurchaseService::class)->completePurchase($purchase);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $bundled->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $separate->id,
        ]);
    }

    public function test_provision_approved_class_enrolls_only_bundle_included_subjects(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $bundled = $this->createSubject($class, [
            'name' => 'Bundled',
            'slug' => 'bundled-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $separate = $this->createSubject($class, [
            'name' => 'Separate Paid',
            'slug' => 'separate-'.uniqid(),
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);
        $this->attachPrice($separate, 50.0);

        ClassEnrollment::create([
            'user_id' => $this->user->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'enrolled_by' => 1,
            'enrolled_at' => now(),
        ]);

        app(AdminStudentEnrollmentService::class)->provisionSubjectEnrollmentsForApprovedClass(
            $this->user->id,
            $class,
            'موافقة',
            1
        );

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $bundled->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $separate->id,
        ]);
    }

    public function test_request_enrollment_on_free_class_allows_separate_purchase_for_paid_subject_despite_class_purchase(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $paidSubject = $this->createSubject($class, [
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);
        $this->attachPrice($paidSubject, 50.0);

        Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 0,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('student.enrollments.request', $paidSubject->id));

        $response->assertOk();
        $response->assertJsonFragment(['requires_payment' => true]);
    }

    public function test_inherit_subject_with_price_in_free_class_is_purchasable_not_accessible(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'inherit',
            'can_purchase_separately' => true,
        ]);
        $this->attachPrice($subject, 250.0);
        $subject->load('schoolClass');

        $pricing = app(SubjectPricingResolver::class);
        $access = app(AccessResolver::class);
        $dto = app(PricingResolver::class)->resolveSubjectAccessData($subject, $this->user);

        $this->assertEquals(250.0, $pricing->getEffectivePrice($subject));
        $this->assertFalse($pricing->isEffectivelyFree($subject));
        $this->assertFalse($pricing->isIncludedInClassBundle($subject));
        $this->assertTrue($pricing->canPurchaseSeparately($subject));
        $this->assertFalse($access->hasSubjectAccess($this->user, $subject));
        $this->assertTrue($access->canPurchaseSubject($this->user, $subject));
        $this->assertFalse($this->user->canAccessSubjectAsStudent($subject));
        $this->assertFalse($dto->canAccess);
        $this->assertTrue($dto->canPurchase);

        $response = $this->actingAs($this->user)
            ->get(route('student.subjects.show', $subject->id));

        $response->assertForbidden();
    }

    public function test_free_class_provision_skips_inherit_subject_with_explicit_price(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $bundled = $this->createSubject($class, [
            'name' => 'Bundled Inherit',
            'slug' => 'bundled-inherit-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $priced = $this->createSubject($class, [
            'name' => 'Priced Inherit',
            'slug' => 'priced-inherit-'.uniqid(),
            'pricing_mode' => 'inherit',
            'can_purchase_separately' => true,
        ]);
        $this->attachPrice($priced, 250.0);

        ClassEnrollment::create([
            'user_id' => $this->user->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'enrolled_by' => 1,
            'enrolled_at' => now(),
        ]);

        app(AdminStudentEnrollmentService::class)->provisionSubjectEnrollmentsForApprovedClass(
            $this->user->id,
            $class,
            'موافقة',
            1
        );

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $bundled->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->user->id,
            'subject_id' => $priced->id,
        ]);
    }

    public function test_always_free_subject_on_paid_class_shows_free_enrollment_not_bundle_only(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, [
            'name' => 'English Always Free',
            'slug' => 'english-free-'.uniqid(),
            'pricing_mode' => 'inherit',
            'is_free_override' => true,
            'can_purchase_separately' => false,
        ]);

        $dto = app(PricingResolver::class)->resolveSubjectAccessData($subject, $this->user);

        $this->assertSame('free', $dto->pricingMode->value);
        $this->assertFalse($dto->canAccess);
        $this->assertFalse($dto->canPurchase);

        $response = $this->actingAs($this->user)
            ->get(route('student.enrollments.class.show', $class->id));

        $response->assertOk();
        $response->assertSee('طلب الانضمام', false);
        $response->assertSee($subject->name, false);
        $response->assertDontSee('عبر الصف فقط', false);
    }

    public function test_paid_class_inherit_subject_requires_class_purchase_before_content_access(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, [
            'name' => 'Science',
            'slug' => 'science-paid-class-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $subject->load('schoolClass');

        $access = app(AccessResolver::class);
        $dto = app(PricingResolver::class)->resolveSubjectAccessData($subject, $this->user);

        $this->assertFalse($access->hasSubjectAccess($this->user, $subject));
        $this->assertFalse($this->user->canAccessSubjectAsStudent($subject));
        $this->assertFalse($dto->canAccess);

        $this->actingAs($this->user)
            ->get(route('student.subjects.show', $subject->id))
            ->assertForbidden();
    }

    public function test_free_class_inherit_subject_requires_enrollment_before_content_access(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $subject = $this->createSubject($class, [
            'name' => 'Social Studies',
            'slug' => 'social-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $subject->load('schoolClass');

        $access = app(AccessResolver::class);
        $dto = app(PricingResolver::class)->resolveSubjectAccessData($subject, $this->user);

        $this->assertFalse($access->hasSubjectAccess($this->user, $subject));
        $this->assertFalse($this->user->canAccessSubjectAsStudent($subject));
        $this->assertFalse($dto->canAccess);

        $this->actingAs($this->user)
            ->get(route('student.subjects.show', $subject->id))
            ->assertForbidden();
    }

    public function test_single_paid_subject_enrollment_does_not_count_as_full_class_access(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $freeSubject = $this->createSubject($class, [
            'name' => 'Free Inherit',
            'slug' => 'free-inherit-'.uniqid(),
            'pricing_mode' => 'inherit',
        ]);
        $paidSubject = $this->createSubject($class, [
            'name' => 'Paid Separate',
            'slug' => 'paid-separate-'.uniqid(),
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);
        $this->attachPrice($paidSubject, 50.0);

        Enrollment::create([
            'user_id' => $this->user->id,
            'subject_id' => $paidSubject->id,
            'enrolled_by' => null,
            'enrolled_at' => now(),
            'status' => 'active',
            'notes' => 'شراء مادة منفردة',
        ]);

        $class->load(['subjects' => fn ($q) => $q->where('is_active', true)]);

        $this->assertFalse($this->user->hasFullAccessToSchoolClass($class));

        $response = $this->actingAs($this->user)
            ->get(route('student.enrollments.class.show', $class->id));

        $response->assertOk();
        $response->assertSee('انضمام للصف كامل', false);
        $response->assertDontSee('منضم لجميع المواد', false);
        $response->assertSee($freeSubject->name, false);
        $response->assertDontSee($paidSubject->name, false);
    }

    public function test_request_enrollment_on_paid_class_free_subject_does_not_require_payment(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $freeSubject = $this->createSubject($class, [
            'pricing_mode' => 'free',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('student.enrollments.request', $freeSubject->id));

        $response->assertOk();
        $response->assertJsonMissing(['requires_payment' => true]);
        $response->assertJsonFragment(['success' => true]);

        $this->assertTrue(
            Enrollment::query()
                ->where('user_id', $this->user->id)
                ->where('subject_id', $freeSubject->id)
                ->where('status', 'active')
                ->exists()
        );
    }

    public function test_always_free_subject_with_manual_approval_creates_pending_enrollment(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'free',
            'is_free_override' => true,
            'free_join_auto_approve' => false,
            'can_purchase_separately' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('student.enrollments.request', $subject->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'under_review' => true,
        ]);

        $this->assertTrue(
            Enrollment::query()
                ->where('user_id', $this->user->id)
                ->where('subject_id', $subject->id)
                ->where('status', 'pending')
                ->exists()
        );

        $this->assertFalse(app(AccessResolver::class)->hasSubjectAccess($this->user, $subject->fresh()));
    }

    public function test_always_free_subject_with_auto_approve_enrolls_immediately(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'free',
            'is_free_override' => true,
            'free_join_auto_approve' => true,
            'can_purchase_separately' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('student.enrollments.request', $subject->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonMissing(['under_review' => true]);

        $this->assertTrue(
            Enrollment::query()
                ->where('user_id', $this->user->id)
                ->where('subject_id', $subject->id)
                ->where('status', 'active')
                ->exists()
        );
    }

    public function test_free_class_manual_approval_overrides_subject_auto_approve(): void
    {
        $class = $this->createClass([
            'is_free' => true,
            'price' => 0,
            'free_join_auto_approve' => false,
        ]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'inherit',
            'is_free' => true,
            'price' => 0,
            'free_join_auto_approve' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('student.enrollments.request', $subject->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'under_review' => true,
        ]);

        $this->assertTrue(
            Enrollment::query()
                ->where('user_id', $this->user->id)
                ->where('subject_id', $subject->id)
                ->where('status', 'pending')
                ->exists()
        );
    }
}
