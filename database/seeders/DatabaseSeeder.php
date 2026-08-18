<?php

namespace Database\Seeders;

use App\Models\{Application,Attendance,Company,Job,JobCategory,Payment,Skill,User,WalletTransaction,Worker};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions=['users.manage','companies.manage','workers.manage','jobs.manage','applications.manage','payments.manage','reports.manage','settings.manage'];
        foreach($permissions as $permission) \Spatie\Permission\Models\Permission::firstOrCreate(['name'=>$permission]);
        foreach(['admin','company','worker'] as $role) \Spatie\Permission\Models\Role::firstOrCreate(['name'=>$role]);
        \Spatie\Permission\Models\Role::findByName('admin')->syncPermissions($permissions);
        $admin=User::factory()->create(['name'=>'Administrator','email'=>'admin@casualhub.id','password'=>'Password123!','email_verified_at'=>now()]); $admin->assignRole('admin'); $admin->wallet()->create();
        $companyUser=User::factory()->create(['name'=>'Nadia Putri','email'=>'company@casualhub.id','password'=>'Password123!','email_verified_at'=>now()]); $companyUser->assignRole('company'); $company=$companyUser->company()->create(['name'=>'Nusa Hospitality Group','slug'=>'nusa-hospitality-group','industry'=>'Hospitality','description'=>'Grup hospitality Indonesia yang menghadirkan pengalaman layanan terbaik.','city'=>'Jakarta','address'=>'Jl. Sudirman No. 88','verification_status'=>'verified','verified_at'=>now()]); $companyUser->wallet()->create(['balance'=>25000000]);
        $workerUser=User::factory()->create(['name'=>'Rizky Pratama','email'=>'worker@casualhub.id','password'=>'Password123!','email_verified_at'=>now()]); $workerUser->assignRole('worker'); $worker=$workerUser->worker()->create(['headline'=>'Event Crew & Barista','bio'=>'Casual worker berpengalaman, disiplin, dan siap bekerja dalam tim.','city'=>'Jakarta','experience_years'=>3,'verification_status'=>'verified']); $workerUser->wallet()->create(['balance'=>1850000]);
        foreach(['Barista','Event Crew','Waiter','Kasir','Warehouse','Promotor'] as $name) Skill::create(['name'=>$name,'slug'=>str($name)->slug()]);
        $worker->skills()->attach(Skill::whereIn('name',['Barista','Event Crew'])->pluck('id'),['level'=>'advanced','years'=>2]);
        $categories=collect(['Hospitality'=>'restaurant','Event'=>'celebration','Retail'=>'storefront','Logistik'=>'inventory_2'])->mapWithKeys(fn($icon,$name)=>[$name=>JobCategory::create(['name'=>$name,'slug'=>str($name)->slug(),'icon'=>$icon])]);
        $jobs=[['Barista Weekend','Hospitality','Jakarta Selatan',250000],['Event Crew Festival Musik','Event','Jakarta Pusat',350000],['Retail Store Assistant','Retail','Tangerang',225000],['Warehouse Picker','Logistik','Bekasi',275000],['Waiter Banquet Hotel','Hospitality','Jakarta Barat',300000],['Brand Promotor','Retail','Depok',325000]];
        foreach($jobs as $i=>[$title,$category,$location,$rate]){$job=Job::create(['company_id'=>$company->id,'job_category_id'=>$categories[$category]->id,'title'=>$title,'slug'=>str($title)->slug().'-'.($i+1),'description'=>'Bergabunglah dengan tim profesional kami untuk pekerjaan harian yang terjadwal, lingkungan suportif, dan pembayaran transparan melalui CasualHub.','location'=>$location,'starts_at'=>now()->addDays(10+$i),'ends_at'=>now()->addDays(12+$i),'daily_rate'=>$rate,'vacancies'=>5+$i,'status'=>'published','application_deadline'=>now()->addDays(7+$i)]);$job->requirements()->createMany([['requirement'=>'Berusia minimal 18 tahun'],['requirement'=>'Disiplin dan mampu bekerja dalam tim']]);}
        $accepted=Application::create(['job_id'=>Job::first()->id,'worker_id'=>$worker->id,'cover_letter'=>'Saya siap bekerja sesuai jadwal dan memiliki pengalaman relevan.','status'=>'accepted','responded_at'=>now()]);
        Attendance::create(['application_id'=>$accepted->id,'work_date'=>now()->addDays(10)->toDateString(),'status'=>'scheduled']);
        $completed=Application::create(['job_id'=>Job::skip(1)->first()->id,'worker_id'=>$worker->id,'cover_letter'=>'Lamaran pekerjaan event crew.','status'=>'completed','responded_at'=>now()->subDays(3)]);
        $payment=Payment::create(['application_id'=>$completed->id,'invoice_number'=>'INV-'.now()->format('Ymd').'-001','subtotal'=>1050000,'platform_fee'=>52500,'total'=>1102500,'status'=>'paid','method'=>'Bank Transfer','transaction_reference'=>'TRX-DEMO-001','paid_at'=>now()->subDay()]);
        WalletTransaction::create(['wallet_id'=>$workerUser->wallet->id,'payment_id'=>$payment->id,'type'=>'credit','amount'=>1050000,'balance_after'=>$workerUser->wallet->balance,'reference'=>'WT-DEMO-001','description'=>'Pembayaran Event Crew Festival Musik']);
    }
}
