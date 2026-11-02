#!/usr/bin/env python3
"""Build complete and standalone WordPress installers; never include runtime secrets."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
from shutil import copyfile
root = Path(__file__).resolve().parent.parent
out = root / 'dist'
out.mkdir(exist_ok=True)
theme = root / 'wp-content/themes/thanh-binh'
plugin = root / 'wp-content/plugins/thanh-binh-orders'
for required in [theme / 'screenshot.jpg', plugin / 'assets/website-preview.jpg', plugin / 'assets/pallet-go-ke-hang.jpg']:
    if not required.is_file(): raise SystemExit('Required image missing: ' + str(required.relative_to(root)))
with ZipFile(out / 'thanh-binh-theme.zip', 'w', ZIP_DEFLATED) as z:
    for file in sorted(theme.rglob('*')):
        if file.is_file(): z.write(file, file.relative_to(theme.parent))
with ZipFile(out / 'thanh-binh-orders.zip', 'w', ZIP_DEFLATED) as z:
    for file in sorted(plugin.rglob('*')):
        if file.is_file(): z.write(file, file.relative_to(plugin.parent))
    z.write(out / 'thanh-binh-theme.zip', 'thanh-binh-orders/packages/thanh-binh-theme.zip')
if not (out / 'thanh-binh-content.xml').exists():
    raise SystemExit('Run bash scripts/export-content.sh before packaging the complete kit.')
with ZipFile(out / 'thanh-binh-wordpress-kit.zip', 'w', ZIP_DEFLATED) as z:
    for name in ['thanh-binh-theme.zip', 'thanh-binh-orders.zip', 'thanh-binh-content.xml']:
        z.write(out / name, name)
    for name in ['TRIEN-KHAI.md', 'QUAN-TRI.md', 'NOI-DUNG-VA-HINH-ANH.md', 'PHP74-XAMPP.md', 'CAI-WEBSITE-HOAN-CHINH.md']:
        z.write(root / 'docs' / name, 'huong-dan/' + name)
for name in ['thanh-binh-orders', 'thanh-binh-theme', 'thanh-binh-wordpress-kit']:
    copyfile(out / (name + '.zip'), out / (name + '-php74.zip'))
copyfile(out / 'thanh-binh-orders.zip', out / 'thanh-binh-website-hoan-chinh-php74.zip')
copyfile(plugin / 'assets/pallet-go-ke-hang.jpg', out / 'pallet-go-ke-hang.jpg')
for file in sorted(out.glob('*.zip')):
    print(file.name, file.stat().st_size, 'bytes')
