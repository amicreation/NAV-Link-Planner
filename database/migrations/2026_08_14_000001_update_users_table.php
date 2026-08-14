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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer')->after('email');
            $table->string('mobile_number')->nullable()->after('role');
            $table->string('company')->nullable()->after('mobile_number');
            $table->string('designation')->nullable()->after('company');
            $table->string('department')->nullable()->after('designation');
            $table->string('status')->default('active')->after('department');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->foreignId('created_by_id')->nullable()->after('last_login_at')->constrained('users')->nullOnDelete();
            $table->string('profile_photo')->nullable()->after('created_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropColumn([
                'role',
                'mobile_number',
                'company',
                'designation',
                'department',
                'status',
                'last_login_at',
                'created_by_id',
                'profile_photo'
            ]);
        });
    }
};
