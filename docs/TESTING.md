# Testing and Release Validation

## Automated source tests

Run on Windows:

```powershell
powershell -ExecutionPolicy Bypass -File .	ests
un-all.ps1
```

Run on Bash-compatible systems:

```bash
bash tests/run-all.sh
```

Run `python tools/scan-secrets.py` before staging files.

The suite covers service registration, settings preservation, migrations, Widget Registry behavior, Workspace controls, REST permissions, Dashboard compatibility, frontend no-write behavior, uninstall cleanup, version consistency, workflow preservation, and v0.1.138-to-v0.1.139 characterization.

## Required release checks

- PHP syntax lint
- JavaScript syntax check
- Secret scan
- ZIP path safety and single-root validation
- Version consistency across plugin header, constants, readme, workflow, and architecture version
- Upgrade test from the previous stable version
- Rollback package verification
- LocalWP staging validation

## v0.1.139 staging result

The user installed v0.1.139 over the verified v0.1.138 staging site and confirmed it worked correctly. The staging screenshot records the active plugin version, floating Workspace in dark mode, website progress at 44% (4 of 9 core pages), and unaffected Site Kit and WPvivid plugins.
