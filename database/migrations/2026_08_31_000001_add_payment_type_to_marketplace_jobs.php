<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('marketplace_jobs', fn(Blueprint $table) => $table->string('payment_type')->default('daily')->after('daily_rate')); } public function down(): void { Schema::table('marketplace_jobs', fn(Blueprint $table) => $table->dropColumn('payment_type')); } };
