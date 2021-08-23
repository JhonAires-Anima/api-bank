# Instalation

##### 1. Install composer (https://getcomposer.org/download/)

##### 2. Install laravel with composer by running "composer global require laravel/installer"

##### 3.  Clone repository (https://github.com/KevinMP532/laravel.git)

##### 4. Create a database named "peliculas" and import the databse.sql file in laravel/database

##### 5. Go to "anima-api"

##### 6. Run composer -vvv update

##### 7. Run composer -vvv install

##### 8.  Run php artisan serv

# Usage

##### 1. Get all records from database
###### Method: GET
###### URL: http://127.0.0.1:8000/api/getAll
###### Body: N/A
###### Notes:
#
##### 2. Get one record by ID
###### Method: GET
###### URL: http://127.0.0.1:8000/api/showById
###### Body: {"idPelicula": <value>}
###### Notes:
#
##### 3. Store a record
###### Method: POST
###### URL: http://127.0.0.1:8000/api/store
###### Body: {"nombre": <nameValue>, "img": <image>}
###### Notes: All new records are given an "activo" value of 1 by default. ID is automatically assigned by AUTO-INCREMENT rule.

