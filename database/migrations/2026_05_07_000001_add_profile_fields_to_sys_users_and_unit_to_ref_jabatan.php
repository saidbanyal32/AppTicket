<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sys_users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('phone');
            $table->timestamp('last_login')->nullable()->after('photo');
        });

        Schema::table('ref_jabatan', function (Blueprint $table) {
            $table->foreignUuid('unit_id')->nullable()->after('level')->constrained('ref_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ref_jabatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::table('sys_users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'last_login']);
        });
    }
};
