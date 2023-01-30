all: php front

php: composer

front: npm compile-front

composer:
	composer install --no-dev --no-scripts

npm:
	npm i

compile-front:
	npm run build
