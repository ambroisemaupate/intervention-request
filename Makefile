
.PHONY: test dev-server
test:
	vendor/bin/phpcs --report=full --report-file=./report.txt -p;
	vendor/bin/phpstan analyse -c phpstan.neon;

dev-server:
	php -S 0.0.0.0:8080 test/router.php
