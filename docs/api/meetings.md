# API — Réunions

Documenter ici la création, la consultation, la modification et la suppression des réunions.
Base locale : `http://localhost:8000/api`. Toutes les routes nécessitent `Authorization: Bearer <token>` et `Accept: application/json`.

## Réunions

- `GET /meetings` : réunions visibles, avec `is_creator`, `my_request`, `can_start`, `can_host`, `can_join` et `participants_count`.
- `POST /meetings` : crée une réunion en état `scheduled`; le créateur ne crée pas de demande d’accès.
- `GET /meetings/{id}` : détails; le créateur reçoit ses demandes séparément via la route dédiée.
- `POST /meetings/{id}/start` : seul le créateur peut faire passer `scheduled` à `live`.
- `POST /meetings/{id}/end` : seul le créateur peut terminer une réunion `live`.

## Demandes d’accès

- `POST /meetings/{id}/join-requests` : crée une demande `pending`; doublon, utilisateur accepté ou créateur refusé (`409`/`422`).
- `GET /meetings/{id}/join-requests` : demandes de la réunion, créateur uniquement.
- `PATCH /meetings/{id}/join-requests/{requestId}` : créateur uniquement, corps `{ "status": "accepted" }` ou `{ "status": "cancelled" }`.

Une demande acceptée ne permet pas d’entrer avant que l’état de la réunion soit `live`. Le créateur peut encore accepter des demandes pendant le direct.

## Vidéo

- `POST /meetings/{id}/access-token` : fournit le jeton LiveKit à l’hôte ou à un participant accepté, uniquement quand la réunion est `live`.

LiveKit doit être configuré côté backend avec `LIVEKIT_URL`, `LIVEKIT_API_KEY` et `LIVEKIT_API_SECRET`.
