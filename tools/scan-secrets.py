from __future__ import annotations
import re
import sys
from pathlib import Path

root = Path(__file__).resolve().parents[1]
patterns = {
    'private key': re.compile(r'-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----'),
    'GitHub token': re.compile(r'gh[pousr]_[A-Za-z0-9_]{30,}'),
    'OpenAI key': re.compile(r'sk-[A-Za-z0-9]{20,}'),
    'AWS access key': re.compile(r'AKIA[0-9A-Z]{16}'),
    'Stripe live key': re.compile(r'(?:sk|rk)_live_[A-Za-z0-9]{16,}'),
}
forbidden_names = {'.env', '.env.local', 'debug.log'}
forbidden_suffixes = {'.sql', '.sqlite', '.db', '.p12', '.pfx'}
hits = []
for path in sorted(root.rglob('*')):
    if not path.is_file() or '.git' in path.parts:
        continue
    rel = path.relative_to(root).as_posix()
    if path.name in forbidden_names or path.suffix.lower() in forbidden_suffixes:
        hits.append(f'Forbidden local/sensitive file: {rel}')
        continue
    try:
        data = path.read_bytes()
        if b'\x00' in data[:4096]:
            continue
        text = data.decode('utf-8')
    except Exception:
        continue
    for label, pattern in patterns.items():
        if pattern.search(text):
            hits.append(f'Possible {label}: {rel}')
if hits:
    print('\n'.join('FAIL: ' + hit for hit in hits))
    sys.exit(1)
print('PASS: No high-confidence secrets or forbidden local files detected.')
