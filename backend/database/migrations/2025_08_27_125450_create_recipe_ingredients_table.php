<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the relationship between recipes and ingredients, multiple ingredients can be used in a recipe
     */
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade'); // remove all recipe ingredients when it's deleted
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade'); // remove all recipes using an ingredient that have been deleted
            $table->float('quantity')->default(1); // grams
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
