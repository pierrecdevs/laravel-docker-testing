test:
	docker compose run -it --rm php ./artisan test

migration:
	docker compose run -it --rm php ./artisan migrate

serve:
	docker compose run -it --rm php ./artisan serve

install:
	docker compose run -it --rm --user $(id -u):$(id -g)) composer install

