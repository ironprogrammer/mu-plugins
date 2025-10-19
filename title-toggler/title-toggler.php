<?php
/**
 * Plugin Name: Title Toggler
 * Description: Provides a toggle switch to display post titles derived from post slugs
 * Version: 1.0.0
 * Author: Brian Alexander
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version for cache busting
define( 'TT_VERSION', '1.0.0' );

/**
 * Enqueue frontend scripts and styles
 */
function tt_enqueue_assets() {
    // Use __DIR__ to make the path dynamic and portable
    $plugin_url = content_url( 'mu-plugins/title-toggler' );

    // Enqueue CSS
    wp_enqueue_style(
        'title-toggler',
        $plugin_url . '/assets/css/toggle.css',
        array(),
        TT_VERSION
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'title-toggler',
        $plugin_url . '/assets/js/toggle.js',
        array(),
        TT_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'tt_enqueue_assets' );

/**
 * Shortcode to display the toggle switch
 * Usage: [title_toggler]
 */
function tt_toggle_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'label_original' => '',
            'label_slug'     => '',
            'title'          => 'swap titles',
        ),
        $atts,
        'title_toggler'
    );

    $output = '<div class="tt-toggle-container">';
    $output .= '<label class="tt-toggle-label" title="' . esc_attr( $atts['title'] ) . '">';

    if ( ! empty( $atts['label_original'] ) ) {
        $output .= '<span class="tt-label-text tt-label-original">' . esc_html( $atts['label_original'] ) . '</span>';
    }

    $output .= '<input type="checkbox" id="tt-toggle" class="tt-toggle-input" aria-label="' . esc_attr( $atts['title'] ) . '">';
    $output .= '<span class="tt-toggle-slider"></span>';

    if ( ! empty( $atts['label_slug'] ) ) {
        $output .= '<span class="tt-label-text tt-label-slug">' . esc_html( $atts['label_slug'] ) . '</span>';
    }

    $output .= '</label>';
    $output .= '</div>';

    return $output;
}
add_shortcode( 'title_toggler', 'tt_toggle_shortcode' );

/**
 * Register a custom block pattern for the toggle
 * This makes it easier to find in the block inserter
 */
function tt_register_block_pattern() {
    register_block_pattern(
        'title-toggler/toggle-pattern',
        array(
            'title'       => 'Title Toggler',
            'description' => 'A toggle switch to display post titles derived from post slugs',
            'content'     => '<!-- wp:shortcode -->[title_toggler]<!-- /wp:shortcode -->',
            'categories'  => array( 'widgets' ),
        )
    );
}
add_action( 'init', 'tt_register_block_pattern' );
