<?php
/**
 * Renders the antispambot block on the frontend.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$text = $attributes['content'] ?? '';

if ( empty( $text ) ) {
	return;
}

$text = preg_replace_callback(
	'/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
	function ( $matches ) {
		return antispambot( $matches[0] );
	},
	$text
);

printf( '<p %1$s>%2$s</p>', get_block_wrapper_attributes(), $text );
