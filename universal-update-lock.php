<?php
/**
 * Plugin Name: Universal Update Lock
 * Description: Temporarily disables WordPress core, plugin, theme, and translation updates and their standard update notices. Deactivate to restore normal WordPress update behavior.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Universal Update Lock
 * License: GPL-2.0-or-later
 * Text Domain: universal-update-lock
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * These filters work for every plugin that uses WordPress's normal update API,
 * including plugins installed from ZIP files and commercial plugins.
 * They do not interfere with installing NEW plugins or themes.
 */
add_filter( 'automatic_updater_disabled', '__return_true', PHP_INT_MAX );
add_filter( 'auto_update_core', '__return_false', PHP_INT_MAX );
add_filter( 'auto_update_plugin', '__return_false', PHP_INT_MAX );
add_filter( 'auto_update_theme', '__return_false', PHP_INT_MAX );
add_filter( 'auto_update_translation', '__return_false', PHP_INT_MAX );

/**
 * Return a fresh-looking, empty update response, with installed plugin versions
 * in `checked` so WP does not continuously re-run its version check.
 */
function uul_plugin_updates_off( $value ) {
    static $result = null;

    if ( null !== $result ) {
        return $result;
    }

    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $checked = array();
    foreach ( get_plugins() as $plugin_file => $plugin_data ) {
        $checked[ $plugin_file ] = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
    }

    $result = (object) array(
        'last_checked' => time(),
        'checked'      => $checked,
        'response'     => array(),
        'no_update'    => array(),
        'translations' => array(),
    );

    return $result;
}

function uul_theme_updates_off( $value ) {
    static $result = null;

    if ( null !== $result ) {
        return $result;
    }

    $checked = array();
    foreach ( wp_get_themes() as $stylesheet => $theme ) {
        $checked[ $stylesheet ] = $theme->get( 'Version' );
    }

    $result = (object) array(
        'last_checked' => time(),
        'checked'      => $checked,
        'response'     => array(),
        'no_update'    => array(),
        'translations' => array(),
    );

    return $result;
}

function uul_core_updates_off( $value ) {
    return (object) array(
        'last_checked'    => time(),
        'version_checked' => get_bloginfo( 'version' ),
        'updates'         => array(),
        'translations'    => array(),
    );
}

// `pre_` prevents ordinary cached update information from surfacing.
// `site_transient_` also removes update data supplied by other extensions.
add_filter( 'pre_site_transient_update_plugins', 'uul_plugin_updates_off', PHP_INT_MAX );
add_filter( 'site_transient_update_plugins', 'uul_plugin_updates_off', PHP_INT_MAX );
add_filter( 'pre_site_transient_update_themes', 'uul_theme_updates_off', PHP_INT_MAX );
add_filter( 'site_transient_update_themes', 'uul_theme_updates_off', PHP_INT_MAX );
add_filter( 'pre_site_transient_update_core', 'uul_core_updates_off', PHP_INT_MAX );
add_filter( 'site_transient_update_core', 'uul_core_updates_off', PHP_INT_MAX );

/**
 * Prevent ordinary manual updates as well as automatic updates. Do not block
 * installing a new plugin/theme or uploading a new ZIP as a fresh install.
 * WordPress calls this filter after preparing the package, but before the
 * upgrader changes installed files.
 */
function uul_block_update_install( $response, $hook_extra ) {
    if ( is_wp_error( $response ) || ! is_array( $hook_extra ) ) {
        return $response;
    }

    $action = isset( $hook_extra['action'] ) ? $hook_extra['action'] : '';
    if ( in_array( $action, array( 'update', 'upgrade' ), true ) ) {
        return new WP_Error(
            'uul_updates_disabled',
            __( 'Updates are disabled by Universal Update Lock. Deactivate it to update WordPress, plugins, themes, or translations.', 'universal-update-lock' )
        );
    }

    return $response;
}
add_filter( 'upgrader_pre_install', 'uul_block_update_install', PHP_INT_MAX, 2 );

// Remove the standard WordPress admin update counter and menu link.
add_filter( 'wp_get_update_data', function ( $data ) {
    if ( isset( $data['counts'] ) && is_array( $data['counts'] ) ) {
        foreach ( $data['counts'] as $name => $count ) {
            $data['counts'][ $name ] = 0;
        }
    }
    return $data;
}, PHP_INT_MAX );

add_action( 'admin_menu', function () {
    remove_submenu_page( 'index.php', 'update-core.php' );
}, PHP_INT_MAX );

add_action( 'admin_bar_menu', function ( $bar ) {
    $bar->remove_node( 'updates' );
}, PHP_INT_MAX );

// Presentation only: protects against previously rendered WP update rows.
add_action( 'admin_head', function () {
    echo '<style id="universal-update-lock-css">'
        . '.plugin-update-tr, .update-nag, .update-message, #wp-admin-bar-updates { display: none !important; }'
        . '</style>';
} );

/**
 * Do not change wp-config.php or delete scheduled cron tasks. On deactivation,
 * clear only WordPress update-result transients so the next normal check can
 * run and repopulate update information with the plugin inactive.
 */
function uul_refresh_update_results() {
    delete_site_transient( 'update_plugins' );
    delete_site_transient( 'update_themes' );
    delete_site_transient( 'update_core' );
}
register_activation_hook( __FILE__, 'uul_refresh_update_results' );
register_deactivation_hook( __FILE__, 'uul_refresh_update_results' );
