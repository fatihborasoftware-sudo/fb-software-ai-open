$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot

php "$PSScriptRoot\Unit\run.php"
php "$PSScriptRoot\Integration\plugin-bootstrap.php"
php "$PSScriptRoot\Integration\dashboard-all-disabled.php"
php "$PSScriptRoot\Integration\workspace-settings-ui.php"
php "$PSScriptRoot\Integration\upgrade-0138-to-0139.php"
php "$PSScriptRoot\Integration\frontend-no-write.php"
php "$PSScriptRoot\Integration\uninstall.php"
python "$PSScriptRoot\Integration\characterization.py"

Write-Host 'All FB Software AI v0.1.139 source tests completed.'
