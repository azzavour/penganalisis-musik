<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trackId')->unique();
            $table->string('trackName')->nullable()->index();
            $table->unsignedBigInteger('artistId');
            $table->string('artistName')->index();
            $table->string('collectionName')->nullable();
            $table->string('primaryGenreName')->index();
            $table->dateTime('releaseDate');
            $table->decimal('trackPrice', 8, 2)->nullable();
            $table->decimal('collectionPrice', 8, 2)->nullable();
            $table->string('country');
            $table->string('currency');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('music_tracks');
    }
};