<?php

namespace Database\Seeders;

use App\Models\{
    Application,
    Attendance,
    Company,
    Job,
    JobCategory,
    Payment,
    Rating,
    Review,
    Skill,
    User,
    WalletTransaction,
    Worker
};
use App\Notifications\WorkflowNotification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Roles & Permissions
        $permissions = [
            'users.manage', 'companies.manage', 'workers.manage',
            'jobs.manage', 'applications.manage', 'payments.manage',
            'reports.manage', 'settings.manage'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        foreach (['admin', 'company', 'worker'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        Role::findByName('admin')->syncPermissions($permissions);

        // 2. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@casualhub.id'],
            ['name' => 'Administrator CoC', 'password' => 'Password123!', 'email_verified_at' => now()]
        );
        $admin->syncRoles(['admin']);
        if (!$admin->wallet) $admin->wallet()->create();

        // 3. Categories
        $categoriesData = [
            'Hospitality' => 'restaurant',
            'Event' => 'celebration',
            'Retail' => 'storefront',
            'Logistik' => 'inventory_2',
        ];
        $categories = [];
        foreach ($categoriesData as $name => $icon) {
            $categories[$name] = JobCategory::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name, 'icon' => $icon]
            );
        }

        // 4. Skills
        $skillNames = [
            'Barista', 'Event Crew', 'Waiter', 'Kasir',
            'Warehouse Picker', 'Brand Promotor', 'Kitchen Steward',
            'Usher VIP', 'Packing & Sorting', 'Ticketing Staff'
        ];
        $skills = [];
        foreach ($skillNames as $name) {
            $skills[$name] = Skill::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name]
            );
        }

        // 5. Companies
        $companiesData = [
            [
                'name' => 'Nusa Hospitality Group',
                'email' => 'company@casualhub.id',
                'industry' => 'Hospitality',
                'city' => 'Jakarta Selatan',
                'address' => 'Jl. Jenderal Sudirman No. 88, SCBD',
                'description' => 'Grup jaringan hotel butik dan restoran fine dining terkemuka di Jakarta.',
                'balance' => 35000000,
            ],
            [
                'name' => 'Karya Event Nusantara',
                'email' => 'event@casualhub.id',
                'industry' => 'Event Organizer',
                'city' => 'Jakarta Pusat',
                'address' => 'Jl. MH Thamrin Kav. 28-30',
                'description' => 'Promotor festival musik nasional dan penyelenggara pameran berskala besar.',
                'balance' => 25000000,
            ],
            [
                'name' => 'Logistik Kilat Express',
                'email' => 'logistik@casualhub.id',
                'industry' => 'Logistik & Pergudangan',
                'city' => 'Bekasi',
                'address' => 'Kawasan Industri MM2100 Blok B12',
                'description' => 'Pusat distribusi logistik pergudangan modern dan rantai pasok express e-commerce.',
                'balance' => 40000000,
            ],
            [
                'name' => 'Mega Retail Indonesia',
                'email' => 'retail@casualhub.id',
                'industry' => 'Retail & Dept Store',
                'city' => 'Tangerang',
                'address' => 'Grand Boulevard BSD City No. 15',
                'description' => 'Jaringan department store dan supermarket ritel modern dengan ratusan gerai.',
                'balance' => 30000000,
            ],
        ];

        $companies = [];
        foreach ($companiesData as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                ['name' => $c['name'], 'password' => 'Password123!', 'email_verified_at' => now()]
            );
            $user->syncRoles(['company']);
            if (!$user->wallet) {
                $user->wallet()->create(['balance' => $c['balance']]);
            } else {
                $user->wallet()->update(['balance' => $c['balance']]);
            }

            $company = Company::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $c['name'],
                    'slug' => str($c['name'])->slug(),
                    'industry' => $c['industry'],
                    'description' => $c['description'],
                    'city' => $c['city'],
                    'address' => $c['address'],
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ]
            );
            $companies[] = $company;
        }

        // 6. Workers
        $workersData = [
            [
                'name' => 'Rizky Pratama',
                'email' => 'worker@casualhub.id',
                'phone' => '081234567890',
                'city' => 'Jakarta Selatan',
                'headline' => 'Barista & Event Crew Profesional',
                'bio' => 'Casual worker berpengalaman 3 tahun di bidang F&B and event management. Disiplin, energik, dan siap shift malam.',
                'exp' => 3,
                'skills' => ['Barista', 'Event Crew', 'Waiter'],
                'balance' => 2450000,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@casualhub.id',
                'phone' => '081298765432',
                'city' => 'Jakarta Pusat',
                'headline' => 'Brand Promotor & Usher VIP',
                'bio' => 'Memiliki komunikasi persuasif, berpenampilan rapi dan menarik. Terbiasa di acara pameran otomotif dan launching produk.',
                'exp' => 2,
                'skills' => ['Brand Promotor', 'Usher VIP', 'Kasir'],
                'balance' => 1800000,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@casualhub.id',
                'phone' => '081345678901',
                'city' => 'Bekasi',
                'headline' => 'Staff Pergudangan & Sorting Picker',
                'bio' => 'Teliti, terbiasa angkat beban, cek stok barang, serta packing rapi di gudang e-commerce.',
                'exp' => 4,
                'skills' => ['Warehouse Picker', 'Packing & Sorting'],
                'balance' => 1250000,
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi@casualhub.id',
                'phone' => '081398765432',
                'city' => 'Tangerang',
                'headline' => 'Kasir Retail & Customer Care',
                'bio' => 'Mahir mengoperasikan POS kasir, cek ketelitian uang, dan melayani pelanggan dengan senyum ramah.',
                'exp' => 2,
                'skills' => ['Kasir', 'Brand Promotor'],
                'balance' => 950000,
            ],
            [
                'name' => 'Dimas Ramadhan',
                'email' => 'dimas@casualhub.id',
                'phone' => '081567890123',
                'city' => 'Jakarta Barat',
                'headline' => 'Banquet Waiter & Steward',
                'bio' => 'Berpengalaman melayani jamuan pesta, kebersihan dapur hotel bintang 5, sigap dan beretika tinggi.',
                'exp' => 3,
                'skills' => ['Waiter', 'Kitchen Steward', 'Barista'],
                'balance' => 1600000,
            ],
        ];

        $workers = [];
        foreach ($workersData as $w) {
            $user = User::firstOrCreate(
                ['email' => $w['email']],
                [
                    'name' => $w['name'],
                    'phone' => $w['phone'],
                    'password' => 'Password123!',
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['worker']);
            if (!$user->wallet) {
                $user->wallet()->create(['balance' => $w['balance']]);
            } else {
                $user->wallet()->update(['balance' => $w['balance']]);
            }

            $worker = Worker::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'city' => $w['city'],
                    'headline' => $w['headline'],
                    'bio' => $w['bio'],
                    'experience_years' => $w['exp'],
                    'verification_status' => 'verified',
                ]
            );

            // attach skills
            $skillIds = [];
            foreach ($w['skills'] as $sName) {
                if (isset($skills[$sName])) {
                    $skillIds[$skills[$sName]->id] = ['level' => 'advanced', 'years' => $w['exp']];
                }
            }
            $worker->skills()->syncWithoutDetaching($skillIds);
            $workers[] = $worker;
        }

        // 7. Rich Jobs (16 Jobs)
        $jobsData = [
            // Company 0: Nusa Hospitality
            [0, 'Hospitality', 'Barista Weekend Shift Pagi', 'daily', 250000, 'Jakarta Selatan', 4, 'published', 5, 8],
            [0, 'Hospitality', 'Waiter Banquet Gala Dinner', 'daily', 300000, 'Jakarta Selatan', 6, 'published', 3, 4],
            [0, 'Hospitality', 'Kitchen Steward Restoran Bintang 5', 'daily', 275000, 'Jakarta Barat', 3, 'published', 7, 10],
            [0, 'Hospitality', 'Barista Pembukaan Outlet SCBD', 'project', 1250000, 'Jakarta Selatan', 2, 'published', 12, 16],
            [0, 'Hospitality', 'Server Cafe Pop-Up Weekend', 'daily', 225000, 'Jakarta Selatan', 5, 'expired', -5, -3],

            // Company 1: Karya Event Nusantara
            [1, 'Event', 'Event Crew Festival Musik Indie', 'daily', 350000, 'Jakarta Pusat', 8, 'published', 2, 4],
            [1, 'Event', 'Usher VIP Pameran Otomotif GIIAS', 'daily', 500000, 'Tangerang', 6, 'published', 6, 9],
            [1, 'Event', 'Stage Hand & Rigging Konser Rock', 'daily', 400000, 'Jakarta Pusat', 5, 'published', 4, 6],
            [1, 'Event', 'Pemasangan Booth Exhibition Hall', 'project', 1500000, 'Jakarta Pusat', 3, 'published', 10, 14],
            [1, 'Event', 'Ticketing Crew Teater Seni', 'daily', 275000, 'Jakarta Pusat', 4, 'expired', -8, -6],

            // Company 2: Logistik Kilat Express
            [2, 'Logistik', 'Warehouse Picker & Packing Shift Malam', 'daily', 300000, 'Bekasi', 10, 'published', 1, 3],
            [2, 'Logistik', 'Sorter Paket Kurir Express Weekend', 'daily', 280000, 'Bekasi', 8, 'published', 3, 5],
            [2, 'Logistik', 'Bongkar Muat Kontainer Gudang Pusat', 'project', 1100000, 'Bekasi', 4, 'published', 8, 11],

            // Company 3: Mega Retail Indonesia
            [3, 'Retail', 'Retail Store Assistant Weekend Promo', 'daily', 230000, 'Tangerang', 6, 'published', 2, 4],
            [3, 'Retail', 'Brand Promotor Gadget Flagship Store', 'daily', 375000, 'Jakarta Selatan', 4, 'published', 4, 7],
            [3, 'Retail', 'Kasir Tambahan Midnight Sale', 'daily', 260000, 'Tangerang', 5, 'published', 5, 8],
        ];

        $jobs = [];
        foreach ($jobsData as $idx => $j) {
            $company = $companies[$j[0]];
            $category = $categories[$j[1]];
            $startsAt = $j[8] >= 0 ? now()->addDays($j[8]) : now()->addDays($j[8]);
            $endsAt = $j[9] >= 0 ? now()->addDays($j[9]) : now()->addDays($j[9]);
            $deadline = $j[8] >= 0 ? now()->addDays(max(1, $j[8] - 2)) : now()->addDays($j[8] - 1);

            $job = Job::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'title' => $j[2],
                ],
                [
                    'job_category_id' => $category->id,
                    'slug' => str($j[2])->slug() . '-' . ($idx + 1),
                    'description' => "Bergabunglah bersama tim profesional {$company->name}. Posisi {$j[2]} ini membutuhkan kandidat disiplin, tepat waktu, berpenampilan bersih, dan memiliki dedikasi tinggi. Seluruh pembayaran upah dijamin aman dan cair tepat waktu via Casual on Call.",
                    'location' => $j[5],
                    'daily_rate' => $j[4],
                    'payment_type' => $j[3],
                    'vacancies' => $j[6],
                    'status' => $j[7],
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'application_deadline' => $deadline,
                ]
            );

            $job->requirements()->delete();
            $job->requirements()->createMany([
                ['requirement' => 'Minimal berusia 18 tahun'],
                ['requirement' => 'Disiplin, tepat waktu, dan komunikatif'],
                ['requirement' => 'Memiliki stamina fisik yang sehat'],
            ]);

            $jobs[] = $job;
        }

        // 8. Applications (Across all statuses: Pending, Accepted, Completed, Rejected)
        $w0 = $workers[0]; // Rizky
        $w1 = $workers[1]; // Siti
        $w2 = $workers[2]; // Budi
        $w3 = $workers[3]; // Dewi
        $w4 = $workers[4]; // Dimas

        // Application 1: Rizky (w0) - ACCEPTED on Job 0 (Barista Weekend)
        $app1 = Application::firstOrCreate(
            ['job_id' => $jobs[0]->id, 'worker_id' => $w0->id],
            [
                'cover_letter' => 'Saya memiliki pengalaman barista di specialty coffee shop selama 3 tahun. Siap bekerja weekend shift pagi.',
                'status' => 'accepted',
                'responded_at' => now()->subDay(),
            ]
        );
        Attendance::firstOrCreate(
            ['application_id' => $app1->id, 'work_date' => $jobs[0]->starts_at->toDateString()],
            ['status' => 'scheduled']
        );

        // Application 2: Rizky (w0) - COMPLETED on Job 5 (Event Crew Festival Musik)
        $app2 = Application::firstOrCreate(
            ['job_id' => $jobs[5]->id, 'worker_id' => $w0->id],
            [
                'cover_letter' => 'Siap bekerja sigap dan stamina prima untuk festival musik.',
                'status' => 'completed',
                'responded_at' => now()->subDays(5),
            ]
        );
        $pay1 = Payment::firstOrCreate(
            ['application_id' => $app2->id],
            [
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-001',
                'subtotal' => 1050000,
                'platform_fee' => 52500,
                'total' => 1102500,
                'status' => 'paid',
                'method' => 'CoC Wallet',
                'transaction_reference' => 'TRX-COC-908123',
                'paid_at' => now()->subDays(2),
            ]
        );

        // Application 3: Rizky (w0) - REJECTED on Job 1 (Waiter Banquet) -> to show motivational quote!
        Application::firstOrCreate(
            ['job_id' => $jobs[1]->id, 'worker_id' => $w0->id],
            [
                'cover_letter' => 'Ingin mencoba pengalaman baru di hospitality.',
                'status' => 'rejected',
                'responded_at' => now()->subDays(2),
            ]
        );

        // Application 4: Siti (w1) - COMPLETED on Job 6 (Usher VIP)
        $app4 = Application::firstOrCreate(
            ['job_id' => $jobs[6]->id, 'worker_id' => $w1->id],
            [
                'cover_letter' => 'Berpengalaman sebagai usher VIP pameran otomotif nasional.',
                'status' => 'completed',
                'responded_at' => now()->subDays(4),
            ]
        );
        Payment::firstOrCreate(
            ['application_id' => $app4->id],
            [
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-002',
                'subtotal' => 1500000,
                'platform_fee' => 75000,
                'total' => 1575000,
                'status' => 'paid',
                'method' => 'Bank Transfer BCA VA',
                'transaction_reference' => 'TRX-VA-882190',
                'paid_at' => now()->subDays(1),
            ]
        );

        // Application 5: Budi (w2) - ACCEPTED on Job 10 (Warehouse Picker)
        $app5 = Application::firstOrCreate(
            ['job_id' => $jobs[10]->id, 'worker_id' => $w2->id],
            [
                'cover_letter' => 'Siap shift malam pergudangan dengan stamina fisik prima.',
                'status' => 'accepted',
                'responded_at' => now()->subDay(),
            ]
        );
        Attendance::firstOrCreate(
            ['application_id' => $app5->id, 'work_date' => now()->toDateString()],
            ['status' => 'present', 'clock_in_at' => now()->subHours(3)]
        );


        // Application 6: Dewi (w3) - PENDING on Job 15 (Kasir Tambahan)
        Application::firstOrCreate(
            ['job_id' => $jobs[15]->id, 'worker_id' => $w3->id],
            [
                'cover_letter' => 'Terbiasa mengoperasikan kasir POS dan rekonsiliasi uang fisik secara teliti.',
                'status' => 'pending',
            ]
        );

        // Application 7: Dimas (w4) - PENDING on Job 2 (Kitchen Steward)
        Application::firstOrCreate(
            ['job_id' => $jobs[2]->id, 'worker_id' => $w4->id],
            [
                'cover_letter' => 'Siap bekerja menjaga kebersihan standar HACCP hotel bintang 5.',
                'status' => 'pending',
            ]
        );

        // Application 8: Siti (w1) - PENDING on Job 14 (Brand Promotor Gadget)
        Application::firstOrCreate(
            ['job_id' => $jobs[14]->id, 'worker_id' => $w1->id],
            [
                'cover_letter' => 'Saya memiliki passion tinggi di bidang teknologi dan gadget.',
                'status' => 'pending',
            ]
        );

        // 9. 2-Way Reviews & Ratings
        // Event company user & Rizky user
        $eventCompanyUser = $companies[1]->user;
        $rizkyUser = $w0->user;

        // Rizky reviews Event Company for App2
        $r1 = Rating::firstOrCreate(
            ['application_id' => $app2->id, 'reviewer_id' => $rizkyUser->id],
            ['reviewee_id' => $eventCompanyUser->id, 'score' => 5]
        );
        Review::firstOrCreate(
            ['rating_id' => $r1->id],
            [
                'title' => 'Pengalaman kerja luar biasa dan gaji cepat cair!',
                'body' => 'Manajemen event sangat profesional, koordinasi briefing jelas, konsumsi disediakan dengan layak, dan upah langsung masuk dompet CoC setelah event selesai.'
            ]
        );

        // Event Company reviews Rizky for App2
        $r2 = Rating::firstOrCreate(
            ['application_id' => $app2->id, 'reviewer_id' => $eventCompanyUser->id],
            ['reviewee_id' => $rizkyUser->id, 'score' => 5]
        );
        Review::firstOrCreate(
            ['rating_id' => $r2->id],
            [
                'title' => 'Worker pekerja keras dan sangat disiplin',
                'body' => 'Rizky datang sebelum shift dimulai, inisiatif tinggi dalam crowd control, dan sangat kooperatif dengan tim panitia.'
            ]
        );

        // 10. Notifications for demo accounts
        $rizkyUser->notifications()->delete();
        $rizkyUser->notify(new WorkflowNotification(
            'Lamaranmu Diterima!',
            'Selamat! Lamaranmu untuk posisi Barista Weekend Shift Pagi di Nusa Hospitality Group telah diterima.',
            route('jobs.my')
        ));
        $rizkyUser->notify(new WorkflowNotification(
            'Pembayaran Gaji Berhasil',
            'Upah sebesar Rp1.050.000 untuk pekerjaan Event Crew Festival Musik telah ditransfer ke dompet CoC Anda.',
            route('wallet.index')
        ));

        $companies[0]->user->notifications()->delete();
        $companies[0]->user->notify(new WorkflowNotification(
            'Pelamar Baru Masuk',
            'Kandidat Dimas Ramadhan baru saja melamar lowongan Kitchen Steward Restoran Bintang 5.',
            route('applications.index')
        ));
    }
}