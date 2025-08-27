<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    /**
     * many (recipes) to many (ingredients) relationship
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * one (recipe) to many (steps) relationship
     */
    public function steps()
    {
        return $this->hasMany(Step::class);
    }
}
