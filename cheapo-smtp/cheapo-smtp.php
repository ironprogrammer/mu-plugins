<?php
/**
 * Plugin Name: Cheapo SMTP
 * Plugin URI: https://github.com/ironprogrammer/mu-plugins/tree/main/cheapo-smtp
 * Description: SMTP configuration via phpmailer_init hook.
 * Author: Brian Alexander
 * Author URI: https://brianalexander.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 *
 * Configuration:
 *   Add the following constants to wp-config.php:
 *
 *   define( 'SMTP_USER',  'your_username' );
 *   define( 'SMTP_TOKEN', 'your_token_or_password' );
 *   define( 'SMTP_HOST',  'your_host' );
 *   define( 'SMTP_PORT',  587 ); // TLS port; or set to 465 for SSL
 *   define( 'SMTP_FROM',  'your_from_email' );
 *   define( 'SMTP_NAME',  'your_from_name' );
 */

add_action( 'phpmailer_init', 'setup_phpmailer_init' );
function setup_phpmailer_init( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->Username   = SMTP_USER;
    $phpmailer->Password   = SMTP_TOKEN;
    $phpmailer->SMTPSecure = 'tls'; // Use 'ssl' if you change port to 465
    $phpmailer->From       = SMTP_FROM;
    $phpmailer->FromName   = SMTP_NAME;
}
