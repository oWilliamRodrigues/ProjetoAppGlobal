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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('mp_preference_id')->nullable()->after('amount');
            $table->string('mp_payment_id')->nullable()->after('mp_preference_id');
            $table->string('mp_payment_status')->nullable()->after('mp_payment_id');
            $table->index('mp_preference_id');
            $table->index('mp_payment_id');
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('mp_preference_id');
            $table->dropIndex('mp_payment_id');
            $table->dropColumn(['mp_preference_id', 'mp_payment_id', 'mp_payment_status']);
        });
    }
};
