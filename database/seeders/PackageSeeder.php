<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'Free Trial', 'tagline' => 'Coba gratis selama 1 bulan', 'price' => '0', 'price_period' => '/ 1 Bulan', 'duration_hours' => 48, 'kak_vesta_tokens' => 10000, 'is_popular' => false, 'benefits' => ['Akses database regulasi', 'Tanya Kak Vesta terbatas']],
            ['name' => 'Personel Bulanan', 'tagline' => 'Untuk kebutuhan jangka pendek', 'price' => '150.000', 'price_period' => '/ 1 Bulan', 'duration_hours' => null, 'kak_vesta_tokens' => 50000, 'is_popular' => false, 'benefits' => ['Akses database regulasi', 'Tanya Kak Vesta', 'Compliance Monitoring']],
            ['name' => 'Personel 6 Bulan', 'tagline' => 'Lebih hemat untuk 6 bulan', 'price' => '800.000', 'price_period' => '/ 6 Bulan', 'duration_hours' => null, 'kak_vesta_tokens' => 300000, 'is_popular' => true, 'benefits' => ['Semua fitur Bulanan', 'Reviews & Reports']],
            ['name' => 'Personel 1 Tahun', 'tagline' => 'Pilihan terbaik untuk setahun', 'price' => '1.500.000', 'price_period' => '/ 1 Tahun', 'duration_hours' => null, 'kak_vesta_tokens' => 600000, 'is_popular' => false, 'benefits' => ['Semua fitur 6 Bulan', 'Prioritas konsultasi']],
            ['name' => 'Personel 2 Tahun', 'tagline' => 'Paling hemat untuk jangka panjang', 'price' => '2.500.000', 'price_period' => '/ 2 Tahun', 'duration_hours' => null, 'kak_vesta_tokens' => 1200000, 'is_popular' => false, 'benefits' => ['Semua fitur 1 Tahun', 'Dukungan prioritas']],
        ];

        foreach ($packages as $sort => $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                [...$package, 'is_active' => true, 'sort' => $sort],
            );
        }
    }
}
