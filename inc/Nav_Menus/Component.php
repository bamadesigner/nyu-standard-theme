<?php
/**
 * WP_Rig\WP_Rig\Nav_Menus\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Nav_Menus;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use WP_Post;
use function add_action;
use function add_filter;
use function register_nav_menus;
use function esc_html__;
use function has_nav_menu;
use function wp_nav_menu;

/**
 * Class for managing navigation menus.
 *
 * Exposes template tags:
 * * `wp_rig()->is_primary_nav_menu_active()`
 * * `wp_rig()->display_primary_nav_menu( array $args = [] )`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const PRIMARY_NAV_MENU_SLUG = 'primary';
	const SECONDARY_NAV_MENU_SLUG = 'secondary';

	const PRIMARY_NAV_MENU_DEPTH = 3;
	const SECONDARY_NAV_MENU_DEPTH = 3;

	/**
	 * Stores whether or not the secondary nav menu is active.
	 *
	 * @var bool
	 */
	private $secondary_nav_menu_active;

	/**
	 * Holds the markup for the secondary nav menu.
	 *
	 * @var string
	 */
	private $secondary_nav_menu;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'nav_menus';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {

		add_action( 'after_setup_theme', [ $this, 'action_register_nav_menus' ] );
		add_filter( 'walker_nav_menu_start_el', [ $this, 'filter_primary_nav_menu_dropdown_symbol' ], 10, 4 );

	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_rig()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags() : array {
		return [
			'is_primary_nav_menu_active'   => [ $this, 'is_primary_nav_menu_active' ],
			'display_primary_nav_menu'     => [ $this, 'display_primary_nav_menu' ],
			'is_secondary_nav_menu_active' => [ $this, 'is_secondary_nav_menu_active' ],
			'get_secondary_nav_menu'       => [ $this, 'get_secondary_nav_menu' ],
			'display_secondary_nav'        => [ $this, 'display_secondary_nav' ],
		];
	}

	/**
	 * Registers the navigation menus.
	 */
	public function action_register_nav_menus() {
		register_nav_menus(
			[
				static::PRIMARY_NAV_MENU_SLUG   => esc_html__( 'Primary', 'wp-rig' ),
				static::SECONDARY_NAV_MENU_SLUG => __( 'Secondary', 'wp-rig' ),
			]
		);
	}

	/**
	 * Adds a dropdown symbol to nav menu items with children.
	 *
	 * Adds the dropdown markup after the menu link element,
	 * before the submenu.
	 *
	 * Javascript converts the symbol to a toggle button.
	 *
	 * @TODO:
	 * - This doesn't work for the page menu because it
	 *   doesn't have a similar filter. So the dropdown symbol
	 *   is only being added for page menus if JS is enabled.
	 *   Create a ticket to add to core?
	 *
	 * @param string  $item_output The menu item's starting HTML output.
	 * @param WP_Post $item Menu item data object.
	 * @param int     $depth Depth of menu item. Used for padding.
	 * @param object  $args An object of wp_nav_menu() arguments.
	 *
	 * @return string Modified nav menu HTML.
	 */
	public function filter_primary_nav_menu_dropdown_symbol( string $item_output, WP_Post $item, int $depth, $args ) : string {

		// Only for our primary menu location.
		if ( empty( $args->theme_location ) || static::PRIMARY_NAV_MENU_SLUG !== $args->theme_location ) {
			return $item_output;
		}

		// Add the dropdown for items that have children.
		if ( ! empty( $item->classes ) && in_array( 'menu-item-has-children', $item->classes ) ) {
			return $item_output . '<span class="dropdown"><i class="dropdown-symbol"></i></span>';
		}

		return $item_output;
	}

	/**
	 * Checks whether the primary navigation menu is active.
	 *
	 * @return bool True if the primary navigation menu is active, false otherwise.
	 */
	public function is_primary_nav_menu_active() : bool {
		return (bool) has_nav_menu( static::PRIMARY_NAV_MENU_SLUG );
	}

	/**
	 * Checks whether the secondary navigation menu is active.
	 *
	 * @return bool True if the secondary navigation menu is active, false otherwise.
	 */
	public function is_secondary_nav_menu_active() : bool {
		if ( isset( $this->secondary_nav_menu_active ) ) {
			return $this->secondary_nav_menu_active;
		}
		$this->secondary_nav_menu_active = (bool) has_nav_menu( static::SECONDARY_NAV_MENU_SLUG );
		return $this->secondary_nav_menu_active;
	}

	/**
	 * Displays the primary navigation menu.
	 *
	 * @param array $args Optional. Array of arguments. See `wp_nav_menu()` documentation for a list of supported
	 *                    arguments.
	 */
	public function display_primary_nav_menu( array $args = [] ) {
		if ( ! isset( $args['container'] ) ) {
			$args['container'] = 'ul';
		}

		$args['depth']          = static::PRIMARY_NAV_MENU_DEPTH;
		$args['theme_location'] = static::PRIMARY_NAV_MENU_SLUG;

		wp_nav_menu( $args );
	}

	/**
	 * Returns the markup for the secondary navigation menu.
	 *
	 * @return string
	 */
	public function get_secondary_nav_menu() : string {
		if ( isset( $this->secondary_nav_menu ) ) {
			return $this->secondary_nav_menu;
		}

		$args = [
			'menu_id'        => 'secondary-menu',
			'container'      => 'ul',
			'depth'          => static::SECONDARY_NAV_MENU_DEPTH,
			'theme_location' => static::SECONDARY_NAV_MENU_SLUG,
			'echo'           => false,
		];

		$this->secondary_nav_menu = wp_nav_menu( $args );

		return $this->secondary_nav_menu;
	}

	/**
	 * Displays the secondary navigation element.
	 */
	public function display_secondary_nav() {

		if ( ! $this->is_secondary_nav_menu_active() ) {
			return;
		}

		$secondary_nav_menu = $this->get_secondary_nav_menu();

		if ( empty( $secondary_nav_menu ) ) {
			return;
		}

		?>
		<nav id="navigation-secondary" class="secondary-navigation nav--toggle-sub" aria-label="<?php esc_attr_e( 'Secondary menu', 'wp-rig' ); ?>">
			<?php echo $secondary_nav_menu; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</nav>
		<?php
	}
}
