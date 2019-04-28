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

	private $index = 0;

	const FPL_NAME = 'front_page_layout';
	const FPL_MAG_VALUE = 'magazine';
	const FPL_DEFAULT_VALUE = 'default';

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
		add_action( 'customize_register', array( $this, 'action_customize_register_magazine' ) );
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
		);
	}

	/**
	 * Determines whether we want to display the magazine layout.
	 *
	 * @return bool Whether we want to display the magazine layout.
	 */
	public function use_magazine_layout() : bool {
		return ( self::FPL_MAG_VALUE === get_theme_mod( self::FPL_NAME ) );
	}

	/**
	 * Adds a setting and control for using the magazine layout.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register_magazine( WP_Customize_Manager $wp_customize ) {

		$layout_choices = array(
			self::FPL_MAG_VALUE => __( 'Magazine layout', 'wp-rig' ),
			self::FPL_DEFAULT_VALUE  => __( 'Default layout', 'wp-rig' ),
		);

		$wp_customize->add_setting(
			self::FPL_NAME,
			array(
				'default'    => self::FPL_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $input ) use ( $layout_choices ) : string {
					if ( array_key_exists( $input, $layout_choices ) ) {
						return $input;
					}
					return '';
				},
			)
		);

		$wp_customize->add_control(
			self::FPL_NAME,
			array(
				'label'   => __( 'Homepage layout', 'wp-rig' ),
				'section' => 'static_front_page',
				'type'    => 'radio',
				'description' => __( 'Which layout do you want to use on the home page to display your posts?', 'wp-rig' ),
				'choices' => $layout_choices,
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
	 * @return  void
	 */
	public function display_magazine() {

		$magazines = new WP_Query( array(
			'post_type' => 'post',
			'posts_per_page' => 4,
		));

		if ( ! $magazines->have_posts() ) {
			return;
		}

		$this->index = 0;

		?>
		<section class="site-magazine">
			<?php

			while ( $magazines->have_posts() ) {
				$magazines->the_post();

				get_template_part( 'template-parts/content/entry', 'magazine' );

				$this->index++;
			}

			?>
		</section>
		<?php

		wp_reset_postdata();

	}
}
