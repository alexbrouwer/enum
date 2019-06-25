export MAKER_PATH ?= vendor/par/maker
-include $(MAKER_PATH)/Makefile

## Tag current commit as patch release
release/patch:
	@$(MAKE) -s version/next/patch | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

## Tag current commit as minor release
release/minor:
	@$(MAKE) -s version/next/minor | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

## Tag current commit as major release
release/major:
	@$(MAKE) -s version/next/major | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

## Coding Standard: check
cs-check:
	@vendor/bin/phpcs

## Coding Standard: fix
cs-fix:
	@vendor/bin/phpcbf

## Analyse code
analyse:
	@vendor/bin/phpstan analyse

test:
	@vendor/bin/phpunit --colors=always

check: cs-check test