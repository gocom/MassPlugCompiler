.PHONY: all build lint lint-fix test test-unit test-static compile clean help

IMAGE?=latest
PHP=docker-compose run --rm $(IMAGE)

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

test-unit: vendor
	$(PHP) bash -c "composer test:unit || exit 1; test -e build/logs/clover.xml && sed -i 's/\/app\///' build/logs/clover.xml || true"

test-static: vendor
	$(PHP) composer test:static

compile: vendor
	$(PHP) composer compile

clean:
	$(PHP) rm -rf vendor composer.lock

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
	@echo "Images:"
	@echo ""
	@echo "  latest"
