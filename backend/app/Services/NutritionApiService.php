<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NutritionApiService
{
    /**
     * getting the ingredients info from the nutrition API
     */
    protected string $base;
    protected string $user;
    protected string $pass;

    /**
     * setting up the API credentials, already defined in the .env file and services config file
     */
    public function __construct()
    {
        $this->base = rtrim(config('services.nutrition.base', env('NUTRITION_API_BASE')), '/');
        $this->user = config('services.nutrition.user', env('NUTRITION_API_USER'));
        $this->pass = config('services.nutrition.pass', env('NUTRITION_API_PASS'));
    }

    /**
     * authenticate the API client
     */
    protected function client()
    {
        return Http::withBasicAuth($this->user, $this->pass);
    }

    /**
     * get an ingredient by name.
     */
    public function getIngredient(string $name): ?array
    {
        $resp = $this->client()->get("{$this->base}/ingredients.php", [
            'ingredient' => $name,
        ]);

        if ($resp->status() === 404) {
            return null; // ingredient not found
        }

        $resp->throw();
        return $resp->json(); // returns {name, carbs, fat, protein}
    }

    /**
     * get all ingredients.
     */
    public function listIngredients(): array
    {
        $resp = $this->client()->get("{$this->base}/ingredients.php");
        $resp->throw();
        return $resp->json();
    }

    /**
     * add a new ingredient.
     */
    public function addIngredient(array $payload): array
    {
        // use asForm for x-www-form-urlencoded content-type
        $resp = $this->client()->asForm()->post("{$this->base}/ingredients.php", [
            'name'    => $payload['name'],
            'carbs'   => $payload['carbs'],
            'fat'     => $payload['fat'],
            'protein' => $payload['protein'],
        ]);

        $resp->throw();
        return $resp->json();
    }
}
