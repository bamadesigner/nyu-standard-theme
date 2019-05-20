<?php
/**
 * Render your site front page, whether the front page displays the blog posts index or a static page.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

wp_rig()->manage_layout();

get_header();

$use_magazine = wp_rig()->use_magazine_layout();

$main_class = 'site-main';

if ( $use_magazine ) {
	wp_rig()->print_styles( 'wp-rig-content', 'wp-rig-magazine' );
} else {
	wp_rig()->print_styles( 'wp-rig-content' );
}

if ( $use_magazine ) {
	wp_rig()->display_magazine();
	$main_class .= ' site-main--magazine';
}

?>
	<main id="primary" class="<?php echo esc_attr( $main_class ); ?>">
		<?php

		if ( $use_magazine && is_home() ) {
			?>
			<h2 class="home-title--latest"><?php esc_html_e( 'Latest posts', 'wp-rig' ); ?></h2>
			<?php

			wp_rig()->set_entry_title_header( 3 );
		}

		while ( have_posts() ) {
			the_post();

			get_template_part( 'template-parts/content/entry', get_post_type() );
		}

		wp_rig()->reset_entry_title_header();

		get_template_part( 'template-parts/content/pagination' );
		?>
	</main><!-- #primary -->
<?php
get_sidebar();
get_footer();
