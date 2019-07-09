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
build:
	@composer install --no-interaction

## Test project
test:
	@composer validate
	@composer -- check
	@composer -- analyse

fix:
	@composer -- cs-fix
