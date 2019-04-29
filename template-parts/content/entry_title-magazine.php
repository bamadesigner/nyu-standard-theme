<?php
/**
 * Template part for displaying a post's title
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( is_singular( get_post_type() ) ) {
	the_title( '<h1 class="entry-title">', '</h1>' );
} else {

	$header_level = wp_rig()->entry_title_header();

	the_title( '<h' . $header_level . ' class="entry-title">', '</h' . $header_level . '>' );
}
