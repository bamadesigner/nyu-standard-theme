<?php
/**
 * Template part for displaying a post's taxonomy terms
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

// What taxonomies do we want to display?
$taxonomies = [ 'category', 'post_tag' ];

// Store display info for each taxonomy.
$taxonomies_display = array();

foreach ( $taxonomies as $taxonomy_name ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	// Use customizer settings to control what's displayed on the home page.
	if ( is_home() ) {

		if ( 'category' == $taxonomy_name && ! wp_rig()->front_page_archive_display_post_categories() ) {
			continue;
		} elseif ( 'post_tag' == $taxonomy_name && ! wp_rig()->front_page_archive_display_post_tags() ) {
			continue;
		}
	}

	$taxonomy = get_taxonomy( $taxonomy_name );

	if ( empty( $taxonomy->name ) || $taxonomy->name != $taxonomy_name ) {
		continue;
	}

	/* translators: separator between taxonomy terms */
	$separator = _x( ', ', 'list item separator', 'wp-rig' );

	switch ( $taxonomy->name ) {
		case 'category':
			$class = 'category-links term-links';
			$list  = get_the_category_list( esc_html( $separator ), '', $post->ID );
			break;
		case 'post_tag':
			$class = 'tag-links term-links';
			$list  = get_the_tag_list( '', esc_html( $separator ), '', $post->ID );
			break;
		default:
			$class = str_replace( '_', '-', $taxonomy->name ) . '-links term-links';
			$list  = get_the_term_list( $post->ID, $taxonomy->name, '', esc_html( $separator ), '' );
			break;
	}

	if ( empty( $list ) ) {
		continue;
	}

	if ( $taxonomy->hierarchical ) {
		/* translators: %s: list of taxonomy terms */
		$placeholder_text = __( 'Categories: %s', 'wp-rig' );
	} else {
		/* translators: %s: list of taxonomy terms */
		$placeholder_text = __( 'Tags: %s', 'wp-rig' );
	}

	$taxonomies_display[ $taxonomy->name ] = [
		'class'       => $class,
		'list'        => $list,
		'placeholder' => $placeholder_text,
	];
}

if ( empty( $taxonomies_display ) ) {
	return;
}

?>
<div class="entry-taxonomies">
	<?php

	// Show terms for all taxonomies associated with the post.
	foreach ( $taxonomies_display as $taxonomy ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		?>
		<span class="<?php echo esc_attr( $taxonomy['class'] ); ?>">
			<?php

			printf(
				esc_html( $taxonomy['placeholder' ]),
				$taxonomy['list'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);

			?>
		</span>
		<?php
	}

	?>
</div><!-- .entry-taxonomies -->
