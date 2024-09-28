<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiamondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diamonds', function (Blueprint $table) {
            $table->id();
            $table->string('stock_id')->unique();
            $table->string('growth_type')->nullable();
            $table->string('status')->nullable();
            $table->string('reference')->nullable();
            $table->string('range')->nullable();
            $table->string('shape')->nullable();
            $table->string('weight')->nullable();
            $table->string('color')->nullable();
            $table->string('clarity')->nullable();
            $table->string('cut')->nullable();
            $table->string('polish')->nullable();
            $table->string('symmetry')->nullable();
            $table->string('fluorescence_intensity')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->decimal('ratio')->nullable();
            $table->string('lab')->nullable();
            $table->date('report_date')->nullable();
            $table->string('report_number')->nullable();
            $table->string('location')->nullable();
            $table->string('discounts', 8, 2)->nullable();
            $table->string('live_rap', 8, 2)->nullable();
            $table->decimal('rap_amount', 8, 2)->nullable();
            $table->decimal('price_per_carat', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('bargaining_price_per_carat', 10, 2)->nullable();
            $table->decimal('bargaining_total_price', 10, 2)->nullable();
            $table->string('depth_percentage', 5, 2)->nullable();
            $table->string('table_percentage', 5, 2)->nullable();
            $table->string('crown_height', 5, 2)->nullable();
            $table->string('crown_angle', 5, 2)->nullable();
            $table->string('pavilion_depth', 5, 2)->nullable();
            $table->string('pavilion_angle', 5, 2)->nullable();
            $table->string('inscription')->nullable();
            $table->text('key_to_symbols')->nullable();
            $table->text('white_inclusion')->nullable();
            $table->text('black_inclusion')->nullable();
            $table->text('open_inclusion')->nullable();
            $table->string('fancy_color')->nullable();
            $table->string('fancy_color_intensity')->nullable();
            $table->string('fancy_color_overtone')->nullable();
            $table->string('girdle_percentage')->nullable();
            $table->string('girdle')->nullable();
            $table->string('culet')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('cert_url')->nullable();
            $table->text('video_url')->nullable();
            $table->text('image_url')->nullable();
            $table->string('treatment')->nullable();
            $table->string('country')->nullable();
            $table->text('cert_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diamonds');
    }
}
