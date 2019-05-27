<?php
/**
 * WP_Rig\WP_Rig\Magazine\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Magazine;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use WP_Query;
use WP_Customize_Manager;

/**
 * Class for managing Magazine support.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const FP_MAG_SECTION = 'front_page_magazine_settings';

	const FP_MAG_NAME = 'show_front_page_magazine';
	const FP_MAG_DEFAULT = false;

	const FP_MAG_POST_DATE = 'front_page_magazine_post_date';
	const FP_MAG_POST_DATE_DEFAULT_VALUE = true;

	/**
	 * Holds the index for the magazine layout.
	 *
	 * @var int
	 */
	private $index = 0;

	/**
	 * Is true if set to use magazine layout.
	 *
	 * @var bool
	 */
	private $use_magazine_layout;

	/**
	 * Holds the setting for magazine display post date.
	 *
	 * @var bool
	 */
	private $display_post_date;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'magazine';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		add_filter( 'body_class', array( $this, 'filter_body_classes' ) );
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
			'use_magazine_layout' => array( $this, 'use_magazine_layout' ),
			'display_magazine'    => array( $this, 'display_magazine' ),
			'magazine_display_post_date' => array( $this, 'magazine_display_post_date' ),
		);
	}

	/**
	 * Determines whether we want to display the magazine layout.
	 *
	 * @return bool Whether we want to display the magazine layout.
	 */
	public function use_magazine_layout() : bool {
		if ( isset( $this->use_magazine_layout ) ) {
			return $this->use_magazine_layout;
		}
		$this->use_magazine_layout = (bool) get_theme_mod( self::FP_MAG_NAME, self::FP_MAG_DEFAULT );
		return $this->use_magazine_layout;
	}

	/**
	 * Registers the image size for the magazine layout.
	 */
	public function add_image_sizes() {
		add_image_size( 'wp-rig-magazine', 600, 600, true );
	}

	/**
	 * Returns true if magazine display is set to show the post date.
	 *
	 * @return bool
	 */
	public function magazine_display_post_date() : bool {
		if ( isset( $this->display_post_date ) ) {
			return $this->display_post_date;
		}

		$this->display_post_date = (bool) get_theme_mod( self::FP_MAG_POST_DATE, self::FP_MAG_POST_DATE_DEFAULT_VALUE );

		return $this->display_post_date;
	}

	/**
	 * Adds a 'has-magazine' class to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array Filtered body classes.
	 */
	public function filter_body_classes( array $classes ) : array {
		if ( $this->use_magazine_layout() ) {
			$classes[] = 'has-magazine';
		}
		return $classes;
	}

	/**
	 * Adds a setting and control for using the magazine layout.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_section(
			self::FP_MAG_SECTION,
			array(
				'title'    => __( '"Magazine" Settings', 'wp-rig' ),
				'priority' => 121,
			)
		);

		$wp_customize->add_setting(
			self::FP_MAG_NAME,
			array(
				'default'    => self::FP_MAG_DEFAULT,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FP_MAG_NAME,
			array(
				'label'   => __( 'Display "Magazine" on homepage', 'wp-rig' ),
				'section' => self::FP_MAG_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'Do you want to display the "Magazine" layout of recent posts on your homepage?', 'wp-rig' ),
			)
		);

		$wp_customize->add_setting(
			self::FP_MAG_POST_DATE,
			array(
				'default'    => self::FP_MAG_POST_DATE_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FP_MAG_POST_DATE,
			array(
				'label'   => __( 'Display post date', 'wp-rig' ),
				'section' => self::FP_MAG_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'Do you want to the show the post date in the magazine layout?', 'wp-rig' ),
			)
		);
	}

	/**
	 * Prints the markup for the magazine.
	 *
	 * @TODO:
	 *  - Don't run if magazine isnt enabled
	 *  - Setup magazine query
	 *
	 * @return  bool - true if magazine is printed.
	 */
	public function display_magazine() {

		if ( ! $this->use_magazine_layout() ) {
			return false;
		}

		$magazines = new WP_Query(
			array(
				'post_type' => 'post',
				'posts_per_page' => 4,
			)
		);

		if ( ! $magazines->have_posts() ) {
			return;
		}

		$this->index = 0;

		?>
		<aside class="site-magazine" aria-label="<?php esc_attr_e( 'Featured posts', 'wp-rig' ); ?>">
			<?php

			while ( $magazines->have_posts() ) {
				$magazines->the_post();

				get_template_part( 'template-parts/content/entry', 'magazine' );

				$this->index++;
			}

			?>
		</aside>
		<?php

		wp_reset_postdata();

		return true;
	}
}
