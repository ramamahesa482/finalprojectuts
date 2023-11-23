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
        Schema::table('patients_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('status');
            $table->foreign('status')->references('status')->on('patients_status')->onUpdate('restrict')->onCascade('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients_records', function (Blueprint $table) {
            $table->dropForeign('patients_records_status_foreign');
            $table->dropIndex('patients_records_status_foreign');
            $table->dropColumn('status');
        });
    }
};
