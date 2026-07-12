# Security Policy

## Supported development version

The current staging-validated save point is **v0.1.139**. Security fixes should be based on the latest approved stable branch unless a separate hotfix plan is approved.

## Reporting a vulnerability

Do not publish exploit details, credentials, private website data, or proof-of-concept attacks in a public issue. Contact the project owner through the FB Software Solutions website or use GitHub private vulnerability reporting when it is available for the repository.

Include:

- Affected version
- WordPress and PHP versions
- Steps to reproduce
- Required user role/capability
- Security impact
- Suggested mitigation, when known

## Security expectations

- No secrets or API credentials in source control
- WordPress nonce and capability checks for state-changing actions
- Sanitization and validation before storage or execution
- Escaping at output
- Controlled registries instead of unrestricted execution
- Backups and rollback plans before high-impact updates
- Logs must avoid credentials and sensitive personal data
