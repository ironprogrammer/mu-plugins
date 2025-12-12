<?php
/**
 * Plugin Name: Cheapo Canonical
 * Plugin URI: https://github.com/ironprogrammer/mu-plugins/tree/main/cheapo-canonical
 * Description: Adds canonical tags to homepage and archives where WordPress doesn't add them.
 * Author: Brian Alexander
 * Author URI: https://brianalexander.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 *
 * WordPress automatically adds canonical tags to single posts and pages,
 * but not to the homepage or archive pages (category, tag, date, author).
 * This plugin fills that gap without duplicating WordPress's own canonical tags.
 *
 * Testing:
 *   Run the validation script to verify canonical tags are properly added:
 *   bash validate.sh http://your-site.test
 */

add_action( 'wp_head', 'cheapo_canonical_tags', 1 );

function cheapo_canonical_tags() {
    // Bail if WordPress already added a canonical tag
    if ( has_action( 'wp_head', 'rel_canonical' ) && ! is_home() && ! is_archive() ) {
        return;
    }
    
    $canonical_url = cheapo_get_canonical_url();
    
    if ( $canonical_url ) {
        echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '" />' . "\n";
    }
}

function cheapo_get_canonical_url() {
    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    
    if ( is_home() || is_front_page() ) {
        $url = home_url( '/' );
    } elseif ( is_category() ) {
        $url = get_category_link( get_queried_object_id() );
    } elseif ( is_tag() ) {
        $url = get_tag_link( get_queried_object_id() );
    } elseif ( is_tax() ) {
        $url = get_term_link( get_queried_object() );
    } elseif ( is_author() ) {
        $url = get_author_posts_url( get_queried_object_id() );
    } elseif ( is_date() ) {
        $year  = get_query_var( 'year' );
        $month = get_query_var( 'monthnum' );
        $day   = get_query_var( 'day' );
        
        if ( $day ) {
            $url = get_day_link( $year, $month, $day );
        } elseif ( $month ) {
            $url = get_month_link( $year, $month );
        } else {
            $url = get_year_link( $year );
        }
    } else {
        return false;
    }
    
    // Add pagination to the URL if needed
    if ( $paged > 1 ) {
        $url = trailingslashit( $url ) . 'page/' . $paged . '/';
    }
    
    return $url;
}
