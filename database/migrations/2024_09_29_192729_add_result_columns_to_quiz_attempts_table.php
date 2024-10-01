<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResultColumnsToQuizAttemptsTable extends Migration
{
    public function up()
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('score')->nullable()->after('answers'); // Add score column
            $table->boolean('is_passed')->default(false)->after('score'); // Add is_passed column
            $table->time('time_taken')->nullable()->after('is_passed'); // Add time_taken column
        });
    }

    public function down()
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['score', 'is_passed', 'time_taken']);
        });
    }
}
