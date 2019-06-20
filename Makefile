# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown

# PROJECT_NAME defaults to name of the current directory.
# should not to be changed if you follow GitOps operating procedures.
PROJECT_NAME = $(notdir $(PWD))

# Note. If you change this, you also need to update docker-compose.yml.
# only useful in a setting with multiple services/ makefiles.
SERVICE_TARGET := workspace

RELEASE_TYPE ?= patch
GITHUB_USER = alexbrouwer
GIT_REPO_NAME = $(PROJECT_NAME)
GIT_BRANCH ?= master
GIT_REMOTE ?= origin

# if vars not set specifially: try default to environment, else fixed value.
# strip to ensure spaces are removed in future editorial mistakes.
# tested to work consistently on popular Linux flavors and Mac.
ifeq ($(user),)
# USER retrieved from env, UID from shell.
HOST_USER ?= $(strip $(if $(USER),$(USER),nodummy))
HOST_UID ?= $(strip $(if $(shell id -u),$(shell id -u),4000))
else
# allow override by adding user= and/ or uid=  (lowercase!).
# uid= defaults to 0 if user= set (i.e. root).
HOST_USER = $(user)
HOST_UID = $(strip $(if $(uid),$(uid),0))
endif

THIS_FILE := $(lastword $(MAKEFILE_LIST))
CMD_ARGUMENTS ?= $(cmd)
DOCKER_COMPOSE = docker-compose -f ./.docker/docker-compose.yml --project-directory ./.docker
SEMVER_DOCKER ?= marcelocorreia/semver
REPO_URL := git@github.com:$(GITHUB_USER)/$(GIT_REPO_NAME).git

# export such that its passed to shell functions for Docker to pick up.
export PROJECT_NAME
export HOST_USER
export HOST_UID

# all our targets are phony (no files to check).
.PHONY: shell help build rebuild service login test clean prune deploy

# suppress makes own output
#.SILENT:

# shell is the first target. So instead of: make shell cmd="whoami", we can type: make cmd="whoami".
# more examples: make shell cmd="whoami && env", make shell cmd="echo hello container space".
# leave the double quotes to prevent commands overflowing in makefile (things like && would break)
# special chars: '',"",|,&&,||,*,^,[], should all work. Except "$" and "`", if someone knows how, please let me know!).
# escaping (\) does work on most chars, except double quotes (if someone knows how, please let me know)
# i.e. works on most cases. For everything else perhaps more useful to upload a script and execute that.
shell:
ifeq ($(CMD_ARGUMENTS),)
	# no command is given, default to shell
	$(DOCKER_COMPOSE) -p $(PROJECT_NAME)_$(HOST_UID) run --rm $(SERVICE_TARGET) sh
else
	# run the command
	$(DOCKER_COMPOSE) -p $(PROJECT_NAME)_$(HOST_UID) run --rm $(SERVICE_TARGET) sh -c "$(CMD_ARGUMENTS)"
endif

# Regular Makefile part for buildpypi itself
help:
	@echo ''
	@echo 'Usage: make [TARGET] [EXTRA_ARGUMENTS]'
	@echo 'Targets:'
	@echo '  build		build docker --image-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  rebuild	rebuild docker --image-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  test		test docker --container-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  service	run as service --container-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  login		run as service and login --container-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  clean		remove docker --image-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo '  deploy		deploy new release'
	@echo '  prune		shortcut for docker system prune -af. Cleanup inactive containers and cache.'
	@echo '  shell		run docker --container-- for current user: $(HOST_USER)(uid=$(HOST_UID))'
	@echo ''
	@echo 'Extra arguments:'
	@echo 'cmd=:	make cmd="whoami"'
	@echo '# user= and uid= allows to override current user. Might require additional privileges.'
	@echo 'user=:	make shell user=root (no need to set uid=0)'
	@echo 'uid=:	make shell user=dummy uid=4000 (defaults to 0 if user= set)'

rebuild:
	# force a rebuild by passing --no-cache
	$(DOCKER_COMPOSE) build --no-cache $(SERVICE_TARGET)

service:
	# run as a (background) service
	$(DOCKER_COMPOSE) -p $(PROJECT_NAME)_$(HOST_UID) up -d $(SERVICE_TARGET)

login: service
	# run as a service and attach to it
	docker exec -it $(PROJECT_NAME)_$(HOST_UID) sh

build:
	# only build the container. Note, docker does this also if you apply other targets.
	$(DOCKER_COMPOSE) build $(SERVICE_TARGET)

clean:
	# remove created images
	@$(DOCKER_COMPOSE) -p $(PROJECT_NAME)_$(HOST_UID) down --remove-orphans --rmi all 2>/dev/null \
	&& echo 'Image(s) for "$(PROJECT_NAME):$(HOST_USER)" removed.' \
	|| echo 'Image(s) for "$(PROJECT_NAME):$(HOST_USER)" already removed.'

prune:
	# clean all that is not actively used
	docker system prune -af

test:
	$(DOCKER_COMPOSE) -p $(PROJECT_NAME)_$(HOST_UID) run --rm $(SERVICE_TARGET) sh -c "composer verify"

all-versions:
	@git ls-remote --tags $(GIT_REMOTE)

current-version: _setup-versions
	@echo $(CURRENT_VERSION)

next-version: _setup-versions
	@echo $(NEXT_VERSION)

deploy: _release

	# Internal targets
_setup-versions:
	$(eval export CURRENT_VERSION=$(shell git ls-remote --tags $(GIT_REMOTE) | grep -v latest | awk '{ print $$2}'|grep -v 'stable'| sort -r --version-sort | head -n1|sed 's/refs\/tags\///g'))
	$(eval export NEXT_VERSION=$(shell docker run --rm --entrypoint=semver $(SEMVER_DOCKER) -c -i $(RELEASE_TYPE) $(CURRENT_VERSION)))

_release: _setup-versions ## Release by adding a new tag. RELEASE_TYPE is 'patch' by default, and can be set to 'minor' or 'major'.
	$(info $(M) Releasing version $(NEXT_VERSION)...)
	@github-release release -u $(GITHUB_USER) -r $(GIT_REPO_NAME) --tag $(NEXT_VERSION) --name $(NEXT_VERSION)

_initial-release:
	@github-release release -u $(GITHUB_USER) -r $(GIT_REPO_NAME) --tag 0.0.0 --name 0.0.0
