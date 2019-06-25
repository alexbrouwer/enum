export MAKER_PATH ?= vendor/par/maker
-include $(MAKER_PATH)/Makefile

.PHONY: release/patch
## Tag current commit as patch release
release/patch:
	@$(MAKE) -s version/next/patch | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

.PHONY: release/minor
## Tag current commit as minor release
release/minor:
	@$(MAKE) -s version/next/minor | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

.PHONY: release/major
## Tag current commit as major release
release/major:
	@$(MAKE) -s version/next/major | xargs -I {} -r $(MAKE) -s git/tag GIT_TAG={}
	@$(MAKE) -s git/tags/push

.PHONY: qa/phpcs
## PHP Code Sniffer
qa/phpcs:
	@vendor/bin/phpcs

.PHONY: qa/phpcbf
## PHP Code Beautifier and Fixer
qa/phpcbf:
	@vendor/bin/phpcbf

.PHONY: qa/phpstan
## PHP Static Analysis Tool
qa/phpstan:
	@vendor/bin/phpstan analyse

.PHONY: qa/phpunit
## PHPUnit
qa/phpunit:
	@vendor/bin/phpunit --colors=always

.PHONY: test
## Run tests
test: qa/phpunit

.PHONY: check
## Run checks
check: qa/phpcs qa/phpunit