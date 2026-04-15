<?php
/**
 * Plugin Name: Antispambot Block
 * Plugin URI: https://github.com/ironprogrammer/mu-plugins/tree/main/antispambot-block
 * Description: A paragraph block that obfuscates email addresses via antispambot() for use in templates.
 * Author: Brian Alexander
 * Author URI: https://brianalexander.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 *
 * Registers a lightweight block restricted to template/template-part editing.
 * WordPress already runs antispambot on post content, but emails placed directly
 * in FSE templates (e.g. footer) are outside that processing scope.
 */

add_filter( 'allowed_block_types_all', function ( $allowed, $context ) {
	if ( ! empty( $context->post ) && ! in_array( $context->post->post_type, array( 'wp_template', 'wp_template_part' ), true ) ) {
		if ( true === $allowed ) {
			$allowed = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );
		}
		return array_values( array_diff( $allowed, array( 'ironprogrammer/antispambot' ) ) );
	}
	return $allowed;
}, 10, 2 );

add_action( 'init', function () {
	wp_register_script(
		'antispambot-block-editor',
		false,
		array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
		'1.0.0'
	);
	wp_add_inline_script(
		'antispambot-block-editor',
		file_get_contents( __DIR__ . '/editor.js' )
	);

	register_block_type( __DIR__ );
} );
