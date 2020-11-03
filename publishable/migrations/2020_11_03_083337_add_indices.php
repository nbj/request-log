<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('request_logs', static function (Blueprint $table) {
            $table->index("created_at");
            $table->index("status");
        });

        Schema::table('request_log_blacklisted_routes', static function (Blueprint $table) {
            $table->index("created_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('request_logs', static function (Blueprint $table) {
            $table->dropIndex("request_logs_created_at_index");
            $table->dropIndex("request_logs_status_index");
        });

        Schema::table('request_log_blacklisted_routes', static function (Blueprint $table) {
            $table->dropIndex("request_log_blacklisted_routes_created_at_index");
        });
    }
}
