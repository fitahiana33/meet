# Commandes utiles

Ce document rassemble les commandes principales du projet. Les commandes sont à exécuter depuis la racine du dépôt, sauf indication contraire.

## Démarrage rapide

Ouvrir deux terminaux.

### Terminal 1 — backend

```powershell
Set-Location backend
php artisan serve --host=127.0.0.1 --port=8000
```

API disponible sur `http://127.0.0.1:8000`.

### Terminal 2 — frontend

```powershell
Set-Location frontend
npm.cmd run dev -- --host 127.0.0.1 --port 5173
```

Interface disponible sur `http://127.0.0.1:5173`.

> Dans PowerShell, `npm.cmd` évite les problèmes liés à la politique d’exécution de `npm.ps1`. Si `npm` fonctionne directement chez vous, vous pouvez utiliser `npm` à la place.

## Backend — installation et configuration

Depuis `backend/` :

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
```

Ne lancez `Copy-Item` que si le fichier `.env` n’existe pas encore. Vérifier dans `.env` :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meeting_platform
DB_USERNAME=root
DB_PASSWORD=
```

### Vérifier la base et les routes

```powershell
php artisan migrate:status
php artisan db:show
php artisan route:list
php artisan about
```

### Migrations

```powershell
php artisan make:migration create_meetings_table
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh
```

`migrate:fresh` supprime toutes les tables de la base configurée. À utiliser uniquement en développement.

### Générer les classes Laravel

```powershell
php artisan make:model Meeting -m
php artisan make:controller MeetingController --api
php artisan make:request StoreMeetingRequest
php artisan make:resource MeetingResource
php artisan make:middleware AdminMiddleware
php artisan make:policy MeetingPolicy --model=Meeting
```

### Tests et qualité

```powershell
php artisan test
php artisan test --filter=ExampleTest
vendor\bin\pint.bat
```

### Nettoyer les caches

```powershell
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## Frontend — installation et commandes

Depuis `frontend/` :

```powershell
npm.cmd install
npm.cmd run dev -- --host 127.0.0.1 --port 5173
npm.cmd run build
npm.cmd run preview
```

### Installer ou retirer une dépendance

```powershell
npm.cmd install axios
npm.cmd install vue-router pinia
npm.cmd uninstall nom-du-package
```

### Vérifier les dépendances

```powershell
npm.cmd list --depth=0
npm.cmd outdated
```

## Vérification complète avant intégration

Depuis la racine du dépôt :

```powershell
Set-Location backend
php artisan migrate:status
php artisan test
Set-Location ..\frontend
npm.cmd run build
```

Arrêter les serveurs avec `Ctrl+C` dans leurs terminaux respectifs.
