# ---------------------------------------------------------
# Smarter Chat for WordPress. See README.md and CLAUDE.md.
#
# PHP isn't required on the developer's machine: the PHP
# targets run in Docker (tests/docker/Dockerfile) when php
# isn't installed.
# ---------------------------------------------------------
SHELL := /bin/bash
# The developer's shell may export NODE_ENV=production, which makes npm skip devDependencies.
NPM_INSTALL := npm install --include=dev
# pre-commit and codespell are installed into venv/ (requirements/local.txt).
PYTHON ?= python3.13
VENV_BIN := venv/bin
PHP_IMAGE := smarter-wordpress-php
ifeq ($(shell command -v php),)
RUN := docker run --rm -v "$(CURDIR)":/app -w /app $(PHP_IMAGE)
DOCKER_IMAGE := docker-image
else
RUN :=
DOCKER_IMAGE :=
endif

.PHONY: all init python-init docker-image composer embed test coverage lint lint-fix smoke dist clean pre-commit-run help

all: help

docker-image:
	docker build -q -t $(PHP_IMAGE) tests/docker >/dev/null

composer: $(DOCKER_IMAGE)
	$(RUN) composer install --no-interaction --no-progress

init: composer python-init
	$(NPM_INSTALL)
	$(VENV_BIN)/pre-commit install

python-init:
	[ -x $(VENV_BIN)/python ] || $(PYTHON) -m venv venv
	$(VENV_BIN)/pip install --upgrade pip
	$(VENV_BIN)/pip install -r requirements/local.txt

embed:
	scripts/embed.sh

test: $(DOCKER_IMAGE)
	@[ -d vendor ] || $(MAKE) composer
	$(RUN) vendor/bin/phpunit --no-coverage

coverage: $(DOCKER_IMAGE)
	@[ -d vendor ] || $(MAKE) composer
	$(RUN) php -d pcov.enabled=1 vendor/bin/phpunit

lint: $(DOCKER_IMAGE)
	@[ -d vendor ] || $(MAKE) composer
	$(RUN) vendor/bin/phpcs

lint-fix: $(DOCKER_IMAGE)
	@[ -d vendor ] || $(MAKE) composer
	-$(RUN) vendor/bin/phpcbf
	$(RUN) vendor/bin/phpcs

smoke:
	tests/smoke/run.sh

dist:
	scripts/dist.sh

pre-commit-run:
	$(VENV_BIN)/pre-commit run --all-files

clean:
	rm -rf vendor node_modules venv build smarter-chat/assets/embed.js smarter-chat/assets/embed

help:
	@echo '===================================================================='
	@echo 'Smarter Chat for WordPress'
	@echo '===================================================================='
	@echo 'init            - composer and npm dev dependencies, venv/, pre-commit hooks'
	@echo 'python-init     - venv/ with pre-commit and codespell (requirements/local.txt)'
	@echo 'embed           - copy the embed script into smarter-chat/assets/'
	@echo 'test            - PHPUnit unit tests'
	@echo 'coverage        - PHPUnit with coverage'
	@echo 'lint            - PHPCS: WordPress Coding Standards, PHPCompatibilityWP'
	@echo 'lint-fix        - PHPCBF, then PHPCS'
	@echo 'smoke           - WordPress in Docker: install, configure, check pages'
	@echo 'dist            - build/smarter-chat-<version>.zip'
	@echo 'pre-commit-run  - every pre-commit hook on every file'
	@echo 'clean           - remove dependencies, venv/ and build output'
