<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('ip_address');
            $table->timestamp('login_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_audits');
    }
};
