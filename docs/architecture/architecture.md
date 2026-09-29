# Architecture de l’application

## Vue d’ensemble

```text
Vue.js (frontend) -- HTTP/JSON --> Laravel (backend) --> MySQL
                                        |
                                        +--> LiveKit/WebRTC (phase vidéo)
```

## Séparation des responsabilités

### Backend

Le backend expose l’API, valide les entrées, applique l’authentification et les permissions, orchestre les services métier et persiste les données.

### Frontend

Le frontend consomme exclusivement l’API documentée, gère l’état de l’interface, la navigation et l’expérience utilisateur.

### Règle d’intégration

Les noms de champs, statuts et formats JSON validés dans l’API ne doivent pas changer sans prévenir l’autre développeur.

## Couches backend

- `Http/Controllers` : entrée HTTP et orchestration légère.
- `Http/Requests` : validation des requêtes.
- `Http/Resources` : format des réponses JSON.
- `Services` : logique métier.
- `Repositories` : accès aux données lorsque nécessaire.
- `Models` : modèle de persistance et relations.
- `Policies` / `Middleware` : autorisation et sécurité.
- `Enums` : statuts et rôles partagés.

## Couches frontend

- `views/` : pages liées aux routes.
- `components/` : composants réutilisables par domaine.
- `layouts/` : structures d’écran.
- `services/` : communication API et intégration vidéo.
- `stores/` : état global Pinia.
- `router/` : routes et guards.
- `composables/` : logique réutilisable côté Vue.
- `utils/` et `constants/` : fonctions et valeurs partagées.
