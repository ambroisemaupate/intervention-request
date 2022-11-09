
.PHONY: test dev-server
test:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p;
	vendor/bin/phpstan analyse -c phpstan.neon;

dev-server:
	php -S 0.0.0.0:8080 test/router.php

buildx:
	docker run --privileged --rm tonistiigi/binfmt --install all
	docker buildx build --push --platform linux/arm64/v8,linux/amd64 --tag ambroisemaupate/intervention-request:latest .
