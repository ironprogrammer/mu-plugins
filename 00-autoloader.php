<?php
/**
 * Plugin Name: Must-Use Plugins Autoloader
 * Plugin URI: https://github.com/ironprogrammer/mu-plugins
 * Description: Autoloads mu-plugins organized in subdirectories.
 * Author: Brian Alexander
 * Author URI: https://brianalexander.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 *
 * Automatically loads plugins from subdirectories within `wp-content/mu-plugins/`.
 * This allows mu-plugins to be organized in folders (like regular plugins)
 * while still being auto-loaded by WordPress.
 *
 * Convention: Looks for a PHP file matching the directory name.
 * Example: `mu-plugins/my-plugin/my-plugin.php`.
 *
 * Usage:
 * - Copy or symlink this file to `wp-content/mu-plugins/`
 * - Symlink plugin directories to `wp-content/mu-plugins/`
 * - Plugins will be auto-discovered and loaded
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mu_plugins_dir = __DIR__;
$subdirs = glob( $mu_plugins_dir . '/*', GLOB_ONLYDIR );

foreach ( $subdirs as $subdir ) {
    $plugin_name = basename( $subdir );
    $plugin_file = $subdir . '/' . $plugin_name . '.php';

    if ( file_exists( $plugin_file ) ) {
        require_once $plugin_file;
    }
}
