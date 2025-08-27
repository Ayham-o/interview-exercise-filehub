# Project Setup and API Usage

Update the `.env` file with your correct user and group ID if necessary, then:
```
docker compose build
docker compose up -d
docker compose run --rm --no-deps backend bash -lc "composer install"
docker compose exec backend php artisan migrate

```

craete backend/.env and add API cresdentials:
```
NUTRITION_API_BASE=https://interview.workcentrix.de
NUTRITION_API_USER=ao
NUTRITION_API_PASS=ppfIEPFDzB
```

## Add new recipe (missing ingredients will be added to API automatically):
```
curl -X POST http://localhost:5000/api/recipes -H "Content-Type: application/json" -H "Accept: application/json" -d '{ "title": "Pancakes", "ingredients": [ {"name": "Ayham-flour", "quantity": 200, "carbs": 1, "fat": 2, "protein": 4}, {"name": "Ayham-cinnamon", "quantity": 100, "carbs": 1.1, "fat": 10.6, "protein": 12.6} ], "steps": [ "Mix dry ingredients", "Add eggs and milk", "Fry on pan" ] }' 
```

## Get a recipe:
```
http://localhost:5000/api/recipes/1
```
OR

```
curl -X GET http://localhost:5000/api/recipes/1 -H "Accept: application/json"
```

## Delete a recipe:
```
curl -X DELETE http://localhost:5000/api/recipes/1 -H "Accept: application/json"
```
