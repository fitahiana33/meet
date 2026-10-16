# Meeting Platform

Plateforme de réunion vidéo basée sur Laravel, Vue.js et MySQL.

## Organisation

- `backend/` : API Laravel, base de données, authentification et règles métier — personne 1.
- `frontend/` : application Vue.js, navigation et interfaces — personne 2.
- `docs/` : architecture, contrats d’API et commandes du projet.
- `database/` : éléments de référence liés à la base de données.

## État actuel

Le backend Laravel et le frontend Vue/Vite sont initialisés. Le backend utilise MySQL avec la base `meeting_platform`. Les fonctionnalités métier, notamment l’authentification, seront ajoutées progressivement.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- MySQL actif sur `127.0.0.1:3306`

## Lancer le projet

### 1. Préparer le backend

Depuis `backend/` :

```powershell
composer install
php artisan key:generate
php artisan migrate --seed
```

Vérifier que `backend/.env` contient les paramètres MySQL. Ne pas partager ce fichier, car il peut contenir des secrets.

### 2. Démarrer le backend

Dans un premier terminal :

```powershell
Set-Location backend
php artisan serve --host=127.0.0.1 --port=8000
```

### 3. Démarrer le frontend

Dans un deuxième terminal :

```powershell
Set-Location frontend
npm.cmd install
npm.cmd run dev -- --host 127.0.0.1 --port 5173
```

a

### Si avec plusieurs pc
```powershell
php artisan serve --host=192.168.43.135 --port=8000
npm run dev -- --host 0.0.0.0 --port 5173
```

Ouvrir ensuite `http://127.0.0.1:5173` dans le navigateur. L’API Laravel est disponible sur `http://127.0.0.1:8000`.

Pour PowerShell, `npm.cmd` est utilisé afin d’éviter les blocages liés à `npm.ps1`.

## Vérifier que tout fonctionne

Backend :

```powershell
Set-Location backend
php artisan migrate:status
php artisan test
```

Frontend :

```powershell
Set-Location frontend
npm.cmd run build
```

La liste détaillée des commandes est disponible dans [docs/commande.md](docs/commande.md).

## Règles de collaboration

- Ne pas travailler directement sur `main`.
- Une fonctionnalité = une branche et un commit ciblé.
- Le contrat d’API et les migrations doivent être documentés avant intégration frontend.
- Ne pas modifier la partie de l’autre développeur sans accord.

Voir [docs/taches.md](docs/taches.md) pour le plan fonctionnel et [docs/github/workflow.md](docs/github/workflow.md) pour le workflow.
