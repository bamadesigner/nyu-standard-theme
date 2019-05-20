<?php
/**
 * WP_Rig\WP_Rig\Layout\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Layout;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use function add_action;
use function add_filter;
use WP_Post;
use WP_Customize_Manager;

/**
 * Class for managing the site layout.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const SITE_LAYOUT_SECTION = 'site_layout';

	const SITE_LAYOUT_NAME = 'site_layout';
	const SITE_LAYOUT_DEFAULT_VALUE = 'sidebar_none';

	const FRONT_PAGE_SECTION = 'static_front_page';

	const FRONT_PAGE_LAYOUT_NAME = 'front_page_layout';
	const FRONT_PAGE_LAYOUT_DEFAULT_VALUE = 'site';
	const FRONT_PAGE_LAYOUT_VALUE_USE_SITE = 'site';

	const POST_LAYOUT_NAME = 'post_layout';
	const POST_LAYOUT_DEFAULT_VALUE = 'site';
	const POST_LAYOUT_VALUE_USE_SITE = 'site';

	const POST_LAYOUT_MB_NAME = 'wp_rig_post_layout';
	const POST_LAYOUT_MB_NONCE = 'custom_nonce';
	const POST_LAYOUT_MB_NONCE_ACTION = 'custom_nonce_action';

	const ARCHIVE_SECTION = 'archive_settings';

	const ARCHIVE_DISPLAY_NAME = 'archive_display';
	const ARCHIVE_DISPLAY_DEFAULT_VALUE = 'excerpt';

	const FRONT_PAGE_ARCHIVE_POST_DATE = 'front_page_archive_post_date';
	const FRONT_PAGE_ARCHIVE_POST_DATE_DEFAULT_VALUE = true;

	const FRONT_PAGE_ARCHIVE_POST_AUTHOR = 'front_page_archive_post_author';
	const FRONT_PAGE_ARCHIVE_POST_AUTHOR_DEFAULT_VALUE = true;

	const FRONT_PAGE_ARCHIVE_DISPLAY_NAME = 'front_page_archive_display';
	const FRONT_PAGE_ARCHIVE_DISPLAY_DEFAULT_VALUE = 'site';
	const FRONT_PAGE_ARCHIVE_DISPLAY_VALUE_USE_SITE = 'site';

	const ARCHIVE_THUMB_NAME = 'archive_thumb';
	const ARCHIVE_THUMB_DEFAULT_VALUE = true;

	const FRONT_PAGE_ARCHIVE_THUMB_NAME = 'front_page_archive_thumb';
	const FRONT_PAGE_ARCHIVE_THUMB_DEFAULT_VALUE = true;

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
	 * Holds the selected identifier for the archive display.
	 *
	 * @var string
	 */
	private $archive_display;

	/**
	 * Choices for the archive display.
	 *
	 * @var array
	 */
	private $archive_display_choices;

	/**
	 * Holds the selected identifier for the front page archive display.
	 *
	 * @var string
	 */
	private $front_page_archive_display;

	/**
	 * Choices for the front page archive display.
	 *
	 * @var array
	 */
	private $front_page_archive_display_choices;

	/**
	 * Holds the setting for archive display thumbnail.
	 *
	 * @var bool
	 */
	private $archive_display_thumb;

	/**
	 * Holds the setting for front page archive display thumbnail.
	 *
	 * @var bool
	 */
	private $front_page_archive_display_thumb;

	/**
	 * Holds the setting for front page archive display post author.
	 *
	 * @var bool
	 */
	private $front_page_archive_display_post_author;

	/**
	 * Holds the setting for front page archive display post date.
	 *
	 * @var bool
	 */
	private $front_page_archive_display_post_date;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'layout';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {

		add_filter( 'body_class', array( $this, 'filter_body_classes' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_meta_box_theme_options' ), 10, 2 );

		add_action( 'customize_register', array( $this, 'action_customize_register' ) );

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
			'manage_layout'           => array( $this, 'manage_layout' ),
			'archive_display_excerpt' => array( $this, 'archive_display_excerpt' ),
			'front_page_archive_display_excerpt' => array( $this, 'front_page_archive_display_excerpt' ),
			'archive_display_thumbnail' => array( $this, 'archive_display_thumbnail' ),
			'front_page_archive_display_thumbnail' => array( $this, 'front_page_archive_display_thumbnail' ),
			'front_page_archive_display_post_author' => array( $this, 'front_page_archive_display_post_author' ),
			'front_page_archive_display_post_date' => array( $this, 'front_page_archive_display_post_date' ),
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
	 * Return array of archive display choices.
	 *
	 * @return array
	 */
	public function get_archive_display_choices() : array {
		if ( isset( $this->archive_display_choices ) ) {
			return $this->archive_display_choices;
		}
		$this->archive_display_choices = array(
			'excerpt' => __( 'Excerpt', 'wp-rig' ),
			'full'    => __( 'Full text', 'wp-rig' ),
		);

		return $this->archive_display_choices;
	}

	/**
	 * Return array of front page archive display choices.
	 *
	 * @return array
	 */
	public function get_front_page_archive_display_choices() : array {
		if ( isset( $this->front_page_archive_display_choices ) ) {
			return $this->front_page_archive_display_choices;
		}
		$this->front_page_archive_display_choices = array(
			'excerpt' => __( 'Excerpt', 'wp-rig' ),
			'full'    => __( 'Full text', 'wp-rig' ),
			'site'    => __( 'Use site archive setting', 'wp-rig' ),
		);

		return $this->front_page_archive_display_choices;
	}

	/**
	 * Returns true if post type is allowed to use the post layout.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	public function is_post_layout_post_type( string $post_type ) : bool {
		return in_array( $post_type, $this->get_post_layout_post_types() );
	}

	/**
	 * Returns list of layout choices that have a sidebar.
	 *
	 * @return array
	 */
	private function get_layout_choices_with_sidebar() : array {
		if ( isset( $this->layout_choices_with_sidebar ) ) {
			return $this->layout_choices_with_sidebar;
		}

		$this->layout_choices_with_sidebar = array( 'sidebar_right', 'sidebar_left' );

		return $this->layout_choices_with_sidebar;
	}

	/**
	 * Returns true if layout is a choice with a sidebar.
	 *
	 * @param string $layout The layout identifer.
	 *
	 * @return bool
	 */
	private function layout_has_sidebar( string $layout ) : bool {
		return in_array( $layout, $this->get_layout_choices_with_sidebar() );
	}

	/**
	 * Returns true if layout has a left sidebar.
	 *
	 * @param string $layout The layout.
	 *
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

		$layout = get_theme_mod( self::SITE_LAYOUT_NAME, self::SITE_LAYOUT_DEFAULT_VALUE );

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

		$layout = get_theme_mod( self::FRONT_PAGE_LAYOUT_NAME, self::FRONT_PAGE_LAYOUT_DEFAULT_VALUE );

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
	 *
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
	 *
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
			wp_rig()->declare_primary_sidebar();
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
	 * Returns string identifier for site layout.
	 *
	 * @return string
	 */
	public function get_archive_display() : string {
		if ( isset( $this->archive_display ) ) {
			return $this->archive_display;
		}
		$display = get_theme_mod( self::ARCHIVE_DISPLAY_NAME, self::ARCHIVE_DISPLAY_DEFAULT_VALUE );

		if ( ! array_key_exists( $display, $this->get_archive_display_choices() ) ) {
			$display = self::ARCHIVE_DISPLAY_DEFAULT_VALUE;
		}

		$this->archive_display = $display;

		return $this->archive_display;
	}

	/**
	 * Returns string identifier for site layout.
	 *
	 * @return string
	 */
	public function get_front_page_archive_display() : string {
		if ( isset( $this->front_page_archive_display ) ) {
			return $this->front_page_archive_display;
		}

		$display = get_theme_mod( self::FRONT_PAGE_ARCHIVE_DISPLAY_NAME, self::FRONT_PAGE_ARCHIVE_DISPLAY_DEFAULT_VALUE );

		if ( ! array_key_exists( $display, $this->get_front_page_archive_display_choices() ) ) {
			$display = self::FRONT_PAGE_ARCHIVE_DISPLAY_DEFAULT_VALUE;
		}

		// Get archive display setting.
		if ( self::FRONT_PAGE_ARCHIVE_DISPLAY_VALUE_USE_SITE == $display ) {
			$display = $this->get_archive_display();
		}

		$this->front_page_archive_display = $display;

		return $this->front_page_archive_display;
	}

	/**
	 * Returns true if archive display is set to "excerpt".
	 *
	 * @return bool
	 */
	public function archive_display_excerpt() : bool {
		return 'excerpt' === $this->get_archive_display();
	}

	/**
	 * Returns true if front page archive display is set to "excerpt".
	 *
	 * @return bool
	 */
	public function front_page_archive_display_excerpt() : bool {
		return 'excerpt' === $this->get_front_page_archive_display();
	}

	/**
	 * Returns true if archive display is set to show the thumbnail.
	 *
	 * @return bool
	 */
	public function archive_display_thumbnail() : bool {
		if ( isset( $this->archive_display_thumb ) ) {
			return $this->archive_display_thumb;
		}

		$this->archive_display_thumb = (bool) get_theme_mod( self::ARCHIVE_THUMB_NAME, self::ARCHIVE_THUMB_DEFAULT_VALUE );

		return $this->archive_display_thumb;
	}

	/**
	 * Returns true if front page archive display is set to show the thumbnail.
	 *
	 * @return bool
	 */
	public function front_page_archive_display_thumbnail() : bool {
		if ( isset( $this->front_page_archive_display_thumb ) ) {
			return $this->front_page_archive_display_thumb;
		}

		$this->front_page_archive_display_thumb = (bool) get_theme_mod( self::FRONT_PAGE_ARCHIVE_THUMB_NAME, self::FRONT_PAGE_ARCHIVE_THUMB_DEFAULT_VALUE );

		return $this->front_page_archive_display_thumb;
	}

	/**
	 * Returns true if front page archive display is set to show the post author.
	 *
	 * @return bool
	 */
	public function front_page_archive_display_post_author() : bool {
		if ( isset( $this->front_page_archive_display_post_author ) ) {
			return $this->front_page_archive_display_post_author;
		}

		$this->front_page_archive_display_post_author = (bool) get_theme_mod( self::FRONT_PAGE_ARCHIVE_POST_AUTHOR, self::FRONT_PAGE_ARCHIVE_POST_AUTHOR_DEFAULT_VALUE );

		return $this->front_page_archive_display_post_author;
	}

	/**
	 * Returns true if front page archive display is set to show the post date.
	 *
	 * @return bool
	 */
	public function front_page_archive_display_post_date() : bool {
		if ( isset( $this->front_page_archive_display_post_date ) ) {
			return $this->front_page_archive_display_post_date;
		}

		$this->front_page_archive_display_post_date = (bool) get_theme_mod( self::FRONT_PAGE_ARCHIVE_POST_DATE, self::FRONT_PAGE_ARCHIVE_POST_DATE_DEFAULT_VALUE );

		return $this->front_page_archive_display_post_date;
	}

	/**
	 * Adds custom classes to indicate whether a sidebar is present to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array Filtered body classes.
	 */
	public function filter_body_classes( array $classes ) : array {
		if ( wp_rig()->is_primary_sidebar_active() ) {

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
	 * Adds settings and controls for the customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {

		$site_layout_choices       = $this->get_site_layout_choices();
		$front_page_layout_choices = $this->get_front_page_layout_choices();

		$wp_customize->add_section(
			self::SITE_LAYOUT_SECTION,
			array(
				'title'    => __( 'Site Layout', 'wp-rig' ),
				'priority' => 50,
			)
		);

		$wp_customize->add_setting(
			self::SITE_LAYOUT_NAME,
			array(
				'default'           => self::SITE_LAYOUT_DEFAULT_VALUE,
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
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
				'label'       => __( 'Site layout', 'wp-rig' ),
				'section'     => self::SITE_LAYOUT_SECTION,
				'type'        => 'radio',
				'description' => __( 'Which layout do you want to use for your site?', 'wp-rig' ),
				'choices'     => $site_layout_choices,
			)
		);

		$archive_display_choices = $this->get_archive_display_choices();

		$wp_customize->add_section(
			self::ARCHIVE_SECTION,
			array(
				'title'    => __( 'Archive Settings', 'wp-rig' ),
				'priority' => 110,
			)
		);

		$wp_customize->add_setting(
			self::ARCHIVE_DISPLAY_NAME,
			array(
				'default'           => self::ARCHIVE_DISPLAY_DEFAULT_VALUE,
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
				'sanitize_callback' => function ( $input ) use ( $archive_display_choices ) : string {
					if ( array_key_exists( $input, $archive_display_choices ) ) {
						return $input;
					}

					return '';
				},
			)
		);

		$wp_customize->add_control(
			self::ARCHIVE_DISPLAY_NAME,
			array(
				'label'       => __( 'Archive display', 'wp-rig' ),
				'section'     => self::ARCHIVE_SECTION,
				'type'        => 'radio',
				'description' => __( 'How do you want to display content in your post archives?', 'wp-rig' ),
				'choices'     => $archive_display_choices,
			)
		);

		$wp_customize->add_setting(
			self::ARCHIVE_THUMB_NAME,
			array(
				'default'    => self::ARCHIVE_THUMB_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::ARCHIVE_THUMB_NAME,
			array(
				'label'   => __( 'Display featured image thumbnail', 'wp-rig' ),
				'section' => self::ARCHIVE_SECTION,
				'type'    => 'checkbox',
				'description' => __( "If archive display is set to \"Excerpt\", do you want to display the post's featured image as a thumbnail in your post archives?", 'wp-rig' ),
			)
		);

		$wp_customize->add_setting(
			self::FRONT_PAGE_LAYOUT_NAME,
			array(
				'default'           => self::FRONT_PAGE_LAYOUT_DEFAULT_VALUE,
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
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
				'label'       => __( 'Homepage layout', 'wp-rig' ),
				'section'     => self::FRONT_PAGE_SECTION,
				'type'        => 'radio',
				'description' => __( 'Which layout do you want to use on your homepage?', 'wp-rig' ),
				'choices'     => $front_page_layout_choices,
			)
		);

		$front_page_archive_display_choices = $this->get_front_page_archive_display_choices();

		$wp_customize->add_setting(
			self::FRONT_PAGE_ARCHIVE_DISPLAY_NAME,
			array(
				'default'           => self::FRONT_PAGE_ARCHIVE_DISPLAY_DEFAULT_VALUE,
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
				'sanitize_callback' => function ( $input ) use ( $front_page_archive_display_choices ) : string {
					if ( array_key_exists( $input, $front_page_archive_display_choices ) ) {
						return $input;
					}

					return '';
				},
			)
		);

		$wp_customize->add_control(
			self::FRONT_PAGE_ARCHIVE_DISPLAY_NAME,
			array(
				'label'       => __( 'Archive display', 'wp-rig' ),
				'section'     => self::FRONT_PAGE_SECTION,
				'type'        => 'radio',
				'description' => __( 'If your homepage display is set to "Your latest posts", how do you want to display the content?', 'wp-rig' ),
				'choices'     => $front_page_archive_display_choices,
			)
		);

		$wp_customize->add_setting(
			self::FRONT_PAGE_ARCHIVE_THUMB_NAME,
			array(
				'default'    => self::FRONT_PAGE_ARCHIVE_THUMB_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FRONT_PAGE_ARCHIVE_THUMB_NAME,
			array(
				'label'   => __( 'Display featured image thumbnail', 'wp-rig' ),
				'section' => self::FRONT_PAGE_SECTION,
				'type'    => 'checkbox',
				'description' => __( "If your homepage display is set to \"Your latest posts\", and your archive display is set to \"Excerpt\", do you want to display the post's featured image as a thumbnail in your homepage recent posts?", 'wp-rig' ),
			)
		);

		$wp_customize->add_setting(
			self::FRONT_PAGE_ARCHIVE_POST_AUTHOR,
			array(
				'default'    => self::FRONT_PAGE_ARCHIVE_POST_AUTHOR_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FRONT_PAGE_ARCHIVE_POST_AUTHOR,
			array(
				'label'   => __( 'Display post author', 'wp-rig' ),
				'section' => self::FRONT_PAGE_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'If your homepage display is set to "Your latest posts", do you want to the show post author?', 'wp-rig' ),
			)
		);

		$wp_customize->add_setting(
			self::FRONT_PAGE_ARCHIVE_POST_DATE,
			array(
				'default'    => self::FRONT_PAGE_ARCHIVE_POST_DATE_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FRONT_PAGE_ARCHIVE_POST_DATE,
			array(
				'label'   => __( 'Display post date', 'wp-rig' ),
				'section' => self::FRONT_PAGE_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'If your homepage display is set to "Your latest posts", do you want to the show post date?', 'wp-rig' ),
			)
		);
	}

	/**
	 * Adds the meta boxes for our theme options.
	 *
	 * @param string  $post_type The post type.
	 * @param WP_Post $post The post object.
	 */
	public function add_meta_boxes( string $post_type, WP_Post $post ) {

		if ( ! $this->is_post_layout_post_type( $post_type ) ) {
			return;
		}

		// Make sure we have layout choices.
		$post_layout_choices = $this->get_post_layout_choices();
		if ( empty( $post_layout_choices ) ) {
			return;
		}

		add_meta_box(
			'wp-rig-layout',
			__( 'Theme Options', 'wp-rig' ),
			array(
				$this,
				'print_meta_box_theme_options',
			),
			$post_type,
			'side',
			'default',
			array(
				'post_layout_choices' => $post_layout_choices,
			)
		);
	}

	/**
	 * Prints the meta box for our theme options.
	 *
	 * @param WP_Post $post The post object.
	 * @param array   $metabox The meta box information.
	 */
	public function print_meta_box_theme_options( WP_Post $post, array $metabox ) {
		if ( ! empty( $metabox['args'] ) ) {
			$args = $metabox['args'];
		} else {
			$args = array();
		}

		if ( ! empty( $args['post_layout_choices'] ) ) {
			$post_layout_choices = $args['post_layout_choices'];
		} else {
			$post_layout_choices = array();
		}

		$selected_layout = $this->get_post_layout_setting( $post );

		// Ensures security when we save the post meta.
		wp_nonce_field( self::POST_LAYOUT_MB_NONCE_ACTION, self::POST_LAYOUT_MB_NONCE );

		?>
		<label for="wp-rig-post-layout" class="components-base-control__label" style="display:block;width:100%;margin-bottom:5px;"><?php esc_html_e( 'Layout for this page?', 'wp-rig' ); ?></label>
		<select id="wp-rig-post-layout" name="<?php echo esc_attr( self::POST_LAYOUT_MB_NAME ); ?>" class="components-select-control__input" style="width:100%;">
			<?php

			foreach ( $post_layout_choices as $value => $label ) :
				?>
				<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $selected_layout == $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php
			endforeach;

			?>
		</select>
		<?php
	}

	/**
	 * Manages saving the theme options meta box.
	 *
	 * @TODO:
	 * - Check if user has permissions to save data?
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post The post object.
	 */
	public function save_meta_box_theme_options( $post_id, $post ) {

		if ( empty( $_POST[ self::POST_LAYOUT_MB_NONCE ] ) ) {
			return;
		}

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::POST_LAYOUT_MB_NONCE ] ) ), self::POST_LAYOUT_MB_NONCE_ACTION ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( empty( $_POST[ self::POST_LAYOUT_MB_NAME ] ) ) {
			$layout = null;
		} else {
			$layout = sanitize_text_field( wp_unslash( $_POST[ self::POST_LAYOUT_MB_NAME ] ) );
		}

		if ( ! array_key_exists( $layout, $this->get_post_layout_choices() ) ) {
			$layout = self::POST_LAYOUT_DEFAULT_VALUE;
		}

		update_post_meta( $post_id, self::POST_LAYOUT_NAME, $layout );

	}
}
