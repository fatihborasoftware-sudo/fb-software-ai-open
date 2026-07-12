# Contributing to FB Software AI

FB Software AI is developed through approved, milestone-based changes.

## Before changing code

1. Review the current project status, architecture, roadmap, and release notes.
2. Prepare a written implementation plan.
3. Wait for explicit approval.
4. Create a rollback-safe backup.
5. Work on one milestone only.

## Branches

- `main` — stable, staging-validated releases
- `develop` — next approved release
- `feature/<name>` — feature work
- `hotfix/<name>` — critical fixes
- `docs` — documentation-only updates

Do not push directly to `main`. Open a pull request and include test evidence.

## Coding requirements

- Preserve existing public IDs and behavior unless an approved migration says otherwise.
- Use WordPress capabilities, nonces, sanitization, validation, and escaping.
- Keep English as the source language and use the `fb-software-ai` text domain.
- Do not add unrestricted code execution.
- Keep new systems modular and compatible with the Action Registry and Widget Registry direction.
- Do not commit generated backups, logs, local databases, `.env` files, credentials, or WordPress uploads.

## Testing

Run:

```powershell
powershell -ExecutionPolicy Bypass -File .	estsun-all.ps1
```

Also validate upgrades and regressions in LocalWP before a release is tagged.

## Pull-request evidence

Include:

- Purpose and approved scope
- Files changed
- Compatibility impact
- Test results
- Upgrade and rollback result
- Screenshots for UI changes
- Updated changelog and documentation
