# All commands have been successfully tested in OS Ubuntu 19.10

install: build dependencies test

build:
	docker build -t $(notdir $(CURDIR)) .

dependencies:
	docker run --rm -v ${PWD}:/app $(notdir $(CURDIR)) composer install

update:
	docker run --rm -v ${PWD}:/app $(notdir $(CURDIR)) composer update

outdated:
	docker run --rm -v ${PWD}:/app $(notdir $(CURDIR)) composer outdated

test:
	docker run --rm -it -v ${PWD}:/app $(notdir $(CURDIR)) vendor/bin/phpunit
