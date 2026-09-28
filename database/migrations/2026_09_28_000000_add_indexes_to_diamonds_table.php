<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToDiamondsTable extends Migration
{
    /**
     * Speed up the inventory filters without changing query results.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('diamonds', function (Blueprint $table) {
            $table->index('status');
            $table->index('location');
            $table->index('shape');
            $table->index('color');
            $table->index('clarity');
            $table->index('cut');
            $table->index('polish');
            $table->index('symmetry');
            $table->index('lab');
            $table->index('reference');
            $table->index('growth_type');
            $table->index('report_number');
        });
    }

    /**
     * Reverse the indexes.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('diamonds', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['location']);
            $table->dropIndex(['shape']);
            $table->dropIndex(['color']);
            $table->dropIndex(['clarity']);
            $table->dropIndex(['cut']);
            $table->dropIndex(['polish']);
            $table->dropIndex(['symmetry']);
            $table->dropIndex(['lab']);
            $table->dropIndex(['reference']);
            $table->dropIndex(['growth_type']);
            $table->dropIndex(['report_number']);
        });
    }
}
