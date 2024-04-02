<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mc_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_key')->nullable();

            $table->string('key');
            $table->text('value')->nullable();
            // $table->text('description');

            // $table->string('group')->default('general');
            $table->timestamps();

        });

        Schema::table('mc_settings', function (Blueprint $table)
        {
            $table->foreign('parent_key')->references('id')->on('mc_settings')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
