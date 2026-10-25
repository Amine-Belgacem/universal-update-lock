=== Universal Update Lock ===
Contributors: universal-update-lock
Tags: updates, disable updates, local development
Requires at least: 5.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Temporarily suppresses standard WordPress update checks, automatic updates,
manual updates via the usual WordPress upgrader, and standard update notices.

Activate to lock WordPress core, plugin, theme and translation updates.
Deactivate to return control to WordPress; its update transients are cleared,
so fresh checks can run again.

Scope: plugins using WordPress's normal update and upgrader APIs (including
commercial plugins). A commercial plugin with a separate proprietary updater
or custom admin banner can bypass those WordPress APIs; such behavior cannot
be guaranteed to be blocked generically. Direct file replacement via FTP,
filesystem or hosting control panel is outside a WordPress plugin's control.

This plugin does not disable installation of NEW plugins or themes.

IMPORTANT: If your site also has independent update-blocking settings in
wp-config.php, another plugin, or a must-use plugin, deactivate/remove those
separately; this plugin cannot reverse them by itself.

Do not leave updates disabled on a publicly accessible production website.
