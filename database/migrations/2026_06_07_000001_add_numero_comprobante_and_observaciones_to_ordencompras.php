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
        Schema::table('ordencompras', function (Blueprint $table) {
            $table->string('numero_comprobante')->nullable()->after('tipopago');
            $table->text('observaciones')->nullable()->after('numero_comprobante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordencompras', function (Blueprint $table) {
            $table->dropColumn(['numero_comprobante', 'observaciones']);
        });
    }
};
