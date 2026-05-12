<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Stage;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Enums\PricingMode;
use App\Services\Pricing\AccessResolver;
use App\Services\Pricing\SubjectPricingResolver;
use App\Services\Pricing\ClassPricingResolver;
use App\Services\Pricing\PricingResolver;
use App\Services\Pricing\PurchasePolicyResolver;
use App\DataTransferObjects\SubjectAccessData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingScenariosTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Stage $stage;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->stage = Stage::create(['name' => 'Test Stage', 'order' => 1]);
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
            'name' => 'Test Class',
            'slug' => 'test-class-' . uniqid(),
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
            'name' => 'Test Subject',
            'slug' => 'test-subject-' . uniqid(),
            'class_id' => $class->id,
            'is_active' => true,
            'is_free' => false,
            'price' => 50,
            'default_currency_id' => $this->currency->id,
            'pricing_mode' => 'inherit',
        ], $attributes));
    }

    // Scenario 1: Free class + inherit subject = Access
    public function test_free_class_with_inherit_subject_grants_access(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        $resolver = app(AccessResolver::class);

        $this->assertTrue($resolver->hasClassAccess($this->user, $class));
        $this->assertTrue($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('free', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 2: Free class + paid subject = Purchase required
    public function test_free_class_with_paid_subject_requires_purchase(): void
    {
        $class = $this->createClass(['is_free' => true, 'price' => 0]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);

        $resolver = app(AccessResolver::class);
        $pricingResolver = app(SubjectPricingResolver::class);

        $this->assertTrue($resolver->hasClassAccess($this->user, $class));
        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('purchasable', $resolver->getSubjectAccessType($this->user, $subject));
        $this->assertEquals(50.0, $pricingResolver->getEffectivePrice($subject));
    }

    // Scenario 3: Paid class + inherit subject = Requires class purchase
    public function test_paid_class_with_inherit_subject_requires_class_purchase(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->user, $class));
        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('requires_class_purchase', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 4: Paid class + free subject = Free access
    public function test_paid_class_with_free_subject_grants_free_access(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'free']);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->user, $class));
        $this->assertTrue($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('free', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 5: Paid class + paid subject = Separate purchase
    public function test_paid_class_with_paid_subject_allows_separate_purchase(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, [
            'pricing_mode' => 'paid',
            'price' => 50,
        ]);

        $resolver = app(AccessResolver::class);
        $policyResolver = app(PurchasePolicyResolver::class);

        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertTrue($policyResolver->canPurchaseSubject($this->user, $subject));
        $this->assertEquals('purchasable', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 6: Hidden subject = No access
    public function test_hidden_subject_grants_no_access(): void
    {
        $class = $this->createClass(['is_free' => true]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'hidden']);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('hidden', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 7: Purchased class grants access to inherit subjects
    public function test_purchased_class_grants_access_to_inherit_subjects(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        $resolver = app(AccessResolver::class);

        $this->assertTrue($resolver->hasClassAccess($this->user, $class));
        $this->assertTrue($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('included_in_class', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 8: Purchased subject grants access
    public function test_purchased_subject_grants_access(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'paid', 'price' => 50]);

        Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => Subject::class,
            'purchasable_id' => $subject->id,
            'purchase_type' => 'subject',
            'price' => 50,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->user, $class));
        $this->assertTrue($resolver->hasSubjectAccess($this->user, $subject));
        $this->assertEquals('purchased', $resolver->getSubjectAccessType($this->user, $subject));
    }

    // Scenario 9: Enrolled user gets access
    public function test_enrolled_user_gets_access(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        ClassEnrollment::create([
            'user_id' => $this->user->id,
            'class_id' => $class->id,
            'status' => 'approved',
            'enrolled_by' => 1,
            'enrolled_at' => now(),
        ]);

        $resolver = app(AccessResolver::class);

        $this->assertTrue($resolver->hasClassAccess($this->user, $class));
        $this->assertTrue($resolver->hasSubjectAccess($this->user, $subject));
    }

    // Scenario 10: Bundle only subject cannot be purchased separately
    public function test_bundle_only_subject_cannot_be_purchased_separately(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'bundle_only']);

        $policyResolver = app(PurchasePolicyResolver::class);
        $pricingResolver = app(SubjectPricingResolver::class);

        $this->assertFalse($policyResolver->canPurchaseSubject($this->user, $subject));
        $this->assertFalse($pricingResolver->canPurchaseSeparately($subject));
    }

    // Scenario 11: DTO contains correct data
    public function test_subject_access_dto_contains_correct_data(): void
    {
        $class = $this->createClass(['is_free' => true]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'free']);

        $resolver = app(PricingResolver::class);
        $dto = $resolver->resolveSubjectAccessData($subject, $this->user);

        $this->assertInstanceOf(SubjectAccessData::class, $dto);
        $this->assertTrue($dto->canAccess);
        $this->assertFalse($dto->canPurchase);
        $this->assertEquals(0.0, $dto->effectivePrice);
        $this->assertEquals('free', $dto->accessType);
        $this->assertTrue($dto->isEffectivelyFree);
    }

    // Scenario 12: Expired purchase does not grant access
    public function test_expired_purchase_does_not_grant_access(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'completed',
            'purchased_at' => now()->subDays(10),
            'expires_at' => now()->subDay(),
        ]);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->user, $class));
        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
    }

    // Scenario 13: Guest user cannot access paid content
    public function test_guest_user_cannot_access_paid_content(): void
    {
        $class = $this->createClass(['is_free' => false, 'price' => 100]);
        $subject = $this->createSubject($class, ['pricing_mode' => 'inherit']);

        $resolver = app(AccessResolver::class);

        $this->assertFalse($resolver->hasClassAccess($this->user, $class));
        $this->assertFalse($resolver->hasSubjectAccess($this->user, $subject));
    }

    // Scenario 14: Badge is correct for each scenario
    public function test_badges_are_correct(): void
    {
        $class = $this->createClass(['is_free' => true]);

        $freeSubject = $this->createSubject($class, ['pricing_mode' => 'free', 'name' => 'Free Subject', 'slug' => 'free-subject-' . uniqid()]);
        $paidSubject = $this->createSubject($class, ['pricing_mode' => 'paid', 'price' => 50, 'name' => 'Paid Subject', 'slug' => 'paid-subject-' . uniqid()]);
        $hiddenSubject = $this->createSubject($class, ['pricing_mode' => 'hidden', 'name' => 'Hidden Subject', 'slug' => 'hidden-subject-' . uniqid()]);

        $resolver = app(AccessResolver::class);

        $freeBadge = $resolver->getSubjectBadge($freeSubject, $this->user);
        $paidBadge = $resolver->getSubjectBadge($paidSubject, $this->user);
        $hiddenBadge = $resolver->getSubjectBadge($hiddenSubject, $this->user);

        $this->assertEquals('مجاني', $freeBadge['text']);
        $this->assertEquals('bg-success', $freeBadge['class']);

        $this->assertEquals('شراء منفصل', $paidBadge['text']);
        $this->assertEquals('bg-warning', $paidBadge['class']);

        $this->assertEquals('مخفي', $hiddenBadge['text']);
        $this->assertEquals('bg-secondary', $hiddenBadge['class']);
    }
}
