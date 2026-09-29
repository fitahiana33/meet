AUTHENTIFICATION
────────────────────────────────────────────
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me


UTILISATEURS — ADMIN
────────────────────────────────────────────
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}


RÉUNIONS
────────────────────────────────────────────
GET    /api/meetings
POST   /api/meetings
GET    /api/meetings/{id}
PUT    /api/meetings/{id}
DELETE /api/meetings/{id}


DEMANDES D'ACCÈS
────────────────────────────────────────────
POST   /api/meetings/{meeting}/requests
GET    /api/meetings/{meeting}/requests

PUT    /api/requests/{request}/accept
PUT    /api/requests/{request}/cancel


MES DEMANDES
────────────────────────────────────────────
GET    /api/my/requests


HISTORIQUE — ADMIN
────────────────────────────────────────────
GET    /api/admin/meetings/history


VIDÉO — PLUS TARD
────────────────────────────────────────────
POST   /api/meetings/{meeting}/video/token