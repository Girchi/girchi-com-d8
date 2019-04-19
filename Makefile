include .env

.PHONY: up down stop prune ps shell drush logs build-ui install 

default: up

DRUPAL_ROOT ?= /var/www/html/web

up:
	@echo "Starting up containers for $(PROJECT_NAME)..."
	docker-compose pull
	docker-compose up -d --remove-orphans

down: stop

stop:
	@echo "Stopping containers for $(PROJECT_NAME)..."
	@docker-compose stop

prune:
	@echo "Removing containers for $(PROJECT_NAME)..."
	@docker-compose down -v

ps:
	@docker ps --filter name='$(PROJECT_NAME)*'

shell:
	docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh

drush:
	docker exec $(shell docker ps --filter name='^/$(PROJECT_NAME)_php' --format "{{ .ID }}") drush -r $(DRUPAL_ROOT) $(filter-out $@,$(MAKECMDGOALS))

logs:
	@docker-compose logs -f $(filter-out $@,$(MAKECMDGOALS))

build-ui:
  @docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) $(shell docker ps --filter name='$(PROJECT_NAME)_ui_builder' --format "{{ .ID }}") sh -c 'rm -rf $(UI_TITLE)/ && git clone $(UI_REPO) && cd $(UI_TITLE)/ && yarn && yarn build'
	sudo chown $(USER):$(USER) ./front -R
	@rm -rf ./front/$(UI_TITLE)/.git/
	@cp -r ./front/$(UI_TITLE)/{*,.[^.]*} ./front
	@rm -rf ./front/$(UI_TITLE)
	@echo -e "\n\nGirchi UI successfully built in ./front folder!"

install:
	./scripts/install.sh

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
