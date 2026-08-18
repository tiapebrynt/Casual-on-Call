<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIcons = [
            'Hospitality' => 'restaurant',
            'Event' => 'celebration',
            'Retail' => 'storefront',
            'Logistik' => 'inventory_2',
        ];

        $categories = collect($categoryIcons)->mapWithKeys(function (string $icon, string $name) {
            $category = JobCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon],
            );
            return [$name => $category];
        });

        $companies = collect([
            ['Nusa Hospitality Group', 'company@casualhub.id', 'Hospitality', 'Jakarta Selatan'],
            ['Kreasi Nusantara Event', 'event@casualhub.id', 'Event Organizer', 'Jakarta Pusat'],
            ['Ritel Kita Indonesia', 'retail@casualhub.id', 'Retail', 'Tangerang'],
            ['Gerak Cepat Logistik', 'logistik@casualhub.id', 'Logistik', 'Bekasi'],
        ])->mapWithKeys(function (array $item) {
            [$name, $email, $industry, $city] = $item;
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'Password123!', 'email_verified_at' => now()],
            );
            $user->assignRole('company');
            $user->wallet()->firstOrCreate([]);
            $company = Company::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['user_id' => $user->id, 'name' => $name, 'industry' => $industry, 'city' => $city, 'verification_status' => 'verified', 'verified_at' => now()],
            );
            return [$name => $company];
        });

        $jobs = [
            ['Barista Weekend', 'Nusa Hospitality Group', 'Hospitality', 'Jakarta Selatan', 275000, 6],
            ['Waiter Banquet Hotel', 'Nusa Hospitality Group', 'Hospitality', 'Jakarta Barat', 310000, 12],
            ['Kitchen Helper', 'Nusa Hospitality Group', 'Hospitality', 'Jakarta Pusat', 260000, 8],
            ['Housekeeping Harian', 'Nusa Hospitality Group', 'Hospitality', 'Bogor', 250000, 10],
            ['Event Crew Festival Musik', 'Kreasi Nusantara Event', 'Event', 'Jakarta Pusat', 375000, 20],
            ['Registration Crew Conference', 'Kreasi Nusantara Event', 'Event', 'ICE BSD', 325000, 14],
            ['Wedding Usher', 'Kreasi Nusantara Event', 'Event', 'Jakarta Selatan', 300000, 10],
            ['Stage Production Assistant', 'Kreasi Nusantara Event', 'Event', 'Ancol', 400000, 8],
            ['Retail Store Assistant', 'Ritel Kita Indonesia', 'Retail', 'Tangerang', 250000, 9],
            ['Brand Promotor Elektronik', 'Ritel Kita Indonesia', 'Retail', 'Depok', 350000, 12],
            ['Kasir Pop-up Store', 'Ritel Kita Indonesia', 'Retail', 'Jakarta Utara', 275000, 6],
            ['Stock Opname Crew', 'Ritel Kita Indonesia', 'Retail', 'Bekasi', 290000, 15],
            ['Warehouse Picker', 'Gerak Cepat Logistik', 'Logistik', 'Bekasi', 300000, 18],
            ['Packer E-commerce', 'Gerak Cepat Logistik', 'Logistik', 'Tangerang', 285000, 20],
            ['Sortation Hub Crew', 'Gerak Cepat Logistik', 'Logistik', 'Jakarta Timur', 315000, 15],
            ['Helper Pengiriman', 'Gerak Cepat Logistik', 'Logistik', 'Depok', 300000, 10],
        ];

        foreach ($jobs as $index => $item) {
            [$title, $companyName, $categoryName, $location, $rate, $vacancies] = $item;
            $start = now()->addDays(14 + $index);
            $job = Job::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($title).'-casualhub'],
                [
                    'company_id' => $companies[$companyName]->id,
                    'job_category_id' => $categories[$categoryName]->id,
                    'title' => $title,
                    'description' => "Bergabung sebagai {$title} dalam tim profesional yang suportif. Pekerjaan memiliki jadwal yang jelas, briefing sebelum shift, serta pembayaran transparan melalui CasualHub.",
                    'location' => $location,
                    'starts_at' => $start,
                    'ends_at' => $start->copy()->addDays(2),
                    'daily_rate' => $rate,
                    'vacancies' => $vacancies,
                    'status' => 'published',
                    'application_deadline' => $start->copy()->subDays(4),
                    'deleted_at' => null,
                ],
            );
            $job->requirements()->delete();
            $job->requirements()->createMany([
                ['requirement' => 'Berusia minimal 18 tahun'],
                ['requirement' => 'Disiplin, komunikatif, dan mampu bekerja dalam tim'],
                ['requirement' => 'Bersedia mengikuti briefing dan jadwal kerja yang ditentukan'],
            ]);
        }
    }
}
