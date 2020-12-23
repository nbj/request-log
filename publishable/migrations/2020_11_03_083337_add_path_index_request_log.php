<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPathIndexRequestLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('request_logs', static function (Blueprint $table) {
            $table->index("path");
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
            $table->dropIndex("request_logs_path_index");
        });
    }
}
