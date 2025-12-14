<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();

            $table->string('sensor');
            $table->string('uid');
            $table->dateTime('timestamp');
            $table->decimal('temperature');
            $table->decimal('humidity');
            $table->decimal('pressure');
            $table->decimal('noise');
            $table->decimal('pm1');
            $table->decimal('pm2_5');
            $table->decimal('pm10');
            $table->decimal('voltage', places: 3);

            $table->timestamps();
        });
    }
};
