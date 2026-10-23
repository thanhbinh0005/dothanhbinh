#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p dist
ids=$(docker compose run --rm -T cli eval '$ids=get_posts(["post_type"=>["post","tb_product"],"post_status"=>"publish","numberposts"=>-1,"fields"=>"ids"]); $privacy=(int)get_option("wp_page_for_privacy_policy"); if ($privacy) $ids[]=$privacy; if (!$ids) WP_CLI::error("No public content selected."); echo implode(",",array_unique($ids));')
docker compose run --rm -T cli export --post__in="$ids" --skip_comments --stdout > dist/thanh-binh-content.xml
python3 - <<'PY'
from pathlib import Path
import xml.etree.ElementTree as ET
ns={'wp':'http://wordpress.org/export/1.2/'}
items=ET.parse('dist/thanh-binh-content.xml').findall('./channel/item')
assert items and all(i.find('wp:post_type',ns).text in ['post','page','tb_product'] for i in items)
assert all(i.find('wp:status',ns).text=='publish' for i in items)
print('Exported',len(items),'public content records; no quotes or drafts.')
PY
