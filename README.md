# Universal Update Lock

Temporarily pause standard WordPress core, plugin, theme, and translation updates. Deactivate the plugin to restore normal WordPress update behavior.

## Install

Requires WordPress 5.8 or later and PHP 7.4 or later.

1. Copy the repository into `wp-content/plugins/universal-update-lock`.
2. Activate **Universal Update Lock** in the Plugins screen.
3. Deactivate it when finished; the plugin clears update transients so fresh checks can run.

## Scope

The plugin suppresses standard update checks, automatic updates, manual updates through the usual upgrader, and standard update notices. It does not prevent installation of new plugins or themes.

Custom proprietary updaters, independent admin banners, and direct filesystem replacement can bypass WordPress's normal update APIs. Settings in `wp-config.php`, other plugins, or must-use plugins must be managed separately.

Do not leave updates disabled on a publicly accessible production site.

[Plugin source](universal-update-lock.php) · [WordPress plugin readme](readme.txt)

Licensed under GPL v2 or later, as declared in the plugin header.
