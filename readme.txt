=== FB Software AI ===
Contributors: fbsoftwaresolutions
Tags: wordpress setup, admin guide, floating widget, workflow
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.139
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

FB Software AI is an administrator-only floating WordPress setup guide with workflow commands, published content creation, admin shortcuts, video guidance, and theme/plugin installer presets.

== Description ==

Main features:

* Floating FB Software AI widget for logged-in administrators only.
* Permanently attached premium backend shortcut rail.
* Workspace categories for themes, required plugins, pages, and the WordPress backend.
* Published page creation with Home and Blog Reading-setting assignment.
* Published Single Blog Post creation with a separate Create Blog Post button.
* Redirect to the Pages or Posts list after fixed content creation.
* Completed page commands hide while the corresponding page exists.
* Fixed WordPress.org theme and plugin installers with install, activate, deactivate, and uninstall states.
* Separate Turkish and English command/Dashboard guide video links with administrator-language selection, fallback, secure saving, and a persistent draggable/resizable guide player.
* Customizable Dashboard welcome panel with a shared banner, language-specific Turkish/English YouTube video and channel buttons, memberships links, shared social profiles, Screen Options visibility, and standard Dismiss behavior.
* Four draggable FB Software AI Dashboard checklists for website steps, plugin setup, website settings, and help/tutorial resources, with non-breaking coming-soon link feedback.
* Custom navigation, theme-installation, and plugin-installation commands.
* Top settings navigation tabs for Overview, Guide Videos, Themes, Plugins, Commands, and per-user Workspace controls.
* Workspace controls can enable, disable, reorder, save, and reset the four FB Software AI Dashboard widgets without changing third-party widgets.
* Plugin-dependent destinations redirect to Plugins when the required plugin is inactive or unavailable.

== Installation ==

1. In WordPress Admin, open Plugins > Add New > Upload Plugin.
2. Upload the plugin ZIP and activate it.
3. Use the floating widget in WordPress Admin, the frontend while logged in as an administrator, or the Customizer.
4. Open Tools > FB Software AI for configuration.

== Development Structure ==

The approved widget design is separated into maintainable stylesheets:

* assets/css/widget.css
* assets/css/video-player.css
* assets/css/settings.css
* assets/css/admin-menu.css
* assets/css/dashboard-welcome.css
* assets/css/workspace-settings.css
* assets/js/workspace-settings.js

Do not add historical/version-number CSS override blocks. Update the owning stylesheet and existing selectors directly.

Localization development rule: keep English as the source language and register every new user-visible string with the `fb-software-ai` text domain or the generated translation catalogue. Do not translate technical IDs, slugs, URLs, or saved values.

== Uninstall ==

Uninstall removes FB Software AI plugin options. It does not delete pages, posts, menus, WordPress Reading settings, themes, or third-party plugins.

== Changelog ==

= 0.1.139 =
* Added a Workspace settings tab for per-user Dashboard widget controls.
* Added safe enable/disable, ordering, save, and reset controls for the four existing FB Software AI widgets.
* Added authenticated REST endpoints with nonce and capability protection.
* Added native Dashboard order synchronization that preserves WordPress core and third-party widgets.
* Added migration 0003 for the Workspace controls contract.

= 0.1.138 =
* Added the Workspace module, formal Widget Registry, immutable widget definitions, and capability-aware widget availability.
* Registered all four existing Dashboard widgets through the new registry while preserving their IDs, titles, renderers, columns, priorities, Screen Options, and drag behavior.
* Added per-user, per-site Workspace layout storage with validated save, load, reset, and default-layout fallback contracts.
* Added extension hooks for widget registration, widget presentation, render lifecycle, and Workspace layout load/save/reset events.
* Added the idempotent 0002 Workspace foundation migration and default Dashboard layout contract without changing existing WordPress Dashboard preferences.
* Preserved the floating widget, Customizer rail, settings, bilingual videos, workflows, AJAX actions, and existing administrator permission behavior.

= 0.1.137 =
* Added the internal namespace and architecture autoloader without changing the existing interface or workflow behavior.
* Added an explicit service container, module registry, core plugin service, and compatibility bridge for staged modularization.
* Added a legacy settings repository that preserves unknown settings fields through merge-based updates.
* Added the idempotent 0001 core schema baseline migration, schema-version repository, request locking, rollback snapshot, and activation/admin upgrade path.
* Preserved all existing workflow command IDs, AJAX action names, Dashboard widget IDs, settings, video structures, and administrator-facing behavior.

= 0.1.136 =
* Added separate Turkish and English YouTube fields for every workflow command and Dashboard guide.
* Added automatic video selection based on the current administrator language, with safe cross-language fallback when one recording is missing.
* Added bilingual Dashboard welcome-video, watch, subscribe, memberships, and YouTube-channel settings while keeping the banner and non-YouTube social profiles shared.
* Migrated existing single video links and YouTube settings into the Turkish profile without deleting legacy values.
* Added language-aware Dashboard checklist links that open the configured guide video or retain the existing coming-soon message when no video exists.
* Preserved English source development and Loco Translate localization support.

= 0.1.135 =
* Added full WordPress text-domain loading with `Domain Path: /languages`.
* Added a generated English translation catalogue and `languages/fb-software-ai.pot` for Loco Translate.
* Added PHP output translation for the welcome panel, floating widget, settings interface, notices, and accessibility labels.
* Added translated workflow data and translated AJAX responses while preserving technical IDs, slugs, URLs, and saved settings.
* Added WordPress JavaScript i18n registration, a localized JavaScript catalogue, and live translation for dynamically created plugin UI.
* Kept English as the only source language; Turkish can be maintained safely through Loco Translate custom/system files.
* Synchronized plugin, workflow, dashboard-layout, and readme versions.

= 0.1.134 =
* Added three new FB Software AI Dashboard widgets: Plugin Setup, Website Settings, and Help and Tutorials.
* Preserved the original FB Software AI website-steps widget.
* Added independent WordPress Screen Options, drag, collapse, and saved-position support for all four widgets.
* Added unique widget IDs, per-widget placeholder messages, and shared compact checklist styling.
* Added one-time placement repair that puts website widgets in column three and plugin/help widgets in column four while preserving unrelated widget order.
* Synchronized plugin, workflow, and readme versions.

= 0.1.133 =
* Repaired FB Software AI Dashboard widget registration for existing WordPress installations.
* Moved registration to a late Dashboard setup priority and added two pre-render fallbacks when another plugin rebuilds the Dashboard boxes.
* Added a one-time administrator preference repair that unhides the widget and places it in Dashboard column three while preserving unrelated widget positions.
* Added duplicate-registration protection and an internal widget version marker for live verification.
* Synchronized plugin, workflow, and readme versions.

= 0.1.132 =
* Added a draggable and collapsible FB Software AI Dashboard widget for the website setup checklist.
* Added ten dummy website guide links using the compact status-box style shown by the AI Status widget.
* Added safe in-dashboard coming-soon feedback so placeholder links do not navigate to broken pages.
* Registered the widget in the fourth Dashboard column when available while preserving Screen Options and drag controls.
* Synchronized plugin, workflow, and readme versions.

= 0.1.131 =
* Added Site Kit by Google to Fixed Commands > Install Plugins.
* Added automatic Site Kit listing in the floating widget required-plugin workflow.
* Added official WordPress.org installation, activation, status detection, deactivation, and uninstall handling for the `google-site-kit` plugin.
* Synchronized plugin, workflow, and readme versions.

= 0.1.130 =
* Added a responsive FB Software AI welcome experience to the WordPress Dashboard.
* Replaced the standard Welcome panel content while retaining WordPress Screen Options and Dismiss behavior.
* Added Media Library banner upload, embedded welcome video, Watch on YouTube, Subscribe, and Memberships links.
* Added YouTube, website, Facebook, Instagram, and X social profile fields.
* Placed all welcome panel controls inside Command Video Links so the complete section can be locked for a demo release.
* Preserved the duplicate-plugin cleanup and stable `fb-software-ai/` installation folder.

= 0.1.129 =
* Unlocked the Command Video Links fields for administrators.
* Restored the Save Video Links button and secure server-side saving.
* Re-enabled saved database video overrides while retaining bundled workflow links as defaults.
* Preserved the v0.1.128 legacy duplicate-plugin consolidation and cleanup.
* Synchronized plugin, workflow, and readme versions.

= 0.1.128 =
* Consolidates inactive legacy copies into the current stable plugin installation.
* Removes malformed backslash-based files created by the early v0.1.125 ZIP package on Linux hosting.
* Keeps the active current plugin, bundled video links, and shared FB Software AI settings.
* Adds a clear administrator result notice and safely skips active or newer copies.
* Synchronized plugin, workflow, and readme versions.

= 0.1.127 =
* Added the first one-time cleanup for inactive legacy FB Software AI copies installed under old folder names.
* Preserved the active current plugin and skipped any newer or active duplicate copy.
* Kept the stable internal plugin folder as `fb-software-ai/`.

= 0.1.126 =
* Locked all command video URL fields as read-only while keeping them visible and copyable.
* Removed the Save Video Links action from the public demo interface.
* Disabled server-side video-link replacement saving in the locked demo release.
* Disabled previously stored database overrides so bundled workflow links remain the release source of truth.
* Synchronized plugin, workflow, and readme versions.

= 0.1.125 =
* Finalized a live-uploadable release package with synchronized plugin, workflow, and readme versions.
* Removed customer-facing demo naming from the shipped plugin metadata.
* Replaced shipped placeholder workflow testing notes with release-ready guide text.
* Added visible workflow-load warnings so missing or invalid workflow bundles do not fail silently.
* Added Contact Form 7 to the fixed plugin installer list.

= 0.1.118 =
* Removed the obsolete gear button and rail open/closed engine.
* Preserved the approved attached shortcut rail and widget appearance.
* Reorganized CSS into four maintainable stylesheets and removed historical rail override blocks.
* Added accessible top settings navigation tabs.
* Restored the Single Blog Post command as a publish-and-return workflow.
* Removed the placeholder Connectors command.
* Added generic plugin-dependent destination handling; inactive WooCommerce routes to Plugins.
* Removed automatic menu synchronization on plugin update and theme activation.
* Added uninstall cleanup for plugin-owned options.
* Synchronized plugin and workflow versions.

= 0.1.117 =
* Finalized the seamless attached shortcut rail.
* Matched the rail skin to #12182D.
* Removed visible top-right and bottom-right corner gaps by joining the rail and main panel as one component.
* Kept the outside rail corners rounded and the panel right corners square.

= 0.1.107 =
* Added the fixed WPvivid installer and its install/activation state handling.

= 0.1.75 =
* Added the expandable, draggable, resizable, and session-restored guide video player.

= 0.1.34 =
* Added the initial administrator-only floating widget, workflow JSON system, published page creation, and content redirects.
