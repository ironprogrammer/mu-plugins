<?php
/**
 * Plugin Name: Cheapo SMTP
 * Description: SMTP config via phpmailer_init.
 * 
 * Add the following to wp-config.php:
 * 
 * define( 'SMTP_USER',  'your_username' );
 * define( 'SMTP_TOKEN', 'your_token_or_password' );
 * define( 'SMTP_HOST',  'your_host' );
 * define( 'SMTP_PORT',  587 ); // TLS port; or set to 465 for SSL
 * define( 'SMTP_FROM',  'your_from_email' );
 * define( 'SMTP_NAME',  'your_from_name' );
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
