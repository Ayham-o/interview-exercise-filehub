# Project Setup and API Usage

## Update the `.env` file with your correct user and group ID if necessary, then:
```
docker compose build
docker compose up -d
```

## Add new recipe using new ingredients:
```
curl -X POST http://localhost:5000/api/recipes \ 
-H "Content-Type: application/json" \ 
-H "Accept: application/json" \ 
-d '{ 
"title": "Pancakes", 
"ingredients": [ 
{"name": "Ayham-flour", "quantity": 200, "carbs": 1, "fat": 2, "protein": 4}, 
{"name": "Ayham-cinnamon", "quantity": 100, "carbs": 1.1, "fat": 10.6, "protein": 12.6} 
], 
"steps": [ 
"Mix dry ingredients", 
"Add eggs and milk", 
"Fry on pan" 
] 
}' 
```

## Add new recipe using existing ingredients:
```
curl -X POST http://localhost:5000/api/recipes \ 
-H "Content-Type: application/json" \ 
-H "Accept: application/json" \ 
-d '{ 
"title": "Pancakes", 
"ingredients": [ 
{"name": "Apple", "quantity": 200}, 
{"name": "Ayham-cinnamon", "quantity": 100, "carbs": 1.1, "fat": 10.6, "protein": 12.6} 
], 
"steps": [ 
"Mix dry ingredients", 
"Add eggs and milk", 
"Fry on pan" 
] 
}' 
```

## Get a recipe:
```
http://localhost:5000/api/recipes/5
```
OR

```
curl -X GET http://localhost:5000/api/recipes/5 -H "Accept: application/json"
```

## Delete a recipe:
```
curl -X DELETE http://localhost:5000/api/recipes/4 -H "Accept: application/json"
```