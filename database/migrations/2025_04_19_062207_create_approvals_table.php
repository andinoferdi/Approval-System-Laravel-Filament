<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->integer('level'); // 1: Direct Manager, 2: Dept Head, 3: HRD/Director
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
