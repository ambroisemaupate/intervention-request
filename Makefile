TAG=4.1.1

.PHONY: test dev-server
test:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p;
	vendor/bin/phpstan analyse -c phpstan.neon;

dev-server:
	# http://0.0.0.0:8080/dev.php/cache/w1000/rhino.jpg
	cd web && php -S 0.0.0.0:8080

buildx_tag:
	docker run --privileged --rm tonistiigi/binfmt --install all
	TAG=${TAG} docker buildx bake --pull --push
