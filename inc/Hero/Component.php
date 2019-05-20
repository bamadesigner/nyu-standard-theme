<?php
/**
 * WP_Rig\WP_Rig\Hero\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Hero;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_filter;

/**
 * Class for managing hero/featured image support.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'hero';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_filter( 'body_class', array( $this, 'filter_body_classes_add_thumbnails' ) );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_rig()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags() : array {
		return array(
			'can_display_hero' => array( $this, 'can_display_hero' ),
			'display_hero'     => array( $this, 'display_hero' ),
		);
	}

	/**
	 * Determines whether conditions are right to display the hero image.
	 *
	 * @return bool Whether we're viewing a singular view and the post has a thumbnail image.
	 */
	public function can_display_hero() : bool {
		return is_singular() && has_post_thumbnail();
	}

	/**
	 * Prints the markup for the site hero.
	 */
	public function display_hero() {
		?>
		<aside class="site-hero" aria-label="<?php esc_attr_e( 'Featured image for the content', 'wp-rig' ); ?>">
			<?php the_post_thumbnail( array( 2000, 675 ) ); ?>
		</aside>
		<?php
	}

	/**
	 * Adds a 'has-post-thumbnail' class to the array of body classes if has thumbnail.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array Filtered body classes.
	 */
	public function filter_body_classes_add_thumbnails( array $classes ) : array {
		if ( $this->can_display_hero() ) {
			$classes[] = 'has-post-thumbnail';
		}

		return $classes;
	}
}
