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
            $table->string('role')->default('admin_outlet')->after('password');
            
            $table->foreignId('outlet_id')
                  ->nullable() 
                  ->after('role')
                  ->constrained('outlets'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            
            $table->dropColumn(['role', 'outlet_id']);
        });
    }
};
