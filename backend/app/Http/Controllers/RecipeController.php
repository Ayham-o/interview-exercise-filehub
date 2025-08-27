<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Step;
use App\Services\NutritionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    protected NutritionApiService $nutritionApi;

    /**
     * inject the NutritionApiService
     */
    public function __construct(NutritionApiService $nutritionApi)
    {
        $this->nutritionApi = $nutritionApi;
    }

    /**
     * POST /recipes
     * create or update recipe
     */
    public function store(Request $request)
    {   
        // ensure valid recipe information
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'exists:recipes,id'],
            'title' => ['required', 'string', 'max:255'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.name' => ['required', 'string'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:0'],
            'ingredients.*.carbs' => ['nullable', 'numeric'],
            'ingredients.*.fat' => ['nullable', 'numeric'],
            'ingredients.*.protein' => ['nullable', 'numeric'],
            'steps' => ['required', 'array', 'min:1'],
        ]);


        $recipe = DB::transaction(function () use ($validated) {
            // either recipe already exists or we create a new one
            $recipe = isset($validated['id'])
                ? Recipe::findOrFail($validated['id'])
                : new Recipe();

            $recipe->title = $validated['title'];
            $recipe->save();

            // reset recipe info if already exists
            $recipe->ingredients()->detach();
            $recipe->steps()->delete();

            // add ingredients
            foreach ($validated['ingredients'] as $ing) {
                $ingredientModel = $this->resolveIngredient($ing);
                $recipe->ingredients()->attach($ingredientModel->id, [
                    'quantity' => (float)$ing['quantity'],
                ]);
            }

            // add steps in correct order
            $stepNumber = 1;
            foreach ($validated['steps'] as $step) {
                $recipe->steps()->create([
                    'step_number' => $stepNumber++,
                    'description' => is_array($step) ? $step['description'] : $step,
                ]);
            }

            return $recipe;
        });

        // load recipe along with its ingredients and steps
        $recipe->load(['ingredients', 'steps' => fn ($q) => $q->orderBy('step_number')]);

        // construct the response
        return response()->json([
            'data' => [
                'id' => $recipe->id,
                'title' => $recipe->title,
                'ingredients' => $recipe->ingredients->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'carbs' => $i->carbs,
                    'fat' => $i->fat,
                    'protein' => $i->protein,
                    'quantity' => $i->pivot->quantity,
                ]),
                'steps' => $recipe->steps,
                'nutrition_totals' => $this->computeNutritionTotals($recipe),
            ]
        ], 201);
    }

    /**
     * GET /recipes/{id}
     * returns a recipe
     */
    public function show($id)
    {
        $recipe = Recipe::with(['ingredients', 'steps' => fn ($q) => $q->orderBy('step_number')])
            ->findOrFail($id); // returns 404 if not found

        // check for missing nutritional info, if so fetch from API. I'm not sure if this is ever needed
        foreach ($recipe->ingredients as $ing) {
            if (is_null($ing->carbs) || is_null($ing->fat) || is_null($ing->protein)) {
                if ($apiData = $this->nutritionApi->getIngredient($ing->name)) {
                    $ing->update($apiData);
                }
            }
        }
        
        // duplicate code, bad
        return response()->json([
            'data' => [
                'id' => $recipe->id,
                'title' => $recipe->title,
                'ingredients' => $recipe->ingredients->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'carbs' => $i->carbs,
                    'fat' => $i->fat,
                    'protein' => $i->protein,
                    'quantity' => $i->pivot->quantity,
                ]),
                'steps' => $recipe->steps,
                'nutrition_totals' => $this->computeNutritionTotals($recipe),
            ]
        ]);
    }

    /**
     * DELETE /recipes/{id}
     * removes recipe and its related info
     */
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * get ingredient object from DB or API, add it to both if it's missing
     */
    protected function resolveIngredient(array $ing): Ingredient
    {
        // check if ingredient exists in local DB
        $ingredient = Ingredient::where('name', $ing['name'])->first();

        // if it doesn't fetch it from API
        if (!$ingredient) {
            $apiData = $this->nutritionApi->getIngredient($ing['name']);
            if ($apiData) {
                $ingredient = Ingredient::create($apiData);
            } elseif (isset($ing['carbs'], $ing['fat'], $ing['protein'])) { // if it is also not found in API we add it
                $this->nutritionApi->addIngredient($ing);
                $ingredient = Ingredient::create($ing);
            } else {
                abort(422, "Ingredient {$ing['name']} not found and no macros provided.");
            }
        }

        return $ingredient;
    }

    /**
     * the nutritional values sum
     */
    protected function computeNutritionTotals(Recipe $recipe): array
    {
        $totals = ['carbs' => 0, 'fat' => 0, 'protein' => 0];

        foreach ($recipe->ingredients as $ing) {
            $factor = $ing->pivot->quantity / 100; // assuming macros are per 100g
            $totals['carbs'] += $factor * $ing->carbs;
            $totals['fat'] += $factor * $ing->fat;
            $totals['protein'] += $factor * $ing->protein;
        }

        return array_map(fn ($v) => round($v, 2), $totals);
    }
}