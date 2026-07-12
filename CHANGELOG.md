# Changelog

All notable stable save points for FB Software AI are recorded here.

## [0.1.139] — 2026-07-12

### Added

- Workspace settings tab for per-user Dashboard widget controls.
- Enable, disable, reorder, save, and reset controls for the four existing FB Software AI widgets.
- Accessible move-up and move-down controls and same-column drag-and-drop ordering.
- Authenticated Workspace REST endpoints with nonce and capability protection.
- Safe synchronization with native WordPress Dashboard ordering while preserving core and third-party widgets.
- Migration `0003-workspace-controls`.

### Preserved

- Four existing Dashboard widget IDs and renderers.
- 63 workflow command records.
- Three legacy AJAX actions.
- Floating widget, Customizer rail, bilingual videos, page/post creation, and theme/plugin commands.

### Validation

- 182 automated assertions/checks passed in the implementation evidence.
- LocalWP staging validation passed on 12 July 2026.

## [0.1.138] — 2026-07-12

- Added the Workspace module, Widget Registry, widget definitions, per-user/per-site layout storage, extension hooks, and migration `0002-workspace-foundation`.
- Passed LocalWP staging validation.

## [0.1.137] — 2026-07-12

- Added namespace/autoloader, service container, module registry, compatibility bridge, settings repository, and migration `0001-core-baseline`.
- Passed LocalWP staging validation.

## [0.1.136]

- Added bilingual Turkish/English YouTube video structures and language-aware fallback.

For the complete historical changelog, see `readme.txt`.
