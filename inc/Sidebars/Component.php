<?php
/**
 * WP_Rig\WP_Rig\Sidebars\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Sidebars;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_action;
use function add_filter;
use function register_sidebar;
use function esc_html__;
use function is_active_sidebar;
use function dynamic_sidebar;
use WP_Customize_Manager;

/**
 * Class for managing sidebars.
 *
 * Exposes template tags:
 * * `wp_rig()->is_primary_sidebar_active()`
 * * `wp_rig()->display_primary_sidebar()`
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const PRIMARY_SIDEBAR_SLUG = 'sidebar-1';
	const FOOTER_SIDEBAR_SLUG = 'footer';

	const SITE_LAYOUT_NAME = 'site_layout';
	const SITE_LAYOUT_DEFAULT_VALUE = 'sidebar_none';

	const FRONT_PAGE_LAYOUT_NAME = 'front_page_layout';
	const FRONT_PAGE_LAYOUT_DEFAULT_VALUE = 'site';
	const FRONT_PAGE_LAYOUT_VALUE_USE_SITE = 'site';

	const POST_LAYOUT_NAME = 'post_layout';
	const POST_LAYOUT_DEFAULT_VALUE = 'site';
	const POST_LAYOUT_VALUE_USE_SITE = 'site';

	/**
	 * Holds the selected identifier for the global site layout.
	 *
	 * @var string
	 */
	private $site_layout;

	/**
	 * Choices for the global site layout.
	 *
	 * @var array
	 */
	private $site_layout_choices;

	/**
	 * Holds the selected identifier for the front page layouts.
	 *
	 * @var string
	 */
	private $front_page_layout;

	/**
	 * Choices for the front page layout.
	 *
	 * @var array
	 */
	private $front_page_layout_choices;

	/**
	 * Choices for the individual post layouts.
	 *
	 * @var array
	 */
	private $post_layout_choices;

	/**
	 * Holds the post types allowed to select their individual layout.
	 *
	 * @var array
	 */
	private $post_layout_post_types;

	/**
	 * Holds the layout choices that have a sidebar.
	 *
	 * @var array
	 */
	private $layout_choices_with_sidebar;

	/**
	 * Holds the layout for the current view.
	 *
	 * @var string
	 */
	private $current_layout;

	/**
	 * Determines if the primary sidebar
	 * has been declared for usage.
	 *
	 * @var bool
	 */
	private $has_primary_sidebar = false;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'sidebars';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {

		$this->layout_choices_with_sidebar = array( 'sidebar_right', 'sidebar_left' );

		add_action( 'customize_register', array( $this, 'action_customize_register_site_layout' ) );
		add_action( 'widgets_init', array( $this, 'action_register_sidebars' ) );
		add_filter( 'body_class', array( $this, 'filter_body_classes' ) );
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
			'get_site_layout'           => array( $this, 'get_site_layout' ),
			'get_front_page_layout'     => array( $this, 'get_front_page_layout' ),
			'manage_layout'             => array( $this, 'manage_layout' ),
			'declare_primary_sidebar'   => array( $this, 'declare_primary_sidebar' ),
			'has_primary_sidebar'       => array( $this, 'has_primary_sidebar' ),
			'is_primary_sidebar_active' => array( $this, 'is_primary_sidebar_active' ),
			'display_primary_sidebar'   => array( $this, 'display_primary_sidebar' ),
			'is_footer_sidebar_active'  => array( $this, 'is_footer_sidebar_active' ),
			'display_footer_sidebar'    => array( $this, 'display_footer_sidebar' ),
		);
	}

	/**
	 * Return array of site layout choices.
	 *
	 * @return array
	 */
	public function get_site_layout_choices() : array {
		if ( isset( $this->site_layout_choices ) ) {
			return $this->site_layout_choices;
		}
		$this->site_layout_choices = array(
			'sidebar_right' => __( 'Content, Primary sidebar', 'wp-rig' ),
			'sidebar_left'  => __( 'Primary Sidebar, Content', 'wp-rig' ),
			'sidebar_none'  => __( 'Full width content', 'wp-rig' ),
		);
		return $this->site_layout_choices;
	}

	/**
	 * Return array of front page layout choices.
	 *
	 * @return array
	 */
	public function get_front_page_layout_choices() : array {
		if ( isset( $this->front_page_layout_choices ) ) {
			return $this->front_page_layout_choices;
		}
		$this->front_page_layout_choices = array(
			'sidebar_right' => __( 'Content, Primary sidebar', 'wp-rig' ),
			'sidebar_left'  => __( 'Primary Sidebar, Content', 'wp-rig' ),
			'sidebar_none'  => __( 'Full width content', 'wp-rig' ),
			'site'          => __( 'Use site layout', 'wp-rig' ),
		);
		return $this->front_page_layout_choices;
	}

	/**
	 * Returns array of individual post layout choices.
	 *
	 * @return array
	 */
	public function get_post_layout_choices() : array {
		if ( isset( $this->post_layout_choices ) ) {
			return $this->post_layout_choices;
		}
		$this->post_layout_choices = array(
			'sidebar_right' => __( 'Content, Primary sidebar', 'wp-rig' ),
			'sidebar_left'  => __( 'Primary Sidebar, Content', 'wp-rig' ),
			'sidebar_none'  => __( 'Full width content', 'wp-rig' ),
			'site'          => __( 'Use site layout', 'wp-rig' ),
		);
		return $this->post_layout_choices;
	}

	/**
	 * Get the post types allowed for selecting a layout.
	 *
	 * @return array
	 */
	public function get_post_layout_post_types() : array {
		if ( isset( $this->post_layout_post_types ) ) {
			return $this->post_layout_post_types;
		}

		$this->post_layout_post_types = get_post_types(
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		return $this->post_layout_post_types;
	}

	/**
	 * Returns true if post type is allowed to use the post layout.
	 *
	 * @param string $post_type The post type.
	 * @return bool
	 */
	public function is_post_layout_post_type( string $post_type ) : bool {
		return in_array( $post_type, $this->get_post_layout_post_types() );
	}

	/**
	 * Returns true if layout is a choice with a sidebar.
	 *
	 * @param string $layout The layout identifer.
	 * @return bool
	 */
	private function layout_has_sidebar( string $layout ) : bool {
		return in_array( $layout, $this->layout_choices_with_sidebar );
	}

	/**
	 * Returns true if layout has a left sidebar.
	 *
	 * @param string $layout The layout.
	 * @return bool
	 */
	private function layout_has_sidebar_left( string $layout ) : bool {
		return ( 'sidebar_left' === $layout );
	}

	/**
	 * Returns string identifier for site layout.
	 *
	 * @return string
	 */
	public function get_site_layout() : string {
		if ( isset( $this->site_layout ) ) {
			return $this->site_layout;
		}
		$layout = get_theme_mod( self::SITE_LAYOUT_NAME );
		if ( ! array_key_exists( $layout, $this->get_site_layout_choices() ) ) {
			$layout = self::SITE_LAYOUT_DEFAULT_VALUE;
		}
		$this->site_layout = $layout;
		return $this->site_layout;
	}

	/**
	 * Returns string identifier for front page layout.
	 *
	 * @return string
	 */
	public function get_front_page_layout() : string {
		if ( isset( $this->front_page_layout ) ) {
			return $this->front_page_layout;
		}

		$layout = get_theme_mod( self::FRONT_PAGE_LAYOUT_NAME );
		if ( ! array_key_exists( $layout, $this->get_front_page_layout_choices() ) ) {
			$layout = self::FRONT_PAGE_LAYOUT_DEFAULT_VALUE;
		}

		// Get site layout setting.
		if ( self::FRONT_PAGE_LAYOUT_VALUE_USE_SITE == $layout ) {
			$layout = $this->get_site_layout();
		}

		$this->front_page_layout = $layout;
		return $this->front_page_layout;
	}

	/**
	 * Returns string identifier for the layout setting for a particular post.
	 *
	 * @param WP_Post $post the post object.
	 * @return string
	 */
	public function get_post_layout_setting( WP_Post $post ) : string {

		$layout = $post->__get( self::POST_LAYOUT_NAME );

		if ( ! array_key_exists( $layout, $this->get_post_layout_choices() ) ) {
			$layout = self::POST_LAYOUT_DEFAULT_VALUE;
		}

		return $layout;
	}

	/**
	 * Returns string identifier for the layout for a particular post.
	 *
	 * @param WP_Post $post the post object.
	 * @return string
	 */
	public function get_post_layout( WP_Post $post ) : string {

		$layout = $post->__get( self::POST_LAYOUT_NAME );

		if ( ! array_key_exists( $layout, $this->get_post_layout_choices() ) ) {
			$layout = self::POST_LAYOUT_DEFAULT_VALUE;
		}

		// Get site layout setting.
		if ( self::POST_LAYOUT_VALUE_USE_SITE == $layout ) {
			$layout = $this->get_site_layout();
		}

		return $layout;
	}

	/**
	 * Takes care of any actions needed to setup/manage the site layout.
	 */
	public function manage_layout() {
		if ( is_front_page() ) {
			$layout = $this->get_front_page_layout();
		} else if ( is_singular() ) {
			$layout = $this->get_post_layout( get_post() );
		} else {
			$layout = $this->get_site_layout();
		}

		$this->declare_layout( $layout );

		if ( $this->layout_has_sidebar( $layout ) ) {
			$this->declare_primary_sidebar();
		}
	}

	/**
	 * Declare the layout for the current view.
	 *
	 * @param string $layout The layout identifier.
	 */
	private function declare_layout( string $layout ) {
		$this->current_layout = $layout;
	}

	/**
	 * Returns the set layout for the current view.
	 *
	 * @return string
	 */
	public function get_layout() {
		return $this->current_layout;
	}

	/**
	 * Allows templates to declare they're
	 * going to use the primary sidebar so the
	 * rest of the layout can be notified.
	 */
	public function declare_primary_sidebar() {
		$this->has_primary_sidebar = true;
	}

	/**
	 * Will return true if the primary
	 * sidebar has been declared.
	 *
	 * @return bool True if the primary sidebar has been declared.
	 */
	public function has_primary_sidebar() {
		return apply_filters( 'wp_rig_has_primary_sidebar', $this->has_primary_sidebar );
	}

	/**
	 * Registers the sidebars.
	 */
	public function action_register_sidebars() {

		register_sidebar(
			array(
				'name'          => esc_html__( 'Primary', 'wp-rig' ),
				'id'            => static::PRIMARY_SIDEBAR_SLUG,
				'description'   => esc_html__( 'Add widgets here for the primary sidebar.', 'wp-rig' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer', 'wp-rig' ),
				'id'            => static::FOOTER_SIDEBAR_SLUG,
				'description'   => esc_html__( 'Add widgets for the footer area.', 'wp-rig' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}

	/**
	 * Adds custom classes to indicate whether a sidebar is present to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array Filtered body classes.
	 */
	public function filter_body_classes( array $classes ) : array {
		if ( $this->is_primary_sidebar_active() ) {

			$classes[] = 'has-sidebar';

			if ( $this->layout_has_sidebar_left( $this->get_layout() ) ) {
				$classes[] = 'has-sidebar--left';
			} else {
				$classes[] = 'has-sidebar--right';
			}
		}
		return $classes;
	}

	/**
	 * Checks whether the primary sidebar is active.
	 *
	 * @return bool True if the primary sidebar is active, false otherwise.
	 */
	public function is_primary_sidebar_active() : bool {
		return $this->has_primary_sidebar() && (bool) is_active_sidebar( static::PRIMARY_SIDEBAR_SLUG );
	}

	/**
	 * Checks whether the footer sidebar is active.
	 *
	 * @return bool True if the footer sidebar is active, false otherwise.
	 */
	public function is_footer_sidebar_active() : bool {
		return (bool) is_active_sidebar( static::FOOTER_SIDEBAR_SLUG );
	}

	/**
	 * Displays the primary sidebar.
	 */
	public function display_primary_sidebar() {
		dynamic_sidebar( static::PRIMARY_SIDEBAR_SLUG );
	}

	/**
	 * Displays the footer sidebar.
	 */
	public function display_footer_sidebar() {
		dynamic_sidebar( static::FOOTER_SIDEBAR_SLUG );
	}

	/**
	 * Adds a setting and control for setting the site layout.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register_site_layout( WP_Customize_Manager $wp_customize ) {

		$site_layout_choices = $this->get_site_layout_choices();
		$front_page_layout_choices = $this->get_front_page_layout_choices();

		$wp_customize->add_section(
			'site_layout',
			array(
				'title'    => __( 'Site Layout', 'wp-rig' ),
				'priority' => 50,
			)
		);

		$wp_customize->add_setting(
			self::SITE_LAYOUT_NAME,
			array(
				'default'    => self::SITE_LAYOUT_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $input ) use ( $site_layout_choices ) : string {
					if ( array_key_exists( $input, $site_layout_choices ) ) {
						return $input;
					}
					return '';
				},
			)
		);

		$wp_customize->add_control(
			self::SITE_LAYOUT_NAME,
			array(
				'label'   => __( 'Site layout', 'wp-rig' ),
				'section' => 'site_layout',
				'type'    => 'radio',
				'description' => __( 'Which layout do you want to use for your site?', 'wp-rig' ),
				'choices' => $site_layout_choices,
			)
		);

		$wp_customize->add_setting(
			self::FRONT_PAGE_LAYOUT_NAME,
			array(
				'default'    => self::FRONT_PAGE_LAYOUT_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $input ) use ( $front_page_layout_choices ) : string {
					if ( array_key_exists( $input, $front_page_layout_choices ) ) {
						return $input;
					}
					return '';
				},
			)
		);

		$wp_customize->add_control(
			self::FRONT_PAGE_LAYOUT_NAME,
			array(
				'label'   => __( 'Homepage layout', 'wp-rig' ),
				'section' => 'static_front_page',
				'type'    => 'radio',
				'description' => __( 'Which layout do you want to use on your homepage?', 'wp-rig' ),
				'choices' => $front_page_layout_choices,
			)
		);
	}
}
