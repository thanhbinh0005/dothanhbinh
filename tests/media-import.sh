#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose run --rm -T cli eval 'if (wp_get_environment_type() !== "local" || get_option("site_icon") || get_option("tb_imported_icon")) WP_CLI::error("Requires local environment with no configured icon; existing data preserved.");' >/dev/null
mkdir -p .runtime/media-test
cleanup() {
  docker compose run --rm -T cli eval '$r=get_option("tb_imported_icon"); if ($r) wp_delete_attachment($r["id"],true); delete_option("tb_imported_icon"); delete_option("site_icon");' >/dev/null
  rm -f .runtime/media-test/bieu-tuong-tb.png
}
trap cleanup EXIT
# A standard WordPress asset is only a transport-test fixture, never a company logo.
docker compose cp wordpress:/var/www/html/wp-includes/images/w-logo-blue-white-bg.png .runtime/media-test/bieu-tuong-tb.png >/dev/null
TB_MEDIA_DIR=.runtime/media-test bash scripts/import-media.sh
before=$(docker compose run --rm -T cli option get site_icon)
TB_MEDIA_DIR=.runtime/media-test bash scripts/import-media.sh
after=$(docker compose run --rm -T cli option get site_icon)
[[ "$before" == "$after" && "$after" =~ ^[0-9]+$ ]]
printf 'PASS original-file import transport, Media registration, icon assignment and idempotency; fixture removed.\n'
