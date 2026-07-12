from __future__ import annotations
import hashlib
import re
import sys
from pathlib import Path

root = Path(__file__).resolve().parents[1]
errors = []

required = [
    'fb-software-ai.php', 'readme.txt', 'uninstall.php', 'LICENSE',
    'README.md', 'CHANGELOG.md', 'ROADMAP.md', 'SECURITY.md',
    'CONTRIBUTING.md', 'workflows/workflows.json',
]
for rel in required:
    if not (root / rel).is_file():
        errors.append(f'Missing required file: {rel}')

php = (root / 'fb-software-ai.php').read_text(encoding='utf-8')
readme = (root / 'readme.txt').read_text(encoding='utf-8')
workflow = (root / 'workflows/workflows.json').read_text(encoding='utf-8')
for value, label in [
    (r'Version:\s*0\.1\.139', 'plugin header'),
    (r"const VERSION = '0\.1\.139'", 'legacy facade'),
]:
    if not re.search(value, php):
        errors.append(f'Version mismatch: {label}')
if 'Stable tag: 0.1.139' not in readme:
    errors.append('Version mismatch: readme stable tag')
if '"version": "0.1.139"' not in workflow:
    errors.append('Version mismatch: workflow JSON')

for path in root.rglob('*'):
    if path.is_file() and path.name in {'.env', '.env.local', 'debug.log'}:
        errors.append(f'Forbidden local file: {path.relative_to(root)}')

if errors:
    print('\n'.join('FAIL: ' + error for error in errors))
    sys.exit(1)
print('PASS: FB Software AI v0.1.139 GitHub save-point source is structurally valid.')
