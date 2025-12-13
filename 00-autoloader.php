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

/**
 * Simple class to track loaded mu-plugins.
 */
class MU_Plugins_Autoloader {
    private static $loaded_plugins = array();

    public static function add_loaded_plugin( $path ) {
        self::$loaded_plugins[] = $path;
    }

    public static function get_loaded_plugins() {
        return self::$loaded_plugins;
    }
}

$mu_plugins_dir = WPMU_PLUGIN_DIR;
$subdirs = glob( $mu_plugins_dir . '/*', GLOB_ONLYDIR );

foreach ( $subdirs as $subdir ) {
    $plugin_name = basename( $subdir );
    $plugin_file = $subdir . '/' . $plugin_name . '.php';

    if ( file_exists( $plugin_file ) ) {
        require_once $plugin_file;
        MU_Plugins_Autoloader::add_loaded_plugin( $plugin_file );
    }
}

/**
 * Append loaded mu-plugins to the autoloader's description.
 *
 * Modifies the description directly via JavaScript injection.
 */
function mu_plugins_autoloader_append_description() {
    // Only proceed if we're on the mustuse plugins page.
    $screen = get_current_screen();
    if ( ! $screen || 'plugins' !== $screen->id || ! isset( $_REQUEST['plugin_status'] ) || 'mustuse' !== $_REQUEST['plugin_status'] ) {
        return;
    }

    $loaded_plugins = MU_Plugins_Autoloader::get_loaded_plugins();

    if ( empty( $loaded_plugins ) ) {
        return;
    }

    // Build list of plugin names.
    $loaded_names = array();
    foreach ( $loaded_plugins as $plugin_path ) {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_data = get_plugin_data( $plugin_path, false, false );

        // Use Name header if available, otherwise fall back to file basename.
        if ( ! empty( $plugin_data['Name'] ) ) {
            $loaded_names[] = $plugin_data['Name'];
        } else {
            $loaded_names[] = basename( $plugin_path, '.php' );
        }
    }

    if ( empty( $loaded_names ) ) {
        return;
    }

    // Output JavaScript to append the loads list to the description.
    $loads_html = '<p><strong>Loaded:</strong> ' . esc_html( implode( ', ', $loaded_names ) ) . '</p>';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var autoloaderRow = document.querySelector('tr[data-plugin="00-autoloader.php"]');
        if (autoloaderRow) {
            var descriptionDiv = autoloaderRow.querySelector('.plugin-description');
            if (descriptionDiv) {
                descriptionDiv.insertAdjacentHTML('beforeend', <?php echo wp_json_encode( $loads_html ); ?>);
            }
        }
    });
    </script>
    <?php
}
add_action( 'admin_footer-plugins.php', 'mu_plugins_autoloader_append_description' );
