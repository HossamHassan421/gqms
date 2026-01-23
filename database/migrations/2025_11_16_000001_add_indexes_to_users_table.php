<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add index for type field for faster filtering
            $table->index('type', 'idx_users_type');
            
            // Add index for uuid for faster lookups
            $table->index('uuid', 'idx_users_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type');
            $table->dropIndex('idx_users_uuid');
        });
    }
}
