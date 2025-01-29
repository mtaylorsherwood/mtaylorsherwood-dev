<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('author');
            $table->integer('pages');
            $table->integer('word_count');
            $table->integer('category');
            $table->integer('genre');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('rating')->default(-1);

            $table->timestamps();
        });
    }
};
