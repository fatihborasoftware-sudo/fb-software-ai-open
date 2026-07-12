# Architecture Summary — v0.1.139

## Compatibility-first modularization

FB Software AI v0.1.139 extends the existing plugin rather than rewriting it. The legacy `FBSA_Demo_Plugin` class remains as a compatibility facade while new services are introduced under the `FBSoftwareAI\` namespace.

## Core

- `Autoloader` — loads namespaced internal classes
- `Container` — resolves shared services
- `ModuleRegistry` and `ModuleInterface` — register modular features
- `Plugin` — coordinates activation, migrations, and modules
- `Version` — central version and schema constants

## Migrations

- `0001-core-baseline` — establishes core schema metadata
- `0002-workspace-foundation` — establishes Workspace defaults
- `0003-workspace-controls` — advances Workspace controls metadata

Migrations are idempotent, lock-aware, and designed to preserve legacy settings and unknown metadata.

## Workspace

- `WidgetRegistry` — stores capability-aware widget definitions
- `LegacyDashboardWidgetAdapter` — registers the four preserved Dashboard widgets
- `UserWorkspaceLayoutRepository` — stores per-user/per-site layouts
- `WorkspaceLayoutValidator` — normalizes IDs, columns, order, visibility, and settings
- `WorkspaceControls` — saves, resets, and validates current-user preferences
- `WorkspaceRestController` — exposes authenticated current-user REST operations
- `DashboardPreferenceSynchronizer` — preserves WordPress core and third-party widget order

## Compatibility boundary

The following identifiers are preserved in v0.1.139:

- Existing settings option names
- Three legacy AJAX actions
- Four Dashboard widget IDs
- 63 workflow command records
- Browser storage keys
- Text domain `fb-software-ai`

New development must extend this architecture without bypassing capabilities, registries, migrations, or compatibility protections.
