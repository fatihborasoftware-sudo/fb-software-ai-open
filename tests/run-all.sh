#!/usr/bin/env bash
set -euo pipefail
WORK="$(cd "$(dirname "$0")/.." && pwd)"
php "$WORK/tests/Unit/run.php"
php "$WORK/tests/Integration/plugin-bootstrap.php"
php "$WORK/tests/Integration/dashboard-all-disabled.php"
php "$WORK/tests/Integration/workspace-settings-ui.php"
php "$WORK/tests/Integration/upgrade-0138-to-0139.php"
php "$WORK/tests/Integration/frontend-no-write.php"
php "$WORK/tests/Integration/uninstall.php"
python3 "$WORK/tests/Integration/characterization.py"
