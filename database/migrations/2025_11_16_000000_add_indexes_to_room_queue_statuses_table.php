<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToRoomQueueStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_queue_statuses', function (Blueprint $table) {
            // Add composite index for user_id and created_at for better query performance
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            
            // Add index for queue_status_id
            $table->index('queue_status_id', 'idx_queue_status');
            
            // Add composite index for user, status, and date filtering
            $table->index(['user_id', 'queue_status_id', 'created_at'], 'idx_user_status_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_queue_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_user_created');
            $table->dropIndex('idx_queue_status');
            $table->dropIndex('idx_user_status_created');
        });
    }
}
