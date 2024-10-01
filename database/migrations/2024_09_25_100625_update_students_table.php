<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            // Remove the foreign key constraint and the user_id column
            if (Schema::hasColumn('students', 'user_id')) {
                $table->dropForeign(['user_id']); // Drop foreign key constraint
                $table->dropColumn('user_id'); // Drop user_id column
            }

            // Remove the old CV column if no longer needed
            if (Schema::hasColumn('students', 'cv')) {
                $table->dropColumn('cv');
            }

            // Add new columns according to the updated requirements
            $table->string('name')->after('id'); // Add name column
            $table->string('email')->unique()->after('name'); // Add email column with unique constraint
            $table->string('phone_no')->after('email'); // Add phone number column
            $table->string('cv_path')->nullable()->after('phone_no'); // Add column to store CV file path
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            // Reverse the changes made in the up() method
            $table->dropColumn(['name', 'email', 'phone_no', 'cv_path']); // Drop new columns

            // Re-add the previous columns if needed (optional)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('cv')->nullable();
        });
    }
};
