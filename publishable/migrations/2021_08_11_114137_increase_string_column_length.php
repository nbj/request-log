<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseStringColumnLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('request_logs', static function (Blueprint $table) {
            $table->string('query_string', 2000)->change();
            $table->string('url', 2000)->change();
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
            $table->string('query')->change();
            $table->string('url')->change();
        });
    }
}
