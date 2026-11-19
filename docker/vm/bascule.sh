#!/bin/bash
# Bascule de l'ancienne app (Apache mod_php 7.4 + MySQL natif) vers la stack Docker, et retour arrière.
#   docker/vm/bascule.sh docker   → sert la nouvelle version (proxy vers 127.0.0.1:8080)
#   docker/vm/bascule.sh ancien   → remet l'ancienne version (retour arrière)
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
    sudo a2dissite -q projmulti-docker
    sudo a2enmod -q php7.4
    sudo a2ensite -q projmulti projmulti-ssl
    sudo systemctl start mysql
    ;;
  *)
    echo "usage: $0 docker|ancien" >&2; exit 1 ;;
esac
sudo apachectl configtest
sudo systemctl reload apache2
echo "Apache rechargé ($1)."
