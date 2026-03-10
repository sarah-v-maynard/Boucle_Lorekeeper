<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderToCharactersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
         Schema::table('characters', function (Blueprint $table) {
            $table->string('BOUC_gender', 300)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('BOUC_gender');
        });
    }
};
