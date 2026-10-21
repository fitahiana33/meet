# Lancer le projet

## Prérequis

- PHP 8.2+ avec l’extension `pdo_mysql`
- Composer
- Node.js et npm
- MySQL en cours d’exécution

## Première installation

Depuis la racine du projet, ouvrez un terminal et exécutez :

```powershell
Set-Location backend
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
```

Créez ensuite la base MySQL dans MySQL Workbench, phpMyAdmin ou votre client MySQL :

```sql
CREATE DATABASE meeting_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Vérifiez les paramètres `DB_*` dans `backend/.env`. Par défaut, le projet se connecte à MySQL sur `127.0.0.1:3306`, avec l’utilisateur `root` sans mot de passe. Adaptez ces valeurs à votre installation. Puis, depuis `backend/`, exécutez :

```powershell
php artisan migrate --seed
```

Ouvrez un deuxième terminal à la racine du projet et installez le frontend :

```powershell
Set-Location frontend
npm.cmd install
```

## Démarrer sur le même ordinateur

Ouvrez deux terminaux.

Dans le premier :

```powershell
Set-Location backend
php artisan serve
```

Dans le deuxième :

```powershell
Set-Location frontend
npm.cmd run dev
```

Ouvrez <http://localhost:5173>.

## Accéder depuis un autre ordinateur du même réseau

Les deux serveurs sont configurés pour écouter sur le réseau local. Sur l’ordinateur qui héberge le projet, trouvez son adresse IPv4 locale (par exemple avec `ipconfig` sous Windows). Sur l’autre ordinateur, ouvrez `http://ADRESSE_IPV4_DU_SERVEUR:5173` (par exemple `http://192.168.1.25:5173`). Le frontend contacte automatiquement l’API sur le même hôte, au port `8000`. Laravel autorise en CORS les adresses IPv4 privées pour le frontend sur le port `5173`.

Si le fichier `backend/.env` existait déjà avant cette configuration, ajoutez-y `SERVER_HOST=0.0.0.0`. Si `frontend/.env` définit une valeur fixe pour `VITE_API_URL`, videz-la pour activer la détection automatique de l’hôte, ou remplacez `localhost` par l’adresse IPv4 du serveur. Redémarrez Vite après une modification du fichier `.env`.

L’accès depuis un autre ordinateur dépend aussi du réseau et du pare-feu : les deux appareils doivent être sur le même réseau, et les connexions entrantes vers les ports TCP `5173` et `8000` doivent être autorisées sur l’ordinateur serveur. N’ouvrez ces ports que sur un réseau privé et ne les publiez pas sur Internet. Une configuration de pare-feu ou de réseau d’entreprise peut encore bloquer l’accès.

## Connexion administrateur

- Email : `admin@gmail.com`
- Mot de passe : `admin123`

