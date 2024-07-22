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
            $table->string('range')->nullable();
            $table->string('shape')->nullable();
            $table->decimal('weight', 8, 3);
            $table->string('color')->nullable();
            $table->string('clarity')->nullable();
            $table->string('cut')->nullable();
            $table->string('polish')->nullable();
            $table->string('symmetry')->nullable();
            $table->string('fluorescence_intensity')->nullable();
            $table->decimal('length', 8, 3)->nullable();
            $table->decimal('width', 8, 3)->nullable();
            $table->decimal('height', 8, 3)->nullable();
            $table->decimal('ratio', 8, 3)->nullable();
            $table->string('lab')->nullable();
            $table->date('report_date')->nullable();
            $table->string('report_number')->nullable();
            $table->string('location')->nullable();
            $table->decimal('discounts', 8, 2)->nullable();
            $table->decimal('live_rap', 8, 2)->nullable();
            $table->decimal('rap_amount', 8, 2)->nullable();
            $table->decimal('price_per_carat', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('depth_percentage', 5, 2)->nullable();
            $table->decimal('table_percentage', 5, 2)->nullable();
            $table->decimal('crown_height', 5, 2)->nullable();
            $table->decimal('crown_angle', 5, 2)->nullable();
            $table->decimal('pavilion_depth', 5, 2)->nullable();
            $table->decimal('pavilion_angle', 5, 2)->nullable();
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
            $table->string('certificate_number')->nullable();
            $table->string('video_url')->nullable();
            $table->string('image_url')->nullable();
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
