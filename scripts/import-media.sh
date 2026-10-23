#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
media_dir=${TB_MEDIA_DIR:-content/media}
# These files are original uploads supplied by the site owner. Never generate copies of the logo.
docker compose exec -T --user www-data wordpress mkdir -p /var/www/html/wp-content/uploads/.tb-import
for item in 'logo:logo-ngang' 'icon:bieu-tuong-tb' 'blocks:chan-cuc-pallet' 'pallet:pallet-go'; do
  key=${item%%:*}; name=${item#*:}
  for ext in jpg jpeg png webp; do
    file="$media_dir/$name.$ext"
    if [ -f "$file" ]; then
      docker compose cp "$file" "wordpress:/var/www/html/wp-content/uploads/.tb-import/tb-$key.$ext"
      docker compose exec -T wordpress chmod 644 "/var/www/html/wp-content/uploads/.tb-import/tb-$key.$ext"
      docker compose run --rm -T -e TB_MEDIA_KEY="$key" -e TB_MEDIA_FILE="/var/www/html/wp-content/uploads/.tb-import/tb-$key.$ext" cli eval-file /var/www/html/wp-content/themes/thanh-binh/import-media.php
      docker compose exec -T wordpress rm -f "/var/www/html/wp-content/uploads/.tb-import/tb-$key.$ext"
      break
    fi
  done
done
