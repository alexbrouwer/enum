export MAKER_PATH ?= vendor/par/maker
-include $(MAKER_PATH)/Makefile

.PHONY: init build test clean

## Init project
init:
	@exit 0;

## Clean project
clean:
	@rm -rf vendor .phpunit.result.cache clover.xml

## Build project
build: code/deps/install
	@composer install --no-interaction

## Test project
test: code/deps/validate code/check code/analyse

## Install dependencies
code/deps/install:
	@composer install --no-interaction

## Validate dependencies
code/deps/validate:
	@composer validate

## Check code
code/check:
	@composer -- check

## Analyse code
code/analyse:
	@composer -- analyse

## Fix code
code/fix:
	@composer -- cs-fix
