#	docker exec -it --user www-data app-jacq /bin/bash

code_management:
	vendor/bin/phpcs --standard=ruleset.xml App # analyze with codesniffer https://github.com/slevomat/coding-standard
	vendor/bin/phpcbf --standard=ruleset.xml App # autofix with codesniffer
	vendor/bin/phpstan analyse --level 3 App # analyze errors with PHPStan
	vendor/bin/phpstan analyse --level 3 --configuration phpstan.neon App --generate-baseline # https://phpstan.org/user-guide/baseline

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests
	vendor/bin/tester -s -p php --colors 1 -C tests --coverage tests/coverage.html --coverage-src App  # HTML report
	vendor/bin/tester -s -p php --colors 1 -C tests --coverage coverage.xml --coverage-src App # Clover XML report

build:
 	NETTE_DEBUG=1 bin/console migrations:generate #  generate empty migration class
 	NETTE_DEBUG=1 bin/console migrations:diff # generate migration class according to entities changes

	NETTE_DEBUG=1 bin/console orm:schema-tool:drop --force --full-database
	NETTE_DEBUG=1 bin/console migrations:migrate --dry-run
	NETTE_DEBUG=1 bin/console migrations:migrate --no-interaction
	NETTE_DEBUG=1 bin/console migrations:migrate first # revert to empty db
#	NETTE_DEBUG=1 bin/console migrations:migrate Database\\Migrations\\Version1
#	NETTE_DEBUG=1 bin/console doctrine:fixtures:load --no-interaction --append
#	NETTE_DEBUG=1 bin/console orm:schema-tool:update --dump-sql --complete

	NETTE_DEBUG=1 bin/console orm:generate-proxies # keep proxies in VCS

data_processing:
	NETTE_DEBUG=1 bin/console curator:importImage

admin:
	NETTE_DEBUG=1 bin/console admin:downloadArchive
	NETTE_DEBUG=1 bin/console admin:bucketParity
	NETTE_DEBUG=1 bin/console admin:harvestExif
	NETTE_DEBUG=1 bin/console admin:refreshJp2


