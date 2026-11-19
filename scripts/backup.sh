#!/bin/bash
# Sauvegarde de la base (conteneur db) : dump compressé, vérifié, rotation 30 jours.
# Cron sur la VM :  0 12,23 * * * /opt/projmulti/projmulti-backend/scripts/backup.sh >> /home/iairoot/backup.log 2>&1
set -o pipefail
BACKUP_DIR=${BACKUP_DIR:-/home/iairoot/backup}
cd "$(dirname "$0")/.."
f="$BACKUP_DIR/backup_$(date +%Y-%m-%d-%Hh%M).sql.gz"
if docker compose exec -T db sh -c 'exec mysqldump --single-transaction --no-tablespaces -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' 2>/dev/null | gzip -9 > "$f" \
   && zcat "$f" | tail -1 | grep -q "Dump completed"; then
  echo "$(date '+%F %T') OK $f $(du -h "$f" | cut -f1)"
else
  echo "$(date '+%F %T') ERREUR $f"; rm -f "$f"
fi
# Le dump de référence d'avant la migration Docker n'est jamais supprimé.
find "$BACKUP_DIR" -name 'backup_*.sql.gz' -mtime +30 ! -name 'backup_2026-07-09-15h00.sql.gz' -delete
