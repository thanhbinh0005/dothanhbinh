#!/usr/bin/env python3
"""Build installable WordPress ZIPs containing public code, never runtime secrets."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
root = Path(__file__).resolve().parent.parent
out = root / 'dist'
out.mkdir(exist_ok=True)
for source, name in [('wp-content/themes/thanh-binh', 'thanh-binh-theme.zip'), ('wp-content/plugins/thanh-binh-orders', 'thanh-binh-orders.zip')]:
    folder = root / source
    with ZipFile(out / name, 'w', ZIP_DEFLATED) as z:
        for file in sorted(folder.rglob('*')):
            if file.is_file(): z.write(file, file.relative_to(folder.parent))
    print(name)

if not (out / 'thanh-binh-content.xml').exists():
    raise SystemExit('Run bash scripts/export-content.sh before packaging the complete kit.')
with ZipFile(out / 'thanh-binh-wordpress-kit.zip', 'w', ZIP_DEFLATED) as z:
    for name in ['thanh-binh-theme.zip', 'thanh-binh-orders.zip', 'thanh-binh-content.xml']:
        z.write(out / name, name)
    for name in ['TRIEN-KHAI.md', 'QUAN-TRI.md', 'NOI-DUNG-VA-HINH-ANH.md']:
        z.write(root / 'docs' / name, 'huong-dan/' + name)
print('thanh-binh-wordpress-kit.zip')
