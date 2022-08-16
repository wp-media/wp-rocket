all: php front

php: composer

front: npm compile-front

composer:
	composer install

npm:
	npm  i

compile-front:
	npm run build
