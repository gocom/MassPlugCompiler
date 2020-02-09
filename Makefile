.PHONY: all build install cs csfix test unit static compile clean help githooks githooks-pre-push

IMAGE?=latest

all:
	@$(MAKE) clean
	@$(MAKE) build
	@$(MAKE) install
	@$(MAKE) cs
	@$(MAKE) test

build:
	docker-compose build $(IMAGE)

install:
	docker-compose run $(IMAGE) bash -c 'test -e vendor || composer install'

update:
	docker-compose run $(IMAGE) composer update

cs:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer cs

csfix:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer csfix

test:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer test

unit:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer test:unit

static:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer test:static

compile:
	@$(MAKE) install
	docker-compose run $(IMAGE) composer compile

clean:
	docker-compose run $(IMAGE) rm -rf vendor composer.lock

githooks:
	echo "make githooks-pre-push" > .git/hooks/pre-push
	chmod +x .git/hooks/pre-push

githooks-pre-push:
	@$(MAKE) test

help:
	@echo "Run mtxpc test suite"
	@echo ""
	@echo "Usage:"
	@echo "  make [command] [IMAGE=<image>]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  make help      Display this message"
	@echo ""
	@echo "  make all       Build and run tests"
	@echo ""
	@echo "  make build     Build image"
	@echo "  make clean     Reset Composer dependencies"
	@echo "  make install   Install Composer dependencies"
	@echo "  make update    Update Composer dependencies"
	@echo ""
	@echo "  make cs        Check code style"
	@echo "  make csfix     Try to fix code style"
	@echo ""
	@echo "  make test      Run linter, static and unit tests"
	@echo "  make unit      Run only unit tests"
	@echo "  make static    Run only static tests"
	@echo ""
	@echo "  make githooks  Install git hooks"
	@echo ""
	@echo "Images:"
	@echo ""
	@echo "  latest"
