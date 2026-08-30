<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\RentalPackage;
use App\Models\PlaystationUnit;
use App\Models\BusinessSetting;
use App\Models\DeliveryRate;
use App\Models\Customer;
use App\Services\BookingService;
use App\Services\GoogleMapsService;
use App\Services\PaymentService;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Carbon\Carbon;

class MaxiboxTest extends TestCase
{
    // NOTE: Tidak menggunakan RefreshDatabase agar tidak merusak data existing.
    // Test menggunakan transaksi database yang di-rollback.

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    private AuthRole $adminRole;
    private AuthRole $driverRole;
    private AuthUser $adminUser;
    private AuthUser $driverUser;
    private AuthUser $inactiveUser;
    private RentalPackage $pkg6h;
    private RentalPackage $pkg12h;
    private RentalPackage $pkg24h;
    private PlaystationUnit $unit1;
    private PlaystationUnit $unit2;
    private BusinessSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = AuthRole::firstOrCreate(
            ['name' => 'Admin_Test_' . Str::random(4)],
            ['id' => Str::uuid(), 'description' => 'Admin test']
        );
        $this->driverRole = AuthRole::firstOrCreate(
            ['name' => 'Driver_Test_' . Str::random(4)],
            ['id' => Str::uuid(), 'description' => 'Driver test']
        );

        // Create users
        $this->adminUser = AuthUser::withoutGlobalScope('active')->create([
            'id'         => (string) Str::uuid(),
            'name'       => 'Admin Test',
            'username'   => 'admin_test_' . Str::random(5),
            'email'      => 'admintest_' . Str::random(5) . '@test.com',
            'password'   => Hash::make('password123'),
            'role_id'    => $this->adminRole->id,
            'active'     => true,
            'is_deleted' => false,
        ]);

        $this->driverUser = AuthUser::withoutGlobalScope('active')->create([
            'id'         => (string) Str::uuid(),
            'name'       => 'Driver Test',
            'username'   => 'driver_test_' . Str::random(5),
            'email'      => 'drivertest_' . Str::random(5) . '@test.com',
            'password'   => Hash::make('password123'),
            'role_id'    => $this->driverRole->id,
            'active'     => true,
            'is_deleted' => false,
        ]);

        $this->inactiveUser = AuthUser::withoutGlobalScope('active')->create([
            'id'         => (string) Str::uuid(),
            'name'       => 'Inactive User',
            'username'   => 'inactive_' . Str::random(5),
            'email'      => 'inactive_' . Str::random(5) . '@test.com',
            'password'   => Hash::make('password123'),
            'role_id'    => $this->adminRole->id,
            'active'     => false,
            'is_deleted' => false,
        ]);

        // Create packages
        $this->pkg6h = RentalPackage::create([
            'code' => 'PKG6H_' . Str::random(4), 'name' => 'Paket 6 Jam Test',
            'duration_hours' => 6, 'price' => 150000,
            'blocked_start_time' => '15:00', 'blocked_end_time' => '00:00',
            'is_active' => true,
        ]);

        $this->pkg12h = RentalPackage::create([
            'code' => 'PKG12H_' . Str::random(4), 'name' => 'Paket 12 Jam Test',
            'duration_hours' => 12, 'price' => 250000,
            'blocked_start_time' => '13:00', 'blocked_end_time' => '19:00',
            'is_active' => true,
        ]);

        $this->pkg24h = RentalPackage::create([
            'code' => 'PKG24H_' . Str::random(4), 'name' => 'Paket 24 Jam Test',
            'duration_hours' => 24, 'price' => 400000,
            'blocked_start_time' => null, 'blocked_end_time' => null,
            'is_active' => true,
        ]);

        // Disable all existing units temporarily (will be restored by transaction rollback)
        PlaystationUnit::query()->update(['is_active' => false]);

        // Create test units
        $this->unit1 = PlaystationUnit::create([
            'unit_code' => 'UNIT1_' . Str::random(4), 'name' => 'PS4 Test Unit 1',
            'status' => 'available', 'is_active' => true,
        ]);
        $this->unit2 = PlaystationUnit::create([
            'unit_code' => 'UNIT2_' . Str::random(4), 'name' => 'PS4 Test Unit 2',
            'status' => 'available', 'is_active' => true,
        ]);

        // Disable existing business settings
        BusinessSetting::query()->update(['is_active' => false]);

        // Create test business setting
        $this->setting = BusinessSetting::create([
            'business_name'       => 'Maxibox Test',
            'latitude'            => -6.2,
            'longitude'           => 106.8,
            'down_payment_amount' => 50000,
            'payment_expiry_minutes' => 60,
            'maximum_delivery_km' => 20,
            'is_active'           => true,
        ]);

        // Create delivery rate
        DeliveryRate::create([
            'name' => 'Test Zone', 'minimum_distance_km' => 0,
            'maximum_distance_km' => 20, 'delivery_fee' => 10000,
            'is_active' => true,
        ]);
    }

    // ─── 1. Login Admin berhasil ─────────────────────────────────────────────

    public function test_admin_login_success(): void
    {
        $response = $this->post('/login', [
            'username' => $this->adminUser->username,
            'password' => 'password123',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->adminUser);
    }

    // ─── 2. Login Driver berhasil ────────────────────────────────────────────

    public function test_driver_login_success(): void
    {
        $response = $this->post('/login', [
            'username' => $this->driverUser->username,
            'password' => 'password123',
        ]);
        $response->assertRedirect('/driver/dashboard');
        $this->assertAuthenticatedAs($this->driverUser);
    }

    // ─── 3. User nonaktif tidak dapat login ──────────────────────────────────

    public function test_inactive_user_cannot_login(): void
    {
        $response = $this->post('/login', [
            'username' => $this->inactiveUser->username,
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    // ─── 4. Driver tidak bisa akses CRUD Admin ───────────────────────────────

    public function test_driver_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->driverUser);
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    // ─── 5. Paket 6 jam ditolak pukul 15:00–24:00 ───────────────────────────

    public function test_package_6h_blocked_at_15(): void
    {
        $blocked = $this->pkg6h->isTimeBlocked('15:00');
        $this->assertTrue($blocked);
    }

    public function test_package_6h_blocked_at_20(): void
    {
        $blocked = $this->pkg6h->isTimeBlocked('20:00');
        $this->assertTrue($blocked);
    }

    public function test_package_6h_allowed_at_10(): void
    {
        $blocked = $this->pkg6h->isTimeBlocked('10:00');
        $this->assertFalse($blocked);
    }

    // ─── 6. Paket 12 jam ditolak pukul 13:00–19:00 ──────────────────────────

    public function test_package_12h_blocked_at_13(): void
    {
        $blocked = $this->pkg12h->isTimeBlocked('13:00');
        $this->assertTrue($blocked);
    }

    public function test_package_12h_allowed_at_10(): void
    {
        $blocked = $this->pkg12h->isTimeBlocked('10:00');
        $this->assertFalse($blocked);
    }

    public function test_package_12h_allowed_at_20(): void
    {
        $blocked = $this->pkg12h->isTimeBlocked('20:00');
        $this->assertFalse($blocked);
    }

    // ─── 7-9. Ketersediaan unit ───────────────────────────────────────────────

    private function makeBookingService(): BookingService
    {
        $mapsService = Mockery::mock(GoogleMapsService::class);
        $mapsService->shouldReceive('calculateRoute')->andReturn([
            'is_serviceable' => true,
            'distance_km'    => 5.0,
            'delivery_fee'   => 10000,
            'message'        => 'OK',
        ]);

        $midtransMock = Mockery::mock(MidtransService::class);
        $midtransMock->shouldReceive('createSnapTransaction')->andReturn([
            'snap_token'   => 'test_snap_token',
            'redirect_url' => 'http://test',
        ]);

        $paymentService = new PaymentService($midtransMock);

        return new BookingService($mapsService, $paymentService);
    }

    private function makeBookingData(string $packageId, string $date = null, string $time = '09:00'): array
    {
        return [
            'full_name'         => 'Customer Test',
            'phone_number'      => '081234567890',
            'rental_date'       => $date ?? now()->addDay()->format('Y-m-d'),
            'rental_time'       => $time,
            'rental_package_id' => $packageId,
            'delivery_address'  => 'Jl Test No 1',
            'google_place_id'   => null,
            'latitude'          => -6.21,
            'longitude'         => 106.81,
            'payment_option'    => 'full',
            'terms_agreed'      => '1',
        ];
    }

    public function test_booked_unit_not_available_on_same_time(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/seluruh unit/i');

        $service = $this->makeBookingService();
        $date    = now()->addDays(5)->format('Y-m-d');

        // Fill both test units with 24h bookings
        $service->createBooking($this->makeBookingData($this->pkg24h->id, $date, '09:00'));
        $service->createBooking($this->makeBookingData($this->pkg24h->id, $date, '09:00'));
        // Third overlapping booking should fail
        $service->createBooking($this->makeBookingData($this->pkg24h->id, $date, '09:00'));
    }

    public function test_two_units_accept_two_bookings_same_time(): void
    {
        $service = $this->makeBookingService();
        $date    = now()->addDays(6)->format('Y-m-d');

        $r1 = $service->createBooking($this->makeBookingData($this->pkg6h->id, $date, '09:00'));
        $r2 = $service->createBooking($this->makeBookingData($this->pkg6h->id, $date, '09:00'));

        $this->assertNotNull($r1['booking']);
        $this->assertNotNull($r2['booking']);
        $this->assertNotEquals($r1['booking']->playstation_unit_id, $r2['booking']->playstation_unit_id);
    }

    public function test_third_booking_rejected_when_both_units_taken(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/seluruh unit/i');

        $service = $this->makeBookingService();
        $date    = now()->addDays(7)->format('Y-m-d');

        $service->createBooking($this->makeBookingData($this->pkg6h->id, $date, '09:00'));
        $service->createBooking($this->makeBookingData($this->pkg6h->id, $date, '09:00'));
        $service->createBooking($this->makeBookingData($this->pkg6h->id, $date, '09:00'));
    }

    // ─── 10. DP = Rp50.000 ───────────────────────────────────────────────────

    public function test_deposit_payment_is_50000(): void
    {
        $service = $this->makeBookingService();
        $data    = $this->makeBookingData($this->pkg24h->id, now()->addDays(10)->format('Y-m-d'));
        $data['payment_option'] = 'deposit';

        $result = $service->createBooking($data);
        $this->assertEquals(50000, $result['booking']->initial_payment_amount);
        $this->assertEquals(50000, $result['payment']->requested_amount);
    }

    // ─── 11. Lunas = total tagihan ────────────────────────────────────────────

    public function test_full_payment_equals_total(): void
    {
        $service = $this->makeBookingService();
        $data    = $this->makeBookingData($this->pkg24h->id, now()->addDays(11)->format('Y-m-d'));
        $data['payment_option'] = 'full';

        $result = $service->createBooking($data);
        $this->assertEquals($result['booking']->total_amount, $result['booking']->initial_payment_amount);
    }

    // ─── 12. Webhook valid update payment ────────────────────────────────────

    public function test_valid_webhook_updates_payment(): void
    {
        // Ensure server key is empty so signature check is bypassed in this test
        \Illuminate\Support\Facades\Config::set('services.midtrans.server_key', '');

        $service = $this->makeBookingService();
        $result  = $service->createBooking($this->makeBookingData($this->pkg24h->id, now()->addDays(12)->format('Y-m-d')));
        $payment = $result['payment'];

        $gross = (string)(int)$payment->requested_amount;

        $response = $this->withoutMiddleware()->postJson('/webhooks/midtrans', [
            'order_id'           => $payment->payment_code,
            'transaction_status' => 'settlement',
            'status_code'        => '200',
            'gross_amount'       => $gross . '.00',
            'signature_key'      => '',
            'transaction_id'     => 'TXN-TEST-123',
        ]);

        $response->assertStatus(200);
        $payment->refresh();
        $this->assertEquals('succeeded', $payment->status);
    }

    // ─── 13. Webhook duplikat tidak menggandakan total_paid ──────────────────

    public function test_duplicate_webhook_not_doubles_total_paid(): void
    {
        \Illuminate\Support\Facades\Config::set('services.midtrans.server_key', '');

        $service = $this->makeBookingService();
        $result  = $service->createBooking($this->makeBookingData($this->pkg24h->id, now()->addDays(13)->format('Y-m-d')));
        $payment = $result['payment'];
        $booking = $result['booking'];

        $gross   = (string)(int)$payment->requested_amount;
        $payload = [
            'order_id'           => $payment->payment_code,
            'transaction_status' => 'settlement',
            'status_code'        => '200',
            'gross_amount'       => $gross . '.00',
            'signature_key'      => '',
            'transaction_id'     => 'TXN-DUP-123',
        ];

        $this->withoutMiddleware()->postJson('/webhooks/midtrans', $payload)->assertStatus(200);
        $this->withoutMiddleware()->postJson('/webhooks/midtrans', $payload)->assertStatus(200);

        $booking->refresh();
        $this->assertEquals($booking->total_amount, $booking->total_paid);
    }

    // ─── 14. Webhook signature tidak valid ditolak ───────────────────────────

    public function test_invalid_webhook_signature_rejected(): void
    {
        // Set a server key to enable signature verification
        \Illuminate\Support\Facades\Config::set('services.midtrans.server_key', 'test-key-for-rejection');

        $response = $this->withoutMiddleware()->postJson('/webhooks/midtrans', [
            'order_id'           => 'PAY-FAKE',
            'transaction_status' => 'settlement',
            'status_code'        => '200',
            'gross_amount'       => '100000.00',
            'signature_key'      => 'invalidsignature',
        ]);

        $response->assertStatus(403);
    }

    // ─── 15. Payment expired setelah 60 menit ───────────────────────────────

    public function test_payment_expires_after_60_minutes(): void
    {
        $service = $this->makeBookingService();
        $result  = $service->createBooking($this->makeBookingData($this->pkg24h->id, now()->addDays(14)->format('Y-m-d')));
        $payment = $result['payment'];
        $booking = $result['booking'];

        // Manually set expires_at to past
        $payment->expires_at = now()->subMinutes(5);
        $payment->save();

        $this->artisan('payments:expire')->assertSuccessful();

        $payment->refresh();
        $booking->refresh();
        $this->assertEquals('expired', $payment->status);
        $this->assertEquals('expired', $booking->booking_status);
    }

    // ─── 16. Driver tidak bisa arrived sebelum lunas ─────────────────────────

    public function test_driver_cannot_mark_arrived_before_full_payment(): void
    {
        // Create booking in delivered state with partial payment
        $customer = Customer::create([
            'full_name'    => 'Test Customer',
            'phone_number' => '6281234567' . rand(100, 999),
        ]);

        $booking = Booking::create([
            'booking_code'           => 'MXB-TEST-' . Str::random(4),
            'customer_id'            => $customer->id,
            'rental_package_id'      => $this->pkg24h->id,
            'playstation_unit_id'    => $this->unit1->id,
            'rental_start_at'        => now()->addDay(),
            'rental_end_at'          => now()->addDays(2),
            'delivery_address'       => 'Test Address',
            'latitude'               => -6.2,
            'longitude'              => 106.8,
            'package_price'          => 400000,
            'delivery_fee'           => 10000,
            'discount_amount'        => 0,
            'total_amount'           => 410000,
            'payment_option'         => 'deposit',
            'initial_payment_amount' => 50000,
            'total_paid'             => 50000,
            'remaining_amount'       => 360000,
            'payment_status'         => 'partial',
            'booking_status'         => 'delivered',
            'terms_accepted'         => true,
        ]);

        $this->actingAs($this->driverUser);

        $response = $this->from('/driver/pesanan/' . $booking->id)
            ->post("/driver/pesanan/{$booking->id}/selesai");

        // Should redirect back with errors (not mark arrived)
        $booking->refresh();
        $this->assertNotEquals('arrived', $booking->booking_status);
    }

    // ─── 17. Driver dapat arrived setelah lunas ──────────────────────────────

    public function test_driver_can_mark_arrived_after_full_payment(): void
    {
        $customer = Customer::create([
            'full_name'    => 'Test Customer Paid',
            'phone_number' => '6281234590' . rand(100, 999),
        ]);

        $booking = Booking::create([
            'booking_code'          => 'MXB-TEST-' . Str::random(4),
            'customer_id'           => $customer->id,
            'rental_package_id'     => $this->pkg24h->id,
            'playstation_unit_id'   => $this->unit1->id,
            'rental_start_at'       => now()->addDay(),
            'rental_end_at'         => now()->addDays(2),
            'delivery_address'      => 'Test Address',
            'latitude'              => -6.2,
            'longitude'             => 106.8,
            'package_price'         => 400000,
            'delivery_fee'          => 10000,
            'discount_amount'       => 0,
            'total_amount'          => 410000,
            'payment_option'        => 'full',
            'initial_payment_amount' => 410000,
            'total_paid'            => 410000,
            'remaining_amount'      => 0,
            'payment_status'        => 'paid',
            'booking_status'        => 'delivered',
            'terms_accepted'        => true,
        ]);

        $this->actingAs($this->driverUser);

        $response = $this->post("/driver/pesanan/{$booking->id}/selesai");
        $booking->refresh();
        $this->assertEquals('arrived', $booking->booking_status);
        $this->assertNotNull($booking->arrived_at);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
