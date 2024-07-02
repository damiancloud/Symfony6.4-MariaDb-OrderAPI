CONTAINER_NAME = php-symfony-app

start:
	docker-compose build
	composer install
	git init
	
stop:
	docker stop $(CONTAINER_NAME) 
	docker stop $(CONTAINER_NAME)-nginx
	docker stop $(CONTAINER_NAME)-mysql

up:
	docker-compose up --force-recreate

console: 
	docker exec -it $(CONTAINER_NAME) bash

migrate:
	docker exec -it $(CONTAINER_NAME) bash -c "php bin/console doctrine:migrations:migrate"

fixtures:
	docker exec -it $(CONTAINER_NAME) bash -c "php bin/console doctrine:fixtures:load"

clear-cache:
	docker exec -it $(CONTAINER_NAME) php bin/console cache:clear

doctrine-clear-cache:
	docker exec -it $(CONTAINER_NAME) php bin/console doctrine:cache:clear-metadata

test:
	docker exec -it $(CONTAINER_NAME) php bin/phpunit

phpstan:
	composer --working-dir=tools/phpstan install
	tools/phpstan/vendor/bin/phpstan analyse src

phpstan5:
	tools/phpstan/vendor/bin/phpstan analyse src --level 5

phpfixer:
	composer --working-dir=tools/phpfixer install
	php tools/phpfixer/vendor/bin/php-cs-fixer fix src/ --rules=@PSR12
