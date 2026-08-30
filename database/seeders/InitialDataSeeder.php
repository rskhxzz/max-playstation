<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\BusinessSetting;
use App\Models\RentalPackage;
use App\Models\PlaystationUnit;
use App\Models\DeliveryRate;
use App\Models\Faq;
use App\Models\TermsCondition;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Roles ─────────────────────────────────────────────────────────
        $adminRole = AuthRole::firstOrCreate(
            ['name' => 'Admin'],
            ['id' => (string) Str::uuid(), 'description' => 'Administrator']
        );

        $driverRole = AuthRole::firstOrCreate(
            ['name' => 'Driver'],
            ['id' => (string) Str::uuid(), 'description' => 'Driver / Kurir']
        );

        // ─── Users ─────────────────────────────────────────────────────────
        $existingAdmin = AuthUser::withoutGlobalScope('active')
            ->where('username', 'admin')->first();

        if (!$existingAdmin) {
            AuthUser::withoutGlobalScope('active')->create([
                'id'         => (string) Str::uuid(),
                'name'       => 'Administrator',
                'username'   => 'admin',
                'email'      => 'admin@maxibox.id',
                'password'   => Hash::make('admin123'),
                'role_id'    => $adminRole->id,
                'active'     => true,
                'is_deleted' => false,
            ]);
            $this->command->info('Admin user created: username=admin, password=admin123');
        } else {
            $this->command->info('Admin user already exists.');
        }

        $existingDriver = AuthUser::withoutGlobalScope('active')
            ->where('username', 'driver')->first();

        if (!$existingDriver) {
            AuthUser::withoutGlobalScope('active')->create([
                'id'         => (string) Str::uuid(),
                'name'       => 'Driver Satu',
                'username'   => 'driver',
                'email'      => 'driver@maxibox.id',
                'password'   => Hash::make('driver123'),
                'role_id'    => $driverRole->id,
                'active'     => true,
                'is_deleted' => false,
            ]);
            $this->command->info('Driver user created: username=driver, password=driver123');
        } else {
            $this->command->info('Driver user already exists.');
        }

        // ─── Business Setting ──────────────────────────────────────────────
        if (BusinessSetting::withoutGlobalScope('not_deleted')->where('is_deleted', false)->count() === 0) {
            BusinessSetting::create([
                'business_name'          => 'Maxibox Playstation',
                'phone_number'           => '6281234567890',
                'address'                => 'Jl. Contoh No. 1, Kota Anda',
                'latitude'               => -6.200000,
                'longitude'              => 106.816666,
                'down_payment_amount'    => 50000,
                'payment_expiry_minutes' => 60,
                'maximum_delivery_km'    => 20,
                'is_active'              => true,
            ]);
            $this->command->info('Business setting created.');
        }

        // ─── Rental Packages ──────────────────────────────────────────────
        if (RentalPackage::count() === 0) {
            $packages = [
                ['code' => 'PKG-6H',  'name' => 'Paket 6 Jam',  'duration_hours' => 6,  'price' => 150000, 'blocked_start_time' => '15:00', 'blocked_end_time' => '00:00', 'description' => 'Sewa PS4 selama 6 jam. Tidak tersedia mulai pukul 15.00.'],
                ['code' => 'PKG-12H', 'name' => 'Paket 12 Jam', 'duration_hours' => 12, 'price' => 250000, 'blocked_start_time' => '13:00', 'blocked_end_time' => '19:00', 'description' => 'Sewa PS4 selama 12 jam. Tidak tersedia pukul 13.00–19.00.'],
                ['code' => 'PKG-24H', 'name' => 'Paket 24 Jam', 'duration_hours' => 24, 'price' => 400000, 'blocked_start_time' => null,    'blocked_end_time' => null,    'description' => 'Sewa PS4 selama 24 jam. Tersedia kapan saja.'],
            ];

            foreach ($packages as $pkg) {
                RentalPackage::create($pkg + ['is_active' => true]);
            }
            $this->command->info('Rental packages created.');
        }

        // ─── PlayStation Units ─────────────────────────────────────────────
        if (PlaystationUnit::count() === 0) {
            PlaystationUnit::create(['unit_code' => 'PS4-01', 'name' => 'PlayStation 4 Unit 1', 'console_type' => 'PS4', 'status' => 'available', 'is_active' => true]);
            PlaystationUnit::create(['unit_code' => 'PS4-02', 'name' => 'PlayStation 4 Unit 2', 'console_type' => 'PS4', 'status' => 'available', 'is_active' => true]);
            $this->command->info('PlayStation units created.');
        }

        // ─── Delivery Rates ────────────────────────────────────────────────
        if (DeliveryRate::count() === 0) {
            $rates = [
                ['name' => 'Zona 1 (0–5 km)',    'minimum_distance_km' => 0,  'maximum_distance_km' => 5,  'delivery_fee' => 10000],
                ['name' => 'Zona 2 (5–10 km)',   'minimum_distance_km' => 5,  'maximum_distance_km' => 10, 'delivery_fee' => 20000],
                ['name' => 'Zona 3 (10–15 km)',  'minimum_distance_km' => 10, 'maximum_distance_km' => 15, 'delivery_fee' => 30000],
                ['name' => 'Zona 4 (15–20 km)',  'minimum_distance_km' => 15, 'maximum_distance_km' => 20, 'delivery_fee' => 40000],
            ];
            foreach ($rates as $rate) {
                DeliveryRate::create($rate + ['is_active' => true]);
            }
            $this->command->info('Delivery rates created.');
        }

        // ─── FAQ ───────────────────────────────────────────────────────────
        if (Faq::count() === 0) {
            $faqs = [
                ['question' => 'Apa saja yang termasuk dalam paket sewa?',        'answer' => 'Paket sewa mencakup PlayStation 4, 2 controller, dan antar jemput ke lokasi Anda.',                             'seq' => 1],
                ['question' => 'Berapa lama proses pengiriman?',                   'answer' => 'Pengiriman biasanya memakan waktu 30–60 menit tergantung lokasi.',                                             'seq' => 2],
                ['question' => 'Apakah bisa bayar sebagian dulu?',                 'answer' => 'Ya! Anda bisa membayar DP sebesar Rp50.000 dan melunasi saat PlayStation tiba.',                              'seq' => 3],
                ['question' => 'Apakah ada game yang tersedia?',                   'answer' => 'PS4 dilengkapi berbagai game populer. Hubungi kami untuk daftar game yang tersedia.',                         'seq' => 4],
                ['question' => 'Bagaimana jika ada kerusakan pada unit?',          'answer' => 'Kerusakan karena kelalaian customer menjadi tanggung jawab customer. Gunakan dengan hati-hati.',              'seq' => 5],
            ];
            foreach ($faqs as $faq) {
                Faq::create($faq + ['is_published' => true]);
            }
            $this->command->info('FAQs created.');
        }

        // ─── Terms & Conditions ────────────────────────────────────────────
        if (TermsCondition::count() === 0) {
            TermsCondition::create([
                'title'     => 'Syarat dan Ketentuan Penyewaan PlayStation',
                'content'   => "1. Customer wajib menyediakan tempat yang aman untuk PlayStation.\n2. Penggunaan PlayStation hanya untuk keperluan gaming pribadi.\n3. Customer bertanggung jawab atas kerusakan akibat kelalaian.\n4. Pembayaran DP tidak dapat dikembalikan jika dibatalkan oleh customer.\n5. Maxibox Playstation berhak membatalkan pesanan jika terjadi kondisi yang tidak memungkinkan.\n6. Customer wajib hadir saat pengantaran dan pengambilan.\n7. Pastikan nomor HP dapat dihubungi untuk koordinasi pengiriman.",
                'version'   => 1,
                'is_active' => true,
            ]);
            $this->command->info('Terms & conditions created.');
        }

        $this->command->info('✅ Initial data seeder selesai!');
    }
}
