# Référence de la base de données

La base cible est MySQL. Les migrations Laravel seront la source de vérité lorsque l’implémentation commencera.

## Entités prévues

- `users` : utilisateurs et rôle (`user`, `admin`).
- `meetings` : réunions créées par un utilisateur.
- `meeting_requests` : demandes d’accès à une réunion.
- `participants` : à confirmer selon le besoin de suivi des participants vidéo.

## Statuts prévus

- Demande : `pending`, `accepted`, `cancelled`.
- Réunion : `waiting`, `active`, `finished`, `cancelled`.

Les changements de schéma doivent être annoncés avant que l’API frontend ne dépende des champs concernés.
