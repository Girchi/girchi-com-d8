include .env

.PHONY: up down stop prune ps shell drush logs build-ui update-ui install

default: up

DRUPAL_ROOT ?= /var/www/html/web

up:
	@mkdir -p front
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
	@mkdir -p front
	@cd front && sudo rm -rf `ls -Ab` && cd ..
	@git clone $(UI_REPO) front
	@docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) $(shell docker ps --filter name='$(PROJECT_NAME)_ui_builder' --format "{{ .ID }}") sh -c 'yarn && yarn build'
	sudo chown $(USER):$(USER) ./front -R
	@echo -e "\n\nGirchi UI successfully built in ./front folder!"

update-ui:
	make build-ui
	./scripts/update_ui.sh

install:
	./scripts/install.sh

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
