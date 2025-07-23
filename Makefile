
.PHONY: test bake dev-server test-functional test-unit
test:
	docker compose run --rm --entrypoint= -e "PHP_CS_FIXER_IGNORE_ENV=1" intervention php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv
	docker compose run --rm --entrypoint= intervention php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon;
	docker compose run --rm --entrypoint= intervention php -d "memory_limit=-1" vendor/bin/phpunit tests/

bake:
	docker run --privileged --rm tonistiigi/binfmt --install all
	docker buildx bake --push intervention

test-functional:
	docker compose run --rm --entrypoint= intervention php -d "memory_limit=-1" vendor/bin/phpunit tests/Functional

test-unit:
	docker compose run --rm --entrypoint= intervention php -d "memory_limit=-1" vendor/bin/phpunit tests/Processor
