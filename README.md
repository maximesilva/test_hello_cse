# API de Gestion de Profils

Une API RESTful Laravel pour la gestion de profils utilisateurs avec authentification et gestion des rôles.

## 🚀 Fonctionnalités

- Authentification avec Sanctum
- Gestion des rôles (Admin)
- CRUD des profils
- Gestion des commentaires
- Documentation API avec Swagger
- Tests automatisés

## 📋 Prérequis

- PHP 8.1 ou supérieur
- Composer
- Node.js et NPM
- Make (pour les commandes make)

## 🛠 Installation

1. Installer les dépendances et configurer le projet
```bash
make install
```

Cette commande va :
- Installer les dépendances PHP et Node
- Générer la clé d'application
- Créer le fichier .env
- Créer et configurer la base de données SQLite
- Exécuter les migrations

2. Générer la documentation API
```bash
make docs-generate
```

3. Démarrer le serveur
```bash
make serve
```

L'application sera accessible à l'adresse : `http://localhost:8000`

## 📚 Documentation API

La documentation Swagger est disponible à l'adresse : `http://localhost:8000/api/documentation`

## 🧪 Tests

Pour exécuter les tests :
```bash
php artisan test
```

## 🔧 Commandes Make disponibles

- `make install` : Installation complète du projet
- `make setup` : Configuration du projet
- `make serve` : Démarrage du serveur
- `make docs-generate` : Génération de la documentation
- `make migrate` : Exécution des migrations
- `make migrate-fresh` : Réinitialisation et exécution des migrations
- `make clear-cache` : Nettoyage du cache
- `make optimize` : Optimisation de l'application
- `make help` : Affiche l'aide

## 👤 Création d'un utilisateur admin

Pour créer un utilisateur administrateur :
```bash
php artisan make:admin-user
```

## 🔐 Authentification

L'API utilise Sanctum pour l'authentification. Pour accéder aux endpoints protégés :

1. Obtenir un token via `/api/login`
2. Inclure le token dans les headers :