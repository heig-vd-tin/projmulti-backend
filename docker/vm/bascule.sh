#!/bin/bash
# Bascule de l'ancienne app (Apache mod_php 7.4 + MySQL natif) vers la stack Docker, et retour arrière.
#   docker/vm/bascule.sh docker   → sert la nouvelle version (proxy vers 127.0.0.1:8080)
#   docker/vm/bascule.sh ancien   → plus possible : l'ancienne installation (PHP 7.4, MySQL natif,
#                                    /var/www/projmulti) a été supprimée le 19.11.2026, archive dans
#                                    ~/backup/ancienne-app-2026-11-19.tar.gz. Pour revenir en arrière,
#                                    redéployer un commit précédent (git checkout + docker compose up -d --build).
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"

case "${1:-}" in
  docker)
    sudo cp "$HERE/projmulti-docker.conf" /etc/apache2/sites-available/projmulti-docker.conf
    sudo a2enmod -q ssl proxy proxy_http headers
    sudo a2dissite -q projmulti projmulti-ssl
    sudo a2ensite -q projmulti-docker
    sudo a2dismod -q php7.4 || true
    ;;
  ancien)
    echo "L'ancienne installation a été supprimée le 19.11.2026 (archive ~/backup/ancienne-app-2026-11-19.tar.gz)." >&2
    echo "Retour arrière : redéployer un commit précédent de ce dépôt (git checkout <commit> && docker compose up -d --build)." >&2
    exit 1
    ;;
  *)
    echo "usage: $0 docker|ancien" >&2; exit 1 ;;
esac
sudo apachectl configtest
sudo systemctl reload apache2
echo "Apache rechargé ($1)."
