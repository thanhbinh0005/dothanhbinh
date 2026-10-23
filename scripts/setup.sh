#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
command -v docker >/dev/null
docker compose version >/dev/null
if [ ! -f .env ]; then
  python3 - <<'PY'
import os,secrets
from pathlib import Path
fd=os.open('.env',os.O_WRONLY|os.O_CREAT|os.O_EXCL,0o600)
with os.fdopen(fd,'w') as f:
 f.write('DB_PASSWORD='+secrets.token_hex(24)+'\nDB_ROOT_PASSWORD='+secrets.token_hex(24)+'\nWP_PORT=8080\n')
PY
fi
find wp-content -type d -exec chmod 755 {} +
find wp-content -type f -exec chmod 644 {} +
mkdir -p .runtime
chmod 700 .runtime
docker compose up -d --wait db wordpress
for attempt in $(seq 1 60); do
  if docker compose exec -T wordpress test -f /var/www/html/wp-includes/version.php; then break; fi
  sleep 1
done
detected_port=$(docker compose port wordpress 80 | sed 's/.*://')
if ! docker compose run --rm cli core is-installed >/dev/null 2>&1; then
  python3 - <<'PY'
from pathlib import Path
import os,secrets
p=Path('.runtime/admin-password')
if not p.exists():
 fd=os.open(p,os.O_WRONLY|os.O_CREAT|os.O_EXCL,0o600)
 with os.fdopen(fd,'w') as f: f.write(secrets.token_urlsafe(24))
PY
  # Password travels over stdin, rather than command-line arguments or logs.
  docker compose run --rm -T cli core install --url="${SITE_URL:-http://localhost:$detected_port}" --title='Thanh Bình Hưng Yên — Pallet gỗ' --admin_user=siteadmin --admin_email="${ADMIN_EMAIL:-admin@example.invalid}" --skip-email --prompt=admin_password < .runtime/admin-password >/dev/null 2>&1
  docker compose run --rm -T cli option update tb_fresh_install 1 >/dev/null
fi
docker compose run --rm -T cli plugin activate thanh-binh-orders
docker compose run --rm -T cli theme activate thanh-binh
if ! docker compose run --rm -T cli option get tb_environment_initialized >/dev/null 2>&1; then
  docker compose run --rm -T cli option update timezone_string Asia/Ho_Chi_Minh
  docker compose run --rm -T cli option update blogdescription 'Pallet gỗ & chân cục pallet — Giải pháp đóng gói theo yêu cầu'
  docker compose run --rm -T cli rewrite structure '/%postname%/' --hard
  docker compose run --rm -T cli option update tb_environment_initialized 1 >/dev/null
fi
docker compose run --rm -T cli eval-file /var/www/html/wp-content/themes/thanh-binh/seed.php
if [ -d content/media ]; then bash scripts/import-media.sh; fi
printf '\nWordPress started. Admin user: siteadmin. Initial password: .runtime/admin-password (local file, do not commit).\n'
printf 'Required: set real company details, email/SMTP and review privacy policy before public deployment.\n'
