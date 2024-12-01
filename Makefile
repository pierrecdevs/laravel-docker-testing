test:
	@docker compose run -it --rm artisan test

migration:
	@docker compose run -it --rm artisan migrate

serve:
	@docker compose run -it --rm artisan serve

install:
	@docker compose run -it --rm --user $(id -u):$(id -g)) composer install

