<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('pin_hash')->nullable();
            $table->string('claim_role')->default('Pemohon');
            $table->string('region')->nullable();
            $table->string('business_entity')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('claim_budgets', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('category', 40); $table->unsignedInteger('period_year'); $table->unsignedTinyInteger('period_month'); $table->unsignedBigInteger('amount'); $table->timestamps(); $table->unique(['user_id', 'category', 'period_year', 'period_month']);
        });
        Schema::create('claims', function (Blueprint $table) {
            $table->id(); $table->string('number', 30)->unique(); $table->foreignId('applicant_id')->constrained('users'); $table->string('region')->nullable(); $table->string('business_entity')->nullable(); $table->string('category', 40); $table->string('type', 60); $table->unsignedBigInteger('total_amount'); $table->string('status', 40)->default('Draft'); $table->text('note'); $table->json('details')->nullable(); $table->unsignedInteger('revision_count')->default(0); $table->timestamps(); $table->index(['applicant_id', 'status']); $table->index(['region', 'business_entity', 'status']);
        });
        Schema::create('claim_receipts', function (Blueprint $table) {
            $table->id(); $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete(); $table->unsignedBigInteger('amount'); $table->date('receipt_date'); $table->string('stored_path'); $table->string('original_name'); $table->string('mime_type', 120); $table->unsignedBigInteger('size_bytes'); $table->timestamps(); $table->index(['receipt_date', 'amount']);
        });
        Schema::create('claim_audit_entries', function (Blueprint $table) {
            $table->id(); $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete(); $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('actor_role', 60)->nullable(); $table->string('action', 60); $table->text('note')->nullable(); $table->json('before_values')->nullable(); $table->json('after_values')->nullable(); $table->timestamp('created_at'); $table->index(['claim_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('claim_audit_entries'); Schema::dropIfExists('claim_receipts'); Schema::dropIfExists('claims'); Schema::dropIfExists('claim_budgets');
        Schema::table('users', function (Blueprint $table) { $table->dropUnique(['username']); $table->dropColumn(['username', 'pin_hash', 'claim_role', 'region', 'business_entity', 'is_active']); });
    }
};
