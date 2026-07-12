# FB Software AI

FB Software AI is a WordPress setup and learning assistant for administrators. The current plugin provides a floating workflow guide, bilingual YouTube guidance, published page and blog-post creation, WordPress admin shortcuts, installer presets, Dashboard widgets, and per-user Workspace controls.

## Current stable save point

- **Version:** 0.1.139
- **Release name:** Workspace Controls Foundation
- **Status:** LocalWP staging validated on 12 July 2026
- **License:** GPL v2 or later
- **WordPress:** Requires 6.0 or later
- **PHP:** Requires 7.4 or later

Version 0.1.139 is the approved stable baseline for the next development slice. It preserves the existing floating widget, Customizer rail, bilingual videos, 63 workflow commands, content-creation actions, and plugin/theme commands while adding per-user controls for the four FB Software AI Dashboard widgets.

## Features in v0.1.139

- Administrator-only floating WordPress workflow guide
- Attached backend shortcut rail
- Turkish and English guide-video profiles with language fallback
- Home, Blog, page, and blog-post creation workflows
- Theme and plugin install/activation workflows
- Custom commands and workflow JSON
- Customizable WordPress Dashboard welcome panel
- Four FB Software AI Dashboard widgets
- Workspace Widget Registry and per-user layout storage
- Enable, disable, reorder, save, and reset Workspace widgets
- REST endpoints protected by WordPress REST nonce and capability checks
- Migration framework with schema versions 0001–0003

## Install the tested plugin

Use the exact staging-validated release asset from the save-point package or the GitHub Release rather than zipping this repository manually:

`fb-software-ai-v0.1.139-workspace-controls.zip`

In WordPress, open **Plugins → Add New Plugin → Upload Plugin**, upload the ZIP, and activate it. When upgrading, install it directly over the previous version without deleting the existing plugin first.

## Development

English is the source language. Every user-visible string must use the `fb-software-ai` text domain or the generated translation catalogue. Technical IDs, slugs, URLs, and stored values must not be translated.

Run the portable source tests:

```powershell
powershell -ExecutionPolicy Bypass -File .	ests
un-all.ps1
```

or:

```bash
bash tests/run-all.sh
```

## Repository workflow

- `main` — staging-validated stable releases
- `develop` — next approved release
- `feature/*` — isolated features
- `hotfix/*` — critical fixes
- `docs` — documentation-only work

Before every implementation: review the project blueprint, prepare a plan, wait for explicit approval, create a backup, implement one milestone, test, package, and update documentation.

## Security

Never commit API keys, passwords, `.env` files, local databases, WordPress uploads, debug logs, backups, or private credentials. See [SECURITY.md](SECURITY.md).

## Documentation

- [Roadmap](ROADMAP.md)
- [Changelog](CHANGELOG.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Data storage](docs/DATA-STORAGE.md)
- [Testing](docs/TESTING.md)
- [Project principles](docs/PROJECT-PRINCIPLES.md)
- [Current project status](docs/project-status/CURRENT-STATUS.md)
- [v0.1.139 release notes](docs/releases/v0.1.139.md)

## Project website

FB Software Solutions: https://fbsoftwaresolutions.com
