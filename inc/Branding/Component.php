<?php
/**
 * WP_Rig\WP_Rig\Branding\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Branding;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_action;
use WP_Customize_Manager;

/**
 * Class for managing the site branding.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const TITLE_TAGLINE_SECTION = 'title_tagline';

	const DISPLAY_TAGLINE_HEADER_NAME = 'header_display_tagline';
	const DISPLAY_TAGLINE_HEADER_DEFAULT_VALUE = true;

	/**
	 * Holds the site description/tagline.
	 *
	 * @var string
	 */
	private $site_description;

	/**
	 * Holds the setting for displaying
	 * the tagline in the header.
	 *
	 * @var bool
	 */
	private $display_tagline_header;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'branding';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {

		add_action( 'customize_register', [ $this, 'action_customize_register' ] );

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
			'get_site_description'   => [ $this, 'get_site_description' ],
			'display_tagline_header' => [ $this, 'display_tagline_header' ],
		];
	}

	/**
	 * Returns the site's description/tagline.
	 *
	 * @return string
	 */
	public function get_site_description() {
		if ( isset( $this->site_description ) ) {
			return $this->site_description;
		}

		$this->site_description = get_bloginfo( 'description' );

		return $this->site_description;
	}

	/**
	 * Returns true if archive display is set to show the thumbnail.
	 *
	 * @return bool
	 */
	public function display_tagline_header() : bool {
		if ( isset( $this->display_tagline_header ) ) {
			return $this->display_tagline_header;
		}

		$this->display_tagline_header = (bool) get_theme_mod( self::DISPLAY_TAGLINE_HEADER_NAME, self::DISPLAY_TAGLINE_HEADER_DEFAULT_VALUE );

		return $this->display_tagline_header;
	}

	/**
	 * Adds settings and controls for the customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_setting(
			self::DISPLAY_TAGLINE_HEADER_NAME,
			[
				'default'           => self::DISPLAY_TAGLINE_HEADER_DEFAULT_VALUE,
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			]
		);

		$wp_customize->add_control(
			self::DISPLAY_TAGLINE_HEADER_NAME,
			[
				'label'       => __( 'Display tagline in header', 'wp-rig' ),
				'section'     => self::TITLE_TAGLINE_SECTION,
				'type'        => 'checkbox',
				'description' => __( 'If checked, will display the tagline below the site title in the header.', 'wp-rig' ),
			]
		);

	}
}
