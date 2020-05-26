
.PHONY: test dev-server
test:
	vendor/bin/phpcs --report=full --report-file=./report.txt --extensions=php --warning-severity=0 --standard=PSR2 -p ./src;
	vendor/bin/phpstan analyse -c phpstan.neon -l 3 src;

dev-server:
	php -S 0.0.0.0:8080 test/router.php
