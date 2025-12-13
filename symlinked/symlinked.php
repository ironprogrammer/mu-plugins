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
 * Get symlink and git status for a MU plugin file.
 *
 * @param string $plugin_file The MU plugin file path.
 * @return array Array with 'symlinked' and 'git' boolean flags.
 */
function get_mu_plugin_status( $plugin_file ) {
    $status = array(
        'symlinked' => false,
        'git' => false,
    );

    // Check if the file itself is a symlink.
    $is_symlink = is_link( $plugin_file );
    if ( $is_symlink ) {
        $status['symlinked'] = true;
    }

    // For git status, check the actual file location (follow symlinks).
    $real_path = $is_symlink ? realpath( $plugin_file ) : $plugin_file;
    if ( $real_path ) {
        $check_dir = dirname( $real_path );
        if ( is_plugin_under_git( $check_dir ) ) {
            $status['git'] = true;
        }
    }

    return $status;
}

/**
 * Append symlink/git status to MU plugin names on the MU plugins page.
 */
function mu_plugins_append_symlink_git_status() {
    // Only proceed if we're on the mustuse plugins page.
    $screen = get_current_screen();
    if ( ! $screen || 'plugins' !== $screen->id || ! isset( $_REQUEST['plugin_status'] ) || 'mustuse' !== $_REQUEST['plugin_status'] ) {
        return;
    }

    // Get all MU plugins.
    $mu_plugins = get_mu_plugins();

    if ( empty( $mu_plugins ) ) {
        return;
    }

    // Build status map for each MU plugin.
    $status_map = array();
    foreach ( $mu_plugins as $plugin_file => $plugin_data ) {
        $full_path = WPMU_PLUGIN_DIR . '/' . $plugin_file;
        $status = get_mu_plugin_status( $full_path );

        if ( $status['symlinked'] || $status['git'] ) {
            $indicators = array();
            if ( $status['symlinked'] ) {
                $indicators[] = 'Symlinked';
            }
            if ( $status['git'] ) {
                $indicators[] = 'Git';
            }
            $status_map[ $plugin_file ] = implode( ', ', $indicators );
        }
    }

    if ( empty( $status_map ) ) {
        return;
    }

    // Output JavaScript to append status indicators to plugin names.
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusMap = <?php echo wp_json_encode( $status_map ); ?>;

        for (var pluginFile in statusMap) {
            var pluginRow = document.querySelector('tr[data-plugin="' + pluginFile + '"]');
            if (pluginRow) {
                var pluginTitle = pluginRow.querySelector('.plugin-title');
                if (pluginTitle) {
                    // Find or create row-actions div
                    var rowActions = pluginTitle.querySelector('.row-actions');
                    if (!rowActions) {
                        rowActions = document.createElement('div');
                        rowActions.className = 'row-actions visible';

                        // Insert after the <strong> tag
                        var strong = pluginTitle.querySelector('strong');
                        if (strong && strong.nextSibling) {
                            pluginTitle.insertBefore(rowActions, strong.nextSibling);
                        } else if (strong) {
                            strong.parentNode.appendChild(rowActions);
                        }
                    }

                    // Parse the status string and add spans
                    var statuses = statusMap[pluginFile].split(', ');
                    for (var i = 0; i < statuses.length; i++) {
                        var statusSpan = document.createElement('span');
                        statusSpan.className = statuses[i].toLowerCase();
                        statusSpan.textContent = statuses[i];

                        // Add separator if not the last item
                        if (i < statuses.length - 1) {
                            statusSpan.textContent += ' | ';
                        }

                        rowActions.appendChild(statusSpan);
                    }
                }
            }
        }
    });
    </script>
    <?php
}
add_action( 'admin_footer-plugins.php', 'mu_plugins_append_symlink_git_status' );

/**
 * Checks if a directory is under Git version control.
 *
 * @param string $directory The directory to check.
 * @return bool True if the directory is under Git version control, false otherwise.
 */
function is_plugin_under_git( $directory ) {
    return is_dir( $directory . '/.git' );
}
