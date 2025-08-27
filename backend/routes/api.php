<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

// create or update recipe
Route::post('/recipes', [RecipeController::class, 'store']);

// get a single recipe
Route::get('/recipes/{id}', [RecipeController::class, 'show']);

// delete a recipe
Route::delete('/recipes/{id}', [RecipeController::class, 'destroy']);
