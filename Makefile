
.PHONY: test dev-server
test:
	php -d "memory_limit=-1" vendor/bin/php-cs-fixer fix --ansi -vvv
	php -d memory_limit=-1 vendor/bin/phpstan analyse -c phpstan.neon;

dev-server:
	# http://0.0.0.0:8080/dev.php/cache/w1000/rhino.jpg
	cd web && php -S 0.0.0.0:8080

bake:
	docker run --privileged --rm tonistiigi/binfmt --install all
	docker buildx bake --load --push intervention

