# Data Storage — v0.1.139

FB Software AI currently uses WordPress-native storage.

## Site options

- `fbsa_demo_settings` — legacy plugin settings, videos, branding, and commands
- `fbsa_schema_versions` — migration schema versions and migration metadata
- `fbsa_migration_lock` — temporary migration request lock
- `fbsa_workspace_default_layouts` — site-level default Workspace layout contract
- Legacy cleanup and notice options retained for compatibility

## User options/meta

- Per-user Workspace layouts are stored separately for each WordPress user/site context.
- Native WordPress Dashboard ordering and hidden-metabox preferences remain authoritative and are preserved.

## Browser storage

The floating widget and guide-video interface retain their established browser-storage keys for theme, position, collapsed state, and video-player session geometry.

## Uninstall boundary

Uninstall removes plugin-owned options and Workspace user preferences. It does not delete pages, posts, menus, Reading settings, installed themes, or third-party plugins.
