<?php
/**
 * The footer widget area
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( ! wp_rig()->is_footer_sidebar_active() ) {
	return;
}

wp_print_styles( array( 'wp-rig-sidebar-footer', 'wp-rig-widgets' ) );

?>
<aside id="footer" class="footer-sidebar widget-area">
	<?php wp_rig()->display_footer_sidebar(); ?>
</aside><!-- #footer -->
