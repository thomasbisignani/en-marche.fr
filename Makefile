FIG=docker-compose
RUN=$(FIG) run --rm app
EXEC=$(FIG) exec app
CONSOLE=bin/console

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-diff watch clear clean test tu tf tj lint ls ly lt lj build up perm deps cc

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

start:          ## Install and start the project
start: build up db web/built perm

stop:           ## Remove docker containers
	$(FIG) kill
	$(FIG) rm -v --force

reset:          ## Reset the whole project
reset: stop start

db:             ## Reset the database and load fixtures
db: vendor
	$(RUN) php -r "for(;;){if(@fsockopen('db',3306)){break;}}" # Wait for MySQL
	$(RUN) $(CONSOLE) doctrine:database:drop --force --if-exists
	$(RUN) $(CONSOLE) doctrine:database:create --if-not-exists
	$(RUN) $(CONSOLE) doctrine:migrations:migrate -n
	$(RUN) $(CONSOLE) doctrine:fixtures:load -n

:##---------------------------------------------------------------------------:

db-diff:        ## Generate a migration by comparing your current database to your mapping information.
db-diff: vendor
	$(RUN) $(CONSOLE) doctrine:migration:diff

watch:          ## Watch the assets and build their development version on change
watch: node_modules
	$(RUN) yarn watch

clear:          ## Remove all the cache, the logs, the sessions and the built assets
clear: perm
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp
	rm -rf var/logs/*
	rm -rf web/built

clean:          ## Clear and remove dependencies
clean: clear
	rm -rf vendor node_modules

deps:           ## Install the project PHP and JS dependencies
deps: vendor web/built

cc:             ## Clear the cache in dev env
cc:
	$(RUN) $(CONSOLE) cache:clear

:##---------------------------------------------------------------------------:

test:           ## Run the PHP and the Javascript tests
test: tu tf tj

tu:             ## Run the PHP unit tests
tu: vendor
	$(RUN) vendor/bin/phpunit --exclude-group functionnal

tf:             ## Run the PHP functional tests
tf: vendor
	$(RUN) vendor/bin/phpunit --group functionnal

tj:             ## Run the Javascript tests
tj: node_modules
	$(RUN) yarn test

lint:           ## Run lint on twig, yaml and Javascript files
lint: lint-sf lint-front

ls:             ## Lint Symfony (twig and yaml) files
ls: ly lt

ly:
	$(RUN) $(CONSOLE) lint:yaml app/config

lt:
	$(RUN) $(CONSOLE) lint:twig app/Resources

lj:             ## Lint the Javascript to follow the convention
lj: node_modules
	$(RUN) yarn lint

build:
	$(FIG) build

up:
	$(FIG) up -d

perm:
	-$(EXEC) chmod 777 -R var

# Rules from files

vendor: composer.lock
	@$(RUN) composer install

composer.lock: composer.json
	@echo compose.lock is not up to date.

app/config/parameters.yml: app/config/parameters.yml.dist
	@$(RUN) composer run-script post-install-cmd

node_modules: yarn.lock
	@$(RUN) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

web/built: front node_modules
	@$(RUN) yarn build-dev
