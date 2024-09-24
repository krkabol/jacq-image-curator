#	docker exec -it --user www-data app-jacq /bin/bash

code_management:
	vendor/bin/phpcs --standard=ruleset.xml App # analyze with codesniffer https://github.com/slevomat/coding-standard
	vendor/bin/phpcbf --standard=ruleset.xml App # autofix with codesniffer


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


# analysis of old data
data_migration:
	NETTE_DEBUG=1 bin/console app:readBucketToDb & disown ## as it is time consuming

data_processing:
	NETTE_DEBUG=1 bin/console curator:importImage
