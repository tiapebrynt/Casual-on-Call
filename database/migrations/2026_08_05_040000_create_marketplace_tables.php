<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
        });

        Schema::create('companies', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name')->index(); $table->string('slug')->unique(); $table->string('industry')->nullable();
            $table->text('description')->nullable(); $table->string('logo')->nullable(); $table->string('website')->nullable();
            $table->string('address')->nullable(); $table->string('city')->nullable()->index(); $table->string('nib')->nullable();
            $table->string('tax_number')->nullable(); $table->string('verification_status')->default('pending')->index();
            $table->timestamp('verified_at')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('workers', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable(); $table->text('bio')->nullable(); $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable(); $table->string('address')->nullable(); $table->string('city')->nullable()->index();
            $table->unsignedTinyInteger('experience_years')->default(0); $table->string('cv_path')->nullable();
            $table->string('portfolio_url')->nullable(); $table->string('verification_status')->default('pending')->index();
            $table->boolean('is_available')->default(true)->index(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('skills', function (Blueprint $table): void { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->text('description')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('worker_skills', function (Blueprint $table): void { $table->foreignId('worker_id')->constrained()->cascadeOnDelete(); $table->foreignId('skill_id')->constrained()->cascadeOnDelete(); $table->string('level')->default('intermediate'); $table->unsignedTinyInteger('years')->default(0); $table->primary(['worker_id','skill_id']); });
        Schema::create('job_categories', function (Blueprint $table): void { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->string('icon')->nullable(); $table->text('description')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('marketplace_jobs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete(); $table->foreignId('job_category_id')->constrained()->restrictOnDelete();
            $table->string('title')->index(); $table->string('slug')->unique(); $table->longText('description'); $table->string('location')->index();
            $table->dateTime('starts_at'); $table->dateTime('ends_at'); $table->decimal('daily_rate', 14, 2); $table->unsignedSmallInteger('vacancies')->default(1);
            $table->string('status')->default('draft')->index(); $table->dateTime('application_deadline'); $table->timestamps(); $table->softDeletes();
            $table->index(['company_id','status']);
        });
        Schema::create('job_requirements', function (Blueprint $table): void { $table->id(); $table->foreignId('job_id')->constrained('marketplace_jobs')->cascadeOnDelete(); $table->string('requirement'); $table->boolean('is_mandatory')->default(true); $table->timestamps(); });
        Schema::create('applications', function (Blueprint $table): void {
            $table->id(); $table->foreignId('job_id')->constrained('marketplace_jobs')->cascadeOnDelete(); $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->text('cover_letter')->nullable(); $table->string('status')->default('pending')->index(); $table->timestamp('responded_at')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->unique(['job_id','worker_id']);
        });
        Schema::create('attendances', function (Blueprint $table): void { $table->id(); $table->foreignId('application_id')->constrained()->cascadeOnDelete(); $table->date('work_date')->index(); $table->dateTime('clock_in_at')->nullable(); $table->dateTime('clock_out_at')->nullable(); $table->decimal('latitude',10,7)->nullable(); $table->decimal('longitude',10,7)->nullable(); $table->string('status')->default('scheduled'); $table->text('notes')->nullable(); $table->timestamps(); $table->softDeletes(); $table->unique(['application_id','work_date']); });
        Schema::create('payments', function (Blueprint $table): void { $table->id(); $table->foreignId('application_id')->constrained()->restrictOnDelete(); $table->string('invoice_number')->unique(); $table->decimal('subtotal',14,2); $table->decimal('platform_fee',14,2)->default(0); $table->decimal('total',14,2); $table->string('status')->default('pending')->index(); $table->string('method')->nullable(); $table->string('transaction_reference')->nullable()->index(); $table->timestamp('paid_at')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('wallets', function (Blueprint $table): void { $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->decimal('balance',16,2)->default(0); $table->decimal('pending_balance',16,2)->default(0); $table->string('currency',3)->default('IDR'); $table->timestamps(); });
        Schema::create('wallet_transactions', function (Blueprint $table): void { $table->id(); $table->foreignId('wallet_id')->constrained()->cascadeOnDelete(); $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete(); $table->string('type')->index(); $table->decimal('amount',16,2); $table->decimal('balance_after',16,2); $table->string('reference')->unique(); $table->string('description'); $table->timestamps(); });
        Schema::create('ratings', function (Blueprint $table): void { $table->id(); $table->foreignId('application_id')->constrained()->cascadeOnDelete(); $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete(); $table->unsignedTinyInteger('score'); $table->timestamps(); $table->unique(['application_id','reviewer_id']); });
        Schema::create('reviews', function (Blueprint $table): void { $table->id(); $table->foreignId('rating_id')->unique()->constrained()->cascadeOnDelete(); $table->string('title')->nullable(); $table->text('body'); $table->boolean('is_visible')->default(true); $table->timestamps(); $table->softDeletes(); });
        Schema::create('reports', function (Blueprint $table): void { $table->id(); $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('subject'); $table->text('description'); $table->string('status')->default('open')->index(); $table->text('resolution')->nullable(); $table->timestamp('resolved_at')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('audit_logs', function (Blueprint $table): void { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('event')->index(); $table->nullableMorphs('auditable'); $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->ipAddress('ip_address')->nullable(); $table->text('user_agent')->nullable(); $table->timestamps(); });
        Schema::create('bookmarks', function (Blueprint $table): void { $table->foreignId('worker_id')->constrained()->cascadeOnDelete(); $table->foreignId('job_id')->constrained('marketplace_jobs')->cascadeOnDelete(); $table->timestamps(); $table->primary(['worker_id','job_id']); });
        Schema::create('settings', function (Blueprint $table): void { $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->string('type')->default('string'); $table->boolean('is_public')->default(false); $table->timestamps(); });
        Schema::create('notifications', function (Blueprint $table): void { $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps(); });
    }

    public function down(): void
    {
        foreach (['notifications','settings','bookmarks','audit_logs','reports','reviews','ratings','wallet_transactions','wallets','payments','attendances','applications','job_requirements','marketplace_jobs','job_categories','worker_skills','skills','workers','companies'] as $table) Schema::dropIfExists($table);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone','avatar','status','deleted_at']));
    }
};
