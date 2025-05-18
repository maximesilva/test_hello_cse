# Variables
PHP = php
ARTISAN = $(PHP) artisan
COMPOSER = composer
NPM = npm

# Couleurs pour les messages
GREEN = \033[0;32m
YELLOW = \033[1;33m
NC = \033[0m # No Color

# Commandes principales
.PHONY: install setup serve docs-generate help

install:
	@echo "${YELLOW}Installation des dépendances...${NC}"
	$(COMPOSER) install
	$(NPM) install
	@echo "${YELLOW}Création du fichier .env...${NC}"
	cp .env.example .env
	@echo "${YELLOW}Génération de la clé d'application...${NC}"
	$(ARTISAN) key:generate
	@echo "${YELLOW}Création de la base de données SQLite...${NC}"
	touch database/database.sqlite
	@echo "${YELLOW}Exécution des migrations...${NC}"
	$(ARTISAN) migrate
	@echo "${GREEN}Installation terminée !${NC}"

setup:
	@echo "${YELLOW}Configuration du projet...${NC}"
	$(ARTISAN) migrate:fresh
	$(ARTISAN) optimize:clear
	$(ARTISAN) optimize
	@echo "${GREEN}Configuration terminée !${NC}"

serve:
	@echo "${YELLOW}Démarrage du serveur de développement...${NC}"
	$(ARTISAN) serve

docs-generate:
	@echo "${YELLOW}Génération de la documentation Swagger...${NC}"
	$(ARTISAN) l5-swagger:generate
	@echo "${GREEN}Documentation générée !${NC}"

# Commandes utilitaires
.PHONY: migrate migrate-fresh clear-cache optimize

migrate:
	$(ARTISAN) migrate

migrate-fresh:
	$(ARTISAN) migrate:fresh

clear-cache:
	$(ARTISAN) optimize:clear

optimize:
	$(ARTISAN) optimize

# Aide
help:
	@echo "${GREEN}Commandes disponibles :${NC}"
	@echo "  ${YELLOW}make install${NC}      - Installe les dépendances et configure le projet"
	@echo "  ${YELLOW}make setup${NC}        - Configure le projet (migrations, cache, etc.)"
	@echo "  ${YELLOW}make serve${NC}        - Démarre le serveur de développement"
	@echo "  ${YELLOW}make docs-generate${NC} - Génère la documentation Swagger"
	@echo "  ${YELLOW}make migrate${NC}      - Exécute les migrations"
	@echo "  ${YELLOW}make migrate-fresh${NC} - Réinitialise et exécute les migrations"
	@echo "  ${YELLOW}make clear-cache${NC}  - Nettoie le cache"
	@echo "  ${YELLOW}make optimize${NC}     - Optimise l'application"
	@echo "  ${YELLOW}make help${NC}         - Affiche cette aide"