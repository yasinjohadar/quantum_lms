<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Purchase;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IbanOnlyPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Stage::create(['name' => 'Stage', 'order' => 1]);
        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
            'order' => 1,
        ]);

        SystemSetting::set('payments_iban_display_name', 'تحويل بنكي مخصص', 'string', 'payments');
        SystemSetting::set('payments_iban_account_iban', 'SA9999999999999999999999', 'string', 'payments');
        SystemSetting::set('payments_iban_account_bank_name', 'بنك الاختبار', 'string', 'payments');
        SystemSetting::set('payments_iban_pending_message', 'رسالة اختبار قيد المعالجة', 'text', 'payments');
        SystemSetting::set('payments_iban_receipt_required', '0', 'boolean', 'payments');
    }

    protected function createPendingPurchase(): Purchase
    {
        $class = SchoolClass::create([
            'name' => 'Paid Class',
            'slug' => 'paid-class-'.uniqid(),
            'stage_id' => 1,
            'is_active' => true,
            'is_free' => false,
            'price' => 100,
            'default_currency_id' => $this->currency->id,
        ]);

        return Purchase::create([
            'user_id' => $this->user->id,
            'purchasable_type' => SchoolClass::class,
            'purchasable_id' => $class->id,
            'purchase_type' => 'class',
            'price' => 100,
            'status' => 'pending',
        ]);
    }

    public function test_payment_fragment_shows_only_bank_transfer_with_settings(): void
    {
        $purchase = $this->createPendingPurchase();

        $response = $this->actingAs($this->user)
            ->get(route('student.purchases.payment.fragment', [
                'purchase' => $purchase->id,
                'return' => 'enrollments',
            ]));

        $response->assertOk();
        $response->assertSee('تحويل بنكي مخصص', false);
        $response->assertSee('SA9999999999999999999999', false);
        $response->assertSee('رسالة اختبار قيد المعالجة', false);
        $response->assertDontSee('المحفظة الإلكترونية', false);
        $response->assertDontSee('Stripe', false);
        $response->assertDontSee('PayPal', false);
        $response->assertDontSee('value="wallet"', false);
        $response->assertDontSee('value="custom"', false);
    }

    public function test_process_iban_payment_returns_pending_review_and_keeps_purchase_pending(): void
    {
        Storage::fake('public');
        $purchase = $this->createPendingPurchase();

        $response = $this->actingAs($this->user)
            ->postJson(route('student.purchases.process-payment', $purchase->id), [
                'payment_method' => 'iban',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'pending_review' => true,
            'message' => 'رسالة اختبار قيد المعالجة',
        ]);

        $purchase->refresh();
        $this->assertSame('pending', $purchase->status);
    }

    public function test_wallet_payment_method_is_rejected(): void
    {
        $purchase = $this->createPendingPurchase();

        $response = $this->actingAs($this->user)
            ->postJson(route('student.purchases.process-payment', $purchase->id), [
                'payment_method' => 'wallet',
            ]);

        $response->assertStatus(422);
    }
}
