╔══════════════════════════════════════════════════════════════════════╗
║          PROJET PLATEFORME DE RÉUNION VIDÉO                        ║
║          Laravel + Vue.js + MySQL + GitHub                         ║
╚══════════════════════════════════════════════════════════════════════╝


======================================================================
                         RÈGLES DU PROJET
======================================================================

Nous sommes 2 développeurs.

PERSONNE 1 = BACKEND
PERSONNE 2 = FRONTEND

IMPORTANT :
- Ne pas modifier le travail de l'autre sans accord.
- Ne pas supprimer/modifier une API déjà validée.
- Ne pas modifier la structure de la base de données créée par l'autre
  sans prévenir.
- Chaque fonctionnalité doit être testée avant de passer à la suivante.
- Chaque fonctionnalité doit être commitée séparément.
- Ne jamais travailler directement sur "main".
- Chaque développeur travaille sur sa propre branche.
- Avant un merge, vérifier que le projet fonctionne.


======================================================================
                         ARCHITECTURE
======================================================================

                    ┌──────────────────┐
                    │     Vue.js       │
                    │    FRONTEND      │
                    └────────┬─────────┘
                             │
                             │ HTTP / JSON
                             ▼
                    ┌──────────────────┐
                    │     Laravel      │
                    │     BACKEND      │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │      MySQL       │
                    │    DATABASE      │
                    └──────────────────┘

Plus tard :

                    ┌──────────────────┐
                    │     LiveKit      │
                    │ VIDEO / AUDIO    │
                    └──────────────────┘


======================================================================
                    RÉPARTITION GÉNÉRALE
======================================================================

PERSONNE 1
-----------
Responsable de :

1. Laravel
2. MySQL
3. Migrations
4. Models
5. Authentification API
6. CRUD utilisateurs API
7. Gestion des réunions API
8. Demandes d'accès API
9. Acceptation/refus des demandes
10. Historique des réunions API
11. Sécurité et permissions backend
12. Tests API


PERSONNE 2
-----------
Responsable de :

1. Vue.js
2. Interface Login
3. Dashboard utilisateur
4. Liste des réunions
5. Création d'une réunion
6. Demande d'accès
7. Liste des demandes
8. Acceptation/refus côté interface
9. Backoffice
10. CRUD utilisateurs côté interface
11. Historique côté interface
12. Navigation et design
13. Tests frontend


======================================================================
                 PARTIE 1 — PERSONNE 1 — BACKEND
======================================================================


---------------------------
ÉTAPE B1 — INITIALISATION
---------------------------

Objectif :
Créer le backend Laravel fonctionnel.

À faire :

1. Créer le projet Laravel dans :

   backend/

2. Configurer MySQL dans .env

3. Vérifier :

   php artisan migrate

4. Vérifier que Laravel démarre.

5. Configurer l'API.

6. Configurer l'authentification API.

À la fin :

   Laravel fonctionne
   +
   MySQL fonctionne
   +
   API fonctionne


NE PAS FAIRE :
- Ne pas créer de pages Vue.
- Ne pas modifier frontend/.
- Ne pas commencer LiveKit.


---------------------------
ÉTAPE B2 — BASE DE DONNÉES
---------------------------

Créer les tables nécessaires.

TABLE users
-----------

Champs minimum :

id
name
email
password
role
created_at
updated_at


TABLE meetings
--------------

Champs minimum :

id
title
description
created_by
status
started_at
ended_at
created_at
updated_at


TABLE meeting_requests
----------------------

Champs minimum :

id
meeting_id
user_id
status
created_at
updated_at


Relations :

users
  │
  ├── meetings
  │      created_by
  │
  └── meeting_requests
           │
           └── meetings


Statuts d'une demande :

pending
accepted
cancelled


Statuts d'une réunion :

waiting
active
finished
cancelled


IMPORTANT :
Ne pas changer les noms des champs après que le frontend
commence à utiliser l'API sans prévenir PERSONNE 2.


---------------------------
ÉTAPE B3 — AUTHENTIFICATION
---------------------------

Créer :

POST /api/login

POST /api/logout

GET /api/me


LOGIN :

Entrée :

email
password


Sortie :

token
user


Exemple :

{
    "token": "...",
    "user": {
        "id": 1,
        "name": "Jean",
        "email": "jean@gmail.com",
        "role": "user"
    }
}


Tester toutes les routes avant de continuer.


---------------------------
ÉTAPE B4 — UTILISATEURS
---------------------------

Créer le CRUD API.

Routes :

GET    /api/users

POST   /api/users

GET    /api/users/{id}

PUT    /api/users/{id}

DELETE /api/users/{id}


Fonctions :

- afficher les utilisateurs
- créer un utilisateur
- afficher un utilisateur
- modifier un utilisateur
- supprimer un utilisateur


IMPORTANT :

L'inscription publique n'existe PAS.

Les utilisateurs sont créés depuis le BACKOFFICE.


Tester avec Postman/Insomnia.


---------------------------
ÉTAPE B5 — RÉUNIONS
---------------------------

Créer les modèles et contrôleurs nécessaires.


Routes :

POST /api/meetings

GET /api/meetings

GET /api/meetings/{id}

PUT /api/meetings/{id}

DELETE /api/meetings/{id}


Règle :

Un utilisateur connecté peut créer une réunion.

Le créateur devient automatiquement :

created_by = utilisateur connecté


Lorsqu'un utilisateur consulte les réunions :

Il doit pouvoir voir :

- titre
- description
- créateur
- statut
- date


Tester toutes les routes.


---------------------------
ÉTAPE B6 — DEMANDE D'ACCÈS
---------------------------

Créer :

POST /api/meetings/{meeting}/requests


Lorsqu'un utilisateur demande à rejoindre :

Créer une ligne :

meeting_id
user_id
status = pending


RÈGLES :

1. Un utilisateur connecté peut demander l'accès.

2. Le créateur de la réunion n'a pas besoin de demander.

3. Un utilisateur ne peut pas envoyer deux demandes
   pour la même réunion si une demande existe déjà.

4. Une demande commence avec :

   pending


---------------------------
ÉTAPE B7 — GESTION DES DEMANDES
---------------------------

Créer :

GET /api/meetings/{meeting}/requests

PUT /api/requests/{request}/accept

PUT /api/requests/{request}/cancel


RÈGLES :

SEUL LE CRÉATEUR DE LA RÉUNION peut :

- voir les demandes de sa réunion
- accepter une demande
- annuler une demande


Lorsqu'une demande est acceptée :

status = accepted


Lorsqu'elle est annulée :

status = cancelled


IMPORTANT :
Vérifier côté Laravel que l'utilisateur est bien
le créateur de la réunion.

Ne jamais faire confiance uniquement au frontend.


---------------------------
ÉTAPE B8 — HISTORIQUE
---------------------------

Créer une route réservée au backoffice :

GET /api/admin/meetings/history


Elle doit retourner :

- réunion
- créateur
- date
- heure de début
- heure de fin
- durée
- statut
- participants si disponible


Cette route sera utilisée par PERSONNE 2.


---------------------------
ÉTAPE B9 — SÉCURITÉ
---------------------------

Vérifier :

- utilisateur connecté
- permissions admin
- propriétaire de la réunion
- propriétaire des demandes
- accès aux données


Un utilisateur normal ne doit PAS pouvoir :

- supprimer un autre utilisateur
- modifier les données admin
- accepter une demande d'une réunion qui ne lui appartient pas
- modifier une réunion qui ne lui appartient pas


---------------------------
ÉTAPE B10 — LIVRAISON BACKEND
---------------------------

Avant de donner le backend à PERSONNE 2 :

[ ] Login testé
[ ] Logout testé
[ ] /me testé
[ ] CRUD users testé
[ ] Création réunion testée
[ ] Liste réunions testée
[ ] Demande d'accès testée
[ ] Acceptation testée
[ ] Annulation testée
[ ] Historique testé
[ ] Permissions testées

Faire un commit :

git add .
git commit -m "feat: complete meeting management API"

Puis push.


======================================================================
                 PARTIE 2 — PERSONNE 2 — FRONTEND
======================================================================


---------------------------
ÉTAPE F1 — INITIALISATION
---------------------------

Créer le projet Vue dans :

frontend/


Installer :

- Vue Router
- Pinia
- Axios


Structure :

frontend/
└── src/
    ├── components/
    ├── views/
    ├── layouts/
    ├── stores/
    ├── services/
    ├── router/
    └── assets/


NE PAS MODIFIER :

backend/


---------------------------
ÉTAPE F2 — ROUTER
---------------------------

Créer les routes :

/login

/dashboard

/meetings/create

/meetings/{id}

/meetings/{id}/requests

/admin/users

/admin/meetings/history


Créer la protection des routes.


Un utilisateur non connecté :

→ /login


Un utilisateur connecté :

→ dashboard


Un utilisateur normal ne doit pas accéder :

→ /admin/*


---------------------------
ÉTAPE F3 — LOGIN
---------------------------

Créer :

/login


Interface :

Email
Mot de passe
[ Se connecter ]


Au clic :

POST /api/login


Si succès :

- sauvegarder le token
- sauvegarder l'utilisateur
- rediriger vers /dashboard


Si erreur :

Afficher un message.


---------------------------
ÉTAPE F4 — DASHBOARD
---------------------------

Créer :

/dashboard


Afficher :

- nom utilisateur
- réunions disponibles
- mes réunions
- mes demandes


Exemple :

---------------------------------
Réunions disponibles

Réunion équipe
Créateur : Paul

[Demander à rejoindre]

---------------------------------

Formation Laravel
Créateur : Marie

[Demander à rejoindre]
---------------------------------


Utiliser :

GET /api/meetings


---------------------------
ÉTAPE F5 — CRÉER UNE RÉUNION
---------------------------

Créer :

/meetings/create


Formulaire :

Titre
Description

[Créer]


Envoyer :

POST /api/meetings


Après succès :

→ afficher confirmation
→ rediriger vers la réunion


---------------------------
ÉTAPE F6 — DEMANDE D'ACCÈS
---------------------------

Dans la liste des réunions :

Bouton :

[Demander à rejoindre]


Appeler :

POST /api/meetings/{id}/requests


Après succès :

Afficher :

"Demande envoyée"


Le bouton devient :

"Demande en attente"


---------------------------
ÉTAPE F7 — MES DEMANDES
---------------------------

Afficher les demandes de l'utilisateur.


Exemple :

Réunion        Statut
-------------------------
Réunion A      En attente
Réunion B      Acceptée
Réunion C      Annulée


Statuts :

pending
accepted
cancelled


Si :

accepted

Afficher :

[Rejoindre]


---------------------------
ÉTAPE F8 — DEMANDES DU CRÉATEUR
---------------------------

Créer :

/meetings/{id}/requests


Afficher :

Nom        Statut       Actions
----------------------------------
Jean       pending      Accepter
                       Annuler

Paul       accepted


Boutons :

[Accepter]

[Annuler]


Appeler :

PUT /api/requests/{id}/accept

ou

PUT /api/requests/{id}/cancel


---------------------------
ÉTAPE F9 — BACKOFFICE USERS
---------------------------

Créer :

/admin/users


Afficher :

Nom
Email
Rôle
Actions


Actions :

[Ajouter]

[Modifier]

[Supprimer]


Utiliser :

GET
POST
PUT
DELETE

/api/users


---------------------------
ÉTAPE F10 — HISTORIQUE
---------------------------

Créer :

/admin/meetings/history


Afficher :

Réunion
Créateur
Date
Début
Fin
Durée
Statut


Utiliser :

GET /api/admin/meetings/history


---------------------------
ÉTAPE F11 — INTERFACE
---------------------------

Après que toutes les fonctionnalités fonctionnent :

- améliorer le design
- responsive
- messages de succès
- messages d'erreur
- loaders
- boutons désactivés pendant les requêtes
- confirmations de suppression


NE PAS modifier la logique backend.


---------------------------
ÉTAPE F12 — LIVRAISON FRONTEND
---------------------------

Vérifier :

[ ] Login
[ ] Logout
[ ] Dashboard
[ ] Liste réunions
[ ] Création réunion
[ ] Demande d'accès
[ ] Liste demandes
[ ] Acceptation
[ ] Annulation
[ ] CRUD utilisateurs
[ ] Historique
[ ] Protection routes
[ ] Responsive


Faire :

git add .
git commit -m "feat: complete meeting platform frontend"

Puis push.


======================================================================
                    ORGANISATION GITHUB
======================================================================


BRANCHE PRINCIPALE :

main


PERSONNE 1 :

feature/backend-auth
feature/backend-users
feature/backend-meetings
feature/backend-requests
feature/backend-history


PERSONNE 2 :

feature/frontend-login
feature/frontend-dashboard
feature/frontend-meetings
feature/frontend-requests
feature/frontend-backoffice


IMPORTANT :

Ne jamais coder directement sur main.


======================================================================
                     RÈGLE DES COMMITS
======================================================================

Un commit = une fonctionnalité.


EXEMPLES :

feat: add authentication API

feat: add users CRUD API

feat: add meetings API

feat: add meeting requests API

feat: add meeting history API

feat: add login page

feat: add meetings dashboard

feat: add meeting request interface

feat: add admin users page

feat: add meeting history page


ÉVITER :

"update"

"modification"

"test"

"fix stuff"

"final"


======================================================================
                 ORDRE DE DÉVELOPPEMENT
======================================================================


                    DÉBUT
                      │
                      ▼
          ┌──────────────────────┐
          │ Architecture + DB   │
          └──────────┬───────────┘
                     │
          ┌──────────┴───────────┐
          ▼                      ▼
   PERSONNE 1              PERSONNE 2
   Backend                  Frontend
          │                      │
          ▼                      ▼
   Auth API                  Login
          │                      │
          ▼                      ▼
   Users API                Dashboard
          │                      │
          ▼                      ▼
   Meetings API             Meetings UI
          │                      │
          ▼                      ▼
   Requests API             Requests UI
          │                      │
          ▼                      ▼
   History API              Backoffice
          │                      │
          └──────────┬───────────┘
                     ▼
              INTÉGRATION
                     │
                     ▼
                TEST GLOBAL
                     │
                     ▼
              APPEL VIDÉO
              LiveKit/WebRTC
                     │
                     ▼
                   FIN


======================================================================
                  RÈGLE DE COMMUNICATION
======================================================================

Quand PERSONNE 1 termine une API :

1. Elle documente l'endpoint.
2. Elle donne :
   - URL
   - méthode
   - paramètres
   - JSON envoyé
   - JSON retourné
   - erreurs possibles

3. Elle prévient PERSONNE 2.

PERSONNE 2 ne doit PAS deviner le fonctionnement de l'API.


Exemple :

POST /api/meetings

BODY :

{
    "title": "Réunion équipe",
    "description": "Réunion hebdomadaire"
}


RESPONSE :

{
    "id": 1,
    "title": "Réunion équipe",
    "description": "Réunion hebdomadaire",
    "created_by": 2,
    "status": "waiting"
}


======================================================================
                         INTERDICTIONS
======================================================================

PERSONNE 1 NE TOUCHE PAS :

- frontend/src/views
- frontend/src/components
- frontend/src/router
- frontend/src/stores


PERSONNE 2 NE TOUCHE PAS :

- backend/app/Models
- backend/app/Http/Controllers
- backend/database/migrations
- backend/routes/api.php


SAUF ACCORD DES DEUX.


======================================================================
                       OBJECTIF FINAL
======================================================================

L'utilisateur peut :

1. Se connecter.
2. Voir les réunions.
3. Créer une réunion.
4. Voir les réunions créées par les autres.
5. Demander à rejoindre une réunion.
6. Attendre l'acceptation.
7. Le créateur voit les demandes.
8. Le créateur accepte ou annule.
9. L'utilisateur accepté peut rejoindre.
10. Les administrateurs gèrent les utilisateurs.
11. Les administrateurs consultent l'historique.
12. La plateforme permet ensuite de rejoindre l'appel vidéo.


======================================================================
                         FIN
======================================================================