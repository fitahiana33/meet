# API — Authentification

Base URL locale : `http://127.0.0.1:8000/api`.

Les jetons sont émis par Laravel Sanctum. Envoyer `Accept: application/json` et `Content-Type: application/json` pour les requêtes JSON.

## Login

`POST /login` — public, limité à 5 tentatives par minute.

Corps :

```json
{
	"email": "client@example.com",
	"password": "your-password"
}
```

Succès (`200`) :

```json
{
	"token": "1|plain-text-token",
	"token_type": "Bearer",
	"user": {
		"id": 1,
		"first_name": "Amina",
		"last_name": "Rakoto",
		"email": "client@example.com",
		"role": { "id": 2, "name": "client" }
	}
}
```

Identifiants invalides : `401`. Champs absents ou invalides : `422`. Compte sans rôle courant `admin` ou `client` : `403`.

Pour les requêtes authentifiées, envoyer `Authorization: Bearer <token>`.

## Utilisateur courant

`GET /me` — Bearer token requis.

Retourne `200` avec `{ "user": { ... } }`. Sans jeton valide : `401`.

## Logout

`POST /logout` — Bearer token requis.

Révoque le jeton courant et retourne `200` avec `{ "message": "Logged out." }`. Sans jeton valide : `401`.
