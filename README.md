# Meeting Platform

Plateforme de réunion vidéo basée sur Laravel, Vue.js et MySQL.

## Organisation

- `backend/` : API Laravel, base de données, authentification et règles métier — personne 1.
- `frontend/` : application Vue.js, navigation et interfaces — personne 2.
- `docs/` : architecture, contrat d’API et organisation Git.
- `database/` : éléments de référence liés à la base de données.

## État actuel

Le dépôt contient uniquement l’ossature du projet. Les dépendances, les fichiers générés par Laravel/Vite et le code métier seront ajoutés dans les étapes dédiées.

## Règles de collaboration

- Ne pas travailler directement sur `main`.
- Une fonctionnalité = une branche et un commit ciblé.
- Le contrat d’API et les migrations doivent être documentés avant intégration frontend.
- Ne pas modifier la partie de l’autre développeur sans accord.

Voir [docs/taches.md](docs/taches.md) pour le plan fonctionnel et [docs/github/workflow.md](docs/github/workflow.md) pour le workflow.
