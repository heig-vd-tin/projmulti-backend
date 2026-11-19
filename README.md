# ProjMulti — backend

Plateforme de répartition des projets multidisciplinaires (HEIG-VD) : les enseignants proposent
des projets, les étudiants en choisissent 5, le responsable lance une répartition automatique
puis l'ajuste à la main.

- Backend : Laravel 13 / PHP 8.5 (ce dépôt)
- Front : Vue 2 + Vuetify 2 ([projmulti-frontend](https://github.com/heig-vd-tin/projmulti-frontend)), compilé dans l'image Docker
- Base : MySQL 8.4 (conteneur `db`)
- Authentification : Keycloak HEIG-VD (jeton Bearer) ; comptes créés à la première connexion
  depuis le LDAP de l'école (rôle et orientation)

## Fonctionnement

L'admin choisit en ligne (page **Réglages**) la phase de la plateforme :

| Phase | Enseignants | Étudiants |
|---|---|---|
| `projects` — saisie des projets | créent / modifient leurs projets | pas d'accès |
| `preferences` — choix des étudiants | consultent | choisissent 5 projets |
| `closed` — fermé (répartition) | consultent | pas d'accès |

Les projets sont rattachés à une **année de cours** (`projects.year`, ex. 2026). « Démarrer le cours
suivant » supprime les choix, la répartition et les étudiants, mais garde les projets : un enseignant
peut consulter ses anciens projets et les reprendre (« Reprendre pour 2027 »).

## Développement local

Prérequis : Docker. Les deux dépôts côte à côte (`../projmulti-frontend`).

```bash
cp .env.example .env        # puis APP_ENV=local, APP_DEBUG=true, ID_USER=<id admin>, mots de passe DB
docker compose -f compose.yaml -f compose.dev.yaml up -d --build
zcat dump.sql.gz | docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" laravel'
docker compose exec app php artisan migrate
```

API sur http://localhost:8080/api. En local (`APP_ENV=local` et `APP_DEBUG=true`), pas de Keycloak :
l'utilisateur `ID_USER` est connecté d'office, et le header `X-Dev-User: <id>` permet d'en changer.
Front en mode dev : `npm run serve` dans le dépôt du front (même principe, `localStorage.devUser = <id>`).

Les sources PHP sont montées dans le conteneur ; ajouter une classe ou un paquet composer demande
un `--build`.

## Production (VM tin-projmulti)

```
/opt/projmulti/projmulti-backend    ce dépôt, avec le .env de production
/opt/projmulti/projmulti-frontend   dépôt du front
```

L'Apache de la VM termine le HTTPS et fait proxy vers le conteneur (`127.0.0.1:8080`).

Mise à jour :

```bash
cd /opt/projmulti/projmulti-frontend && git pull
cd /opt/projmulti/projmulti-backend && git pull
scripts/backup.sh
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

Sauvegardes : `scripts/backup.sh` (cron 12 h et 23 h, rétention 30 jours, dans `/home/iairoot/backup`).
Restauration : `zcat backup_….sql.gz | docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" laravel'`.

Variables sensibles uniquement dans `.env` (jamais dans le code) : `APP_KEY`, mots de passe MySQL,
`KEYCLOAK_REALM_PUBLIC_KEY`, `LDAP_PASSWORD`.
