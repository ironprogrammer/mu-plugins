<?php
/**
 * Plugin Name: Symlinked
 * Plugin URI: https://github.com/ironprogrammer/mu-plugins/tree/main/symlinked
 * Description: Adds indicators to plugin actions menu if plugin is symlinked and/or under source control.
 * Author: Brian Alexander
 * Author URI: https://brianalexander.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 *
 * Why do this?
 * Because until https://core.trac.wordpress.org/ticket/36710 is resolved,
 * it's too easy to accidentally update or delete a symlinked plugin
 * (which is likely also under source control 🤯).
 *
 * Related:
 * - https://core.trac.wordpress.org/ticket/29408
 * - https://wordpress.org/plugins/local-development/
 */

add_filter( 'plugin_action_links', 'add_symlink_git_status_actions', 10, 2 );

function add_symlink_git_status_actions( $actions, $plugin_file ) {
    $plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );

    if ( is_link( $plugin_dir ) ) {
        $symlink_status = 'Symlinked';
        $actions['symlinked'] = $symlink_status;
    }

    if ( is_plugin_under_git( $plugin_dir ) ) {
        $git_status = 'Git';
        $actions['git'] = $git_status;
    }

    return $actions;
}

/**
 * Checks if a directory is under Git version control.
 *
 * @param string $directory The directory to check.
 * @return bool True if the directory is under Git version control, false otherwise.
 */
function is_plugin_under_git( $directory ) {
    return is_dir( $directory . '/.git' );
}
