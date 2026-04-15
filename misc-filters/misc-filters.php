<?php
/**
 * Plugin Name: Misc Filters
 * Description: A place to filter random things
 */

// Remove image sitemap from Jetpack sitemap index
add_filter( 'jetpack_sitemap_image_skip_post', '__return_true' );

// Remove users sitemap from default WP sitemap index
add_filter( 'wp_sitemaps_add_provider', function( $provider, $name ) {
    if ( 'users' === $name ) {
        return false;
    }
    return $provider;
}, 10, 2 );
