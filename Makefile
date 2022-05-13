.PHONY: all build lint lint-fix test test-integration test-unit test-static repl generate-fixtures process-reports compile clean help

IMAGE?=latest
PHP=docker-compose run --rm php

all: test

build:
	docker-compose build $(IMAGE)

vendor:
	$(PHP) composer install

lint: vendor
	$(PHP) composer lint

lint-fix: vendor
	$(PHP) composer lint-fix

test: vendor
	$(PHP) composer test

test-integration: vendor
	$(PHP) composer test:integration

test-unit: vendor
	$(PHP) composer test:unit

test-static: vendor
	$(PHP) composer test:static

repl: vendor
	$(PHP) composer repl

compile: vendor
	$(PHP) composer compile

clean:
	$(PHP) rm -rf vendor composer.lock

generate-fixtures:
	$(PHP) composer generate-fixtures

process-reports:
	$(PHP) bash -c "test -e build/logs/clover.xml && sed -i 's/\/app\///' build/logs/clover.xml"

help:
	@echo "Run mtxpc test suite"
	@echo ""
	@echo "Usage:"
	@echo "  make [command] [IMAGE=<image>]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  $$ make help"
	@echo "  Display this message"
	@echo ""
	@echo "  $$ make build"
	@echo "  Re-builds Docker image"
	@echo ""
	@echo "  $$ make compile"
	@echo "  Build Phar"
	@echo ""
	@echo "  $$ make clean"
	@echo "  Delete Composer dependencies"
	@echo ""
	@echo "  $$ make install"
	@echo "  Install Composer dependencies"
	@echo ""
	@echo "  $$ make lint"
	@echo "  Check code style"
	@echo ""
	@echo "  $$ make lint-fix"
	@echo "  Try to fix code style"
	@echo ""
	@echo "  $$ make test"
	@echo "  Run linter, static and unit tests"
	@echo ""
	@echo "  $$ make test-unit"
	@echo "  Run only unit tests"
	@echo ""
	@echo "  $$ make test-static"
	@echo "  Run only static tests"
	@echo ""
	@echo "  $$ make repl"
	@echo "  Start read-eval-print loop shell"
	@echo ""
	@echo "  $$ make generate-fixtures"
	@echo "  Generates test fixtures"
	@echo ""
	@echo "  $$ make process-reports"
	@echo "  Formats test reports to use relative local file paths"
	@echo ""
	@echo "Images:"
	@echo ""
	@echo "  latest"
